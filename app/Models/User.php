<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type_id', // ✅ Foreign key
        'region_id',    // ✅ Foreign key
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

    /**
     * Relationship: A user belongs to a user type.
     */
    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    /**
     * Relationship: A user belongs to a region.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
