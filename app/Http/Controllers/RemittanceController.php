<?php
namespace App\Http\Controllers;

use App\Models\Remittance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RemittanceController extends Controller
{
    /**
     * Store a new remittance.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hospital_id'        => 'required|exists:hospitals,id',
            'amount'             => 'required|numeric|min:0.01',
            'payment_method'     => 'required|in:Paystack,Bank Deposit',
            'payment_status'     => 'required|in:pending,success,decline',
            'payment_reference'  => 'required|string|unique:remittances,payment_reference',
            'transaction_date'   => 'required|date',
            'description'        => 'nullable|string',
        ]);

        try {
            $remittance = Remittance::create([
                ...$validated,
                'remitter_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Remittance recorded successfully.',
                'data' => $remittance
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Remittance Store Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to record remittance. Please try again.'
            ], 500);
        }
    }

    /**
     * Retrieve remittance transactions.
     * - Admins see all transactions
     * - Remitters see only their transactions
     */
    public function getRemittances(Request $request)
    {
        try {
            $query = Remittance::with(['hospital', 'remitter'])
                ->orderBy('transaction_date', 'desc');

            if ($request->user()->role === 'remitter') {
                $query->where('remitter_id', $request->user()->id);
            }

            $transactions = $query->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Throwable $e) {
            Log::error('Get Remittances Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions.'
            ], 500);
        }
    }

    public function fetchRemitterRemittances(Request $request)
    {
        try {
            // Get authenticated user's ID
            $remitterId = auth()->id(); // Changed from $request->input()

            $hospitals = Remittance::where('remitter_id', $remitterId)
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
}

