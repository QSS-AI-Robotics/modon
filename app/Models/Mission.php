<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
class Mission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'mission_date', 'note', 'region_id', 'pilot_id','delete_reason'];


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
        // return $this->belongsTo(Region::class);
        return $this->belongsTo(Region::class, 'region_id');
    }
    public function approvals()
    {
        return $this->hasOne(MissionApproval::class);
    }
    
    /**
     * A mission has many locations.
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'mission_location');
    }
    public function pilot()
    {
        return $this->belongsTo(User::class, 'pilot_id');
    }
}
