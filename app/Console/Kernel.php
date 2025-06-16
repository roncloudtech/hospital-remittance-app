<?php

namespace App\Console;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // Register your custom commands here
    ];

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('your:command')->daily();
        $schedule->call(function () {
            \Log::info('Scheduler is working');
        })->everyMinute();

    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
