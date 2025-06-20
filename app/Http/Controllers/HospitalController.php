<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Hospital;
use App\Models\Remittance;
use Illuminate\Http\Request;
use App\Models\HospitalRemittance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HospitalController extends Controller
{
    // Create a new hospital
    public function addHospital(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hospital_id' => 'required|string|max:10|unique:hospitals,hospital_id|min:9',
            'hospital_name' => 'required|string',
            'military_division' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string|unique:hospitals',
            'hospital_remitter' => 'required|exists:users,id',
            'monthly_remittance_target' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        try {
            $hospital = new Hospital();
            $hospital->hospital_id = $request->input('hospital_id');
            $hospital->hospital_name = $request->input('hospital_name');
            $hospital->military_division = $request->input('military_division');
            $hospital->address = $request->input('address');
            $hospital->phone_number = $request->input('phone_number');
            $hospital->hospital_remitter = $request->input('hospital_remitter');
            $hospital->monthly_remittance_target = $request->input('monthly_remittance_target');
            $hospital->save();
    
            // Get remitter info
            $remitter = User::findOrFail($hospital->hospital_remitter);
            $email = $remitter->email;
            $name = $remitter->firstname . ' ' . $remitter->lastname;
    
            // Send email notification
            Mail::send('emails.new-hospital-notification', [
                'hospital' => $hospital,
                'email' => $email,
                'name' => $name
            ], function ($message) use ($hospital, $email, $name) {
                $message->to($email);
                $message->subject('New Hospital Assigned');
            });

            return response()->json([
                'message' => $hospital->hospital_name . ' created successfully and a mail has been sent to' . $email,
                'hospital' => $hospital,
                'user' => $remitter,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "errors" => $e->getMessage(),
            ], 500);
        }

    }

    // Fetch all hospitals
    public function getHospitals()
    {
        $hospitals = Hospital::all();
        return $hospitals;
    }

    // Fetch one hospitals
    public function oneHospital($id)
    {
        try {
            $hospital = Hospital::where('id', $id)->first();

            return response()->json([
                'success' => true,
                'hospitals' => $hospital,
                'id' => $id,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hospitals',
                'error' => $e->getMessage(),
                'e' => $e,
            ], 500);
        }
    }

    // Fetch all hospitals belonging to a particular remitter
    public function fetchRemitterHospitals(Request $request)
    {
        try {
            $remitterId = auth()->id();
            $hospitals = Hospital::where('hospital_remitter', $remitterId)->get();

            return response()->json([
                'success' => true,
                'hospitals' => $hospitals
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hospitals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get a single hospital
    public function show(Hospital $hospital)
    {
        return $hospital->load('creator');
    }

    // Update a hospital
    public function updateHospital($id, Request $request)
    {
        $hospital = Hospital::find($id);

        if (!$hospital) {
            return response()->json([
                'success' => false,
                'message' => 'Hospital not found'
            ], 404);
        }

        $request->validate([
            'hospital_id' => 'string|max:10|unique:hospitals,hospital_id,' . $hospital->id,
            'hospital_name' => 'string',
            'military_division' => 'string',
            'address' => 'string',
            'phone_number' => 'string|unique:hospitals,phone_number,' . $hospital->id,
            'created_by' => 'exists:users,id',
            'monthly_remittance_target' => 'required|numeric|min:1',
        ]);

        $hospital->update($request->only([
            'hospital_id',
            'hospital_name',
            'military_division',
            'address',
            'phone_number',
            'hospital_remitter',
            'monthly_remittance_target',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Hospital updated successfully',
            'data' => $hospital
        ]);
    }

    // Delete a hospital
    public function destroy(Hospital $hospital)
    {
        $hospital->delete();
        return response()->noContent();
    }

    //  Remitter HospitalSummary
    public function remitterHospitalsSummary()
    {
        $user = Auth::user();

        // Only remitters should access this
        if ($user->role !== 'remitter') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hospitals = Hospital::where('hospital_remitter', $user->id)->get();

        $summary = [];

        foreach ($hospitals as $hospital) {
            $remittances = HospitalRemittance::where('hospital_id', $hospital->id)
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $monthlyData = [];

            foreach ($remittances as $remit) {
                // $target = $hospital->monthly_remittance_target;
                $target = $remit->monthly_target;

                // Get total paid from the Remittance table for that month and year
                $amountPaid = Remittance::where('hospital_id', $hospital->id)
                    ->where('payment_status', 'success')
                    ->whereYear('transaction_date', $remit->year)
                    ->whereMonth('transaction_date', $remit->month)
                    ->sum('amount');

                // $balance = $target - $amountPaid;
                $balance = $amountPaid - $target;

                $monthlyData[] = [
                    'month' => $remit->month,
                    'year' => $remit->year,
                    'target' => $target,
                    'amount_paid' => $amountPaid,
                    'balance' => $balance,
                ];
            }

            $summary[] = [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->hospital_name,
                // 'monthly_target' => $hospital->monthly_remittance_target,
                'monthly_target' => $target,
                'records' => $monthlyData,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    // Admin HospitalSummary
    public function adminHospitalsSummary()
    {
        $user = Auth::user();

        // Only admins should access this
        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $hospitals = Hospital::all(); // Get all hospitals

            $summary = [];

            foreach ($hospitals as $hospital) {
                $remittances = HospitalRemittance::where('hospital_id', $hospital->id)
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();

                $monthlyData = [];

                foreach ($remittances as $remit) {
                    // $target = $hospital->monthly_remittance_target ?? 0;
                    $target = $remit->monthly_target ?? 0;

                    $amountPaid = Remittance::where('hospital_id', $hospital->id)
                        ->where('payment_status', 'success')
                        ->whereYear('transaction_date', $remit->year)
                        ->whereMonth('transaction_date', $remit->month)
                        ->sum('amount');

                    $balance = $amountPaid - $target;

                    $monthlyData[] = [
                        'month' => $remit->month,
                        'year' => $remit->year,
                        'target' => $target,
                        'amount_paid' => $amountPaid,
                        'balance' => $balance,
                    ];
                }

                $summary[] = [
                    'hospital_id' => $hospital->id,
                    'hospital_name' => $hospital->hospital_name,
                    // 'monthly_target' => $hospital->monthly_remittance_target,
                    'monthly_target' => $target,
                    'records' => $monthlyData,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hospital summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}