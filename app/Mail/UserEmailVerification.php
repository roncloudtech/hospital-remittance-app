<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserEmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tempPassword;


    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
        
    }

    public function build()
    {
        return $this->view('emails.user-verification');
    }
}