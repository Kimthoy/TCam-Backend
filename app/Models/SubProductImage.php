<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubProductImage extends Model
{
    protected $fillable = [
        'sub_product_id',
        'image_path',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function subProduct()
    {
        return $this->belongsTo(SubProduct::class);
    }
}
