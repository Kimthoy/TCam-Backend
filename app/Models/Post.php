<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    // Option 1: Explicit fillable (recommended for clarity & security)
    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'content',
        'feature_image',
        'images',
        'is_active',
        'is_featured',
        'published_at',
    ];

    // Option 2: Or use guarded if you prefer (uncomment one only)
    // protected $guarded = ['id'];

    protected $casts = [
        'images'        => 'array',           // JSON → array
        'published_at'  => 'datetime',
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
    ];

    // Automatically append this accessor to JSON responses
    protected $appends = ['feature_image_url'];

    // Relationships
    // app/Models/Post.php
    // app/Models/Post.php
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            PostCategory::class,
            'post_category_pivot',
            'post_id',
            'post_category_id'
        );
    }
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    // scope for only active/published posts
    public function scopePublished($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }


    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Accessors
    public function getFeatureImageUrlAttribute(): ?string
    {
        if (!$this->feature_image) {
            return null;
        }

        return Storage::disk('public')->url($this->feature_image);
    }

    // Optional: Helper to get first image from images JSON array
    public function getFirstImageUrlAttribute(): ?string
    {
        return $this->images[0] ?? null
            ? Storage::disk('public')->url($this->images[0])
            : $this->feature_image_url;
    }
}
