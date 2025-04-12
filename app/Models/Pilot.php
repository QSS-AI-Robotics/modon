<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pilot extends Model
{
    protected $fillable = ['user_id', 'license_no', 'license_expiry'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
