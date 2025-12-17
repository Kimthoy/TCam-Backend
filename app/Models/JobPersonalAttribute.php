<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPersonalAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'attribute_text',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
