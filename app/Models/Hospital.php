<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
    */

    protected $fillable = [
        'hospital_id',
        'hospital_name',
        'military_division',
        'address',
        'phone_number',
        'hospital_remitter',
        'monthly_remittance_target',
    ];
}
