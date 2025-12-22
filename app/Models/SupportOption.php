<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportOption extends Model
{
    protected $table = 'support_options';

    protected $fillable = [
        'option_title',
        'option_description',
        'display_order',
        'is_active',
    ];
}
