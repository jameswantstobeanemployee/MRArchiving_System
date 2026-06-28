<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Mark overdue checkouts every day at midnight
        $schedule->command('checkouts:mark-overdue')->dailyAt('00:05');

        // Run scheduled backups every 15 minutes (checks internally if due)
        $schedule->command('backups:run-scheduled')->everyFifteenMinutes();

        // Send retention alerts daily at 8 AM
        $schedule->command('retention:send-alerts')->dailyAt('08:00');

        // Purge hard-deleted destroyed charts daily at 2 AM
        $schedule->command('charts:purge-destroyed')->dailyAt('02:00');

        // Send daily digest notifications at 8 AM
        $schedule->command('notifications:daily-digest')->dailyAt('08:05');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
