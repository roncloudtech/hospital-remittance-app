<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalRemittance extends Model
{
    protected $fillable = [
        'hospital_id', 
        'year', 
        'month', 
        'monthly_target', 
        'amount_paid', 
        'carryover'
    ];
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function getBalanceAttribute()
    {
        return ($this->monthly_target + $this->carryover) - $this->amount_paid;
    }

}

