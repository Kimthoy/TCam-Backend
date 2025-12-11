<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'link',
        'short_description',
        'logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['logo_url'];

    public function category()
    {
        return $this->belongsTo(CustomerCategory::class);
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? Storage::disk('public')->url($this->logo)
            : asset('images/placeholder-customer.png');
    }
}
