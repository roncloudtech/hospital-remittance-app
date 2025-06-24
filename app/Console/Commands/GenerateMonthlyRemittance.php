<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Hospital;
use Illuminate\Console\Command;
use App\Models\HospitalRemittance;
use Illuminate\Support\Facades\Mail;

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

                // Get remitter info
                // $remitter = User::findOrFail($hospital->hospital_remitter);
                // $email = $remitter->email;
                // $name = $remitter->firstname . ' ' . $remitter->lastname;
                // $monthWord = Carbon::create()->month($month)->format('F');
                // Send email notification
                // Mail::send('emails.monthly_target', [
                //     'name' => $name,
                //     'year' => $year,
                //     'email' => $email,
                //     'month' => $month,
                //     'hospital' => $hospital,
                //     'monthWord' => $monthWord,
                // ], function ($message) use ($email, $monthWord, $year) {
                //     $message->to($email);
                //     $message->subject("$monthWord $year Monthly Target");
                // });

                $this->info("Created remittance for hospital  {$hospital->hospital_name}");
            } else {
                $this->warn("Remittance already exists for hospital ID {$hospital->id} ({$year}-{$month})");
            }
        }

        $this->info("Remittance generation complete.");
    }
}