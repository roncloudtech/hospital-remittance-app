<?php

namespace App\Providers;
use App\Models\Hospital;
use App\Models\Remittance;
use App\Observers\HospitalObserver;
use App\Observers\RemittanceObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Hospital::observe(HospitalObserver::class);
        Remittance::observe(RemittanceObserver::class);
        Schema::defaultStringLength(190);
    }
}
