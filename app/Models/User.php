<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
        'trial_started_at',
        'trial_ends_at',
        'subscription_status',
    ];

    protected $casts = [
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }
}
