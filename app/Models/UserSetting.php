<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'currency',
        'language',
        'timezone',
        'reminders_enabled',
        'reminder_hour',
        'reminder_minute'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'telegram_id');
    }
}
