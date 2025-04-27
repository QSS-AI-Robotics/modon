<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'user_id',
        'audience',
        'region_ids',
        'user_ids',
        'is_global',
        'expires_at',
    ];
    
    protected $casts = [
        'audience'    => 'array',
        'region_ids'  => 'array',
        'user_ids'    => 'array',
        'is_global'   => 'boolean',
        'expires_at'  => 'datetime',
    ];

    public function users()
{
    return $this->belongsToMany(User::class, 'notification_user')
                ->withPivot('is_read')
                ->withTimestamps();
}
}
