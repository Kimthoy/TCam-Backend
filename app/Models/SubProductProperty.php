<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubProductProperty extends Model
{
    protected $fillable = [
        'sub_product_id',
        'key',
        'value',
    ];

    public function subProduct()
    {
        return $this->belongsTo(SubProduct::class);
    }
}
