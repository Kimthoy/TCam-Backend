<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'post_categories';

    protected $fillable = [
        'name',
        'description'
    ];

    // Relationship with posts
    public function posts()
    {
        return $this->belongsToMany(
            Post::class,
            'post_category_pivot',
            'post_category_id',
            'post_id'
        );
    }
}
