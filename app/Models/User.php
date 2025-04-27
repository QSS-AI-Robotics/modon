<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
/**
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Region[] $regions
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',          // ✅ added here
        'user_type_id',
        'force_password_reset',
     
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
            'force_password_reset' => 'boolean', 
        ];
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function regions()
    {
        return $this->belongsToMany(Region::class, 'user_region');
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
    public function pilot()
    {
        return $this->hasOne(Pilot::class);
    }
    public function assignedRegions()
    {
        return $this->belongsToMany(Region::class, 'pilot_region');
    }

    public function assignedLocations()
    {
        return $this->belongsToMany(Location::class, 'user_location');
    }
    public function assignedMissions()
    {
        return $this->hasMany(Mission::class, 'pilot_id');
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }
}
