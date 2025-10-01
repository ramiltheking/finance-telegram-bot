<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telegram_payment_charge_id',
        'provider_payment_charge_id',
        'amount',
        'currency',
        'invoice_payload',
        'status',
        'paid_at',
        'failed_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'invoice_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'telegram_id');
    }
}
