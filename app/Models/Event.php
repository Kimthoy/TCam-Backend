<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'title',
        'subtitle',
        'event_date',
        'location',
        'category',
        'poster_image',
        'description',
        'participants',
        'certifications',
        'certificates',
        'is_published',
    ];

    protected $casts = [
        'event_date'     => 'date',
        'participants'   => 'array',
        'certifications' => 'array',
        'certificates'   => 'array',
        'is_published'   => 'boolean',
    ];

    // Accessor to get full URL
    public function getPosterImageUrlAttribute(): ?string
    {
        return $this->poster_image
            ? Storage::disk('public')->url($this->poster_image)
            : null;
    }
}
