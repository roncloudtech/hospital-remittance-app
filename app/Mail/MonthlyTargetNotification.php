<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyTargetNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $hospital;
    public $month;
    public $year;
    public $target;

    public function __construct($hospital, $month, $year, $target)
    {
        $this->hospital = $hospital;
        $this->month = $month;
        $this->year = $year;
        $this->target = $target;
    }

    public function build()
    {
        return $this->subject('🧾 New Monthly Remittance Target')
            ->markdown('emails.monthly_target');
    }
}
