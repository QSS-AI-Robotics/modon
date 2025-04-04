<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'latitude', 'longitude', 'map_url', 'description', 'region_id'];

    /**
     * A location belongs to a region.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
