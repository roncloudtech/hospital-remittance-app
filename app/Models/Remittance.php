<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Remittance extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'hospital_id',
        'remitter_id',
        'amount',
        'transaction_date',
        'description',
        'payment_reference',
        'payment_status',
        'updated_by',
        'payment_method',
        'payment_evidence',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'float',
    ];

    /**
     * Get the hospital that received the remittance.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the user who made the remittance.
     */
    public function remitter()
    {
        return $this->belongsTo(User::class, 'remitter_id');
    }

    /**
     * Get the user who last updated the remittance.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
