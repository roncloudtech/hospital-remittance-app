<?php

namespace App\Console\Commands;  // ADDED NAMESPACE

use App\Models\Hospital;
use App\Models\User;
use App\Models\HospitalRemittance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailMonthlyTargetSummary extends Command
{
    protected $signature = 'remittance:mail-monthly-summary';
    protected $description = 'Mail monthly target summary (including balances) to each remitter for their hospitals';

    public function handle()
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;
        $monthName = $now->format('F');

        // Eager load remitter relationship
        $hospitals = Hospital::with('remitter')->get();

        foreach ($hospitals as $hospital) {
            // Use relationship instead of direct ID access
            $remitter = $hospital->remitter;  // FIXED RELATIONSHIP ACCESS

            // Skip if no valid remitter or email
            if (!$remitter || !filter_var($remitter->email, FILTER_VALIDATE_EMAIL)) {
                $this->warn("❌ Invalid or missing email for remitter of hospital: {$hospital->hospital_name}");
                continue;
            }

            // Calculate previous balance
            $previousBalance = HospitalRemittance::where('hospital_id', $hospital->id)
                ->where(function ($q) use ($year, $month) {
                    $q->where('year', '<', $year)
                      ->orWhere(function ($q2) use ($year, $month) {
                          $q2->where('year', $year)->where('month', '<', $month);
                      });
                })
                ->sum('monthly_target') 
                - HospitalRemittance::where('hospital_id', $hospital->id)
                    ->where(function ($q) use ($year, $month) {
                        $q->where('year', '<', $year)
                          ->orWhere(function ($q2) use ($year, $month) {
                              $q2->where('year', $year)->where('month', '<', $month);
                          });
                    })
                    ->sum('amount_paid');

            $newTarget = $hospital->monthly_remittance_target;
            $totalDue = $previousBalance + $newTarget;

            // Send email
            Mail::send('emails.monthly_remittance_summary', [
                'name' => $remitter->firstname,
                'hospital' => $hospital,
                'monthName' => $monthName,
                'year' => $year,
                'previousBalance' => $previousBalance,
                'newTarget' => $newTarget,
                'totalDue' => $totalDue,
            ], function ($message) use ($remitter, $hospital, $monthName, $year) {
                $message->to($remitter->email);
                $message->subject("{$monthName} {$year} Remittance Target - {$hospital->hospital_name}");
            });

            $this->info("📤 Email sent to {$remitter->email} for {$hospital->hospital_name}");
        }

        $this->info("✅ Monthly remittance summary emails completed.");
    }
}