<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobQualification extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'education_level',
        'experience_required',
        'technical_skills',
        'soft_skills',
        'language_requirement',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
