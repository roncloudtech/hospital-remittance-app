<?php
namespace App\Observers;

use App\Models\Remittance;
use App\Models\HospitalRemittance;
use Carbon\Carbon;

class RemittanceObserver
{
    /**
     * Handle the Remittance "created" or "updated" event.
     */
    public function saved(Remittance $remittance)
    {
        if ($remittance->payment_status !== 'success') {
            return; // Only update if payment was successful
        }

        $month = $remittance->transaction_date->month;
        $year = $remittance->transaction_date->year;
        $hospitalId = $remittance->hospital_id;

        // Sum all successful remittances for the same hospital/month/year
        $totalPaid = Remittance::where('hospital_id', $hospitalId)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->where('payment_status', 'success')
            ->sum('amount');

        // Update or create the HospitalRemittance entry
        $remittanceEntry = HospitalRemittance::where('hospital_id', $hospitalId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($remittanceEntry) {
            $remittanceEntry->amount_paid = $totalPaid;
            $remittanceEntry->save();
        }
    }
}
