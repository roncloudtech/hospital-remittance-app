<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'ip_address',
    ];

    
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

