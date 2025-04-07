<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drone extends Model
{
    use HasFactory;

    protected $fillable = ['model', 'sr_no', 'user_id'];

    /**
     * Relationship: Drone belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
