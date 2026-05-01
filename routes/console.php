<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Schedule
|--------------------------------------------------------------------------
| Run: php artisan schedule:run  (manually)
| Or add to server cron: * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
*/

Schedule::command('inventory:check-low-stock')
    ->everyMinute()          // Every morning at 8 AM
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/low-stock.log'));
