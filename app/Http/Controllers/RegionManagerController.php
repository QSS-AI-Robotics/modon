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
class RegionManagerController extends Controller
{

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
        } elseif ($userType === 'region_manager') {
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

    




    
 
public function approve(Request $request)
{
    $request->validate([
        'mission_id' => 'required',
        'decision'   => 'required|in:approve,reject',
    ]);

    $user = Auth::user();
    $userType = strtolower(optional($user->userType)->name);
    $missionId = $request->mission_id;
    $decision  = $request->decision === 'approve' ? 1 : 2;

    // ✅ Get mission (even soft-deleted)
    $mission = Mission::withTrashed()->find($missionId);
    if (! $mission) {
        Log::warning("❌ Mission not found for ID: $missionId");
        return response()->json(['message' => 'Mission not found.'], 404);
    }

    // ✅ Region access check for region_manager
    if ($userType === 'region_manager') {
        $regionIds = optional($user)->regions()->pluck('regions.id');
        if (! $regionIds->contains($mission->region_id)) {
            Log::warning("🚫 Unauthorized region_manager (User ID: $user->id) tried to approve mission in region {$mission->region_id}");
            return response()->json(['message' => 'You are not authorized to approve this mission.'], 403);
        }
    }

    // ✅ Determine approval column
    $approvalColumn = match ($userType) {
        'region_manager' => 'region_manager_approved',
        'modon_admin'    => 'modon_admin_approved',
        default => null,
    };

    if (! $approvalColumn) {
        Log::warning("❌ User type $userType is not allowed to approve.");
        return response()->json(['message' => 'User type not allowed to approve.'], 403);
    }

    // ✅ Update or create the mission approval record
    $approval = MissionApproval::firstOrNew(['mission_id' => $missionId]);
    $approval->{$approvalColumn} = $decision;
    $approval->save();

    // ✅ Refresh and evaluate approval status
    $approval->refresh();

    $isFullyApproved = 0;
    $newMissionStatus = 'Pending'; // default

    if (
        $approval->region_manager_approved == 2 ||
        $approval->modon_admin_approved == 2
    ) {
        $isFullyApproved = 2;
        $newMissionStatus = 'Rejected';
    } elseif (
        $approval->region_manager_approved == 1 &&
        $approval->modon_admin_approved == 1
    ) {
        $isFullyApproved = 1;
        $newMissionStatus = 'Approved';
    }

    // ✅ Log approval decision
    Log::info("📋 Mission #$missionId approval update by $userType (User ID: $user->id):");
    Log::info("➡️ $approvalColumn = $decision");
    Log::info("✅ is_fully_approved = $isFullyApproved");
    Log::info("📌 Mission status will be updated to: $newMissionStatus");

    // ✅ Save final states
    $approval->update(['is_fully_approved' => $isFullyApproved]);
    $mission->status = $newMissionStatus;
    $mission->save();

    return response()->json(['message' => 'Mission approval updated successfully.']);
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

            // ✅ Created By (user who created the mission)
            $mission->created_by = [
                'id'        => $mission->user->id   ?? null,
                'name'      => $mission->user->name ?? null,
                'user_type' => $mission->user->userType->name ?? null,
            ];

            // ✅ Locations
            $mission->locations = $mission->locations->map(fn($loc) => [
                'id'        => $loc->id,
                'name'      => $loc->name,
                'latitude'  => $loc->geoLocation->latitude  ?? null,
                'longitude' => $loc->geoLocation->longitude ?? null,
            ])->values();

            // ✅ Clean up unneeded relations
            unset($mission->approvals, $mission->pilot, $mission->user);
            return $mission;
        });

    return response()->json(['missions' => $missions]);
}

    // public function getmanagermissions()
    // {
    //     if (! Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access.'], 401);
    //     }
    
    //     $user     = Auth::user();
    //     $userType = optional($user->userType)->name ?? '';
    
    //     // ✅ Compute region IDs once
    //     $regionIds = $user instanceof User
    //         ? $user->regions()->pluck('regions.id')->toArray()
    //         : [];
    
    //     // ✅ Build query, only restrict by region_id when NOT admin
    //     $missions = Mission::query()
    //         ->when(
    //             ! in_array($userType, ['qss_admin', 'modon_admin']),
    //             fn($q) => $q->whereIn('region_id', $regionIds)
    //         )
    //         ->with([
    //             'inspectionTypes:id,name',
    //             'locations:id,name',
    //             'locations.geoLocation:location_id,latitude,longitude',
    //             'pilot:id,name',
    //             'approvals:id,mission_id,region_manager_approved,modon_admin_approved',
    //         ])
    //         ->get()
    //         ->map(function ($mission) {
    //             $mission->approval_status = [
    //                 'region_manager_approved' => $mission->approvals->region_manager_approved ?? null,
    //                 'modon_admin_approved'    => $mission->approvals->modon_admin_approved    ?? null,
    //             ];
    //             $mission->pilot_info = [
    //                 'id'   => $mission->pilot->id   ?? null,
    //                 'name' => $mission->pilot->name ?? null,
    //             ];
    //             $mission->locations = $mission->locations->map(fn($loc) => [
    //                 'id'        => $loc->id,
    //                 'name'      => $loc->name,
    //                 'latitude'  => $loc->geoLocation->latitude  ?? null,
    //                 'longitude' => $loc->geoLocation->longitude ?? null,
    //             ])->values();
    
    //             unset($mission->approvals, $mission->pilot);
    //             return $mission;
    //         });
    
    //     return response()->json(['missions' => $missions]);
    // }
    

   


    

   
   
    
    

    /**
     * Store a new mission.
     */
    public function storeMission(Request $request)
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

        // … rest of your logic unchanged …
        $mission->inspectionTypes()->sync([$request->inspection_type]);
        $mission->locations()->sync($request->locations);

        $regionApproved = $userType === 'region_manager';
        MissionApproval::create([
            'mission_id'                => $mission->id,
            'region_manager_approved'   => $regionApproved,
            'modon_admin_approved'      => false,
            'is_fully_approved'         => false,
        ]);

        // geo‐location saving…
        if (isset($request->locations[0])) {
            GeoLocation::updateOrCreate(
                ['location_id' => $request->locations[0]],
                ['latitude'    => $request->latitude,
                 'longitude'   => $request->longitude]
            );
        }

        // … return response …
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

    $regionIds = $user instanceof User
        ? $user->regions()->pluck('regions.id')->toArray()
        : [];

    $mission = Mission::with('approvals')->findOrFail($id);

    if (!in_array($mission->region_id, $regionIds)) {
        return response()->json(['error' => 'You are not authorized to delete this mission.'], 403);
    }

    $approval = $mission->approvals;

    $hasBeenApproved = $approval && (
        $approval->city_manager_approved ||
        $approval->region_manager_approved ||
        $approval->modon_admin_approved
    );

    Log::info('🧑‍💼 User attempting to delete approved mission', [
        'user_id' => $user->id,
        'user_type' => optional($user->userType)->name ?? 'N/A'
    ]);

    $isRegionManager = optional($user->userType)->name === 'region_manager';

    if ($hasBeenApproved && !$isRegionManager) {
        return response()->json([
            'error' => '❌ This mission has already been approved. Only the region manager can delete it now.'
        ], 403);
    }

    // ✅ Require delete reason from everyone
    if (!$request->delete_reason) {
        return response()->json([
            'error' => 'Please provide a reason for deleting this mission.'
        ], 422);
    }

    // ✅ Store the reason
    $mission->delete_reason = $request->delete_reason;
    $mission->deleted_by = $user->id;
    $mission->save();

    // ✅ Soft delete
    $mission->delete();

    return response()->json(['message' => '✅ Mission deleted successfully!']);
}

    // public function destroyMission($id)
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //     }
    
    //     $user = Auth::user();
    
    //     // ✅ Get all region IDs this user is assigned to
    //     $regionIds = $user instanceof \App\Models\User
    //         ? $user->regions()->pluck('regions.id')->toArray()
    //         : [];
    
    //     // ✅ Find mission with approvals
    //     $mission = Mission::with('approvals')->findOrFail($id);
    
    //     // ✅ Ensure user is assigned to this mission's region
    //     if (!in_array($mission->region_id, $regionIds)) {
    //         return response()->json(['error' => 'You are not authorized to delete this mission.'], 403);
    //     }
    
    //     // ✅ Get the approval record
    //     $approval = $mission->approvals;
    
    //     $hasBeenApproved = $approval && (
    //         $approval->city_manager_approved ||
    //         $approval->region_manager_approved ||
    //         $approval->modon_manager_approved
    //     );
    
    //     // ✅ If any approvals exist...
    //     if ($hasBeenApproved) {
    //         Log::info('🧑‍💼 User attempting to delete approved mission', [
    //             'user_id' => $user->id,
    //             'user_type' => optional($user->userType)->name ?? 'N/A'
    //         ]);
    //         $isRegionManager = optional($user->userType)->name === 'region_manager';
    
    //         if (!$isRegionManager) {
    //             return response()->json([
    //                 'error' => '❌ This mission has already been approved. Only the region manager can delete it now.'
    //             ], 403);
    //         }
    //     }
    
    //     // ✅ Passed all checks — soft delete
    //     $mission->delete();
    
    //     return response()->json(['message' => '✅ Mission deleted successfully!']);
    // }
    
  
     

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

    // ✅ Validate input (including geo coords)
    $request->validate([
        'mission_id'       => 'required|exists:missions,id',
        'inspection_type'  => 'required|exists:inspection_types,id',
        'mission_date'     => 'required|date',
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

    return response()->json(['message' => '✅ Mission updated successfully!']);
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
}
    

