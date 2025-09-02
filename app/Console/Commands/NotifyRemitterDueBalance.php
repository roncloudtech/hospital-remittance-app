<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Hospital;
use App\Models\HospitalRemittance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyRemitterDueBalance extends Command
{
    protected $signature = 'remittance:notify-due-remitters';
    protected $description = 'Send daily reminder to remitters who have not completed their total remittance due';

    public function handle()
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;
        $monthName = $now->format('F');

        $hospitals = Hospital::with('remitter')->get();

        foreach ($hospitals as $hospital) {
            $remitter = $hospital->remitter;

            if (!$remitter || !filter_var($remitter->email, FILTER_VALIDATE_EMAIL)) {
                $this->warn("❌ Invalid or missing email for remitter of hospital: {$hospital->hospital_name}");
                continue;
            }

            // Previous months balance (before current month)
            $previousRemittances = HospitalRemittance::where('hospital_id', $hospital->id)
                ->where(function ($q) use ($year, $month) {
                    $q->where('year', '<', $year)
                        ->orWhere(function ($q2) use ($year, $month) {
                            $q2->where('year', $year)->where('month', '<', $month);
                        });
                })->get();

            $previousTarget = $previousRemittances->sum('monthly_target');
            $previousPaid = $previousRemittances->sum('amount_paid');
            $previousBalance = $previousTarget - $previousPaid;

            // Current month target
            $currentRemittance = HospitalRemittance::where('hospital_id', $hospital->id)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            $currentTarget = $currentRemittance ? $currentRemittance->monthly_target : 0;
            $currentPaid = $currentRemittance ? $currentRemittance->amount_paid : 0;

            $totalPaid = $previousPaid + $currentPaid;
            $totalDue = $previousBalance + $currentTarget - $currentPaid;

            if ($totalDue <= 0) {
                $this->info("✅ No due balance for {$hospital->hospital_name}");
                continue;
            }

            // Send email
            Mail::send('emails.reminder_due_balance', [
                'name' => $remitter->firstname,
                'hospital' => $hospital,
                'monthName' => $monthName,
                'year' => $year,
                'previousBalance' => $previousBalance,
                'currentTarget' => $currentTarget,
                // 'totalPaid' => $totalPaid,
                'totalPaid' => $currentPaid,
                'totalDue' => $totalDue,
            ], function ($message) use ($remitter, $hospital, $monthName, $year) {
                $message->to($remitter->email);
                $message->subject("⏰ Remittance Due Reminder - {$hospital->hospital_name} ({$monthName} {$year})");
            });

            $this->info("📤 Reminder sent to {$remitter->email} for {$hospital->hospital_name}");
        }

        $this->info("✅ Daily due reminder emails sent.");
    }
}
