<?php

declare(strict_types=1);

namespace App\Jobs\Kijiji;

use App\Models\KijijiListing;
use App\Models\KijijiFilter;
use App\Services\Kijiji\KijijiScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Scans a single filter, creates/updates listings, dispatches PriceDropJob when applicable.
 */
class ScanSingleFilterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public function __construct(
        public KijijiFilter $filter
    ) {
        $this->onQueue('kijiji');
    }

    public function handle(KijijiScraperService $scraper): void
    {
        try {
            $url = $scraper->buildUrlFromFilter($this->filter);
            $html = $scraper->fetchHtml($url);

            if (! $html) {
                Log::channel('kijiji')->warning('No HTML fetched for filter', [
                    'filter_id' => $this->filter->id,
                    'url' => $url,
                ]);

                return;
            }

            $listings = $scraper->parseListings($html);

            foreach ($listings as $data) {
                try {
                    $this->processListing($data);
                } catch (\Throwable $e) {
                    Log::channel('kijiji')->warning('Single listing processing failed', [
                        'external_id' => $data['external_id'] ?? 'unknown',
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::channel('kijiji')->error('ScanSingleFilterJob failed', [
                'filter_id' => $this->filter->id,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function processListing(array $data): void
    {
        if (empty($data['external_id']) || empty($data['url']) || empty($data['title'])) {
            return;
        }

        $price = (int) ($data['price'] ?? 0);
        if ($price <= 0) {
            return;
        }

        DB::transaction(function () use ($data, $price) {
            $listing = KijijiListing::with('trackedListing')->firstOrNew(['external_id' => $data['external_id']]);

            $oldPrice = $listing->exists ? $listing->price : null;
            $tracked = $listing->trackedListing;
            $isTracked = $tracked !== null && $tracked->is_tracking;

            $listing->fill([
                'title' => $data['title'],
                'price' => $price,
                'url' => $data['url'],
                'year' => $data['year'] ?? null,
                'mileage' => $data['mileage'] ?? null,
                'location' => $data['location'] ?? null,
                'status' => 'active',
                'last_checked_at' => now(),
            ]);
            $listing->save();

            if ($isTracked && $tracked && $tracked->notify_on_price_drop && $oldPrice !== null && $price < $oldPrice) {
                PriceDropNotificationJob::dispatch($listing, (int) $oldPrice, $price);
                $tracked->update(['last_notified_price' => $price]);
            }
        });
    }
}
