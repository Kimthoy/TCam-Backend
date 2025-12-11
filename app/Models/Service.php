<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'category_id',
        'description',
        'feature_image',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function getImageUrlAttribute()
    {
        if (!$this->feature_image) {
            return null;
        }

        return Storage::disk('public')->url($this->feature_image);
    }
}
