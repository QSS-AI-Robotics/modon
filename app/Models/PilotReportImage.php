<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotReportImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pilot_report_id',
        'inspection_type_id', // ✅ Add this
        'location_id', // ✅ Add this
        'description', // ✅ Add this
        'image_path',
    ];

    /**
     * An image belongs to a report.
     */
    public function report()
    {
        return $this->belongsTo(PilotReport::class);
    }

    /**
     * An image belongs to an inspection type.
     */
    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class, 'inspection_type_id');
    }

    /**
     * An image belongs to a location.
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
