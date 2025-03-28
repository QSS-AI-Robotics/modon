<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relationship: A user type has many users.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function navigationLinks()
    {
        return $this->belongsToMany(NavigationLink::class);
    }
}
