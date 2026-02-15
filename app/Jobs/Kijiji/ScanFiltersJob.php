<?php

declare(strict_types=1);

namespace App\Jobs\Kijiji;

use App\Models\KijijiFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches ScanSingleFilterJob for each active filter.
 * No parallel execution - jobs are queued sequentially.
 */
class ScanFiltersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public function __construct()
    {
        $this->onQueue('kijiji');
    }

    public function handle(): void
    {
        try {
            $filters = KijijiFilter::active()->get();

            if ($filters->isEmpty()) {
                Log::channel('kijiji')->info('No active filters to scan.');

                return;
            }

            foreach ($filters as $filter) {
                ScanSingleFilterJob::dispatch($filter);
            }

            Log::channel('kijiji')->info('Dispatched filter scan jobs', [
                'count' => $filters->count(),
            ]);

            CheckTrackedListingsJob::dispatch();
        } catch (\Throwable $e) {
            Log::channel('kijiji')->error('ScanFiltersJob failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
