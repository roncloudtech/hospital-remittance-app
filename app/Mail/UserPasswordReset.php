<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class UserPasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tempPassword;
    public $resetLink;

    public function __construct(User $user, string $tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
        $this->resetLink = url('/verify-email/' . $user->id);
    }
    public function build()
    {
        return $this->view('emails.user-password-reset')->with([
            'tempPassword' => $this->tempPassword,
            'resetLink' => $this->resetLink,
        ])->subject('Your Account Credentials');
    }

    // public function build()
    // {
    //     return $this->subject('Your Account Credentials')
    //         ->view('emails.user-password-reset');
    // }
}

// class UserPasswordReset extends Mailable
// {
//     use Queueable, SerializesModels;

//     public $user;
//     public $url;

//     public function __construct(User $user)
//     {
//         $this->user = $user;

//         $this->url = url('/reset-password/' . $user->id); 
//     }

//     public function build()
//     {
//         return $this->view('emails.user-password-reset');
//     }
// }
















//     use Queueable, SerializesModels;

//     /**
//      * Create a new message instance.
//      */
//     public function __construct()
//     {
//         //
//     }

//     /**
//      * Get the message envelope.
//      */
//     public function envelope(): Envelope
//     {
//         return new Envelope(
//             subject: 'User Password Reset',
//         );
//     }

//     /**
//      * Get the message content definition.
//      */
//     public function content(): Content
//     {
//         return new Content(
//             view: 'view.name',
//         );
//     }

//     /**
//      * Get the attachments for the message.
//      *
//      * @return array<int, \Illuminate\Mail\Mailables\Attachment>
//      */
//     public function attachments(): array
//     {
//         return [];
//     }
// }
