<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplicationInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'email',
        'phone_number',
        'telegram_link',
        'note',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
