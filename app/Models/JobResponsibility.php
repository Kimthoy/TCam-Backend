<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobResponsibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'responsibility_text',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
