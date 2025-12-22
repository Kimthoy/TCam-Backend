<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// app/Models/Banner.php
class Banner extends Model
{
    use SoftDeletes;

        protected $fillable = [
            'title',
            'subtitle',
            'image',
            'link',
            'status',
            'page', 
        ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = ['image_url', 'is_active'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // Add this accessor
    public function getIsActiveAttribute()
    {
        return (bool) $this->status;
    }

    public function scopePublished($query)
    {
        return $query->where('status', true);
    }
}
