<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoLocation extends Model
{
    use HasFactory;

    protected $table = 'geo_location';

    protected $fillable = ['location_id', 'latitude', 'longitude'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

