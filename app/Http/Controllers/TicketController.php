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
                $path = $request->file('evidence')->store('evidences');


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

    //All Tickets
    public function allTickets(Request $request)
    {
        // Optionally ensure only admins can access
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $tickets = Ticket::with('user:id,name,email') // eager load user info
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }

    // Remiiter Tickets
    public function userTickets(Request $request)
    {
        $user = $request->user();

        $tickets = Ticket::where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }
}