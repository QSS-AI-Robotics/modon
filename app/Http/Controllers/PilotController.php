<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\Location;
use App\Models\PilotReport;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\InspectionType;
use App\Models\PilotReportImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // ✅ Import Log Facade

use Illuminate\Pagination\LengthAwarePaginator;
class PilotController extends Controller
{
    /**
     * Display the pilot's mission page.
     */
    public function index()
    {   
        $user     = Auth::user();
        $userType = strtolower(optional($user->userType)->name ?? 'control');

        return view('pilot.pilot', compact('userType','userType')); // ✅ Loads `pilot.blade.php`
    }


    public function pilotDecisionOldbkp(Request $request, Mission $mission)
    {
        $request->validate([
            'decision'       => 'required|in:approve,reject',
            'rejection_note' => 'nullable|string',
        ]);

        $pilot = Auth::user();

        if ($mission->pilot_id !== $pilot->id) {
            Log::warning("🚫 Unauthorized pilot (User ID: $pilot->id) tried to decide on mission #{$mission->id}");
            return response()->json(['message' => 'You are not authorized to respond to this mission.'], 403);
        }

        $decision = $request->decision === 'approve' ? 1 : 2;

        Log::info("🛩️ Pilot (ID: {$pilot->id}) submitted decision for Mission #{$mission->id}: " . ($decision === 1 ? 'Approved' : 'Rejected'));

        // ✅ Build approval data update
        $approvalData = [
            'is_fully_approved' => $decision,
            'pilot_approved'    => $decision,
        ];

        if ($decision === 2) {
            $approvalData['rejected_by']    = $pilot->id;
            $approvalData['rejection_note'] = $request->rejection_note ?? 'Rejected by pilot';
        }

        // ✅ Update or create the mission approval record
        $approval = \App\Models\MissionApproval::updateOrCreate(
            ['mission_id' => $mission->id],
            $approvalData
        );

        Log::info("✅ MissionApproval updated", [
            'mission_id'        => $approval->mission_id,
            'pilot_approved'    => $approval->pilot_approved,
            'is_fully_approved' => $approval->is_fully_approved,
            'rejected_by'       => $approval->rejected_by,
            'rejection_note'    => $approval->rejection_note,
        ]);

        // ✅ Update mission status accordingly
        // $mission->status = $decision === 1 ? 'Awaiting' : 'Rejected';
        $mission->status = $decision === 1 ? 'Awaiting Report' : 'Rejected';
        $mission->save();

        Log::info("✅ Mission status updated", [
            'mission_id' => $mission->id,
            'status'     => $mission->status,
        ]);

        return response()->json(['message' => 'Pilot decision recorded successfully.']);
    }

//     public function pilotDecision(Request $request, Mission $mission)
// {
//     $request->validate([
//         'decision'       => 'required|in:approve,reject',
//         'rejection_note' => 'nullable|string',
//     ]);

//     $pilot = Auth::user();
//     $currentUserEmail = $pilot->email;
//     $pilotName = $pilot->name;
//     if ($mission->pilot_id !== $pilot->id) {
//         Log::warning("🚫 Unauthorized pilot (User ID: $pilot->id) tried to decide on mission #{$mission->id}");
//         return response()->json(['message' => 'You are not authorized to respond to this mission.'], 403);
//     }

//     $decision = $request->decision === 'approve' ? 1 : 2;
//     $region_id = $mission->region_id;
//     Log::info("🛩️ Pilot (ID: {$pilot->id}) submitted decision for Mission #{$mission->id}: " . ($decision === 1 ? 'Approved' : 'Rejected'));
//     $users = DB::table('user_region')
//             ->join('users', 'user_region.user_id', '=', 'users.id')
//             ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
//             ->where('user_region.region_id', $mission->region_id)
//             ->where('user_types.name', '!=', 'pilot') // Exclude users with user_type_name "pilot"
//             ->select('users.id', 'users.email', 'user_types.name as user_type_name')
//             ->get();
    
//         $formattedUsers = $users->map(function ($user) {
//             return (array) $user; // Cast stdClass to array
//         })->toArray();
    
//         Log::info('👥 Users associated with the region (excluding pilots):', $formattedUsers);

//         $adminEmails = DB::table('users')
//             ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
//             ->whereIn('user_types.name', ['qss_admin', 'modon_admin']) // Filter by user type names
//             ->select('users.id', 'users.email', 'user_types.name as user_type_name')
//             ->get();

//     // ✅ Build approval data update
//     $approvalData = [
//         'is_fully_approved' => $decision,
//         'pilot_approved'    => $decision,
//     ];

//     if ($decision === 2) {
//         $approvalData['rejected_by']    = $pilot->id;
//         $approvalData['rejection_note'] = $request->rejection_note ?? 'Rejected by pilot';
//     }

//     // Simulate the approval record for debugging purposes
//     $approval = (object) array_merge($approvalData, [
//         'mission_id' => $mission->id,
//     ]);

//     Log::info("✅ Simulated MissionApproval", [
//         'mission_id'        => $approval->mission_id,
//         'region_id'         => $region_id,
//         'pilot_approved'    => $approval->pilot_approved,
//         'is_fully_approved' => $approval->is_fully_approved,
//         'rejected_by'       => $approval->rejected_by ?? null,
//         'rejection_note'    => $approval->rejection_note ?? null,
//     ]);

//     // Simulate mission status update for debugging purposes
//     $simulatedStatus = $decision === 1 ? 'Awaiting Report' : 'Rejected';

//     Log::info("✅ Simulated Mission status update", [
//         'mission_id' => $mission->id,
//         'status'     => $simulatedStatus,
//     ]);

//     // Uncomment the following lines after debugging to enable actual database updates
//     /*
//     // ✅ Update or create the mission approval record
//     $approval = \App\Models\MissionApproval::updateOrCreate(
//         ['mission_id' => $mission->id],
//         $approvalData
//     );

//     // ✅ Update mission status accordingly
//     $mission->status = $decision === 1 ? 'Awaiting Report' : 'Rejected';
//     $mission->save();
//     */

//     // ✅ Return all computed fields in the JSON response
//     return response()->json([
//         'message'           => 'Pilot decision recorded successfully (debug mode).',
//         'mission_id'        => $mission->id,
//         'region_id'         => $region_id,
//         'pilot_name'        => $pilotName,
//         'current_user_email' => $currentUserEmail,
//         'admin_emails'      => $adminEmails->toArray(),
//         'is_fully_approved' => $approval->is_fully_approved,
//         'users_associated_with_region' => $formattedUsers,
//         'pilot_approved'    => $approval->pilot_approved,
//         'rejected_by'       => $approval->rejected_by ?? null,
//         'rejection_note'    => $approval->rejection_note ?? null,
//         'status'            => $simulatedStatus
        
//     ]);
// }
public function pilotDecisionOld(Request $request, Mission $mission)
{
    $request->validate([
        'decision'       => 'required|in:approve,reject',
        'rejection_note' => 'nullable|string',
    ]);

    $pilot = Auth::user();
    $currentUserEmail = $pilot->email;
    $pilotName = $pilot->name;

    if ($mission->pilot_id !== $pilot->id) {
        Log::warning("🚫 Unauthorized pilot (User ID: $pilot->id) tried to decide on mission #{$mission->id}");
        return response()->json(['message' => 'You are not authorized to respond to this mission.'], 403);
    }

    $decision = $request->decision === 'approve' ? 1 : 2;
    $region_id = $mission->region_id;

    Log::info("🛩️ Pilot (ID: {$pilot->id}) submitted decision for Mission #{$mission->id}: " . ($decision === 1 ? 'Approved' : 'Rejected'));

    $users = DB::table('user_region')
        ->join('users', 'user_region.user_id', '=', 'users.id')
        ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
        ->where('user_region.region_id', $mission->region_id)
        ->where('user_types.name', '!=', 'pilot') // Exclude users with user_type_name "pilot"
        ->select('users.id', 'users.email', 'user_types.name as user_type_name')
        ->get();

    $formattedUsers = $users->map(function ($user) {
        return (array) $user; // Cast stdClass to array
    })->toArray();

    Log::info('👥 Users associated with the region (excluding pilots):', $formattedUsers);

    $adminEmails = DB::table('users')
        ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
        ->whereIn('user_types.name', ['qss_admin', 'modon_admin']) // Filter by user type names
        ->select('users.id', 'users.email', 'user_types.name as user_type_name')
        ->get();

    // ✅ Build approval data update
    $approvalData = [
        'is_fully_approved' => $decision,
        'pilot_approved'    => $decision,
    ];

    if ($decision === 2) {
        $approvalData['rejected_by']    = $pilot->id;
        $approvalData['rejection_note'] = $request->rejection_note ?? 'Rejected by pilot';
    }

    // ✅ Update or create the mission approval record
    $approval = \App\Models\MissionApproval::updateOrCreate(
        ['mission_id' => $mission->id],
        $approvalData
    );

    Log::info("✅ MissionApproval updated", [
        'mission_id'        => $approval->mission_id,
        'region_id'         => $region_id,
        'pilot_approved'    => $approval->pilot_approved,
        'is_fully_approved' => $approval->is_fully_approved,
        'rejected_by'       => $approval->rejected_by ?? null,
        'rejection_note'    => $approval->rejection_note ?? null,
    ]);

    // ✅ Update mission status accordingly
    $mission->status = $decision === 1 ? 'Awaiting Report' : 'Rejected';
    $mission->save();

    Log::info("✅ Mission status updated", [
        'mission_id' => $mission->id,
        'status'     => $mission->status,
    ]);

    // ✅ Return all computed fields in the JSON response
    return response()->json([
        'message'           => 'Pilot decision recorded successfully.',
        'mission_id'        => $mission->id,
        'region_id'         => $region_id,
        'pilot_name'        => $pilotName,
        'current_user_email' => $currentUserEmail,
        'admin_emails'      => $adminEmails->toArray(),
        'is_fully_approved' => $approval->is_fully_approved,
        'users_associated_with_region' => $formattedUsers,
        'pilot_approved'    => $approval->pilot_approved,
        'rejected_by'       => $approval->rejected_by ?? null,
        'rejection_note'    => $approval->rejection_note ?? null,
        'status'            => $mission->status,
    ]);
}
public function pilotDecision(Request $request, Mission $mission)
{
    $request->validate([
        'decision'       => 'required|in:approve,reject',
        'rejection_note' => 'nullable|string',
    ]);

    $pilot = Auth::user();
    $currentUserEmail = $pilot->email;
    $pilotName = $pilot->name;

    if ($mission->pilot_id !== $pilot->id) {
        Log::warning("🚫 Unauthorized pilot (User ID: $pilot->id) tried to decide on mission #{$mission->id}");
        return response()->json(['message' => 'You are not authorized to respond to this mission.'], 403);
    }

    $decision = $request->decision === 'approve' ? 1 : 2;
    $region_id = $mission->region_id;

    Log::info("🛩️ Pilot (ID: {$pilot->id}) submitted decision for Mission #{$mission->id}: " . ($decision === 1 ? 'Approved' : 'Rejected'));

    // ✅ Reload full mission with required relations
    $mission = Mission::with([
        'approvals',
        'locations:id,name',
        'locations.geoLocation:location_id,latitude,longitude',
        'user:id,name,user_type_id',
        'user.userType:id,name',
        'region:id,name',
        'inspectionTypes:id,name'
    ])->findOrFail($mission->id);

    // ✅ Use GetEmailsService instead of old manual queries
    $emailsService = new \App\Services\GetEmailsService();
    $allMails = $emailsService->getUsersByMission($mission->id);

    // ✅ Exclude current user from the emails
    $filteredMails = collect($allMails)->filter(function ($u) use ($currentUserEmail) {
        return $u['email'] !== $currentUserEmail;
    })->values();

    // ✅ Log for debugging
    Log::info("📧 Emails to notify (excluding pilot): ", $filteredMails->pluck('email')->toArray());

    // ✅ Build approval data update
    $approvalData = [
        'is_fully_approved' => $decision,
        'pilot_approved'    => $decision,
    ];

    if ($decision === 2) {
        $approvalData['rejected_by']    = $pilot->id;
        $approvalData['rejection_note'] = $request->rejection_note ?? 'Rejected by pilot';
    }

    // ✅ Update or create the mission approval record
    $approval = \App\Models\MissionApproval::updateOrCreate(
        ['mission_id' => $mission->id],
        $approvalData
    );

    Log::info("✅ MissionApproval updated", [
        'mission_id'        => $approval->mission_id,
        'region_id'         => $region_id,
        'pilot_approved'    => $approval->pilot_approved,
        'is_fully_approved' => $approval->is_fully_approved,
        'rejected_by'       => $approval->rejected_by ?? null,
        'rejection_note'    => $approval->rejection_note ?? null,
    ]);

    // ✅ Update mission status accordingly
    $mission->status = $decision === 1 ? 'Awaiting Report' : 'Rejected';
    $mission->save();

    Log::info("✅ Mission status updated", [
        'mission_id' => $mission->id,
        'status'     => $mission->status,
    ]);

    return response()->json([
        'message'           => 'Pilot decision recorded successfully.',
        'mission_id'        => $mission->id,
        'region_id'         => $region_id,
        'pilot_name'        => $pilotName,
        'current_user_email' => $currentUserEmail,
        'allmails'          => $filteredMails,
        'is_fully_approved' => $approval->is_fully_approved,
        'pilot_approved'    => $approval->pilot_approved,
        'rejected_by'       => $approval->rejected_by ?? null,
        'rejection_note'    => $approval->rejection_note ?? null,
        'status'            => $mission->status,
    ]);
}



// public function pilotDecision(Request $request, Mission $mission)
// {
//     $request->validate([
//         'decision'       => 'required|in:approve,reject',
//         'rejection_note' => 'nullable|string',
//     ]);

//     $pilot = Auth::user();
//     $currentUserEmail = $pilot->email;
//     $pilotName = $pilot->name;

//     if ($mission->pilot_id !== $pilot->id) {
//         Log::warning("🚫 Unauthorized pilot (User ID: $pilot->id) tried to decide on mission #{$mission->id}");
//         return response()->json(['message' => 'You are not authorized to respond to this mission.'], 403);
//     }

//     $decision = $request->decision === 'approve' ? 1 : 2;
//     $region_id = $mission->region_id;

//     Log::info("🛩️ Pilot (ID: {$pilot->id}) submitted decision for Mission #{$mission->id}: " . ($decision === 1 ? 'Approved' : 'Rejected'));

//     $mission = Mission::with([
//         'approvals',
//         'locations:id,name',
//         'locations.geoLocation:location_id,latitude,longitude',
//         'user:id,name,user_type_id',
//         'user.userType:id,name',
//         'region:id,name',
//         'inspectionTypes:id,name'
//     ])->findOrFail($request->mission_id);
//     $locationIds = $mission->locations->pluck('id')->toArray();
 

//       // 📧 Fetch users with roles qss_admin, modon_admin, manager
// $adminUsers = DB::table('users')
// ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
// ->whereIn('user_types.name', ['qss_admin', 'modon_admin', 'manager'])
// ->select('users.name', 'users.email', 'user_types.name as user_type_name')
// ->get();

// // 📧 Fetch city managers associated with the mission's locations

// $cityManagerUserIds = DB::table('user_location')
// ->whereIn('location_id', $locationIds)
// ->pluck('user_id');

// $cityManagers = DB::table('users')
// ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
// ->whereIn('users.id', $cityManagerUserIds)
// ->where('user_types.name', '!=', 'pilot')
// ->select('users.name', 'users.email', 'user_types.name as user_type_name')
// ->get();

// // 📧 Fetch all users related to the specific region (excluding pilots)
// $regionusers = DB::table('user_region')
// ->join('users', 'user_region.user_id', '=', 'users.id')
// ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
// ->where('user_region.region_id', $mission->region_id)
// ->where('user_types.name', 'region_manager') // <-- Only region_manager
// ->select('users.id','users.name','users.email', 'user_types.name as user_type_name')
// ->get();
// // return response()->json(['Region Manager' => $regionusers]);
// // 📝 Log the region managers only
// Log::info('👥 Region managers for the region:', $regionusers->toArray());
// foreach ($regionusers as $u) {
// Log::info('📧 Region manager email:', ['email' => $u->email]);
// }
// // 📝 Log individual region user emails
// foreach ($regionusers as $u) {
// Log::info('📧 Region user email:', ['email' => $u->email]);
// }

// // 📝 Log the city managers
// Log::info('🏙️ City managers for locations:', $cityManagers->toArray());
// // 📝 Log individual city manager emails
// foreach ($cityManagers as $u) {
// Log::info('📧 City manager email:', ['email' => $u->email]);
// }

// // 📝 Log the admin users
// Log::info('👤 Admin users:', $adminUsers->toArray());
// // 📝 Log individual admin user emails
// foreach ($adminUsers as $u) {
// Log::info('📧 Admin user email:', ['email' => $u->email]);
// }

// // Fetch the pilot's email
// $pilotEmail = DB::table('users')
// ->where('id', $mission->pilot_id)
// ->value('email');

// Log::info('✈ Pilot Email:', ['pilot_email' => $pilotEmail]);


// $regionandcityusers = $cityManagers
// ->merge($regionusers)
// ->unique('email')
// ->values();
// //return response()->json(['users' => $regionandcityusers]);
//     // ✅ Build approval data update
//     $approvalData = [
//         'is_fully_approved' => $decision,
//         'pilot_approved'    => $decision,
//     ];

//     if ($decision === 2) {
//         $approvalData['rejected_by']    = $pilot->id;
//         $approvalData['rejection_note'] = $request->rejection_note ?? 'Rejected by pilot';
//     }

//     // ✅ Update or create the mission approval record
//     $approval = \App\Models\MissionApproval::updateOrCreate(
//         ['mission_id' => $mission->id],
//         $approvalData
//     );

//     Log::info("✅ MissionApproval updated", [
//         'mission_id'        => $approval->mission_id,
//         'region_id'         => $region_id,
//         'pilot_approved'    => $approval->pilot_approved,
//         'is_fully_approved' => $approval->is_fully_approved,
//         'rejected_by'       => $approval->rejected_by ?? null,
//         'rejection_note'    => $approval->rejection_note ?? null,
//     ]);

//     // ✅ Update mission status accordingly
//     $mission->status = $decision === 1 ? 'Awaiting Report' : 'Rejected';
//     $mission->save();

//     Log::info("✅ Mission status updated", [
//         'mission_id' => $mission->id,
//         'status'     => $mission->status,
//     ]);

//    // ✅ Return all computed fields in the JSON response
//     return response()->json([
//         'message'           => 'Pilot decision recorded successfully.',
//         'mission_id'        => $mission->id,
//         'region_id'         => $region_id,
//         'pilot_name'        => $pilotName,
//         'current_user_email' => $currentUserEmail,
//         'admin_emails'      => $adminUsers,
//         'is_fully_approved' => $approval->is_fully_approved,
//         'users_associated_with_region' => $regionandcityusers,
//         'pilot_approved'    => $approval->pilot_approved,
//         'rejected_by'       => $approval->rejected_by ?? null,
//         'rejection_note'    => $approval->rejection_note ?? null,
//         'status'            => $mission->status,
//     ]);
// }
    /**
     * Fetch missions assigned to the pilot's region.
     */
    public function getAllApprovedMissionsByUserType(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    $userType = strtolower(optional($user->userType)->name ?? '');

    if ($userType !== 'pilot') {
        return response()->json(['error' => 'Forbidden. Only accessible for pilots.'], 403);
    }

    $regionIds = $user instanceof \App\Models\User
        ? $user->regions()->pluck('regions.id')->toArray()
        : [];

    Log::info("🔍 Pilot: {$user->id}");
    Log::info("📍 Regions: ", $regionIds);

    $perPage = $request->query('per_page', 10); // Optional: defaults to 10
    $missions = Mission::query()
        ->where('pilot_id', $user->id)
        ->whereIn('region_id', $regionIds)
        ->whereHas('approvals', function ($q) {
            $q->where('region_manager_approved', 1)
              ->where('modon_admin_approved', 1);
        })
        ->with([
            'inspectionTypes:id,name',
            'locations:id,name',
            'locations.geoLocation:location_id,latitude,longitude',
            'locations.locationAssignments.region:id,name',
            'pilot:id,name',
            'approvals:id,mission_id,region_manager_approved,modon_admin_approved,pilot_approved',
            'user:id,name,user_type_id',
            'user.userType:id,name',
        ])
        ->orderByDesc('id')
        ->paginate($perPage);

    $missions->getCollection()->transform(function ($mission) {
        $mission->approval_status = [
            'region_manager_approved' => $mission->approvals?->region_manager_approved ?? 0,
            'modon_admin_approved'    => $mission->approvals?->modon_admin_approved ?? 0,
            'pilot_approved'          => $mission->approvals?->pilot_approved ?? 0,
        ];

        $mission->pilot_info = [
            'id'   => $mission->pilot->id   ?? null,
            'name' => $mission->pilot->name ?? null,
        ];

        $mission->created_by = [
            'id'        => $mission->user->id   ?? null,
            'name'      => $mission->user->name ?? null,
            'user_type' => $mission->user->userType->name ?? null,
        ];

        $mission->locations = $mission->locations->map(function ($loc) {
            $region = $loc->locationAssignments->pluck('region')->filter()->first();
            return [
                'id'          => $loc->id,
                'name'        => $loc->name,
                'latitude'    => $loc->geoLocation->latitude  ?? null,
                'longitude'   => $loc->geoLocation->longitude ?? null,
                'region_id'   => $region?->id,
                'region_name' => $region?->name,
            ];
        })->values();

        unset($mission->approvals, $mission->pilot, $mission->user);
        return $mission;
    });

    Log::info("✅ Pilot Missions returned: " . $missions->count());

    return response()->json($missions);
}
    // public function getAllApprovedMissionsByUserType()
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }
    
    //     $user = Auth::user();
    //     $userType = strtolower(optional($user->userType)->name ?? '');
    
    //     if ($userType !== 'pilot') {
    //         return response()->json(['error' => 'Forbidden. Only accessible for pilots.'], 403);
    //     }
        
    //     $regionIds = $user instanceof \App\Models\User
    //              ? $user->regions()->pluck('regions.id')->toArray()
    //              : [];

     
    
    //     Log::info("🔍 Pilot: {$user->id}");
    //     Log::info("📍 Regions: ", $regionIds);
    
    //     $missions = Mission::query()
    //         ->where('pilot_id', $user->id)
    //         ->whereIn('region_id', $regionIds)
    //         ->whereHas('approvals', function ($q) {
    //             $q->where('region_manager_approved', 1)
    //               ->where('modon_admin_approved', 1);
    //         })
    //         ->with([
    //             'inspectionTypes:id,name',
    //             'locations:id,name',
    //             'locations.geoLocation:location_id,latitude,longitude',
    //             'locations.locationAssignments.region:id,name',
    //             'pilot:id,name',
    //             'approvals:id,mission_id,region_manager_approved,modon_admin_approved,pilot_approved',
    //             'user:id,name,user_type_id',
    //             'user.userType:id,name',
    //         ])
    //         ->get()
    //         ->map(function ($mission) {
    //             $mission->approval_status = [
    //                 'region_manager_approved' => $mission->approvals?->region_manager_approved ?? 0,
    //                 'modon_admin_approved'    => $mission->approvals?->modon_admin_approved    ?? 0,
    //                 'pilot_approved'          => $mission->approvals?->pilot_approved          ?? 0,
    //             ];
    
    //             $mission->pilot_info = [
    //                 'id'   => $mission->pilot->id   ?? null,
    //                 'name' => $mission->pilot->name ?? null,
    //             ];
    
    //             $mission->created_by = [
    //                 'id'        => $mission->user->id   ?? null,
    //                 'name'      => $mission->user->name ?? null,
    //                 'user_type' => $mission->user->userType->name ?? null,
    //             ];
    
    //             $mission->locations = $mission->locations->map(function ($loc) {
    //                 $region = $loc->locationAssignments->pluck('region')->filter()->first();
    //                 return [
    //                     'id'          => $loc->id,
    //                     'name'        => $loc->name,
    //                     'latitude'    => $loc->geoLocation->latitude  ?? null,
    //                     'longitude'   => $loc->geoLocation->longitude ?? null,
    //                     'region_id'   => $region?->id,
    //                     'region_name' => $region?->name,
    //                 ];
    //             })->values();
    
    //             unset($mission->approvals, $mission->pilot, $mission->user);
    //             return $mission;
    //         });
    
    //     Log::info("✅ Pilot Missions returned: " . $missions->count());
    
    //     return response()->json(['missions' => $missions]);
    // }
    
 


    public function getMissions()
    {
        // ✅ Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        // ✅ Get the authenticated pilot's region
        $user = Auth::user();
        $regionId = $user->region_id;
    
        Log::info("Fetching Missions for Region ID:", ['region_id' => $regionId, 'user_id' => $user->id]);
    
        // ✅ Fetch only missions for the pilot's region
        $missions = Mission::where('region_id', $regionId)
            ->with(['inspectionTypes:id,name', 'locations:id,name'])
            ->get();
    
        return response()->json(['missions' => $missions]);
    }
    

    public function getReports(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $user = Auth::user();
        $userType = optional($user->userType)->name ?? 'unknown';

        Log::info("📥 Reports Requested by User", [
            'user_id'   => $user->id,
            'user_type' => $userType,
            'mission_id' => $request->input('mission_id')
        ]);

        $missionId = $request->input('mission_id');

        $reports = PilotReport::when($missionId, function ($query) use ($missionId) {
                $query->where('mission_id', $missionId);
            })
            ->with([
                'mission',
                'images'
            ])
            ->get();

        return response()->json(['reports' => $reports]);
    }
    public function fetchReportByMission(Request $request)
    {
        $missionId = $request->input('mission_id');
    
        if (!$missionId) {
            return response()->json(['error' => 'Mission ID is required'], 422);
        }
    
        $report = PilotReport::with(['mission', 'images'])
            ->where('mission_id', $missionId)
            ->first();
    
        if (!$report) {
            return response()->json(['error' => 'No report found for this mission'], 404);
        }
    
        return response()->json(['report' => $report]);
    }
    
    // public function getReports(Request $request)
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //     }

    //     $missionId = $request->input('mission_id');

    //     $reports = PilotReport::when($missionId, function ($query) use ($missionId) {
    //             $query->where('mission_id', $missionId);
    //         })
    //         ->with([
    //             'mission',
    //             'images'
    //         ])
    //         ->get();

    //     return response()->json(['reports' => $reports]);
    // }

   
  

    /**
     * Store a new pilot report.
     */
    public function storeReportOld(Request $request)
     {
         Log::info("🚀 Incoming Report Submission");
         Log::info("📥 Request Data:", $request->except(['images_0']));
         Log::info("🖼 Uploaded Files:", $request->file('images_0') ?? []);
     
         $request->validate([
             'mission_id'   => 'required|exists:missions,id',
             'video_url'    => 'nullable|url',
             'description'  => 'nullable|string',
             'images_0.*'   => 'required|image|mimes:jpeg,png,jpg,gif|max:102048',
         ]);
     
         // ✅ Prevent double submission
         $mission = Mission::findOrFail($request->mission_id);
         if ($mission->report_submitted == 1) {
             Log::warning("⚠️ Report already submitted for Mission ID: {$mission->id}");
             return response()->json([
                 'message' => 'A report has already been submitted for this mission.'
             ], 409);
         }
     
         $reportReference = 'REP-' . Str::random(8);
         $report = PilotReport::create([
             'report_reference' => $reportReference,
             'mission_id'       => $mission->id,
             'video_url'        => $request->video_url,
             'description'      => $request->description,
         ]);
     
         Log::info("✅ Report Created", ['report_id' => $report->id]);
     
         if ($request->hasFile('images_0')) {
             $images = $request->file('images_0');
             Log::info("📸 Total images received: " . count($images));
     
             foreach ($images as $index => $image) {
                 $path = $image->store('reports', 'public');
     
                 $imageModel = PilotReportImage::create([
                     'pilot_report_id' => $report->id,
                     'image_path'      => "storage/$path",
                 ]);
     
                 Log::info("✅ Image Saved", [
                     'image_id'   => $imageModel->id,
                     'image_path' => $imageModel->image_path,
                 ]);
             }
         } else {
             Log::warning("⚠ No images uploaded with report.");
         }
     
         // ✅ Properly update mission status
         $mission->report_submitted = 1;
         $mission->status = 'Completed';
         $mission->save();
     
         Log::info("✅ Mission marked as completed", [
             'mission_id'         => $mission->id,
             'report_submitted'   => $mission->report_submitted,
             'status'             => $mission->status,
         ]);
     
         return response()->json([
             'message' => '✅ Report created successfully!',
             'report'  => $report
         ]);
     }
     public function storeReport(Request $request)
     {
         Log::info("🚀 Incoming Report Submission");
         Log::info("📥 Request Data:", $request->except(['images_0']));
         Log::info("🖼 Uploaded Files:", $request->file('images_0') ?? []);
     
         $request->validate([
             'mission_id'   => 'required|exists:missions,id',
             'video_url'    => 'nullable|url',
             'description'  => 'nullable|string',
             'images_0.*'   => 'required|image|mimes:jpeg,png,jpg,gif|max:102048',
         ]);
     
         // Collect missionData fields (optional, if you're using it elsewhere)
         $missionData = (object)[
             'createdBy'    => $request->createdBy,
             'geoLocations' => $request->geoLocations,
             'pilotname'    => $request->pilotname,
             'regionInfo'   => $request->regionInfo,
             'locationInfo' => $request->locationInfo,
             'dateInfo'     => $request->dateInfo,
             'programInfo'  => $request->programInfo,
         ];
     
         $user = Auth::user();
         $userType = strtolower(optional($user->userType)->name);
         $missionId = $request->mission_id;
         $currentUserEmail = $user->email;
     
         // ✅ Get the mission
         $mission = Mission::with([
             'approvals',
             'locations:id,name',
             'locations.geoLocation:location_id,latitude,longitude',
             'user:id,name,user_type_id',
             'user.userType:id,name',
             'region:id,name',
             'inspectionTypes:id,name'
         ])->findOrFail($missionId);
     
         // ✅ Prevent double submission
         if ($mission->report_submitted == 1) {
             Log::warning("⚠️ Report already submitted for Mission ID: {$mission->id}");
             return response()->json([
                 'message' => 'A report has already been submitted for this mission.'
             ], 409);
         }
     
         // ✅ Create the report
         $reportReference = 'REP-' . Str::random(8);
         $report = PilotReport::create([
             'report_reference' => $reportReference,
             'mission_id'       => $mission->id,
             'video_url'        => $request->video_url,
             'description'      => $request->description,
         ]);
     
         Log::info("✅ Report Created", ['report_id' => $report->id]);
     
         // ✅ Save uploaded images
         if ($request->hasFile('images_0')) {
             $images = $request->file('images_0');
             Log::info("📸 Total images received: " . count($images));
     
             foreach ($images as $index => $image) {
                 $path = $image->store('reports', 'public');
     
                 $imageModel = PilotReportImage::create([
                     'pilot_report_id' => $report->id,
                     'image_path'      => "storage/$path",
                 ]);
     
                 Log::info("✅ Image Saved", [
                     'image_id'   => $imageModel->id,
                     'image_path' => $imageModel->image_path,
                 ]);
             }
         } else {
             Log::warning("⚠ No images uploaded with report.");
         }
     
         // ✅ Update mission status
         $mission->report_submitted = 1;
         $mission->status = 'Completed';
         $mission->save();
     
         Log::info("✅ Mission marked as completed", [
             'mission_id'         => $mission->id,
             'report_submitted'   => $mission->report_submitted,
             'status'             => $mission->status,
         ]);
     
         // ✅ Fetch all relevant users using service (instead of manual DB queries!)
         $emailsService = new \App\Services\GetEmailsService();
         $allMails = $emailsService->getUsersByMission($mission->id);
     
         // ✅ Filtered mail list - remove current user
         $filteredMails = collect($allMails)->filter(function ($u) use ($currentUserEmail) {
             return $u['email'] !== $currentUserEmail;
         })->values();
     
         Log::info("📧 Users to notify (excluding current user): ", $filteredMails->toArray());
     
         return response()->json([
             'message' => '✅ Report created successfully!',
             'report'  => $report,
             'users_emails' => $filteredMails, // Use filtered emails
             'mission_data' => $missionData,
         ]);
     }
     
     
    //  public function storeReport(Request $request)
    //  {
    //      Log::info("🚀 Incoming Report Submission");
    //      Log::info("📥 Request Data:", $request->except(['images_0']));
    //      Log::info("🖼 Uploaded Files:", $request->file('images_0') ?? []);
     
    //      $request->validate([
    //          'mission_id'   => 'required|exists:missions,id',
    //          'video_url'    => 'nullable|url',
    //          'description'  => 'nullable|string',
    //          'images_0.*'   => 'required|image|mimes:jpeg,png,jpg,gif|max:102048',
    //      ]);
     
    //      // Collect all relevant fields into an object (stdClass or array)
    //      $missionData = (object)[
    //          'createdBy'    => $request->createdBy,
    //          'geoLocations' => $request->geoLocations,
    //          'pilotname'    => $request->pilotname,
    //          'regionInfo'   => $request->regionInfo,
    //          'locationInfo' => $request->locationInfo,
    //          'dateInfo'     => $request->dateInfo,
    //          'programInfo'  => $request->programInfo,
    //      ];
    //      $user = Auth::user();
    //      $userType = strtolower(optional($user->userType)->name);
    //      $missionId = $request->mission_id;
    //      $currentUserEmail = $user->email;
     
    //      // 📧 Fetch users with roles qss_admin, modon_admin, manager
    //      $adminUsers = DB::table('users')
    //          ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    //          ->whereIn('user_types.name', ['qss_admin', 'modon_admin', 'manager'])
    //          ->select('users.name', 'users.email', 'user_types.name as user_type_name')
    //          ->get();
     
    //      $mission = Mission::with([
    //          'approvals',
    //          'locations:id,name',
    //          'locations.geoLocation:location_id,latitude,longitude',
    //          'user:id,name,user_type_id',
    //          'user.userType:id,name',
    //          'region:id,name',
    //          'inspectionTypes:id,name'
    //      ])->findOrFail($request->mission_id);
     
    //      $locationIds = $mission->locations->pluck('id')->toArray();
     
    //      $cityManagerUserIds = DB::table('user_location')
    //          ->whereIn('location_id', $locationIds)
    //          ->pluck('user_id');
     
    //      $cityManagers = DB::table('users')
    //          ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    //          ->whereIn('users.id', $cityManagerUserIds)
    //          ->where('user_types.name', '!=', 'pilot')
    //          ->select('users.name', 'users.email', 'user_types.name as user_type_name')
    //          ->get();
     
    //      // 📧 Fetch all users related to the specific region (excluding pilots)
    //      $regionusers = DB::table('user_region')
    //          ->join('users', 'user_region.user_id', '=', 'users.id')
    //          ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    //          ->where('user_region.region_id', $mission->region_id)
    //          ->where('user_types.name', 'region_manager') // <-- Only region_manager
    //          ->select('users.id','users.name','users.email', 'user_types.name as user_type_name')
    //          ->get();
     
    //      // Merge currentUserEmail, adminUsers, cityManagers, regionusers into a single array
    //      $mergedUsers = collect();
    //      //$mergedUsers->push(['email' => $currentUserEmail, 'type' => 'current_user']);
    //      foreach ($adminUsers as $admin) {
    //          $mergedUsers->push(['email' => $admin->email, 'type' => $admin->user_type_name]);
    //      }
    //      foreach ($cityManagers as $cityManager) {
    //          $mergedUsers->push(['email' => $cityManager->email, 'type' => $cityManager->user_type_name]);
    //      }
    //      foreach ($regionusers as $regionUser) {
    //          $mergedUsers->push(['email' => $regionUser->email, 'type' => $regionUser->user_type_name]);
    //      }
    //      $mergedUsers = $mergedUsers->unique('email')->values();
     
    //      // Return for debugging BEFORE DB operations
    //     //  return response()->json([
    //     //      'debug' => [
    //     //          'merged_users' => $mergedUsers,
    //     //          'mission_data' => $missionData,
    //     //      ]
    //     //  ]);
     
       
    //      // --- Uncomment below for actual DB operations and response ---
     
    //      // ✅ Prevent double submission
    //      $mission = Mission::findOrFail($request->mission_id);
    //      if ($mission->report_submitted == 1) {
    //          Log::warning("⚠️ Report already submitted for Mission ID: {$mission->id}");
    //          return response()->json([
    //              'message' => 'A report has already been submitted for this mission.'
    //          ], 409);
    //      }
     
    //      $reportReference = 'REP-' . Str::random(8);
    //      $report = PilotReport::create([
    //          'report_reference' => $reportReference,
    //          'mission_id'       => $mission->id,
    //          'video_url'        => $request->video_url,
    //          'description'      => $request->description,
    //      ]);
     
    //      Log::info("✅ Report Created", ['report_id' => $report->id]);
     
    //      if ($request->hasFile('images_0')) {
    //          $images = $request->file('images_0');
    //          Log::info("📸 Total images received: " . count($images));
     
    //          foreach ($images as $index => $image) {
    //              $path = $image->store('reports', 'public');
     
    //              $imageModel = PilotReportImage::create([
    //                  'pilot_report_id' => $report->id,
    //                  'image_path'      => "storage/$path",
    //              ]);
     
    //              Log::info("✅ Image Saved", [
    //                  'image_id'   => $imageModel->id,
    //                  'image_path' => $imageModel->image_path,
    //              ]);
    //          }
    //      } else {
    //          Log::warning("⚠ No images uploaded with report.");
    //      }
     
    //      // ✅ Properly update mission status
    //      $mission->report_submitted = 1;
    //      $mission->status = 'Completed';
    //      $mission->save();
     
    //      Log::info("✅ Mission marked as completed", [
    //          'mission_id'         => $mission->id,
    //          'report_submitted'   => $mission->report_submitted,
    //          'status'             => $mission->status,
    //      ]);
     
    //      // --- Updated response: include merged_users and mission_data for reference ---
    //      return response()->json([
    //          'message' => '✅ Report created successfully!',
    //          'report'  => $report,
    //          'users_emails' => $mergedUsers,
    //          'mission_data' => $missionData,
    //      ]);
      
    //  }
     
    public function deleteMissionReport(Request $request)
    {
        $request->validate([
            'report_id' => 'required|exists:pilot_reports,id',
            'mission_id' => 'required|exists:missions,id',
        ]);
    
        $missionData = $this->getMissionJoinedData($request->mission_id);
        $user = Auth::user();
        $currentUserEmail = $user->email;
    
        $mission = Mission::with([
            'approvals',
            'locations:id,name',
            'locations.geoLocation:location_id,latitude,longitude',
            'user:id,name,user_type_id',
            'user.userType:id,name',
            'region:id,name',
            'inspectionTypes:id,name'
        ])->findOrFail($request->mission_id);
    
        // ✅ Use GetEmailsService instead of old queries
        $emailsService = new \App\Services\GetEmailsService();
        $allMails = $emailsService->getUsersByMission($mission->id);
    
        // ✅ Exclude the current user from the emails list
        $filteredMails = collect($allMails)->filter(function ($u) use ($currentUserEmail) {
            return $u['email'] !== $currentUserEmail;
        })->values();
    
        // ✅ Proceed to delete the report and images
        $report = PilotReport::with('images')->find($request->report_id);
    
        if (!$report) {
            return response()->json(['message' => 'Report not found.'], 404);
        }
    
        // Delete associated images
        foreach ($report->images as $image) {
            // Make sure to strip "storage/" if using Storage::delete
            $relativePath = str_replace('storage/', '', $image->image_path);
            if (Storage::exists($relativePath)) {
                Storage::delete($relativePath);
            }
            $image->delete();
        }
    
        // Delete the report
        $report->delete();
    
        // ✅ Update mission status
        $mission->status = 'Awaiting Report';
        $mission->report_submitted = 0;
        $mission->save();
    
        Log::info('✅ Report deleted', [
            'report_id' => $request->report_id,
            'mission_id' => $request->mission_id
        ]);
    
        return response()->json([
            'message' => 'Pilot report and images deleted successfully.',
            'users_emails' => $filteredMails,
            'mission_data' => $missionData,
        ]);
    }
    

    //  public function deleteMissionReport(Request $request)
    //  {
    //      $request->validate([
    //          'report_id' => 'required|exists:pilot_reports,id',
    //          'mission_id' => 'required|exists:missions,id',
    //      ]);
    //      $missionData = $this->getMissionJoinedData($request->mission_id);
    //             $regionId = $missionData['region']['id'];
    //             //return response()->json(['regionId'=>$regionId]);
    //             // 📧 Fetch users with roles qss_admin, modon_admin, manager
    //             $adminUsers = DB::table('users')
    //             ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    //             ->whereIn('user_types.name', ['qss_admin', 'modon_admin', 'manager'])
    //             ->select('users.name', 'users.email', 'user_types.name as user_type_name')
    //             ->get();

    //         $mission = Mission::with([
    //             'approvals',
    //             'locations:id,name',
    //             'locations.geoLocation:location_id,latitude,longitude',
    //             'user:id,name,user_type_id',
    //             'user.userType:id,name',
    //             'region:id,name',
    //             'inspectionTypes:id,name'
    //         ])->findOrFail($request->mission_id);

    //         $locationIds = $mission->locations->pluck('id')->toArray();

    //         $cityManagerUserIds = DB::table('user_location')
    //             ->whereIn('location_id', $locationIds)
    //             ->pluck('user_id');

    //         $cityManagers = DB::table('users')
    //             ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    //             ->whereIn('users.id', $cityManagerUserIds)
    //             ->where('user_types.name', '!=', 'pilot')
    //             ->select('users.name', 'users.email', 'user_types.name as user_type_name')
    //             ->get();

    //         // 📧 Fetch all users related to the specific region (excluding pilots)
    //         $regionusers = DB::table('user_region')
    //             ->join('users', 'user_region.user_id', '=', 'users.id')
    //             ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    //             ->where('user_region.region_id', $regionId)
    //             ->where('user_types.name', 'region_manager') // <-- Only region_manager
    //             ->select('users.id','users.name','users.email', 'user_types.name as user_type_name')
    //             ->get();

    //         // Merge currentUserEmail, adminUsers, cityManagers, regionusers into a single array
    //         $mergedUsers = collect();
    //         //$mergedUsers->push(['email' => $currentUserEmail, 'type' => 'current_user']);
    //         foreach ($adminUsers as $admin) {
    //             $mergedUsers->push(['email' => $admin->email, 'type' => $admin->user_type_name]);
    //         }
    //         foreach ($cityManagers as $cityManager) {
    //             $mergedUsers->push(['email' => $cityManager->email, 'type' => $cityManager->user_type_name]);
    //         }
    //         foreach ($regionusers as $regionUser) {
    //             $mergedUsers->push(['email' => $regionUser->email, 'type' => $regionUser->user_type_name]);
    //         }
    //         $mergedUsers = $mergedUsers->unique('email')->values();
    // //          return response()->json(["users_emails" => $mergedUsers,
    // //     "mission_data" => $missionData
    // // ]);
     
    //      $report = PilotReport::with('images')->find($request->report_id);
     
    //      if (!$report) {
    //          return response()->json(['message' => 'Report not found.'], 404);
    //      }
     
    //      // Delete associated images
    //      foreach ($report->images as $image) {
    //          Storage::delete($image->image_path);
    //          $image->delete();
    //      }
     
    //      // Delete the report
    //      $report->delete();
     
    //      // Update mission status
    //      $mission = Mission::find($request->mission_id);

    //      if ($mission) {
    //          $mission->status = 'Awaiting Report';
    //          $mission->report_submitted = 0;
    //          $mission->save();
    //      }
     
    //      Log::info('✅ Report deleted', [
    //          'report_id' => $request->report_id,
    //          'mission_id' => $request->mission_id
    //      ]);
     
    //      return response()->json([
    //          'message' => 'Pilot report and images deleted successfully.',
    //          "users_emails" => $mergedUsers,
    //         "mission_data" => $missionData,
    //      ]);
    //  }
     

    


    /**
     * Fetch a single report for editing.
     */
    public function editReport($id)
    {
        $report = PilotReport::with('images')->findOrFail($id);
    
        // ✅ Fetch associated incidents with images
        $incidents = $report->images->map(function ($image) {
            return [
                'id' => $image->id,
                'inspection_type_id' => $image->inspection_type_id,
                'location_id' => $image->location_id,
                'description' => $image->description,
                'images' => [$image->image_path], // Extend this if multiple images per incident
            ];
        });
    
        // ✅ Fetch only inspections from `mission_inspection_type`
        $inspections = InspectionType::whereIn('id', function ($query) use ($report) {
            $query->select('inspection_type_id')
                ->from('mission_inspection_type')
                ->where('mission_id', $report->mission_id);
        })->get(['id', 'name']);
    
        // ✅ Fetch only locations from `mission_location`
        $locations = Location::whereIn('id', function ($query) use ($report) {
            $query->select('location_id')
                ->from('mission_location')
                ->where('mission_id', $report->mission_id);
        })->get(['id', 'name']);
    
        return response()->json([
            'mission_id' => $report->mission_id,
            'start_datetime' => $report->start_datetime,
            'end_datetime' => $report->end_datetime,
            'video_url' => $report->video_url,
            'description' => $report->description,
            'incidents' => $incidents,
            'inspections' => $inspections,  // ✅ Only relevant inspections from `mission_inspection_type`
            'locations' => $locations,      // ✅ Only relevant locations from `mission_location`
        ]);
    }
    




    /**
     * Update an existing report.
     */
    public function updateReport(Request $request, $reportId)
    {
        Log::info("🚀 Incoming Report Update Request", [
            'report_id' => $reportId,
            'request_raw' => $request->all()
        ]);
    
        $requestData = json_decode($request->input('data'), true);
    
        if (!$requestData) {
            return response()->json(['error' => 'Invalid JSON format'], 400);
        }
    
        Log::info("📦 Parsed Structured Data", ['data' => $requestData]);
    
        $report = PilotReport::find($reportId);
        if (!$report) {
            return response()->json(['error' => 'Report not found'], 404);
        }
    
        $report->update([
            'start_datetime' => $requestData['start_datetime'],
            'end_datetime' => $requestData['end_datetime'],
            'video_url' => $requestData['video_url'] ?? null,
            'description' => $requestData['description'] ?? '',
        ]);
    
        Log::info("✅ Report fields updated", ['report_id' => $reportId]);
    
        // ✅ Fetch existing images from DB
        $existingImages = PilotReportImage::where('pilot_report_id', $reportId)->get()->keyBy('image_path');
        Log::info("🔍 Existing DB Images", ['images' => $existingImages->keys()]);
    
        $incomingPaths = [];
    
        foreach ($requestData['pilot_report_images'] as $item) {
    
            $existingPath = ltrim($item['previous_image'] ?? '', '/');
            $incomingPaths[] = $existingPath;
    
            // ✅ Existing image case
            if ($existingPath && $existingImages->has($existingPath)) {
    
                $image = $existingImages->get($existingPath);
    
                $image->update([
                    'inspection_type_id' => $item['inspection_id'],
                    'location_id' => $item['location_id'],
                    'description' => $item['description'] ?? '',
                ]);
    
                Log::info("🔄 Updated Image Record", ['image_path' => $existingPath]);
    
                // ✅ Check for replacement image
                if (!empty($item['new_images']) && $request->hasFile("images_{$item['index']}")) {
                    foreach ($request->file("images_{$item['index']}") as $file) {
    
                        $newPath = $file->store('reports', 'public');
    
                        if ($image->image_path !== "storage/$newPath") {
                            if (Storage::exists(str_replace('storage/', '', $image->image_path))) {
                                Storage::delete(str_replace('storage/', '', $image->image_path));
                            }
    
                            $image->update(['image_path' => "storage/$newPath"]);
                            Log::info("🖼️ Replaced Image File", ['new_path' => "storage/$newPath"]);
                        }
                    }
                }
    
            } 
            // ✅ New image case
            elseif (!empty($item['new_images']) && $request->hasFile("images_{$item['index']}")) {
    
                foreach ($request->file("images_{$item['index']}") as $file) {
    
                    $newPath = $file->store('reports', 'public');
    
                    PilotReportImage::create([
                        'pilot_report_id' => $report->id,
                        'inspection_type_id' => $item['inspection_id'],
                        'location_id' => $item['location_id'],
                        'description' => $item['description'] ?? '',
                        'image_path' => "storage/$newPath",
                    ]);
    
                    $incomingPaths[] = "storage/$newPath";
    
                    Log::info("🆕 New Image Added", ['path' => "storage/$newPath"]);
                }
            }
        }
    
        // ✅ Delete removed images
        foreach ($existingImages as $path => $image) {
            if (!in_array($path, $incomingPaths)) {
                if (Storage::exists(str_replace('storage/', '', $path))) {
                    Storage::delete(str_replace('storage/', '', $path));
                }
                $image->delete();
                Log::info("🗑️ Deleted Removed Image", ['image_path' => $path]);
            }
        }
    
        // ✅ Update mission report status
        $mission = Mission::findOrFail($report->mission_id);
    
        $mission->report_submitted = 1;
        $mission->status = 'Completed';
        $mission->save();
    
        Log::info("✅ Mission marked as completed", [
            'mission_id' => $mission->id,
            'report_submitted' => $mission->report_submitted,
            'status' => $mission->status,
        ]);
    
        // ✅ ✅ ✅ EMAILS: Use GetEmailsService (no more old queries!)
        $emailsService = new \App\Services\GetEmailsService();
        $allMails = $emailsService->getUsersByMission($mission->id);
    
        // ✅ Remove current user from emails list
        $user = Auth::user();
        $currentUserEmail = $user->email;
 
      
        $filteredMails = collect($allMails)->filter(function ($u) use ($currentUserEmail) {
            return $u['email'] !== $currentUserEmail;
        })->values();
    
        return response()->json([
            'message' => '✅ Report and images updated successfully',
            'report' => $report,
            'allmails' => $filteredMails,
        ]);
    }
    

