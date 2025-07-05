<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('remittance:generate-monthly')->everyMinute();
        // $schedule->command('mail:remitter-balances')->everyMinute();
        // $schedule->command('mail:remitter-balances')->monthlyOn(1, '09:00');
        $schedule->command('reminders:send')
        ->monthlyOn(1, '09:00') // Runs on the 1st of every month at 9:00 AM
        ->timezone('Africa/Lagos');
        $schedule->call(function () {
            Log::info('✅ Laravel 12 scheduler works at ' . now());
        })->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
