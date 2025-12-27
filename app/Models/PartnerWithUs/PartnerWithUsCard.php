<?php

namespace App\Models\PartnerWithUs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerWithUsCard extends Model
{
    use HasFactory;

    protected $table = 'partner_with_us_cards';

    protected $fillable = [
        'section_id',
        'icon',
        'icon_color',
        'title',
        'description',
    ];

    public function section()
    {
        return $this->belongsTo(PartnerWithUsSection::class, 'section_id');
    }
}
