<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\TicketReply;
use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        // Validate user input
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string',
            'message' => 'required|string',
            'evidence' => 'nullable|file|mimes:jpeg,jpg,png,pdf',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Store evidence file if provided
            $path = null;
            if ($request->hasFile('evidence')) {
                // $path = $request->file('evidence')->store('evidences');
                $path = $request->file('evidence')->store('evidences', 'public');

            }

            // Create and save the ticket
            $ticket = new Ticket();
            $ticket->subject = $request->input('subject');
            $ticket->message = $request->input('message');
            $ticket->evidence_path = $path;
            $ticket->user_id = $request->user()->id;
            // $ticket->user_id = $userId;
            $ticket->save();

            return response()->json([
                'success' => true,
                'message' => 'Ticket submitted successfully.',
                'data' => $ticket
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Ticket submission error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit ticket. Please try again.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function allTickets(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $tickets = Ticket::with('user:id,email')->latest()->get();

            return response()->json([
                'success' => true,
                'tickets' => $tickets,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Admin ticket fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tickets',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Remiiter Tickets
    public function userTickets(Request $request)
    {
        $user = $request->user();

        $tickets = Ticket::where('user_id', $user->id)->latest()->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,resolved,closed',
        ]);

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);
        }

        $ticket->status = $request->status;
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket
        ]);
    }

}