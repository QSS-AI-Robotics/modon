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
        Log::info("🔍 Filters => Status: {$statusFilter}, Date: {$dateFilter}");
    
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
                'approvals:id,mission_id,region_manager_approved,modon_admin_approved,pilot_approved',
                'user:id,name,user_type_id',
                'user.userType:id,name',
            ])
            ->get()
            ->map(function ($mission) {
                $mission->approval_status = [
                    'region_manager_approved' => $mission->approvals?->region_manager_approved ?? 0,
                    'modon_admin_approved'    => $mission->approvals?->modon_admin_approved    ?? 0,
                    'pilot_approved'          => $mission->approvals?->pilot_approved          ?? 0,
                ];
    
                $mission->pilot_info = [
                    'id'   => $mission->pilot->id   ?? null,
                    'name' => $mission->pilot->name ?? null,
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
    
        return response()->json(['missions' => $missions]);
    }
    

    // public function getAllMissionsByUserType()
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }
    
    //     $user = Auth::user();
    //     $userType = strtolower(optional($user->userType)->name ?? '');
    //     $regionIds = $user instanceof \App\Models\User
    //     ? $user->regions()->pluck('regions.id')->toArray()
    //     : [];
    
    //     $locationIds = $user instanceof \App\Models\User
    //         ? $user->assignedLocations()->pluck('locations.id')->toArray()
    //         : [];
    
    //     Log::info("🔍 User: {$user->id}, Type: {$userType}");
    //     Log::info("📍 Regions: ", $regionIds);
    //     Log::info("📍 Locations: ", $locationIds);
    
    //     $missions = Mission::query()
    //         ->when($userType === 'region_manager', function ($q) use ($regionIds) {
    //             $q->whereIn('region_id', $regionIds);
    //         })
    //         ->when($userType === 'city_manager', function ($q) use ($regionIds, $locationIds) {
    //             $q->whereIn('region_id', $regionIds)
    //               ->whereHas('locations', fn($lq) => $lq->whereIn('locations.id', $locationIds));
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
    //                 'pilot_approved'         => $mission->approvals?->pilot_approved    ?? 0,
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
    
    //    Log::info("✅ Missions returned: " . $missions->count());
    
    //     return response()->json(['missions' => $missions]);
    // }
    
















    
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
            'mission_id'      => 'required',
            'decision'        => 'required|in:approve,reject',
            'rejection_note'  => 'nullable|string',
        ]);
    
        $user = Auth::user();
        $userType = strtolower(optional($user->userType)->name);
        $missionId = $request->mission_id;
        $decision  = $request->decision === 'approve' ? 1 : 2;
    
        $mission = Mission::withTrashed()->find($missionId);
        if (! $mission) {
            Log::warning("❌ Mission not found for ID: $missionId");
            return response()->json(['message' => 'Mission not found.'], 404);
        }
    
        if ($userType === 'region_manager') {
            $regionIds = optional($user)->regions()->pluck('regions.id');
            if (! $regionIds->contains($mission->region_id)) {
                Log::warning("🚫 Unauthorized region_manager (User ID: $user->id) tried to approve mission in region {$mission->region_id}");
                return response()->json(['message' => 'You are not authorized to approve this mission.'], 403);
            }
        }
    
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
    
        // ✅ If rejected, record the rejecting user and note
        if ($decision === 2) {
            $approval->rejected_by    = $user->id;
            $approval->rejection_note = $request->rejection_note ?? null;
        }
    
        $approval->save();
Log::info("📋 $userType approved mission #$missionId with value: $decision");

// ✅ Retrieve all columns of the mission_approvals table for the specific mission
$approvalDetails = MissionApproval::where('mission_id', $missionId)->first();

// Log the approval details for debugging or auditing purposes
Log::info("📋 Mission Approval Details:", $approvalDetails->toArray());

// Return the response with the approval details
return response()->json([
    'message' => 'Mission decision saved.',
    'approval_details' => $approvalDetails,
]);
    
        // Return the response with the approval details
