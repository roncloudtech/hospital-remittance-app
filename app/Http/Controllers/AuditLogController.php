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

        // 🔍 Filters
        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->actor_id);
        }

        if ($request->filled('actor_role')) {
            $query->where('actor_role', $request->actor_role);
        }

        if ($request->filled('action')) {
            $query->where('action', 'LIKE', '%' . $request->action . '%');
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(20);

        // 🧠 Append actor_name
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
