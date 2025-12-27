<?php

namespace App\Models\PartnerWithUs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerWithUsSection extends Model
{
    use HasFactory;

    protected $table = 'partner_with_us_sections';

    protected $fillable = [
        'title',
        'subtitle',
    ];

    public function cards()
    {
        return $this->hasMany(PartnerWithUsCard::class, 'section_id');
    }
}