//      public function updateReport(Request $request, $reportId)
// {
//     Log::info("🚀 Incoming Report Update Request", [
//         'report_id' => $reportId,
//         'request_raw' => $request->all()
//     ]);
//     $requestData = json_decode($request->input('data'), true);

//     if (!$requestData) {
//         return response()->json(['error' => 'Invalid JSON format'], 400);
//     }

//     Log::info("📦 Parsed Structured Data", ['data' => $requestData]);

//     $report = PilotReport::find($reportId);
//     if (!$report) {
//         return response()->json(['error' => 'Report not found'], 404);
//     }

//     $report->update([
//         'start_datetime' => $requestData['start_datetime'],
//         'end_datetime' => $requestData['end_datetime'],
//         'video_url' => $requestData['video_url'] ?? null,
//         'description' => $requestData['description'] ?? '',
//     ]);

//     Log::info("✅ Report fields updated", ['report_id' => $reportId]);

//     // Fetch existing images from DB
//     $existingImages = PilotReportImage::where('pilot_report_id', $reportId)->get()->keyBy('image_path');
//     Log::info("🔍 Existing DB Images", ['images' => $existingImages->keys()]);

//     // Keep track of all image paths from frontend
//     $incomingPaths = [];

//     foreach ($requestData['pilot_report_images'] as $item) {
//         $existingPath = ltrim($item['previous_image'] ?? '', '/');
//         $incomingPaths[] = $existingPath;

