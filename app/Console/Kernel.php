<?php

namespace App\Console;

use Illuminate\Support\Facades\Log;
use App\Console\Commands\TestSchedule;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        TestSchedule::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('test:schedule')
        ->everyMinute()
        ->withoutOverlapping();

        // $schedule->command('test:schedule')->everyMinute();
        // $schedule->command('your:command')->daily();
        // $schedule->call(function () {
        //     \Log::info('Scheduler is working');
        // })->everyMinute();

    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
