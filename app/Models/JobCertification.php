<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'certification_name',
        'is_required',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
