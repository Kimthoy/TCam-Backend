<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficePhone extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'phone_number',
        'label',
        'is_primary',
        'is_active',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
