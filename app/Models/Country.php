<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_name',
        'icon_color',
        'display_order',
        'is_active',
    ];

    // Relationships
    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}
