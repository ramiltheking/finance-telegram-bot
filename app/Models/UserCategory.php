<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCategory extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'title'
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'telegram_id');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'category', 'name')
            ->where('category_type', 'custom');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
