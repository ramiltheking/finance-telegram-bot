<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'name_ru',
        'slug',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'category', 'slug')
            ->where('category_type', 'system');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
