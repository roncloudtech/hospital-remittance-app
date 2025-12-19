<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Hospital extends Model
{
    use SoftDeletes, Notifiable;
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

    public function remitter()
    {
        return $this->belongsTo(User::class, 'hospital_remitter');
    }

}
