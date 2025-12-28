<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemittanceStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $remittance,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'action' => 'remittance_status_updated',
            'description' =>
                "Your remittance payment of ₦" .
                number_format($this->remittance->amount, 2) .
                " to {$this->remittance->hospital->hospital_name} " .
                "was updated from {$this->oldStatus} to {$this->newStatus}.",
            'remittance_id' => $this->remittance->id,
            'hospital_id' => $this->remittance->hospital_id,
            'hospital_name' => $this->remittance->hospital->hospital_name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'amount' => $this->remittance->amount,
            'reference' => $this->remittance->payment_reference,
        ];
    }
}
