<?php

namespace App\Services;

use App\Models\User;
use App\Models\Mission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // 👈 Add this for logging

class GetEmailsService
{
    /**
     * Get all relevant users for a mission:
     * City managers (locations), region managers, general managers, modon admins, qss admins, and pilot.
     */
    public function getUsersByMission(int $missionId)
    {
        // ✅ 1️⃣ Get mission with region, locations, pilot
        $mission = Mission::withTrashed() // Include soft-deleted missions
            ->with(['region', 'pilot'])
            ->findOrFail($missionId);
    
        $regionId = $mission->region_id;
    
        Log::info("📌 Mission ID: {$missionId} | Region ID: {$regionId}");
    
        // ✅ 2️⃣ Get location IDs from mission_location table directly
        $locationIds = DB::table('mission_location')
            ->where('mission_id', $missionId)
            ->pluck('location_id')
            ->toArray();
    
        Log::info("📍 Location IDs: ", $locationIds);
    
        // ✅ 3️⃣ City Managers (assigned to those locations)
        $cityManagers = User::whereHas('userType', function ($q) {
                $q->where('name', 'city_manager');
            })
            ->whereHas('assignedLocations', function ($q) use ($locationIds) {
                $q->whereIn('locations.id', $locationIds);
            })
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->select('users.name','users.email','user_types.name as role','user_types.hierarchy_level')
            ->get();
    
        Log::info('👨‍💼 City Managers:', $cityManagers->toArray());
    
        // ✅ 4️⃣ Region Managers
        $regionManagers = User::whereHas('userType', function ($q) {
                $q->where('name', 'region_manager');
            })
            ->whereHas('regions', function ($q) use ($regionId) {
                $q->where('regions.id', $regionId);
            })
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->select('users.name','users.email','user_types.name as role','user_types.hierarchy_level')
            ->get();
    
        Log::info('👨‍💼 Region Managers:', $regionManagers->toArray());
    
        // ✅ 5️⃣ General Managers
        $generalManagers = User::whereHas('userType', function ($q) {
                $q->where('name', 'general_manager');
            })
            ->whereHas('regions', function ($q) use ($regionId) {
                $q->where('regions.id', $regionId);
            })
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->select('users.name','users.email','user_types.name as role','user_types.hierarchy_level')
            ->get();
    
        Log::info('👨‍💼 General Managers:', $generalManagers->toArray());
    
        // ✅ 6️⃣ Modon Admins
        $modonAdmins = User::whereHas('userType', function ($q) {
                $q->where('name', 'modon_admin');
            })
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->select('users.name','users.email','user_types.name as role','user_types.hierarchy_level')
            ->get();
    
        Log::info('👨‍💼 Modon Admins:', $modonAdmins->toArray());
    
        // ✅ 7️⃣ QSS Admins
        $qssAdmins = User::whereHas('userType', function ($q) {
                $q->where('name', 'qss_admin');
            })
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->select('users.name','users.email','user_types.name as role','user_types.hierarchy_level')
            ->get();
    
        Log::info('👨‍💼 QSS Admins:', $qssAdmins->toArray());
    
        // ✅ 8️⃣ Pilot
        $pilot = User::where('users.id', $mission->pilot_id)
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->select('users.name', 'users.email', DB::raw("'pilot' as role"), 'user_types.hierarchy_level')
            ->get();
    
        Log::info('🛩️ Pilot:', $pilot->toArray());
    
        // ✅ 9️⃣ Convert all to array before merging to avoid collection key conflicts!
        $allUsers = collect(array_merge(
            $cityManagers->toArray(),
            $regionManagers->toArray(),
            $generalManagers->toArray(),
            $modonAdmins->toArray(),
            $qssAdmins->toArray(),
            $pilot->toArray()
        ))
        ->unique('email')
        ->sortBy('hierarchy_level')
        ->values();
    
        Log::info('✅ Final combined user list:', $allUsers->toArray());
    
        return $allUsers;
    }
    
}
