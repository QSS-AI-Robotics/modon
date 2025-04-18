<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_reference',
        'mission_id',
        'video_url',
        'description'
    ];
    /**
     * A report belongs to a mission.
     */
    public function mission()
    {
        // return $this->belongsTo(Mission::class);
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    /**
     * A report has multiple images.
     */
    public function images()
    {
        return $this->hasMany(PilotReportImage::class);
    }
}
