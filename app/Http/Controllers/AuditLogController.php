<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Admin: View & filter audit logs
     */

    public function index(Request $request)
    {
        $query = AuditLog::with('actor')->latest();

        /*
        |--------------------------------------------------------------------------
        | GLOBAL SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                // Search action, description, IP
                $q->where('action', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('ip_address', 'LIKE', "%{$search}%")
                    ->orWhere('actor_role', 'LIKE', "%{$search}%");

                // Search actor firstname / lastname
                $q->orWhereHas('actor', function ($actorQuery) use ($search) {
                    $actorQuery->where('firstname', 'LIKE', "%{$search}%")
                        ->orWhere('lastname', 'LIKE', "%{$search}%");
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | DATE FILTERS (optional, still works)
        |--------------------------------------------------------------------------
        */
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(20);

        /*
        |--------------------------------------------------------------------------
        | APPEND ACTOR NAME
        |--------------------------------------------------------------------------
        */
        $logs->getCollection()->transform(function ($log) {
            $log->actor_name = $log->actor
                ? "{$log->actor->firstname} {$log->actor->lastname}"
                : 'System / Deleted User';

            return $log;
        });

        return response()->json([
            'logs' => $logs,
        ]);
    }
}