//         // If image already exists in DB (update case)
//         if ($existingPath && $existingPath !== 'N/A' && $existingImages->has($existingPath)) {
//             $image = $existingImages->get($existingPath);

//             $image->update([
//                 'inspection_type_id' => $item['inspection_id'],
//                 'location_id' => $item['location_id'],
//                 'description' => $item['description'] ?? '',
//             ]);
//             Log::info("🔄 Updated Image Record", ['image_path' => $existingPath]);

//             // Replace image if new one uploaded
//             if (!empty($item['new_images']) && $request->hasFile("images_{$item['index']}")) {
//                 foreach ($request->file("images_{$item['index']}") as $file) {
//                     $path = $file->store('reports', 'public');

//                     if ($image->image_path !== "storage/$path") {
//                         // Optionally delete old image file from storage
//                         if (Storage::exists(str_replace('storage/', '', $image->image_path))) {
//                             Storage::delete(str_replace('storage/', '', $image->image_path));
//                         }

//                         $image->update(['image_path' => "storage/$path"]);
//                         Log::info("🖼️ Replaced Image File", ['new_path' => "storage/$path"]);
//                     }
//                 }
//             }
//         }
//         // New Image Case
//         elseif (!empty($item['new_images']) && $request->hasFile("images_{$item['index']}")) {
//             foreach ($request->file("images_{$item['index']}") as $file) {
//                 $path = $file->store('reports', 'public');

