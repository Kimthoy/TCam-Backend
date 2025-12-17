<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    protected $table = 'about_us';

    protected $fillable = [
        'title',
        'company_image',
        'founding_year',
        'founders_info',
        'intro_text',
        'operational_offices',
        'services_description',
        'company_profile',
        'project_count',
        'vision',
        'mission',
        'value_proposition',
    ];

    protected $casts = [
        'operational_offices' => 'array', // automatically cast JSON to array
    ];
}
