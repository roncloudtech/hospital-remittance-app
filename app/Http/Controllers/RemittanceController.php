<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Remittance;
use Illuminate\Http\Request;

class RemittanceController extends Controller
{
    //
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'transaction_date' => 'required|date'
        ]);

        $remittance = Remittance::create([
            ...$validated,
            'remitter_id' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Remittance successful',
            'data' => $remittance
        ], 201);
    }
}
