<?php

namespace App\Notifications;

use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminActionNotification extends Notification
{
    use Queueable;

    public function __construct(public AuditLog $log) {}

    public function via($notifiable)
    {
        return ['database']; // later add mail if needed
    }

    public function toDatabase($notifiable)
    {
        return [
            'action' => $this->log->action,
            'description' => $this->log->description,
            'actor_role' => $this->log->actor_role,
            'ip_address' => $this->log->ip_address,
            'time' => $this->log->created_at,
        ];
    }
}
