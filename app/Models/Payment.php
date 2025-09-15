<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'inv_id',
        'user_id',
        'amount',
        'status',
        'payload'
    ];

    protected $casts = [
        'payload'=>'array',
    ];
}
