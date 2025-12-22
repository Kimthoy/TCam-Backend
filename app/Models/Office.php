<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'office_name',
        'address',
        'city',
        'province',
        'display_order',
        'is_active',
    ];

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function phones()
    {
        return $this->hasMany(OfficePhone::class);
    }

    public function emails()
    {
        return $this->hasMany(OfficeEmail::class);
    }

    public function websites()
    {
        return $this->hasMany(OfficeWebsite::class);
    }
}
