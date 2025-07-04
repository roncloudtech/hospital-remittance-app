<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hospital;
use App\Models\User;
use App\Models\HospitalRemittance;
use App\Models\Remittance;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class MailRemitterBalanceSummary extends Command
{
    protected $signature = 'mail:remitter-balances';

    protected $description = 'Send each remitter their expected hospital balance for the new month';

    public function handle()
    {
        $this->info("\u{1F4E7} Starting remitter balance summary...");

        $month = now()->month;
        $year = now()->year;
        $monthName = Carbon::create()->month($month)->format('F');

        // Get all remitters
        $remitters = User::where('role', 'remitter')->get();

        foreach ($remitters as $remitter) {
            $hospitals = Hospital::where('hospital_remitter', $remitter->id)->get();

            if ($hospitals->isEmpty()) {
                $this->warn("\u{26A0}\uFE0F No hospitals found for remitter: {$remitter->firstname}");
                continue;
            }

            $hospitalSummaries = [];

            foreach ($hospitals as $hospital) {
                $remittances = HospitalRemittance::where('hospital_id', $hospital->id)
                    ->where(function ($query) use ($year, $month) {
                        $query->where('year', '<', $year)
                            ->orWhere(function ($q) use ($year, $month) {
                                $q->where('year', $year)->where('month', '<=', $month);
                            });
                    })
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();

                $totalTarget = 0;
                $totalPaid = 0;

                foreach ($remittances as $remit) {
                    $target = $remit->monthly_target;

                    $amountPaid = Remittance::where('hospital_id', $hospital->id)
                        ->where('payment_status', 'success')
                        ->whereYear('transaction_date', $remit->year)
                        ->whereMonth('transaction_date', $remit->month)
                        ->sum('amount');

                    $totalTarget += $target;
                    $totalPaid += $amountPaid;
                }

                $balance = $totalTarget - $totalPaid;

                if ($balance > 0) {
                    $hospitalSummaries[] = [
                        'name' => $hospital->hospital_name,
                        'balance' => number_format($balance, 2)
                    ];
                }
            }

            if (count($hospitalSummaries)) {
                try {
                    Mail::send('emails.remitter_balance_summary', [
                        'remitter' => $remitter,
                        'month' => $month,
                        'year' => $year,
                        'monthName' => $monthName,
                        'summaries' => $hospitalSummaries
                    ], function ($message) use ($remitter, $monthName, $year) {
                        $message->to($remitter->email)
                                ->subject("\u{1F4CA} {$monthName} {$year} Remittance Balance Summary");
                    });

                    $this->info("\u{2705} Sent email to {$remitter->email}");
                } catch (\Exception $e) {
                    $this->error("\u{274C} Failed to send to {$remitter->email}: {$e->getMessage()}");
                }
            } else {
                $this->warn("\u{2139}\uFE0F No outstanding balances for remitter: {$remitter->email}");
            }
        }

        $this->info("\u{1F389} Remitter balance mailing complete.");
    }
}









// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use App\Models\Hospital;
// use App\Models\User;
// use App\Models\HospitalRemittance;
// use Illuminate\Support\Facades\Mail;
// use Carbon\Carbon;

// class MailRemitterBalanceSummary extends Command
// {
//     protected $signature = 'mail:remitter-balances';

//     protected $description = 'Send each remitter their expected hospital balance for the new month';

//     public function handle()
//     {
//         $this->info("📧 Starting remitter balance summary...");

//         $month = now()->month;
//         $year = now()->year;
//         $monthName = Carbon::create()->month($month)->format('F');

//         // Get all remitters
//         $remitters = User::where('role', 'remitter')->get();

//         foreach ($remitters as $remitter) {
//             $hospitals = Hospital::where('hospital_remitter', $remitter->id)->get();

//             if ($hospitals->isEmpty()) {
//                 $this->warn("⚠️ No hospitals found for remitter: {$remitter->firstname}");
//                 continue;
//             }

//             $hospitalSummaries = [];

//             foreach ($hospitals as $hospital) {
//                 // Sum up unpaid balances from all months
//                 $totalBalance = HospitalRemittance::where('hospital_id', $hospital->id)
//                     ->where(function($query) use ($year, $month) {
//                         $query->where('year', '<', $year)
//                               ->orWhere(function ($q) use ($month, $year) {
//                                   $q->where('year', $year)->where('month', '<=', $month);
//                               });
//                     })
//                     ->sum('balance');

//                 if ($totalBalance > 0) {
//                     $hospitalSummaries[] = [
//                         'name' => $hospital->hospital_name,
//                         'balance' => number_format($totalBalance, 2)
//                     ];
//                 }
//             }

//             if (count($hospitalSummaries)) {
//                 try {
//                     Mail::send('emails.remitter_balance_summary', [
//                         'remitter' => $remitter,
//                         'month' => $month,
//                         'year' => $year,
//                         'monthName' => $monthName,
//                         'summaries' => $hospitalSummaries
//                     ], function ($message) use ($remitter, $monthName, $year) {
//                         $message->to($remitter->email)
//                                 ->subject("📊 {$monthName} {$year} Remittance Balance Summary");
//                     });

//                     $this->info("✅ Sent email to {$remitter->email}");
//                 } catch (\Exception $e) {
//                     $this->error("❌ Failed to send to {$remitter->email}: {$e->getMessage()}");
//                 }
//             } else {
//                 $this->warn("ℹ️ No outstanding balances for remitter: {$remitter->email}");
//             }
//         }

//         $this->info("🎉 Remitter balance mailing complete.");
//     }
// }
