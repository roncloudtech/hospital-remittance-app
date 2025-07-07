<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HospitalRemittance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HospitalRemittanceController extends Controller
{
    // Adding new remittance information
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hospital_id' => 'required|exists:hospitals,id',
            'monthly_target' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        try {
            $remittance = new HospitalRemittance();
            $remittance->hospital_id = $request->input('hospital_id');
            $remittance->year = Carbon::now()->year;
            $remittance->month = Carbon::now()->month;
            $remittance->monthly_target = $request->input('monthly_target');
            $remittance->save();

            return response()->json([
                'remittance' => $remittance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "errors" => $e->getMessage(),
            ], 500);
        }
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
            'target' => $remittance->monthly_target,
            'paid' => $remittance->amount_paid ?? 0,
            'balance' => $hospital->monthly_remittance_target - ($remittance->amount_paid ?? 0)
        ]);
    }

    public function getHospitalCumulativeStatus($hospital_id, $year, $month)
    {
        $hospital = Hospital::findOrFail($hospital_id);
        $monthlyTarget = $hospital->monthly_remittance_target;
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