//                 PilotReportImage::create([
//                     'pilot_report_id' => $report->id,
//                     'inspection_type_id' => $item['inspection_id'],
//                     'location_id' => $item['location_id'],
//                     'description' => $item['description'] ?? '',
//                     'image_path' => "storage/$path",
//                 ]);

//                 $incomingPaths[] = "storage/$path";
//                 Log::info("🆕 New Image Added", ['path' => "storage/$path"]);
//             }
//         }
//     }

//     // Delete records not present in request anymore
//     foreach ($existingImages as $path => $image) {
//         if (!in_array($path, $incomingPaths)) {
//             if (Storage::exists(str_replace('storage/', '', $path))) {
//                 Storage::delete(str_replace('storage/', '', $path));
//             }
//             $image->delete();
//             Log::info("🗑️ Deleted Removed Image", ['image_path' => $path]);
//         }
//     }

//     return response()->json(['message' => '✅ Report and images updated successfully']);
// }

public function updateMissionReport(Request $request)
{
    $request->validate([
        'report_id' => 'required|exists:pilot_reports,id',
        'description' => 'required|string',
        'video_url' => 'nullable|string',
        'removed_images' => 'nullable|string',
        'new_images.*' => 'nullable|image|max:5120',
    ]);
    // Use the helper function to get all joined data
    $missionData = $this->getMissionJoinedData($request->mission_id);
    $regionId = $missionData['region']['id'];
    //return response()->json(['regionId'=>$regionId]);
    // 📧 Fetch users with roles qss_admin, modon_admin, manager
    $adminUsers = DB::table('users')
    ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    ->whereIn('user_types.name', ['qss_admin', 'modon_admin', 'manager'])
    ->select('users.name', 'users.email', 'user_types.name as user_type_name')
    ->get();

$mission = Mission::with([
    'approvals',
    'locations:id,name',
    'locations.geoLocation:location_id,latitude,longitude',
    'user:id,name,user_type_id',
    'user.userType:id,name',
    'region:id,name',
    'inspectionTypes:id,name'
])->findOrFail($request->mission_id);

$locationIds = $mission->locations->pluck('id')->toArray();

$cityManagerUserIds = DB::table('user_location')
    ->whereIn('location_id', $locationIds)
    ->pluck('user_id');

$cityManagers = DB::table('users')
    ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    ->whereIn('users.id', $cityManagerUserIds)
    ->where('user_types.name', '!=', 'pilot')
    ->select('users.name', 'users.email', 'user_types.name as user_type_name')
    ->get();

// 📧 Fetch all users related to the specific region (excluding pilots)
$regionusers = DB::table('user_region')
    ->join('users', 'user_region.user_id', '=', 'users.id')
    ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
    ->where('user_region.region_id', $regionId)
    ->where('user_types.name', 'region_manager') // <-- Only region_manager
    ->select('users.id','users.name','users.email', 'user_types.name as user_type_name')
    ->get();

// Merge currentUserEmail, adminUsers, cityManagers, regionusers into a single array
$mergedUsers = collect();
//$mergedUsers->push(['email' => $currentUserEmail, 'type' => 'current_user']);
foreach ($adminUsers as $admin) {
    $mergedUsers->push(['email' => $admin->email, 'type' => $admin->user_type_name]);
}
foreach ($cityManagers as $cityManager) {
    $mergedUsers->push(['email' => $cityManager->email, 'type' => $cityManager->user_type_name]);
}
foreach ($regionusers as $regionUser) {
    $mergedUsers->push(['email' => $regionUser->email, 'type' => $regionUser->user_type_name]);
}
$mergedUsers = $mergedUsers->unique('email')->values();
    // return response()->json(["users_emails" => $mergedUsers,
    //     "mission_data" => $missionData
    // ]);
    $report = PilotReport::findOrFail($request->report_id);

    

    $report->description = $request->description;
    $report->video_url = $request->video_url;
    $report->save();

    // Only delete if real images are marked
    $idsToRemove = json_decode($request->removed_images, true);
    if (is_array($idsToRemove) && count($idsToRemove)) {
        foreach ($idsToRemove as $imageId) {
            $image = PilotReportImage::find($imageId);
            if ($image && $image->pilot_report_id == $report->id) {
                Storage::delete($image->image_path);
                $image->delete();
            }
        }
    }

    // Handle uploads
    if ($request->hasFile('new_images')) {
        foreach ($request->file('new_images') as $imageFile) {
            if ($imageFile->isValid()) {
                $path = $imageFile->store('pilot_reports', 'public');
                PilotReportImage::create([
                    'pilot_report_id' => $report->id,
                    'image_path' => 'storage/' . $path,
                ]);
            }
        }
    }

    $report->load('images');

    return response()->json([
        'message' => 'Report updated successfully!',
        "users_emails" => $mergedUsers,
        "mission_data" => $missionData,
        'report' => $report
    ]);
}



    
     
 




    
    /**
     * Delete a report.
     */

    public function destroyReport($id)
    {
        // ✅ Write a log before processing the deletion
        file_put_contents(storage_path('logs/debug_reportlog.txt'), "DELETE request received for report ID: $id\n", FILE_APPEND);

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $report = PilotReport::find($id);

        if (!$report) {
            return response()->json(['error' => 'Report not found.'], 404);
        }

        // ✅ Log deletion attempt
        file_put_contents(storage_path('logs/debug_reportlog.txt'), "Deleting report ID: $id\n", FILE_APPEND);

        // ✅ Delete images from storage and database
        $images = PilotReportImage::where('pilot_report_id', $report->id)->get();
        foreach ($images as $image) {
            $imagePath = public_path($image->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath); // ✅ Delete image from storage
            }
            $image->delete(); // ✅ Delete image record from database
        }

        // ✅ Store mission ID before deleting the report
        $missionId = $report->mission_id;

        // ✅ Delete the report
        $report->delete();

        // ✅ Check if there are still reports for this mission
        $remainingReports = PilotReport::where('mission_id', $missionId)->exists();

        // ✅ If no reports remain, update `report_submitted` to 0
        if (!$remainingReports) {
            // Mission::where('id', $missionId)->update(['report_submitted' => 0]);
            Mission::where('id', $missionId)->update([
                'report_submitted' => 0,
                'status' => 'Pending' // ✅ Also update status to 'Completed'
            ]);
            
            file_put_contents(storage_path('logs/debug_reportlog.txt'), "Mission ID $missionId updated: report_submitted = 0\n", FILE_APPEND);
        }

        // ✅ Confirm the report was deleted
        file_put_contents(storage_path('logs/debug_reportlog.txt'), "Report ID $id and its images successfully deleted.\n", FILE_APPEND);

        return response()->json([
            'message' => 'Report and relevant images deleted successfully. Mission updated if needed.',
            'status' => 'success'
        ], 200);
    }

    
        /**
     * Update mission status.
     */
    public function updateMissionStatus(Request $request)
    {
        $request->validate([
            'mission_id' => 'required|exists:missions,id',
            'status' => 'required|string'
        ]);

        $mission = Mission::findOrFail($request->mission_id);
        $mission->status = $request->status;
        $mission->save();

        return response()->json([
            'message' => 'Mission status updated successfully.',
            'mission' => $mission
        ]);
    }
    /**
 * Helper to fetch all mission-related joined data.
 */
