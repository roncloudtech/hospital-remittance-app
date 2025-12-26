<?php
namespace App\Http\Controllers;

use App\Events\ActionPerformed;
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
            'hospital_id' => 'required|exists:hospitals,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:Paystack,Bank Deposit',
            'payment_status' => 'required|in:pending,success,decline',
            'payment_reference' => 'required|string|unique:remittances,payment_reference',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'payment_evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $evidencePath = null;
            if ($request->hasFile('payment_evidence')) {
                $evidencePath = $request->file('payment_evidence')->store('payment_evidence', 'public');
            }

            $remittance = Remittance::create([
                ...$validated,
                'remitter_id' => $request->user()->id,
                'payment_evidence' => $evidencePath,
            ]);

            event(new ActionPerformed([
                'actor_id' => $request->user()->id,
                'actor_role' => $request->user()->role,
                'action' => 'create_remittance',
                'description' => "{$request->user()->email} made a new remittance payment of ₦" .
                    number_format($remittance->amount) .
                    " using {$remittance->payment_method} to {$remittance->hospital->hospital_name}.",
                'metadata' => [
                    'remittance_id' => $remittance->id,
                    'hospital_id' => $remittance->hospital_id,
                    'hospital_name' => $remittance->hospital->hospital_name,
                    'amount' => $remittance->amount,
                    'payment_method' => $remittance->payment_method,
                    'payment_status' => $remittance->payment_status,
                    'reference' => $remittance->payment_reference,
                ],
            ]));


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

    public function allRemittances()
    {
        try {
            $query = Remittance::with(['hospital', 'remitter'])
                ->orderBy('transaction_date', 'desc');

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

    // Admin approve or decline remittance
    public function updateRemittance($id, $action)
    {
        try {
            // --------------------------------------------------
            // Validate allowed actions
            // --------------------------------------------------
            if (!in_array($action, ['success', 'decline', 'pending'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid remittance action'
                ], 400);
            }

            // --------------------------------------------------
            // Find remittance
            // --------------------------------------------------
            $remittance = Remittance::with('hospital')->find($id);

            if (!$remittance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Remittance not found'
                ], 404);
            }

            // --------------------------------------------------
            // Prevent duplicate approval
            // --------------------------------------------------
            if ($remittance->payment_status === 'success' && $action === 'success') {
                return response()->json([
                    'success' => false,
                    'message' => 'Remittance already approved'
                ], 400);
            }

            // --------------------------------------------------
            // Capture old status
            // --------------------------------------------------
            $oldStatus = $remittance->payment_status;

            // --------------------------------------------------
            // Update status
            // --------------------------------------------------
            $remittance->payment_status = $action;
            $remittance->save();

            // --------------------------------------------------
            // 🔔 AUDIT / NOTIFICATION EVENT
            // --------------------------------------------------
            event(new ActionPerformed([
                'actor_id' => auth()->id(),
                'actor_role' => auth()->user()?->role,
                'action' => 'update_remittance_status',
                'description' => auth()->user()->email .
                    " updated remittance status from {$oldStatus} to {$action} " .
                    "for {$remittance->hospital->hospital_name}.",
                'metadata' => [
                    'remittance_id' => $remittance->id,
                    'hospital_id' => $remittance->hospital_id,
                    'hospital_name' => $remittance->hospital->hospital_name,
                    'old_status' => $oldStatus,
                    'new_status' => $action,
                    'amount' => $remittance->amount,
                    'reference' => $remittance->payment_reference,
                ],
            ]));

            return response()->json([
                'success' => true,
                'message' => "Remittance status updated to {$action}",
                'data' => $remittance,
            ]);

        } catch (\Throwable $e) {
            Log::error('Update Remittance Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update remittance status'
            ], 500);
        }
    }

}

