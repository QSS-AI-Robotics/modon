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
        'start_datetime',
        'end_datetime',
        'video_url',
        'description'
    ];

    /**
     * A report belongs to a mission.
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * A report has multiple images.
     */
    public function images()
    {
        return $this->hasMany(PilotReportImage::class);
    }
}
