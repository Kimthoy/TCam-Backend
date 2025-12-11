<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'ip_address',
        'user_agent',
        'handled'
    ];

    protected $casts = [
        'handled' => 'boolean',
        'created_at' => 'datetime',
    ];
}
