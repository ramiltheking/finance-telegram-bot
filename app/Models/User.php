<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }
}
