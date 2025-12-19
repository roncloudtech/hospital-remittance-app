<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TicketReply extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachment',
    ];

    /**
     * Reply belongs to a ticket.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Reply made by a user (admin or remitter).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
