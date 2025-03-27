<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationLink extends Model
{
    protected $fillable = ['name', 'url', 'sort_order'];

    public function userTypes()
    {
        return $this->belongsToMany(UserType::class);
    }
}
