<?php

declare(strict_types=1);

namespace App\Jobs\Kijiji;

use App\Models\TrackedListing;
use App\Services\Kijiji\KijijiScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Checks tracked listings for sold status. Dispatches SoldNotificationJob when sold.
 */
class CheckTrackedListingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 300;

    public function __construct()
    {
        $this->onQueue('kijiji');
    }

    public function handle(KijijiScraperService $scraper): void
    {
        try {
            $tracked = TrackedListing::tracking()
                ->with('kijijiListing')
                ->get();

            foreach ($tracked as $record) {
                try {
                    $listing = $record->kijijiListing;
                    if (! $listing || $listing->status === 'sold') {
                        continue;
                    }

                    $result = $scraper->fetchListingPage($listing->url);
                    $status = $result['status'];

                    if ($status === 'sold') {
                        $listing->update(['status' => 'sold', 'last_checked_at' => now()]);

                        if ($record->notify_on_sold) {
                            SoldNotificationJob::dispatch($listing);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::channel('kijiji')->warning('Check single tracked listing failed', [
                        'tracked_id' => $record->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::channel('kijiji')->error('CheckTrackedListingsJob failed', [
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
