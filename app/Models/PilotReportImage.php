<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotReportImage extends Model
{
    use HasFactory;

    protected $fillable = ['pilot_report_id', 'image_path'];

    /**
     * An image belongs to a report.
     */
    public function report()
    {
        return $this->belongsTo(PilotReport::class);
    }
}
