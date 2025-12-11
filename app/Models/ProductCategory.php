<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['products_count'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    // This is the CORRECT way — Laravel handles the query safely
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}
