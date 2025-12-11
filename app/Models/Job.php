<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'company',
        'location',
        'salary',
        'job_type',
        'experience',
        'description',
        'requirements',
        'benefits',
        'apply_email',
        'apply_link',
        'deadline',
        'is_active',
        'is_closed'
    ];

    protected $casts = [
        'deadline'   => 'date',
        'is_active'  => 'boolean',
        'is_closed'  => 'boolean',
    ];

    protected $appends = ['feature_image_url'];

    public function getFeatureImageUrlAttribute(): ?string
    {
        if (!$this->feature_image ?? null) {
            return null;
        }

        return Storage::disk('public')->url($this->feature_image);
    }

    // Scope: Only active, not closed, and deadline not passed
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('is_closed', false)
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', today());
            });
    }
}
