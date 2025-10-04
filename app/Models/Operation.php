<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'currency',
        'category',
        'category_type',
        'description',
        'occurred_at',
        'meta',
        'status'
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'meta' => 'array',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'telegram_id');
    }

    public function systemCategory()
    {
        return $this->belongsTo(Category::class, 'category', 'slug');
    }

    public function userCategory()
    {
        return $this->belongsTo(UserCategory::class, 'category', 'name');
    }

    public function categoryRelation()
    {
        if ($this->category_type === 'system') {
            return $this->belongsTo(Category::class, 'category', 'slug');
        } else {
            return $this->belongsTo(UserCategory::class, 'category', 'name');
        }
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeCategoryType($query, $categoryType)
    {
        return $query->where('category_type', $categoryType);
    }

    public function getCategoryNameAttribute()
    {
        if ($this->category_type === 'system' && $this->categoryRelation) {
            return $this->categoryRelation->name_ru ?? $this->categoryRelation->name;
        } elseif ($this->category_type === 'custom' && $this->categoryRelation) {
            return $this->categoryRelation->name;
        }

        return $this->category;
    }
}
