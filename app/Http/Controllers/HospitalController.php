<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HospitalController extends Controller
{
    public function getHospital()
    {
        // Fetch all users using Eloquent ORM
        $hospitals = Hospital::all();
        return $hospitals;
    }
    // Get all hospitals
    public function index()
    {
        return Hospital::with('creator')->get();
    }

    // Create a new hospital
    public function addHospital(Request $request)
    {
        // Validate user entry
        $validator = Validator::make($request->all(), [
            'hospital_id' => 'required|string|max:10|unique:hospitals,hospital_id|min:9',
            'hospital_name' => 'required|string',
            'military_division' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string|unique:hospitals',
            'hospital_remitter' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $hospital = new Hospital;
        $hospital->hospital_id = $request->input('hospital_id');
        $hospital->hospital_name = $request->input('hospital_name');
        $hospital->military_division = $request->input('military_division');
        $hospital->address = $request->input('address');
        $hospital->phone_number = $request->input('phone_number');
        $hospital->hospital_remitter = $request->input('hospital_remitter');
        $hospital->save();

        return response()->json([
            'message' => $hospital->hospital_name . 'created successfully',
            'hospital' => $hospital
        ], 201);
    }

    // Fetch all hospitals
    public function getHospitals()
    {
        $hospitals = Hospital::all();
        return $hospitals;
    }

    public function fetchRemitterHospitals(Request $request)
    {
        try {
            // Get authenticated user's ID
            $remitterId = auth()->id(); // Changed from $request->input()

            $hospitals = Hospital::where('hospital_remitter', $remitterId)
                ->get();

            return response()->json([
                'success' => true,
                'hospitals' => $hospitals
            ]);

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
    public function update(Request $request, Hospital $hospital)
    {
        $request->validate([
            'hospital_id' => 'string|max:10|unique:hospitals,hospital_id,' . $hospital->id,
            'hospital_name' => 'string',
            'military_division' => 'string',
            'address' => 'string',
            'phone_number' => 'string|unique:hospitals,phone_number,' . $hospital->id,
            'created_by' => 'exists:users,id',
        ]);

        $hospital->update($request->all());
        return $hospital;
    }

    // Delete a hospital (soft delete)
    public function destroy(Hospital $hospital)
    {
        $hospital->delete();
        return response()->noContent();
    }
}