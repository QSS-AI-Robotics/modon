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
            ])
            ->get()
            ->map(function ($mission) {
                $mission->approval_status = [
                    'region_manager_approved' => $mission->approvals->region_manager_approved ?? null,
                    'modon_admin_approved'    => $mission->approvals->modon_admin_approved    ?? null,
                ];
                $mission->pilot_info = [
                    'id'   => $mission->pilot->id   ?? null,
                    'name' => $mission->pilot->name ?? null,
                ];
                $mission->locations = $mission->locations->map(fn($loc) => [
                    'id'        => $loc->id,
                    'name'      => $loc->name,
                    'latitude'  => $loc->geoLocation->latitude  ?? null,
                    'longitude' => $loc->geoLocation->longitude ?? null,
                ])->values();
    
                unset($mission->approvals, $mission->pilot);
                return $mission;
            });
    
        return response()->json(['missions' => $missions]);
    }
    

    // public function getmanagermissions()
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //     }
    
    //     $user      = Auth::user();
    //     $regionIds = $user instanceof User
    //         ? $user->regions()->pluck('regions.id')
    //         : collect();
    
    //     $missions = Mission::whereIn('region_id', $regionIds)
    //         ->with([
    //             'inspectionTypes:id,name',
    //             'locations:id,name',
    //             // eager‑load geoLocation on each location
    //             'locations.geoLocation:location_id,latitude,longitude',
    //             'pilot:id,name',
    //             'approvals:id,mission_id,region_manager_approved,modon_admin_approved'
    //         ])
    //         ->get()
    //         ->map(function ($mission) {
    //             // build approval_status & pilot_info as before
    //             $mission->approval_status = [
    //                 'region_manager_approved' => $mission->approvals->region_manager_approved ?? null,
    //                 'modon_admin_approved'    => $mission->approvals->modon_admin_approved    ?? null,
    //             ];
    //             $mission->pilot_info = [
    //                 'id'   => $mission->pilot->id   ?? null,
    //                 'name' => $mission->pilot->name ?? null,
    //             ];
    
    //             // remap locations to include geoLocation
    //             $mission->locations = $mission->locations->map(function ($loc) {
    //                 return [
    //                     'id'        => $loc->id,
    //                     'name'      => $loc->name,
    //                     'latitude'  => $loc->geoLocation->latitude  ?? null,
    //                     'longitude' => $loc->geoLocation->longitude ?? null,
    //                 ];
    //             })->values();
    
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

    // public function storeMission(Request $request)
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //     }
    
    //     $request->validate([
    //         'inspection_type' => 'required|exists:inspection_types,id',
    //         'mission_date' => ['required', 'date', 'after_or_equal:today'],
    //         'note' => 'nullable|string',
    //         'locations' => 'required|array',
    //         'locations.*' => 'exists:locations,id',
    //         'pilot_id' => 'required|exists:users,id',
    //         'latitude' => 'required|numeric|between:-90,90',
    //         'longitude' => 'required|numeric|between:-180,180',
    //     ]);
    
    //     try {
    //         $user = Auth::user();
    //         $regionIds = $user instanceof \App\Models\User ? $user->regions()->pluck('regions.id') : collect();
    //         $regionId = $regionIds->first();
    //         $userId = $user->id;
    
    //         // ✅ Create the mission
    //         $mission = Mission::create([
    //             'mission_date' => $request->mission_date,
    //             'note' => $request->note,
    //             'region_id' => $regionId,
    //             'user_id' => $userId,
    //             'pilot_id' => $request->pilot_id,
    //         ]);
    
    //         // ✅ Sync inspection type and locations
    //         $mission->inspectionTypes()->sync([$request->inspection_type]);
    //         $mission->locations()->sync($request->locations);
    
    //         // ✅ Determine approval status
    //         $userType = optional($user->userType)->name;
    //         $regionApproved = false;
    
    //         if ($userType === 'region_manager') {
    //             $regionApproved = true;
    //         }
    
    //         // ✅ Create approval record (city_manager_approved removed)
    //         MissionApproval::create([
    //             'mission_id' => $mission->id,
    //             'region_manager_approved' => $regionApproved,
    //             'modon_admin_approved' => false,
    //             'is_fully_approved' => false,
    //         ]);
    
    //         // ✅ Fetch geo coordinates
    //         $geoLocations = GeoLocation::whereIn('location_id', $request->locations)
    //             ->get()
    //             ->map(function ($geo) {
    //                 return [
    //                     'location_id' => $geo->location_id,
    //                     'latitude' => $geo->latitude,
    //                     'longitude' => $geo->longitude,
    //                 ];
    //             });
    
    //         // ✅ Save/Update geo location for the first location
    //         if (isset($request->locations[0])) {
    //             $geo = GeoLocation::updateOrCreate(
    //                 ['location_id' => $request->locations[0]],
    //                 ['latitude' => $request->latitude, 'longitude' => $request->longitude]
    //             );
    
    //             Log::info('📍 Geo Location saved:', [
    //                 'location_id' => $geo->location_id,
    //                 'latitude' => $geo->latitude,
    //                 'longitude' => $geo->longitude
    //             ]);
    //         }
    
    //         return response()->json([
    //             'message' => 'Mission created successfully!',
    //             'mission' => [
    //                 'id' => $mission->id,
    //                 'inspection_type' => [
    //                     'id' => $request->inspection_type,
    //                     'name' => InspectionType::find($request->inspection_type)?->name
    //                 ],
    //                 'mission_date' => $mission->mission_date,
    //                 'locations' => $mission->locations->map(fn($loc) => ['id' => $loc->id, 'name' => $loc->name]),
    //                 'geo_locations' => $geoLocations,
    //             ]
    //         ], 201);
    
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Failed to create mission.',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    





    

    
    

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
    // public function updateMission(Request $request)
    // {
        

    //     Log::info('🔍 mission_id received:', ['id' => $request->all()]);
    //     Log::info("🚀 Incoming Mission Update Request", ['data' => $request->all()]);

    //     // ✅ Validate input
    //     $request->validate([
    //         'mission_id' => 'required|exists:missions,id',
    //         'inspection_type' => 'required|exists:inspection_types,id',
    //         'mission_date' => 'required|date',
    //         'note' => 'nullable|string',
    //         'locations' => 'required|array',
    //         'locations.*' => 'exists:locations,id',
    //         'pilot_id' => 'required|exists:users,id', // ✅ Validate pilot_id
    //     ]);

    //     // ✅ Find mission
    //     $mission = Mission::findOrFail($request->mission_id);

    //     // ✅ Update mission fields
    //     $mission->mission_date = $request->mission_date;
    //     $mission->note = $request->note ?? "";
    //     $mission->pilot_id = $request->pilot_id; // ✅ Update pilot
    //     $mission->save();

    //     // ✅ Sync inspection type (only one now)
    //     $mission->inspectionTypes()->sync([$request->inspection_type]);

    //     // ✅ Sync locations
    //     $mission->locations()->sync($request->locations);

    //     return response()->json(['message' => '✅ Mission updated successfully!']);
    // }



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
    

