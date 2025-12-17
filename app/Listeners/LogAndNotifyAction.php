<?php

namespace App\Listeners;

use App\Events\ActionPerformed;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\AdminActionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class LogAndNotifyAction
{
    public function handle(ActionPerformed $event)
    {
        // Debug entry to help detect duplicate handling
        \Log::debug('LogAndNotifyAction::handle called', [
            'data' => $event->data,
            'time' => now()->toDateTimeString(),
        ]);

        $data = $event->data;

        // Idempotency guard: skip duplicates within a short window
        try {
            $actorId = $data['actor_id'] ?? null;
            $action = $data['action'] ?? null;
            $ip = request()->ip();

            if ($actorId && $action) {
                $recent = AuditLog::where('actor_id', $actorId)
                    ->where('action', $action)
                    ->where('ip_address', $ip)
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->exists();

                if ($recent) {
                    \Log::warning('Duplicate activity detected; skipping AuditLog/Notification', [
                        'actor_id' => $actorId,
                        'action' => $action,
                        'ip' => $ip,
                    ]);
                    return;
                }
            }
        } catch (\Throwable $e) {
            // log error and continue to avoid breaking flow
            \Log::error('Idempotency guard error: ' . $e->getMessage());
        }

        // Save audit log
        $log = AuditLog::create([
            'actor_id' => $data['actor_id'] ?? null,
            'actor_role' => $data['actor_role'] ?? null,
            'action' => $data['action'],
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'description' => $data['description'] ?? null,
            'ip_address' => request()->ip(),
        ]);

        // Notify admins
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new AdminActionNotification($log));
    }
}


// class LogAndNotifyAction
// {
//     /**
//      * Create the event listener.
//      */
//     public function __construct()
//     {
//         //
//     }

//     /**
//      * Handle the event.
//      */
//     public function handle(object $event): void
//     {
//         //
//     }
// }
