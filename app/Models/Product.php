<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['feature_image_url'];

    protected $casts = [
    
        'is_published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }


    public function getFeatureImageUrlAttribute(): ?string
    {
        if (!$this->feature_image) {
            return null;
        }

        return Storage::disk('public')->url($this->feature_image);
    }

     public function subProducts()
    {
        return $this->hasMany(SubProduct::class);
    }
}
