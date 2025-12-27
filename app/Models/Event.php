<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class Event extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'event_date', 'location', 'category',
        'poster_image', 'description', 'participants', 'certifications', 'certificates', 'is_published'
    ];

    protected $casts = [
        'participants' => 'array',
        'certifications' => 'array',
        'certificates' => 'array',
        'is_published' => 'boolean',
        'event_date' => 'date',
    ];

    // Optional: Add poster_image_url accessor
    public function getPosterImageUrlAttribute()
    {
        return $this->poster_image ? asset("storage/{$this->poster_image}") : null;
    }
}
