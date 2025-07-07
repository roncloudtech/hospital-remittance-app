<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('remittance:generate-monthly')->monthlyOn(1,'01:15');
        $schedule->command('remittance:notify-due-remitters')->dailyAt('10:00');
        $schedule->command('remittance:mail-monthly-summary')->monthlyOn(1, '09:00');
        $schedule->call(function () {
            Log::info('✅ Laravel 12 scheduler works at ' . now());
        })->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\MailMonthlyTargetSummary::class,
        \App\Console\Commands\NotifyRemitterDueBalance::class,
    ];
}