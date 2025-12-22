<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhyJoinUs extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'section_tag',
        'section_title',
        'section_description',
        'items',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'items' => 'array', // automatically casts JSON to array
        'status' => 'boolean',
    ];
}
