<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = ['start_datetime', 'end_datetime', 'note', 'region_id'];

    /**
     * A mission belongs to multiple inspection types.
     */
    public function inspectionTypes()
    {
        return $this->belongsToMany(InspectionType::class, 'mission_inspection_type');
    }
    

    /**
     * A mission belongs to a region.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * A mission has many locations.
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'mission_location');
    }
}
