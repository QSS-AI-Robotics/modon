<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;


class NotificationController extends Controller
{

    // public function fetchUserNotifications()
    // {
    //     try {
    //         $user = Auth::user();
    //         $userType = strtolower(optional($user->userType)->name);
        
    //         $regionIds = $user instanceof \App\Models\User
    //             ? $user->regions()->pluck('regions.id')->toArray()
    //             : [];
        
    //         $notifications = Notification::where(function ($q) use ($user, $userType, $regionIds) {
    //             $q->where('is_global', true)
    //               ->orWhereJsonContains('user_ids', $user->id)
    //               ->orWhereJsonContains('audience', $userType)
    //               ->orWhere(function ($query) use ($regionIds) {
    //                   foreach ($regionIds as $regionId) {
    //                       $query->orWhereJsonContains('region_ids', $regionId);
    //                   }
    //               });
    //         })
    //         ->latest()
    //         ->take(10)
    //         ->get();
        
    //         return response()->json($notifications);
    
    //     } catch (\Throwable $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
    

    public function fetchUserNotifications()
    {
        try {
            $user = Auth::user();
            $userId = $user->id;
            $userType = strtolower(optional($user->userType)->name);
        
            $regionIds = $user instanceof \App\Models\User
                ? $user->regions()->pluck('regions.id')->toArray()
                : [];
        
                $notifications = Notification::where(function ($q) use ($user, $userId) {
                    $q->where('is_global', true)
                      ->orWhereJsonContains('user_ids', $userId);
                })->latest()->take(10)->get();
        
            return response()->json($notifications);
    
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

