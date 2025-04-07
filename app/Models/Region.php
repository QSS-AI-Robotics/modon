<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relationship: A region has many users.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function missions()
    {
        return $this->hasMany(\App\Models\Mission::class);
    }
}