protected function getMissionJoinedData($missionId)
{
    $mission = \App\Models\Mission::findOrFail($missionId);

    // 1. Missions table
    $user_id = $mission->user_id;
    $region_id = $mission->region_id;
    $mission_date = $mission->mission_date;
    $pilot_id = $mission->pilot_id;
    $pilot = Auth::user();
    $currentUserEmail = $pilot->email;
    $pilotName = $pilot->name;

    // 2. Regions table
    $region = \App\Models\Region::find($region_id);
    $region_name = $region ? $region->name : null;

    // 3. Mission Inspection Types and Inspection Types
    $inspectionTypeIds = DB::table('mission_inspection_type')
        ->where('mission_id', $mission->id)
        ->pluck('inspection_type_id');
    $inspectionTypes = \App\Models\InspectionType::whereIn('id', $inspectionTypeIds)
        ->pluck('name', 'id');

    // 4. Mission Locations and Locations
    $locationIds = DB::table('mission_location')
        ->where('mission_id', $mission->id)
        ->pluck('location_id');
    $locations = \App\Models\Location::whereIn('id', $locationIds)
        ->pluck('name', 'id');

    // 5. Geo Locations
    $geoLocations = DB::table('geo_location')
        ->whereIn('location_id', $locationIds)
        ->get(['location_id', 'latitude', 'longitude']);

    // 6. Users and User Types
    $user = \App\Models\User::find($user_id);
    $user_name = $user ? $user->name : null;
    $user_type_id = $user ? $user->user_type_id : null;
    $user_type = $user && $user->userType ? $user->userType->name : null;

    // Prepare response data
    return [
        'mission' => [
            'id' => $mission->id,
            'pilot_name' => $pilotName,
            'user_id' => $user_id,
            'region_id' => $region_id,
            'mission_date' => $mission_date,
            'pilot_id' => $pilot_id,
        ],
        'region' => [
            'id' => $region_id,
            'name' => $region_name,
        ],
        'inspection_types' => $inspectionTypes,
        'locations' => $locations,
        'geo_locations' => $geoLocations,
        'user' => [
            'id' => $user_id,
            'name' => $user_name,
            'user_type_id' => $user_type_id,
            'user_type' => $user_type,
        ],
    ];
}

}