return response()->json([
    'message' => 'Mission decision saved.',
    'approval_details' => $approvalDetails,
]);
    }
    

    // public function approve(Request $request)
    // {
    //     $request->validate([
    //         'mission_id'      => 'required',
    //         'decision'        => 'required|in:approve,reject',
    //         'rejection_note'  => 'nullable|string',
    //     ]);
    
    //     $user = Auth::user();
    //     $userType = strtolower(optional($user->userType)->name);
    //     $missionId = $request->mission_id;
    //     $decision  = $request->decision === 'approve' ? 1 : 2;
    
    //     // ✅ Get mission (even soft-deleted)
    //     $mission = Mission::withTrashed()->find($missionId);
    //     if (! $mission) {
    //         Log::warning("❌ Mission not found for ID: $missionId");
    //         return response()->json(['message' => 'Mission not found.'], 404);
    //     }
    
    //     // ✅ Region access check for region_manager
    //     if ($userType === 'region_manager') {
    //         $regionIds = optional($user)->regions()->pluck('regions.id');
    //         if (! $regionIds->contains($mission->region_id)) {
    //             Log::warning("🚫 Unauthorized region_manager (User ID: $user->id) tried to approve mission in region {$mission->region_id}");
    //             return response()->json(['message' => 'You are not authorized to approve this mission.'], 403);
    //         }
    //     }
    
    //     // ✅ Determine approval column
    //     $approvalColumn = match ($userType) {
    //         'region_manager' => 'region_manager_approved',
    //         'modon_admin'    => 'modon_admin_approved',
    //         default => null,
    //     };
    
    //     if (! $approvalColumn) {
    //         Log::warning("❌ User type $userType is not allowed to approve.");
    //         return response()->json(['message' => 'User type not allowed to approve.'], 403);
    //     }
    
    //     // ✅ Update or create the mission approval record
    //     $approval = MissionApproval::firstOrNew(['mission_id' => $missionId]);
    //     $approval->{$approvalColumn} = $decision;
    
    //     // ✅ If rejected, also record who & why
    //     if ($decision === 2) {
    //         $approval->rejected_by    = $user->id;
    //         $approval->rejection_note = $request->rejection_note ?? null;
    //     }
    
    //     $approval->save();
    
    //     // ✅ Refresh and evaluate approval status
    //     $approval->refresh();
    
    //     $isFullyApproved   = 0;
    //     $newMissionStatus  = 'Pending'; // default
    
    //     if (
    //         $approval->region_manager_approved == 2 ||
    //         $approval->modon_admin_approved == 2
    //     ) {
    //         $isFullyApproved  = 2;
    //         $newMissionStatus = 'Rejected';
    //     } elseif (
    //         $approval->region_manager_approved == 1 &&
    //         $approval->modon_admin_approved == 1
    //     ) {
    //         $isFullyApproved  = 1;
    //         $newMissionStatus = 'Approved';
    //     }
    
    //     // ✅ Log approval decision
    //     Log::info("📋 Mission #$missionId approval update by $userType (User ID: $user->id):");
    //     Log::info("➡️ $approvalColumn = $decision");
    //     Log::info("✅ is_fully_approved = $isFullyApproved");
    //     Log::info("📌 Mission status will be updated to: $newMissionStatus");
    
    //     // ✅ Save final statuses
    //     $approval->update(['is_fully_approved' => $isFullyApproved]);
    //     $mission->status = $newMissionStatus;
    //     $mission->save();
    
    //     return response()->json(['message' => 'Mission approval updated successfully.']);
    // }


    
 


    
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

//     public function getmanagermissions()
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
//             'locations.locationAssignments.region:id,name',
//             'pilot:id,name',
//             'approvals:id,mission_id,region_manager_approved,modon_admin_approved',
//             'user:id,name,user_type_id',
//             'user.userType:id,name',
//         ])
//         ->get()
//         ->map(function ($mission) {
//             // ✅ Approval Status
//             $mission->approval_status = [
//                 'region_manager_approved' => $mission->approvals->region_manager_approved ?? null,
//                 'modon_admin_approved'    => $mission->approvals->modon_admin_approved    ?? null,
//             ];

//             // ✅ Pilot Info
//             $mission->pilot_info = [
//                 'id'   => $mission->pilot->id   ?? null,
//                 'name' => $mission->pilot->name ?? null,
//             ];

//             // ✅ Created By
//             $mission->created_by = [
//                 'id'        => $mission->user->id   ?? null,
//                 'name'      => $mission->user->name ?? null,
//                 'user_type' => $mission->user->userType->name ?? null,
//             ];

