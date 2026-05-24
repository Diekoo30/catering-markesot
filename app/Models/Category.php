<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ["name","description","unit","is_active","sort_order","enable_ahp_recommendation","enable_cross_sell"];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'enable_ahp_recommendation' => 'boolean',
        'enable_cross_sell' => 'boolean',
    ];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    protected static function booted()
    {
        static::deleting(function ($category) {
            $category->menuItems()->delete();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

}
