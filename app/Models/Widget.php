<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Widget extends Model
{
       use HasFactory;

    protected $fillable = [
        'app_name',
        'app_sort_desc',
        'abstract_desc',
        'app_logo',
        'contact_email',
        'contact_number',
        'contact_address',
        'contact_facebook',
        'contact_youtube',
        'contact_telegram',
        'footer_ownership',
    ];
  public function getAppLogoUrlAttribute(): ?string
    {
        if (!$this->app_logo) {
            return null;
        }

        return Storage::disk('public')->url($this->app_logo);
    }
}
