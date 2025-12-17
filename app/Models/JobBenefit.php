<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobBenefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'benefit_title',
        'benefit_description',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
