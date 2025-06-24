<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalRemittance extends Model
{
    protected $fillable = ['hospital_id', 'year', 'month', 'monthly_target', 'amount_paid'];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    // public function getBalanceAttribute()
    // {
    //     return $this->hospital->monthly_remittance_target - $this->amount_paid;
    // }

    public function getBalanceAttribute()
    {
        $carryOver = $this->attributes['carryover'] ?? 0;
        return ($this->hospital->monthly_remittance_target + $carryOver) - $this->amount_paid;
    }

}

