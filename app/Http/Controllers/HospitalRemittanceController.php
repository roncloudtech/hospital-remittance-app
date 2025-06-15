<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Models\HospitalRemittance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HospitalRemittanceController extends Controller
{
    //
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'hospital_id' => 'required|exists:hospitals,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $remittance = HospitalRemittance::updateOrCreate(
            [
                'hospital_id' => $request->hospital_id,
                'year' => $request->year,
                'month' => $request->month
            ],
            [
                'amount_paid' => DB::raw('amount_paid + ' . $request->amount_paid)
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'target' => $remittance->hospital->monthly_remittance_target,
                'paid' => $remittance->amount_paid,
                'balance' => $remittance->balance
            ]
        ]);
    }

    public function getHospitalMonthlyStatus($hospital_id, $year, $month)
    {
        $hospital = Hospital::findOrFail($hospital_id);
        $remittance = HospitalRemittance::firstOrNew([
            'hospital_id' => $hospital_id,
            'year' => $year,
            'month' => $month
        ]);

        return response()->json([
            'hospital' => $hospital->hospital_name,
            'target' => $hospital->monthly_remittance_target,
            'paid' => $remittance->amount_paid ?? 0,
            'balance' => $hospital->monthly_remittance_target - ($remittance->amount_paid ?? 0)
        ]);
    }

    public function getHospitalCumulativeStatus($hospital_id, $year, $month)
    {
        $hospital = Hospital::findOrFail($hospital_id);
        $monthlyTarget = $hospital->monthly_remittance_target;

        // Total months passed = ((year - start_year) * 12) + month
        $startYear = HospitalRemittance::where('hospital_id', $hospital_id)->min('year') ?? $year;
        $startMonth = 1;

        // Calculate cumulative target up to this month
        $monthsPassed = (($year - $startYear) * 12) + $month;
        $totalExpected = $monthlyTarget * $monthsPassed;

        // Sum actual remittances
        $totalPaid = HospitalRemittance::where('hospital_id', $hospital_id)
            ->where(function ($query) use ($year, $month) {
                $query->where('year', '<', $year)
                    ->orWhere(function ($q) use ($year, $month) {
                        $q->where('year', $year)
                            ->where('month', '<=', $month);
                    });
            })->sum('amount_paid');

        $balance = $totalExpected - $totalPaid;

        return response()->json([
            'hospital' => $hospital->hospital_name,
            'target_per_month' => $monthlyTarget,
            'months_counted' => $monthsPassed,
            'total_expected' => $totalExpected,
            'total_paid' => $totalPaid,
            'outstanding_balance' => $balance
        ]);
    }
}
