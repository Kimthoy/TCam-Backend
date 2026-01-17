<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubProduct extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'description',
        'price',
        'is_active',
    ];

    // 👇 Auto-include in JSON response
    protected $appends = ['feature_image_url'];

    // ========================
    // Relationships
    // ========================

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(SubProductImage::class);
    }

    public function properties()
    {
        return $this->hasMany(SubProductProperty::class);
    }

    // ========================
    // Accessors
    // ========================

    public function getFeatureImageUrlAttribute(): ?string
    {
        // Ensure images relation is loaded
        if (!$this->relationLoaded('images')) {
            $this->load('images');
        }

        // Pick primary image or fallback to first
        $image = $this->images
            ->firstWhere('is_primary', true)
            ?? $this->images->first();

        if (!$image) {
            return null;
        }

        return Storage::disk('public')->url($image->image_path);
    }
}
