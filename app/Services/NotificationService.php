<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public static function create(array $data): Notification
    {
        $notification = Notification::create([
            'title'      => $data['title'] ?? 'Notification',
            'message'    => $data['message'] ?? '',
            'type'       => $data['type'] ?? 'general',
            'user_id'    => $data['user_id'] ?? null,
            'audience'   => $data['audience'] ?? null,
            'region_ids' => $data['region_ids'] ?? [],
            'user_ids'   => $data['user_ids'] ?? [],
            'is_global'  => $data['is_global'] ?? false,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return $notification;
    }
}
