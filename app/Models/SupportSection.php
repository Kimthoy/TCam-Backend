<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportSection extends Model
{
    protected $table = 'support_sections';

    protected $fillable = [
        'section_title',
        'section_description',
        'iso_certification',
        'is_active',
    ];
}
