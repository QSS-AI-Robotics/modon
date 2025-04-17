<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'region_manager_approved',
        'modon_admin_approved',
        'is_fully_approved',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}