//             // ✅ Locations with region name from locationAssignments
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



                // to get emails for specific region
                $users = DB::table('user_region')
                ->join('users', 'user_region.user_id', '=', 'users.id')
                ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
                ->where('user_region.region_id', $regionId)
                ->where('user_types.name', '!=', 'pilot') // Exclude users with user_type_name "pilot"
                ->select('users.id', 'users.email', 'user_types.name as user_type_name')
                ->get();
        
                Log::info('👥 Users associated with the region (excluding pilots):', $users->toArray());
        
        
        
                // Fetch emails of all qss_admin and modon_admin users
                $adminEmails = DB::table('users')
                    ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
                    ->whereIn('user_types.name', ['qss_admin', 'modon_admin']) // Filter by user type names
                    ->select('users.id', 'users.email', 'user_types.name as user_type_name')
                    ->get();
        
                Log::info('👤 Admin Emails (qss_admin and modon_admin):', $adminEmails->toArray());
        
                // Collect all emails into a single array
                $allEmails = $users->pluck('email')->merge($adminEmails->pluck('email'))->unique()->values();
                Log::info('👤 All Emails :', $allEmails->toArray());
        
                // end get emials for specific region





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
            $modonApproved  = $userType === 'modon_admin';
            
            MissionApproval::create([
                'mission_id'              => $mission->id,
                'region_manager_approved' => $regionApproved,
                'modon_admin_approved'    => $modonApproved,
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
                    'allmails'        => $allEmails,
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
    
        // 📬 Fetch emails
        $users = DB::table('user_region')
            ->join('users', 'user_region.user_id', '=', 'users.id')
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->where('user_region.region_id', $mission->region_id)
            ->where('user_types.name', '!=', 'pilot')
            ->select('users.id', 'users.email', 'user_types.name as user_type_name')
            ->get();
    
        $adminEmails = DB::table('users')
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->whereIn('user_types.name', ['qss_admin', 'modon_admin'])
            ->select('users.id', 'users.email', 'user_types.name as user_type_name')
            ->get();
    
        $allEmails = $users->pluck('email')->merge($adminEmails->pluck('email'))->unique()->values();
    
        // ✅ Store delete metadata
        $mission->delete_reason = $request->delete_reason;
        $mission->deleted_by = $user->id;
        $mission->save();
    
        $mission->delete(); // Uncomment this if you're doing soft deletes
    
        return response()->json([
            'message' => '✅ Mission deleted successfully!',
            'mission' => [
                'id'              => $mission->id,
                'mission_date'    => $mission->mission_date,
                'region'          => $regionName,
                'created_by'      => $createdBy,
                'deleted_by'      => $user->name . ' (' . $userType . ')',
                'deleted_reason'  => $request->delete_reason,
                'inspection_type' => $inspectionType,
                'locations'       => $locations,
                'allmails'        => $allEmails,
            ]
        ]);
    }
        
    

  
     

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
            'region_id' => 'required|exists:regions,id',
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

        // Fetch users associated with the region, excluding pilots
            $users = DB::table('user_region')
            ->join('users', 'user_region.user_id', '=', 'users.id')
            ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
            ->where('user_region.region_id', $request->region_id)
            ->where('user_types.name', '!=', 'pilot') // Exclude users with user_type_name "pilot"
            ->select('users.id', 'users.email', 'user_types.name as user_type_name')
            ->get();

            Log::info('👥 Users associated with the region (excluding pilots):', $users->toArray());

            // Fetch the pilot's email


            // Fetch emails of all qss_admin and modon_admin users
            $adminEmails = DB::table('users')
                ->join('user_types', 'users.user_type_id', '=', 'user_types.id')
                ->whereIn('user_types.name', ['qss_admin', 'modon_admin']) // Filter by user type names
                ->select('users.id', 'users.email', 'user_types.name as user_type_name')
                ->get();

            Log::info('👤 Admin Emails (qss_admin and modon_admin):', $adminEmails->toArray());

            // Collect all emails into a single array
            $allEmails = $users->pluck('email')->merge($adminEmails->pluck('email'))->unique()->values();
            Log::info('👤 All Emails :', $allEmails->toArray());
            // ✅ Return response with mission details and additional data
            return response()->json([
                'message' => '✅ Mission updated successfully!',
                'mission' => [
                    'id'              => $mission->id,
                    'inspection_type' => [
                        'id'   => $request->inspection_type,
                        'name' => InspectionType::find($request->inspection_type)?->name,
                    ],
                    'mission_date'    => $mission->mission_date,
                    'locations'       => $mission->locations->map(fn($l) => ['id' => $l->id, 'name' => $l->name]),
                    'allmails'                     => $allEmails, 
                ],
                'users_associated_with_region' => $users,

                'admin_emails'                 => $adminEmails,
                
            ]);
    }
    // public function updateMission(Request $request)
    // {
    //     Log::info("🔍 Incoming Mission Update Request", ['data' => $request->all()]);

    //     // ✅ Validate input (including geo coords)
    //     $request->validate([
    //         'mission_id'       => 'required|exists:missions,id',
    //         'region_id' => 'required|exists:regions,id',
    //         'inspection_type'  => 'required|exists:inspection_types,id',
    //         'mission_date'     => 'required|date',
    //         'note'             => 'nullable|string',
    //         'locations'        => 'required|array',
    //         'locations.*'      => 'exists:locations,id',
    //         'pilot_id'         => 'required|exists:users,id',
    //         'latitude'         => 'required|numeric|between:-90,90',
    //         'longitude'        => 'required|numeric|between:-180,180',
    //     ]);

    //     // ✅ Find and update mission fields
    //     $mission = Mission::findOrFail($request->mission_id);
    //     $mission->mission_date = $request->mission_date;
    //     $mission->note         = $request->note ?? "";
    //     $mission->pilot_id     = $request->pilot_id;
    //     $mission->region_id    = $request->region_id;
    //     $mission->save();

    //     // ✅ Sync inspection type & locations
    //     $mission->inspectionTypes()->sync([$request->inspection_type]);
    //     $mission->locations()->sync($request->locations);

    //     // ✅ Update geo_location for the first selected location
    //     if (isset($request->locations[0])) {
    //         $geo = GeoLocation::updateOrCreate(
    //             ['location_id' => $request->locations[0]],
    //             [
    //                 'latitude'  => $request->latitude,
    //                 'longitude' => $request->longitude,
    //             ]
    //         );
    //         Log::info('📍 Geo Location updated:', [
    //             'location_id' => $geo->location_id,
    //             'latitude'    => $geo->latitude,
    //             'longitude'   => $geo->longitude,
    //         ]);
    //     }

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
    

