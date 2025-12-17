<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ActionPerformed;
use App\Listeners\LogAndNotifyAction;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ActionPerformed::class => [
            LogAndNotifyAction::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
