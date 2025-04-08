<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',          // ✅ added here
        'user_type_id',
        'region_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function drones()
    {
        return $this->hasMany(Drone::class);
    }

    /**
     * Get full image URL or default
     */
    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/users/' . $this->image) 
            : asset('images/default-user.png'); // Make sure this path exists
    }
    
}
