<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'latitude', 'longitude', 'map_url', 'description'];

    /**
     * A location belongs to a region.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function locationAssignments()
    {
        return $this->hasMany(LocationAssignment::class);
    }
    public function users()
{
    return $this->belongsToMany(User::class, 'user_location');
}

}
