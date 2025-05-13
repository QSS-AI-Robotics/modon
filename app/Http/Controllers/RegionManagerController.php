<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User; 
use App\Models\Mission;
use App\Models\InspectionType;
use App\Models\GeoLocation;
use Illuminate\Http\Request;
use App\Models\Region;   
use Illuminate\Support\Facades\Auth; // ✅ Import Auth facade
use Illuminate\Support\Facades\Log;
use App\Models\MissionApproval; 
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; 
use App\Services\GetEmailsService;
use Mpdf\Mpdf;
class RegionManagerController extends Controller
{


    public function getAllMissionsByUserType(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $userType = strtolower(optional($user->userType)->name ?? '');

        $regionIds = $user instanceof \App\Models\User
            ? $user->regions()->pluck('regions.id')->toArray()
            : [];

        $locationIds = $user instanceof \App\Models\User
            ? $user->assignedLocations()->pluck('locations.id')->toArray()
            : [];

        $statusFilter = strtolower($request->query('status', ''));
        $dateFilter = $request->query('date');

        Log::info("🔍 User: {$user->id}, Type: {$userType}");
        Log::info("📍 Regions: ", $regionIds);
        Log::info("📍 Locations: ", $locationIds);
        Log::info("🔍 Filters => Status {$statusFilter}, Date: {$dateFilter}");

        $missions = Mission::query()
            ->when($userType === 'region_manager', function ($q) use ($regionIds) {
                $q->whereIn('region_id', $regionIds);
            })
            ->when($userType === 'city_manager', function ($q) use ($regionIds, $locationIds) {
                $q->whereIn('region_id', $regionIds)
                ->whereHas('locations', fn($lq) => $lq->whereIn('locations.id', $locationIds));
            })
            ->when($dateFilter, function ($q) use ($dateFilter) {
                $q->whereDate('mission_date', $dateFilter);
            })
            ->when($statusFilter && $statusFilter !== 'all', function ($q) use ($statusFilter) {
                $q->whereRaw('LOWER(status) = ?', [strtolower($statusFilter)]);
            })
            ->with([
                'inspectionTypes:id,name',
                'locations:id,name',
                'locations.geoLocation:location_id,latitude,longitude',
                'locations.locationAssignments.region:id,name',
                'pilot:id,name,email',
                'approvals:id,mission_id,region_manager_approved,modon_admin_approved,pilot_approved,general_manager_approved',
                'user:id,name,user_type_id',
                'user.userType:id,name',
            ])
            ->orderBy('id', 'desc') // newest first
            ->paginate(9);

        $missions->getCollection()->transform(function ($mission) {
            $mission->approval_status = [
                'region_manager_approved' => $mission->approvals?->region_manager_approved ?? 0,
                'modon_admin_approved'    => $mission->approvals?->modon_admin_approved ?? 0,
                'pilot_approved'          => $mission->approvals?->pilot_approved ?? 0,
                'general_manager_approved'=> $mission->approvals?->general_manager_approved ?? 0,
            ];

            $mission->pilot_info = [
                'id'    => $mission->pilot->id    ?? null,
                'name'  => $mission->pilot->name  ?? null,
                'email' => $mission->pilot->email ?? null,
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

        Log::info("✅ Missions returned: " . $missions->count());

        foreach ($missions as $mission) {
            Log::info("📝 Mission ID: {$mission->id} | Approval Status:", $mission->approval_status);
        }
        
        return response()->json([
            'data' => $missions->items(),       // paginated mission data
            'current_page' => $missions->currentPage(),
            'last_page' => $missions->lastPage(),
            'per_page' => $missions->perPage(),
            'total' => $missions->total(),
        ]);
    }

    public function getAllMissionsByUserTypeNew(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    $userType = strtolower(optional($user->userType)->name ?? '');

    $regionIds = $user instanceof \App\Models\User
        ? $user->regions()->pluck('regions.id')->toArray()
        : [];

    $locationIds = $user instanceof \App\Models\User
        ? $user->assignedLocations()->pluck('locations.id')->toArray()
        : [];

    $statusFilter = strtolower($request->query('status', ''));
    $dateFilter = $request->query('date');
    $search = $request->query('search', ''); // New search parameter

    Log::info("🔍 User: {$user->id}, Type: {$userType}");
    Log::info("📍 Regions: ", $regionIds);
    Log::info("📍 Locations: ", $locationIds);
    Log::info("🔍 Filters => Status {$statusFilter}, Date: {$dateFilter}, Search: {$search}");

    $missions = Mission::query()
        ->when($userType === 'region_manager', function ($q) use ($regionIds) {
            $q->whereIn('region_id', $regionIds);
        })
        ->when($userType === 'city_manager', function ($q) use ($regionIds, $locationIds) {
            $q->whereIn('region_id', $regionIds)
                ->whereHas('locations', fn($lq) => $lq->whereIn('locations.id', $locationIds));
        })
        ->when($dateFilter, function ($q) use ($dateFilter) {
            $q->whereDate('mission_date', $dateFilter);
        })
        ->when($statusFilter && $statusFilter !== 'all', function ($q) use ($statusFilter) {
            $q->whereRaw('LOWER(status) = ?', [strtolower($statusFilter)]);
        })
        ->when($search, function ($q) use ($search) {
            $q->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%") // Example: mission name
                    ->orWhereHas('pilot', function ($pilotQuery) use ($search) {
                        $pilotQuery->where('name', 'LIKE', "%{$search}%"); // Example: pilot name
                    });
            });
        })
        ->with([
            'inspectionTypes:id,name',
            'locations:id,name',
            'locations.geoLocation:location_id,latitude,longitude',
            'locations.locationAssignments.region:id,name',
            'pilot:id,name,email',
            'approvals:id,mission_id,region_manager_approved,modon_admin_approved,pilot_approved',
            'user:id,name,user_type_id',
            'user.userType:id,name',
        ])
        ->orderBy('id', 'desc') // newest first
        ->paginate(9);

    $missions->getCollection()->transform(function ($mission) {
        $mission->approval_status = [
            'region_manager_approved' => $mission->approvals?->region_manager_approved ?? 0,
            'modon_admin_approved'    => $mission->approvals?->modon_admin_approved ?? 0,
            'pilot_approved'          => $mission->approvals?->pilot_approved ?? 0,
        ];

        $mission->pilot_info = [
            'id'    => $mission->pilot->id    ?? null,
            'name'  => $mission->pilot->name  ?? null,
            'email' => $mission->pilot->email ?? null,
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

    Log::info("✅ Missions returned: " . $missions->count());

    return response()->json([
        'data' => $missions->items(),       // paginated mission data
        'current_page' => $missions->currentPage(),
        'last_page' => $missions->lastPage(),
        'per_page' => $missions->perPage(),
        'total' => $missions->total(),
    ]);
}

   

   


    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('signin.form')->with('error', 'Please log in first.');
        }

        $user     = Auth::user();
        $userType = strtolower(optional($user->userType)->name ?? 'control');

        // —————— Region IDs for filtering pilots/locations ——————
        $regionIds = optional($user)->regions()->pluck('regions.id')->toArray();

        // —————— City‑level users: single assigned location ——————
        $location = in_array($userType, ['city_manager','city_supervisor'])
            ? $user->assignedLocations->first()
            : null;

        $locationData = $location
            ? ['id'=>$location->id,'name'=>$location->name]
            : null;

        // —————— Pilots ——————
        if (in_array($userType, ['modon_admin','qss_admin'])) {
            $pilots = User::whereHas('userType', fn($q)=> $q->where('name','pilot'))
                        ->get();
        } else {
            $pilots = User::whereHas('regions', fn($q) => $q->whereIn('regions.id',$regionIds))
                        ->whereHas('userType', fn($q)=> $q->where('name','pilot'))
                        ->get();
        }

   
        // —————— Locations ——————
        if (in_array($userType, ['modon_admin','qss_admin'])) {
            $locations = Location::with(['locationAssignments.region:id,name']) // load only id and name of region
                        ->select('id', 'name')
                        ->get();
        } elseif (in_array($userType, ['region_manager','general_manager'])) {
            $locations = Location::whereHas('locationAssignments', fn($q) =>
                                $q->whereIn('region_id', $regionIds))
                        ->with(['locationAssignments.region:id,name']) // eager-load region
                        ->select('id', 'name')
                        ->get();
        } else {
            $locations = collect();
        }


        // —————— Regions ——————
        if (in_array($userType, ['modon_admin','qss_admin'])) {
            // Exclude the special “all” region
            $regions = Region::where('name', '<>', 'all')
                            ->select('id','name')
                            ->get();
        } else {
            $regions = Region::whereIn('id', $regionIds)
                            ->where('name', '<>', 'all')
                            ->select('id','name')
                            ->get();
        }

        Log::info('📍 Regions passed to view:', $regions->pluck('name')->toArray());

        return view('missions.index', compact(
            'userType',
            'locationData',
            'locations',
            'pilots',
            'regions'
        ));
    }

//     public function approve(Request $request)
// {
//     $request->validate([
//         'mission_id'      => 'required',
//         'decision'        => 'required|in:approve,reject',
//         'rejection_note'  => 'nullable|string',
//     ]);

//     $user = Auth::user();
//     $userType = strtolower(optional($user->userType)->name);
//     $missionId = $request->mission_id;
//     $decisionValue = $request->decision === 'approve' ? 1 : 2;
//     $currentUserEmail = $user->email;

//     $mission = Mission::with([
//         'approvals',
//         'region:id,name',
//         'user:id,name,user_type_id',
//         'user.userType:id,name'
//     ])->findOrFail($missionId);

//     if (! $mission) {
//         return response()->json(['message' => 'Mission not found.'], 404);
//     }

//     $regionId = $mission->region_id;

//     // ✅ Get all location IDs in this region (using location_assignments)
//     $locationIds = \App\Models\LocationAssignment::where('region_id', $regionId)
//                     ->pluck('location_id')->unique()->toArray();

//     // ✅ Get location names (for reference/logging if needed)
//     $locationNames = \App\Models\Location::whereIn('id', $locationIds)->pluck('name')->toArray();

//     // ✅ Approval column mapping
//     $approvalColumn = match ($userType) {
//         'general_manager' => 'general_manager_approved',
//         'region_manager'  => 'region_manager_approved',
//         'modon_admin'     => 'modon_admin_approved',
//         'pilot'           => 'pilot_approved',
//         default => null,
//     };

//     if (! $approvalColumn && $userType !== 'city_manager') {
//         return response()->json(['message' => 'User type not allowed to approve.'], 403);
//     }

//     // ✅ Load or create approval record
//     $approval = MissionApproval::firstOrNew(['mission_id' => $missionId]);

//     if ($approvalColumn) {
//         $approval->{$approvalColumn} = $decisionValue;
//     }

//     // ✅ If rejected, record who and why
//     if ($decisionValue === 2) {
//         $approval->rejected_by = $user->id;
//         $approval->rejection_note = $request->rejection_note ?? null;
//         $mission->status = 'Rejected';
//         $mission->save();
//     }

//     // ✅ If pilot approved, set fully approved and update status
//     if ($userType === 'pilot' && $decisionValue === 1) {
//         $approval->is_fully_approved = 1;
//         $mission->status = 'Awaiting Report';
//         $mission->save();
//     }

//     $approval->save();

//     // ✅ --- Email logic per your hierarchy ---

//     $emailsToNotify = collect();

//     // If city manager approves, notify general managers
//     if ($userType === 'city_manager') {
//         $generalManagers = User::whereHas('userType', fn($q) => $q->where('name', 'general_manager'))->pluck('email');
//         $emailsToNotify = $generalManagers;
//     }

//     // If general manager approves, notify region managers for the region
//     if ($userType === 'general_manager') {
//         $regionManagers = User::whereHas('userType', fn($q) => $q->where('name', 'region_manager'))
//             ->whereHas('regions', fn($q) => $q->where('regions.id', $regionId))
//             ->pluck('email');
//         $emailsToNotify = $regionManagers;
//     }

//     // If region manager approves, notify modon admins
//     if ($userType === 'region_manager') {
//         $modonAdmins = User::whereHas('userType', fn($q) => $q->where('name', 'modon_admin'))->pluck('email');
//         $emailsToNotify = $modonAdmins;
//     }

//     // If modon admin approves, notify qss admins + pilot
//     if ($userType === 'modon_admin') {
//         $qssAdmins = User::whereHas('userType', fn($q) => $q->where('name', 'qss_admin'))->pluck('email');

//         $pilotEmail = User::where('id', $mission->pilot_id)->value('email');

//         $emailsToNotify = $qssAdmins;

//         if ($pilotEmail) {
//             $emailsToNotify = $emailsToNotify->push($pilotEmail);
//         }
//     }

//     // ✅ Logging for debugging
//     Log::info("✅ Mission #$missionId approved/rejected by $userType ($currentUserEmail).");
//     Log::info("✅ Locations in region: ", $locationNames);
//     Log::info("✅ Emails to notify: ", $emailsToNotify->unique()->toArray());

//     return response()->json([
//         'message' => 'Mission decision saved.',
//         'user_type' => $userType,
//         'current_user_email' => $currentUserEmail,
//         'mission_id' => $missionId,
//         'approval_column' => $approvalColumn,
//         'decision_value' => $decisionValue,
//         'approval_details' => $approval,
//         'locations' => $locationNames,
//         'allmails' => $emailsToNotify->unique()->values()
//     ]);
// }
public function approve(Request $request)
{
    $request->validate([
        'mission_id'      => 'required',
        'decision'        => 'required|in:approve,reject',
        'rejection_note'  => 'nullable|string',
    ]);

    $user = Auth::user();
    $userType = strtolower(optional($user->userType)->name);
    $missionId = $request->mission_id;
    $decisionValue = $request->decision === 'approve' ? 1 : 2;
    $currentUserEmail = $user->email;

    $mission = Mission::with([
        'approvals',
        'region:id,name',
        'user:id,name,user_type_id',
        'user.userType:id,name'
    ])->findOrFail($missionId);

    if (! $mission) {
        return response()->json(['message' => 'Mission not found.'], 404);
    }

    $regionId = $mission->region_id;

    // ✅ Approval column mapping
    $approvalColumn = match ($userType) {
        'general_manager' => 'general_manager_approved',
        'region_manager'  => 'region_manager_approved',
        'modon_admin'     => 'modon_admin_approved',
        'pilot'           => 'pilot_approved',
        default => null,
    };

    if (! $approvalColumn && $userType !== 'city_manager') {
        return response()->json(['message' => 'User type not allowed to approve.'], 403);
    }

    // ✅ Load or create approval record
    $approval = MissionApproval::firstOrNew(['mission_id' => $missionId]);

    if ($approvalColumn) {
        $approval->{$approvalColumn} = $decisionValue;
    }

    // ✅ If rejected, record who and why
    if ($decisionValue === 2) {
        $approval->rejected_by = $user->id;
        $approval->rejection_note = $request->rejection_note ?? null;
        $mission->status = 'Rejected';
        $mission->save();
    }

    // ✅ If pilot approved, set fully approved and update status
    if ($userType === 'pilot' && $decisionValue === 1) {
        $approval->is_fully_approved = 1;
        $mission->status = 'Awaiting Report';
        $mission->save();
    }

    $approval->save();

    // ✅ ---------------------------
    // ✅ Get filtered users using service
    // ✅ ---------------------------

    $emailsService = new \App\Services\GetEmailsService();
    $allMails = $emailsService->getUsersByMission($mission->id);

    // ✅ Current user hierarchy level
    $currentUserHierarchy = optional($user->userType)->hierarchy_level ?? 99;

    // ✅ Collect the possible hierarchy levels from the data (sorted ascending)
    $allHierarchies = collect($allMails)->pluck('hierarchy_level')->unique()->sort()->values();

    if ($request->decision === 'approve') {

        // ✅ Find the next IMMEDIATE higher role (smaller hierarchy_level)
        $index = $allHierarchies->search($currentUserHierarchy);  // Find current user's hierarchy index

        $nextLevel = null;
        if ($index !== false && $index > 0) {
            // The previous item in the sorted list is the next higher role
            $nextLevel = $allHierarchies[$index - 1];
        }

        Log::info("🔔 Next hierarchy level to notify (approve): " . ($nextLevel ?? 'None'));

        // ✅ Filter users of that exact next level (excluding current user)
        $filteredMails = collect($allMails)->filter(function ($u) use ($nextLevel, $currentUserEmail) {
            return $u['hierarchy_level'] === $nextLevel && $u['email'] !== $currentUserEmail;
        })->values();

    } else {
        // ✅ Rejected → Notify ALL lower roles (higher hierarchy_level numbers)
        $lowerLevels = $allHierarchies->filter(fn($level) => $level > $currentUserHierarchy);

        Log::info("🔔 Lower levels to notify (reject): ", $lowerLevels->toArray());

        $filteredMails = collect($allMails)->filter(function ($u) use ($lowerLevels, $currentUserEmail) {
            return $lowerLevels->contains($u['hierarchy_level']) && $u['email'] !== $currentUserEmail;
        })->values();
    }

    Log::info("📧 Final emails to notify:", $filteredMails->toArray());

    

    // ✅ Logging for debugging
    Log::info("✅ Mission #$missionId approved/rejected by $userType ($currentUserEmail).");
    Log::info("✅ Emails to notify: ", $filteredMails->toArray());

    return response()->json([
        'message' => 'Mission decision saved.',
        'user_name' => $user->name,
        'user_type' => $userType,
        'current_user_email' => $currentUserEmail,
        'mission_id' => $missionId,
        'rejection_note'=>  $request->rejection_note ?? null,
        'approval_column' => $approvalColumn,
        'decision_value' => $decisionValue,
        'approval_details' => $approval,
        'allmails' => $filteredMails,
    ]);
}



    
 


    
    public function getInspectionTypes()
    {
        $types = InspectionType::select('id', 'name', 'description')->get();

        return response()->json([
            'status' => 'success',
            'inspectionTypes' => $types
        ]);
    }

    // Get Locations Based on User's Region(s)
    public function getLocations()
    {
        $user = Auth::user();
        $regionIds = optional(Auth::user())->regions()->pluck('regions.id');
    

        $locations = Location::whereHas('locationAssignments', function ($query) use ($regionIds) {
            $query->whereIn('region_id', $regionIds);
        })
        ->with('locationAssignments.region:id,name')
        ->get();

        return response()->json([
            'status' => 'success',
            'locations' => $locations
        ]);
    }    
    


       /**
     * Display the missions page for the authenticated user's region.
     */
    public function getmanagermissions()
{
    if (! Auth::check()) {
        return response()->json(['error' => 'Unauthorized access.'], 401);
    }

    $user     = Auth::user();
    $userType = optional($user->userType)->name ?? '';

    // ✅ Compute region IDs once
    $regionIds = $user instanceof User
        ? $user->regions()->pluck('regions.id')->toArray()
        : [];

    Log::info("🔐 Logged in user: ID = {$user->id}, Type = {$userType}");
    Log::info("🌍 Region IDs assigned to user: ", $regionIds);

    // ✅ Build query, only restrict by region_id when NOT admin
    $missions = Mission::query()
        ->when(
            ! in_array($userType, ['qss_admin', 'modon_admin']),
            fn($q) => $q->whereIn('region_id', $regionIds)
        )
        ->with([
            'inspectionTypes:id,name',
            'locations:id,name',
            'locations.geoLocation:location_id,latitude,longitude',
            'locations.locationAssignments.region:id,name',
            'pilot:id,name',
            'approvals:id,mission_id,region_manager_approved,modon_admin_approved',
            'user:id,name,user_type_id',
            'user.userType:id,name',
        ])
        ->get()
        ->map(function ($mission) {
            // ✅ Approval Status
            $mission->approval_status = [
                'region_manager_approved' => $mission->approvals->region_manager_approved ?? null,
                'modon_admin_approved'    => $mission->approvals->modon_admin_approved    ?? null,
            ];

            // ✅ Pilot Info
            $mission->pilot_info = [
                'id'   => $mission->pilot->id   ?? null,
                'name' => $mission->pilot->name ?? null,
            ];

            // ✅ Created By
            $mission->created_by = [
                'id'        => $mission->user->id   ?? null,
                'name'      => $mission->user->name ?? null,
                'user_type' => $mission->user->userType->name ?? null,
            ];

            // ✅ Locations with region name from locationAssignments
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

    Log::info("📦 Total Missions fetched: " . $missions->count());
    Log::info("🧾 Mission IDs: ", $missions->pluck('id')->toArray());

    return response()->json(['missions' => $missions]);
}

public function storeMissionOldBilal(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    }

    $user     = Auth::user();
    $userType = optional($user->userType)->name;

    // ✅ Get the list of regions this user may assign to
    $allowedRegionIds = $user instanceof User
        ? $user->regions()->pluck('regions.id')->toArray()
        : [];

    $request->validate([
        'inspection_type' => 'required|exists:inspection_types,id',
        'mission_date'    => ['required','date','after_or_equal:today'],
        'note'            => 'nullable|string',
        'locations'       => 'required|array',
        'locations.*'     => 'exists:locations,id',
        'pilot_id'        => 'required|exists:users,id',
        'latitude'        => 'required|numeric|between:-90,90',
        'longitude'       => 'required|numeric|between:-180,180',
        'region_id'       => 'required|exists:regions,id',
    ]);

    $regionId = $request->region_id;

    // 🔒 Ensure non‑admins can only assign to their regions
    if (! in_array($userType, ['modon_admin','qss_admin'])
        && ! in_array($regionId, $allowedRegionIds)) {
        return response()->json([
            'error' => 'You are not allowed to assign a mission to that region.'
        ], 403);
    }

    try {
        // ✅ Create the mission with the supplied region_id
        $mission = Mission::create([
            'mission_date' => $request->mission_date,
            'note'         => $request->note,
            'region_id'    => $regionId,
            'user_id'      => $user->id,
            'pilot_id'     => $request->pilot_id,
        ]);

        $mission->inspectionTypes()->sync([$request->inspection_type]);
        $mission->locations()->sync($request->locations);

        $regionApproved = in_array($userType, ['region_manager', 'modon_admin']);
        $modonApproved  = $userType === 'modon_admin';
        $pilotApproved  = false; // always false on mission creation

        MissionApproval::create([
            'mission_id'              => $mission->id,
            'region_manager_approved' => $regionApproved,
            'modon_admin_approved'    => $modonApproved,
            'pilot_approved'          => $pilotApproved,
            'is_fully_approved'       => false,
        ]);

        // geo‐location saving…
        if (isset($request->locations[0])) {
            GeoLocation::updateOrCreate(
                ['location_id' => $request->locations[0]],
                ['latitude'    => $request->latitude,
                 'longitude'   => $request->longitude]
            );
        }

        // 📧 Fetch users with roles qss_admin, modon_admin, manager
        $adminUsers = DB::table('users')
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->whereIn('user_types.name', ['qss_admin', 'modon_admin', 'manager'])
            ->select('users.name', 'users.email', 'user_types.name as user_type_name')
            ->get();

        // 📧 Fetch city managers associated with the mission's locations
        $locationIds = $request->locations;

        $cityManagerUserIds = DB::table('user_location')
            ->whereIn('location_id', $locationIds)
            ->pluck('user_id');

        $cityManagers = DB::table('users')
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->whereIn('users.id', $cityManagerUserIds)
            ->where('user_types.name', '!=', 'pilot')
            ->select('users.name', 'users.email', 'user_types.name as user_type_name')
            ->get();

        // Combine admin users and city managers
        $allUsers = $adminUsers->merge($cityManagers)->unique('email')->values();

        return response()->json([
            'message' => 'Mission created successfully!',
            'mission' => [
                'id'              => $mission->id,
                'inspection_type' => [
                    'id'   => $request->inspection_type,
                    'name' => InspectionType::find($request->inspection_type)?->name,
                ],
                'mission_date'    => $mission->mission_date,
                'locations'       => $mission->locations->map(fn($l)=>['id'=>$l->id,'name'=>$l->name]),
                'allmails'  => $allUsers,
            ],
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error'   => 'Failed to create mission.',
            'message' => $e->getMessage(),
        ], 500);
    }
}
    /**
     * Store a new mission.
     */

     public function storeMission(Request $request)
     {
         if (!Auth::check()) {
             return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
         }
     
         $user = Auth::user();
         $userType = optional($user->userType)->name;
     
         // ✅ Get the list of regions this user can assign to
         $allowedRegionIds = $user instanceof User
             ? $user->regions()->pluck('regions.id')->toArray()
             : [];
     
         // ✅ Validation
         $request->validate([
             'inspection_type' => 'required|exists:inspection_types,id',
             'mission_date'    => ['required', 'date', 'after_or_equal:today'],
             'note'            => 'nullable|string',
             'locations'       => 'required|array',
             'locations.*'     => 'exists:locations,id',
             'pilot_id'        => 'required|exists:users,id',
             'latitude'        => 'required|numeric|between:-90,90',
             'longitude'       => 'required|numeric|between:-180,180',
             'region_id'       => 'required|exists:regions,id',
         ]);
     
         $regionId = $request->region_id;
     
         // ✅ Restrict non-admin users from assigning to unauthorized regions
         if (!in_array($userType, ['modon_admin', 'qss_admin']) && !in_array($regionId, $allowedRegionIds)) {
             return response()->json(['error' => 'You are not allowed to assign a mission to that region.'], 403);
         }
     
         try {
             // ✅ Create mission
             $mission = Mission::create([
                 'mission_date' => $request->mission_date,
                 'note'         => $request->note,
                 'region_id'    => $regionId,
                 'user_id'      => $user->id,
                 'pilot_id'     => $request->pilot_id,
             ]);
     
             // ✅ Attach inspection type and locations
             $mission->inspectionTypes()->sync([$request->inspection_type]);
             $mission->locations()->sync($request->locations);
     
             // ✅ Create approval record
             MissionApproval::create([
                 'mission_id'              => $mission->id,
                 'region_manager_approved' => in_array($userType, ['region_manager', 'modon_admin']),
                 'modon_admin_approved'    => $userType === 'modon_admin',
                 'pilot_approved'          => false,
                 'is_fully_approved'       => false,
             ]);
     
             // ✅ Save GeoLocation for first location
             if (isset($request->locations[0])) {
                 GeoLocation::updateOrCreate(
                     ['location_id' => $request->locations[0]],
                     [
                         'latitude'  => $request->latitude,
                         'longitude' => $request->longitude
                     ]
                 );
             }
     
             // ✅ Get region name for response
             $regionName = Region::where('id', $regionId)->value('name');
     
             $regionManagers = User::whereHas('userType', function ($q) {
                $q->where('name', 'region_manager');
            })
            ->whereHas('regions', function ($q) use ($regionId) {
                $q->where('regions.id', $regionId);
            })
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->select('users.name', 'users.email', 'user_types.hierarchy_level')
            ->get();
            
     
            //  return response()->json([
            //      'message' => 'Mission created successfully!',
            //      'mission' => [
            //          'id'              => $mission->id,
            //          'created_by' => [
            //              'name' => $user->name,
            //              'type' => $userType,
            //          ],
            //          'inspection_type' => [
            //              'id'   => $request->inspection_type,
            //              'name' => InspectionType::find($request->inspection_type)?->name,
            //          ],
            //          'region_name'     => $regionName,
            //          'mission_date'    => $mission->mission_date,
            //          'locations'       => $mission->locations->map(fn($l) => ['id' => $l->id, 'name' => $l->name]),
            //          'latitude'        => $request->latitude,
            //          'longitude'       => $request->longitude,
            //          'allmails'        => $regionManagers, // ✅ ONLY general managers' emails
            //      ],
            //  ], 201);
            return response()->json([
                'message' => 'Mission created successfully!',
                'mission' => [
                    'id' => $mission->id,
                    'created_by' => [
                        'name' => $user->name,
                        'type' => $userType,
                    ],
                    'inspection_type' => [
                        'id' => $request->inspection_type,
                        'name' => InspectionType::find($request->inspection_type)?->name,
                    ],
                    'region_name' => $regionName,
                    'mission_date' => $mission->mission_date,
                    'locations' => $mission->locations->map(function ($l) {
                        return [
                            'id' => $l->id,
                            'name' => $l->name,
                            'latitude' => $l->geoLocation->latitude ?? null,
                            'longitude' => $l->geoLocation->longitude ?? null,
                        ];
                    }),
                    'allmails' => $regionManagers, // ✅ ONLY general managers' emails
                ],
            ], 201);
            
     
         } catch (\Exception $e) {
             return response()->json([
                 'error'   => 'Failed to create mission.',
                 'message' => $e->getMessage(),
             ], 500);
         }
     }
     






   

    /**
     * Delete a mission.
     */

    public function destroyMission(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $user = Auth::user();
        $userType = strtolower(optional($user->userType)->name ?? '');
        $regionIds = $user instanceof User
            ? $user->regions()->pluck('regions.id')->toArray()
            : [];
    
        $mission = Mission::with([
            'approvals',
            'locations:id,name',
            'locations.geoLocation:location_id,latitude,longitude',
            'user:id,name,user_type_id',
            'user.userType:id,name',
            'region:id,name',
            'inspectionTypes:id,name'
        ])->findOrFail($id);
    
        // ✅ Extract mission details
        $regionName = $mission->region?->name;
        $createdBy = $mission->user?->name . ' (' . $mission->user?->userType?->name . ')';
        $locationIds = $mission->locations->pluck('id')->toArray();
        $locations = $mission->locations->map(function ($loc) {
            return [
                'name'      => $loc->name,
                'latitude'  => $loc->geoLocation->latitude ?? null,
                'longitude' => $loc->geoLocation->longitude ?? null,
            ];
        });

        $inspectionType = $mission->inspectionTypes->first()?->name ?? 'N/A';
    
        // ✅ Log mission details before deletion
        Log::info('🗺️ Mission Details Before Deletion', [
            'mission_id' => $mission->id,
            'region'     => $regionName,
            'created_by' => $createdBy,
            'locations'  => $locations,
        ]);
    
        // ✅ Allow modon_admin to bypass region check
        if ($userType !== 'modon_admin' && !in_array($mission->region_id, $regionIds)) {
            return response()->json(['error' => 'You are not authorized to delete this mission.'], 403);
        }
    
        $approval = $mission->approvals;
    
        $hasBeenApproved = $approval && (
            $approval->city_manager_approved ||
            $approval->region_manager_approved ||
            $approval->modon_admin_approved
        );
    
        Log::info('🧑‍💼 User attempting to delete mission', [
            'user_id'   => $user->id,
            'user_type' => $userType,
            'approved'  => $hasBeenApproved,
        ]);
    
        if ($hasBeenApproved && $userType !== 'modon_admin' && $userType !== 'region_manager') {
            return response()->json([
                'error' => '❌ This mission has already been approved. Only the region manager or modon admin can delete it.'
            ], 403);
        }
    
        if (!$request->delete_reason) {
            return response()->json([
                'error' => 'Please provide a reason for deleting this mission.'
            ], 422);
        }
    
        // ✅ Store delete metadata
        $mission->delete_reason = $request->delete_reason;
        $mission->deleted_by = $user->id;
        $mission->save();
    
        $mission->delete(); // Soft delete
        
 
  
        $emailsService = new GetEmailsService();
        $allmails = $emailsService->getUsersByMission($mission->id);
        $currentUserHierarchy = optional($user->userType)->hierarchy_level ?? 99;

        $filteredMails = collect($allmails)->filter(function ($u) use ($currentUserHierarchy, $user) {
            return $u['hierarchy_level'] >= $currentUserHierarchy
                && $u['email'] !== $user->email;   // Exclude current user
        })->values();
        


        return response()->json([
            'message' => '✅ Mission deleted successfully!',
            'mission' => [
                'id'              => $mission->id,
                'created_by' => [
                    'name' => $user->name,
                    'type' => $userType,
                ],
                'mission_date'    => $mission->mission_date,
                'region_name'     => $regionName,
                'deleted_by'      => $user->name . ' (' . $userType . ')',
                'deleted_reason'  => $request->delete_reason,
                'inspection_type' => $inspectionType,
                'locations'       => $locations,
                'allmails' => $filteredMails,
            ]
        ]);
    }
    
    // Delete a mission
    // edit a mission
    public function editMission($id)
    {
        // ✅ Fetch the mission details
        $mission = Mission::with(['inspectionTypes:id,name', 'locations:id,name'])->findOrFail($id);

        // ✅ Fetch all available inspection types and locations (for selection)
        $allInspectionTypes = InspectionType::all();
        $allLocations = Location::where('region_id', Auth::user()->region_id)->get();

        return response()->json([
            'mission' => $mission,
            'all_inspection_types' => $allInspectionTypes,
            'selected_inspections' => $mission->inspectionTypes,
            'all_locations' => $allLocations,
            'selected_locations' => $mission->locations
        ]);
    }
    // update a mission

    public function updateMission(Request $request)
    {
        Log::info("🔍 Incoming Mission Update Request", ['data' => $request->all()]);
        $user     = Auth::user();
        $userType = optional($user->userType)->name;
    
        // ✅ Validate input (including geo coords)
        $request->validate([
            'mission_id'       => 'required|exists:missions,id',
            'region_id' => 'required|exists:regions,id',
            'inspection_type'  => 'required|exists:inspection_types,id',
            'mission_date'     => ['required', 'date', 'after_or_equal:today'],
            'note'             => 'nullable|string',
            'locations'        => 'required|array',
            'locations.*'      => 'exists:locations,id',
            'pilot_id'         => 'required|exists:users,id',
            'latitude'         => 'required|numeric|between:-90,90',
            'longitude'        => 'required|numeric|between:-180,180',
        ]);
    
        // ✅ Find and update mission fields
        $mission = Mission::findOrFail($request->mission_id);
        $mission->mission_date = $request->mission_date;
        $mission->note         = $request->note ?? "";
        $mission->pilot_id     = $request->pilot_id;
        $mission->region_id    = $request->region_id;
        $mission->save();
    
        // ✅ Sync inspection type & locations
        $mission->inspectionTypes()->sync([$request->inspection_type]);
        $mission->locations()->sync($request->locations);
    
        // ✅ Update geo_location for the first selected location
        if (isset($request->locations[0])) {
            $geo = GeoLocation::updateOrCreate(
                ['location_id' => $request->locations[0]],
                [
                    'latitude'  => $request->latitude,
                    'longitude' => $request->longitude,
                ]
            );
            Log::info('📍 Geo Location updated:', [
                'location_id' => $geo->location_id,
                'latitude'    => $geo->latitude,
                'longitude'   => $geo->longitude,
            ]);
        }
    
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $regionId = $request->region_id;
    
        // ✅ Get the region name
        $regionName = Region::where('id', $regionId)->value('name');
    
        /**
         * ✅ Use GetEmailsService (replacing the old query blocks!)
         */
        $emailsService = new GetEmailsService();
        $allMails = $emailsService->getUsersByMission($mission->id);
    
        // ✅ Filter by hierarchy level (optional)
        $currentUserHierarchy = optional($user->userType)->hierarchy_level ?? 99;
    
        $filteredMails = collect($allMails)->filter(function ($u) use ($currentUserHierarchy, $user) {
            return $u['hierarchy_level'] >= $currentUserHierarchy && $u['email'] !== $user->email;
        })->values();
        


            return response()->json([
                'message' => '✅ Mission updated successfully!',
                'mission' => [
                    'id'              => $mission->id,
                    'region_name'     => $regionName,
                    'latitude'        => $latitude,
                    'longitude'       => $longitude,
                    'created_by' => [
                        'name' => $user->name,
                        'type'   => $userType,
                    ],
                    'inspection_type' => [
                        'id'   => $request->inspection_type,
                        'name' => InspectionType::find($request->inspection_type)?->name,
                    ],
                    'mission_date'    => $mission->mission_date,
                    'locations'       => $mission->locations->map(function ($l) use ($latitude, $longitude) {
                        return [
                            'id'        => $l->id,
                            'name'      => $l->name,
                            'latitude'  => $latitude,   // mission latitude
                            'longitude' => $longitude,  // mission longitude
                        ];
                    }),
                    'allmails' => $filteredMails,
                ],
            ]);

        
    }
    
   

    // public function getMissionStats()
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //     }

    //     $regionId = Auth::user()->region_id;

    //     // ✅ Count Total Missions in the Region
    //     $totalMissions = Mission::where('region_id', $regionId)->count();

    //     // ✅ Count Completed Missions in the Region
    //     $completedMissions = Mission::where('region_id', $regionId)
    //         ->where('status', 'Completed')
    //         ->count();

    //     // ✅ Return JSON Response
    //     return response()->json([
    //         'total_missions' => $totalMissions,
    //         'completed_missions' => $completedMissions
    //     ]);
    // }

        

    public function downloadMissionPDF(Request $request)
    {
        $data = $request->only(['owner', 'pilot', 'region', 'program', 'location', 'geo', 'description', 'images','missiondate']);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans', // DejaVu Sans supports Arabic
        ]);

        // Render view to HTML
        $html = view('pdf.mission_report', compact('data'))->render();

        // Write HTML to PDF
        $mpdf->WriteHTML($html);

        // Return PDF download
        return response($mpdf->Output('mission_report.pdf', 'I'))->header('Content-Type', 'application/pdf');
    }

//     public function downloadMissionPDF(Request $request)
// {
//     // Arabic test data for full field coverage
//     $data = [
//         'owner'       => 'محمد بن سلمان',
//         'pilot'       => 'مازن العتيبي',
//         'region'      => 'المنطقة الوسطى',
//         'program'     => 'مراقبة الأضرار على الطرق',
//         'location'    => 'الرياض - المدينة الصناعية الأولى',
//         'geo'         => '24.7136°N, 46.6753°E',
//         'description' => 'هذا نص تجريبي باللغة العربية للتأكد من دعم اللغة بشكل صحيح في ملف الـ PDF.',
//         'images'      => $request->images ?? []
//     ];

//     $mpdf = new Mpdf([
//         'mode' => 'utf-8',
//         'format' => 'A4',
//         'default_font' => 'dejavusans',
//         'directionality' => 'rtl', // optional, helps with general RTL support
//     ]);

//     // Render Blade view
//     $html = view('pdf.mission_report', compact('data'))->render();

//     // Write HTML to PDF
//     $mpdf->WriteHTML($html);

//     // Output the PDF inline
//     return response($mpdf->Output('mission_report.pdf', 'I'))
//         ->header('Content-Type', 'application/pdf');
// }
}
    

