<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    use HasFactory;

    protected $table = 'industries';

    protected $fillable = [
        'industry_name',
        'industry_description',
        'solutions',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'solutions' => 'array',
        'status' => 'boolean',
    ];
}
