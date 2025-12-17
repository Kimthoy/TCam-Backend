<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class ApplyCV extends Model
{
    //
     use HasFactory;

    protected $fillable = [
        'job_id',
        'first_name',
        'last_name',
        'gender',
        'position_apply',
        'email',
        'phone_number',
        'hear_about_job',
        'referral_name',
        'cv_file',
        'consent',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'consent' => 'boolean',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
    public function getCvFileAttribute($value)
    {
        return $value ? Storage::url($value) : null;
    }
}
