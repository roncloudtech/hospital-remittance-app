<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Hospital;
use App\Models\HospitalRemittance;

class GenerateMonthlyHospitalRemittances extends Command
{
    protected $signature = 'generate:monthly-remittances';
    protected $description = 'Create remittance rows for each hospital every month';

    // public function handle()
    // {
    //     $hospitals = Hospital::all();
    //     $now = Carbon::now();
    //     $currentMonth = $now->month;
    //     $currentYear = $now->year;

    //     foreach ($hospitals as $hospital) {
    //         $previous = HospitalRemittance::where('hospital_id', $hospital->id)
    //             ->where('month', $currentMonth - 1)
    //             ->where('year', $currentYear)
    //             ->first();

    //         $carryOver = $previous ? ($hospital->monthly_remittance_target - $previous->amount_paid) : 0;

    //         HospitalRemittance::firstOrCreate([
    //             'hospital_id' => $hospital->id,
    //             'month' => $currentMonth,
    //             'year' => $currentYear,
    //             'monthly_target' => $hospital->monthly_remittance_target,
    //         ], [
    //             'amount_paid' => $hospital->monthly_remittance_target,
    //             'carryover' => $carryOver,
    //         ]);
    //     }

    //     $this->info('Monthly hospital remittances generated.');
    // }

//     public function generateMonthlyHospitalRemittances()
// {
//     $hospitals = Hospital::all();
//     $now = Carbon::now();
//     $currentMonth = $now->month;
//     $currentYear = $now->year;

//     foreach ($hospitals as $hospital) {
//         $previous = HospitalRemittance::where('hospital_id', $hospital->id)
//             ->where(function ($query) use ($currentMonth, $currentYear) {
//                 $query->where('month', $currentMonth - 1)->where('year', $currentYear);
//             })->first();

//         $carryOver = $previous ? ($hospital->monthly_remittance_target - $previous->amount_paid) : 0;

//         HospitalRemittance::firstOrCreate([
//             'hospital_id' => $hospital->id,
//             'month' => $currentMonth,
//             'year' => $currentYear,
//         ], [
//             'amount_paid' => 0,
//             'carryover' => $carryOver, // Optional column if you want to track this
//         ]);
//     }
// }
}
