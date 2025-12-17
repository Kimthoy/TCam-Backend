<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_title',
        'job_slug',
        'location',
        'closing_date',
        'hiring_number',
        'job_summary',
        'status',
    ];

    /**
     * ONE-TO-ONE relationships
     */
    public function qualification()
    {
        return $this->hasOne(JobQualification::class);
    }

    public function application_info()
    {
        return $this->hasOne(JobApplicationInfo::class);
    }

    /**
     * ONE-TO-MANY relationships (lists)
     */
    public function responsibilities()
    {
        return $this->hasMany(JobResponsibility::class);
    }

    public function benefits()
    {
        return $this->hasMany(JobBenefit::class);
    }

    public function certifications()
    {
        return $this->hasMany(JobCertification::class);
    }

    public function attributes()
    {
        return $this->hasMany(JobPersonalAttribute::class);
    }
   
}
