<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    use HasFactory;

    protected $fillable = [
        'telegram_id','username','first_name','last_name','operations',
        'trial_started_at','trial_ends_at','subscription_status'
    ];

    protected $casts = [
        'operations' => 'array',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function operations() {
        return $this->hasMany(Operation::class);
    }
}
