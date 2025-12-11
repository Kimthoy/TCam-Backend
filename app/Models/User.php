<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = ['photo_url'];

    // =============================
    // PASSWORD HASHING
    // =============================
    public function setPasswordAttribute($value)
    {
        if (empty($value)) return;
        $this->attributes['password'] = Hash::needsRehash($value)
            ? Hash::make($value)
            : $value;
    }

    // =============================
    // PHOTO URL ACCESSOR
    // =============================
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . ltrim($this->photo, '/')) : null;
    }

    // =============================
    // JWT METHODS
    // =============================
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'email' => $this->email
        ];
    }
}
    