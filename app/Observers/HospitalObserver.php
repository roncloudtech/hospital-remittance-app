<?php

namespace App\Observers;

use App\Models\Hospital;
use App\Models\HospitalRemittance;
use Carbon\Carbon;

class HospitalObserver
{
    /**
     * Handle the Hospital "created" event.
     */
    // public function created(Hospital $hospital): void
    // {
    //     //
    //     $now = Carbon::now();

    //     HospitalRemittance::create([
    //         'hospital_id' => $hospital->id,
    //         'year' => $now->year,
    //         'month' => $now->month,
    //         'monthly_target' => $hospital->monthly_remittance_target,
    //         'amount_paid' => 0,
    //         'balance' => $hospital->monthly_target,
    //     ]);
    // }

    /**
     * Handle the Hospital "updated" event.
     */
    public function updated(Hospital $hospital): void
    {
        //
    }

    /**
     * Handle the Hospital "deleted" event.
     */
    public function deleted(Hospital $hospital): void
    {
        //
    }

    /**
     * Handle the Hospital "restored" event.
     */
    public function restored(Hospital $hospital): void
    {
        //
    }

    /**
     * Handle the Hospital "force deleted" event.
     */
    public function forceDeleted(Hospital $hospital): void
    {
        //
    }
}
