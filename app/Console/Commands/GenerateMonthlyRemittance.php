<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Hospital;
use Illuminate\Console\Command;
use App\Models\HospitalRemittance;

class GenerateMonthlyRemittance extends Command
{
    protected $signature = 'remittance:generate-monthly';

    protected $description = 'Generate monthly remittance records for all hospitals';

    public function handle()
    {
        // \Log::info('🎯 Monthly Remittance Command ran at ' . now());
        $currentDate = Carbon::now();
        $year = $currentDate->year;
        // $month = 5;
        $month = $currentDate->month;

        $this->info("Generating remittance for {$year}-{$month}...");

        $hospitals = Hospital::all();

        foreach ($hospitals as $hospital) {
            $exists = HospitalRemittance::where('hospital_id', $hospital->id)
                ->where('year', $year)
                ->where('month', $month)
                ->exists();

            if (!$exists) {
                HospitalRemittance::create([
                    'hospital_id' => $hospital->id,
                    'year' => $year,
                    'month' => $month,
                    'monthly_target' => $hospital->monthly_remittance_target,
                    'amount_paid' => 0,
                    'balance' => $hospital->monthly_remittance_target,
                ]);
                $this->info("Created remittance for hospital  {$hospital->hospital_name}");
            } else {
                $this->warn("Remittance already exists for hospital ID {$hospital->id} ({$year}-{$month})");
            }
        }

        $this->info("Remittance generation complete.");
    }
}