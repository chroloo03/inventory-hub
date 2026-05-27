<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
| This is where scheduled commands are registered. The scheduler runs
| every minute via a cron job or Windows Task Scheduler.
|
| To test locally run: php artisan schedule:work
*/

// Send low stock alerts every day at 8:00 AM.
// Only alerts for items not yet notified (smart deduplication).
Schedule::command('inventory:send-low-stock-alerts')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        logger('Low stock alert check completed successfully.');
    })
    ->onFailure(function () {
        logger('Low stock alert check FAILED.');
    });
