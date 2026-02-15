<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Kijiji\ScanFiltersJob;
use Illuminate\Console\Command;

class KijijiScanCommand extends Command
{
    protected $signature = 'kijiji:scan';

    protected $description = 'Manually trigger Kijiji filter scan (dispatches to kijiji queue)';

    public function handle(): int
    {
        ScanFiltersJob::dispatch();

        $this->info('ScanFiltersJob dispatched to kijiji queue.');

        return self::SUCCESS;
    }
}
