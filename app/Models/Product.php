<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
     protected $fillable = [
        'title', 'slug', 'sku', 'price', 'main_image',
        'gallery_images', 'short_description', 'long_description',
        'meta_title', 'meta_description', 'meta_keywords',
        'is_active', 'is_variable'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'is_active' => 'boolean',
        'is_variable' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }
}
