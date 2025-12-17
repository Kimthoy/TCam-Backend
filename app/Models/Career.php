<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Career extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'job_title',
        'company',
        'location',
        'experience',
        'skills',
        'salary',
        'benefits',
        'description',
        'job_type',
        'feature_image',
        'contact_email',
        'contact_phone',
        'deadline',
        'featured',
        'education_level',
        'language_requirements',
        'slug',
        'status'
    ];
    protected $casts = [
        'deadline'   => 'date',
        'status'  => 'boolean',
    ];
    protected $appends = ['feature_image_url'];

    public function getFeatureImageUrlAttribute(): ?string
    {
        if (!$this->feature_image ?? null) {
            return null;
        }

        return Storage::disk('public')->url($this->feature_image);
    }

}
