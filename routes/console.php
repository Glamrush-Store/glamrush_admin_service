<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('dashboard:aggregate-analytics')
    ->cron(config('dashboard.aggregation.schedule', '*/30 * * * *'))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('cache-metrics:aggregate')
    ->cron(config('cache_metrics.aggregation_schedule', env('CACHE_METRICS_AGGREGATION_SCHEDULE', '*/5 * * * *')))
    ->withoutOverlapping()
    ->onOneServer();
