<?php

use App\Jobs\Kijiji\ScanFiltersJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Kijiji Car Tracking Scheduler
|--------------------------------------------------------------------------
| Scans active filters hourly. Jobs run on kijiji queue (single worker).
*/
Schedule::job(new ScanFiltersJob, 'kijiji')->hourly();
