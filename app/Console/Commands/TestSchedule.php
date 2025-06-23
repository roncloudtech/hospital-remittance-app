<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class TestSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Laravel scheduler by logging every minute';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        Log::info('Scheduled task ran at: ' . now());
        $this->info('Test schedule executed at ' . now());
    }
}
