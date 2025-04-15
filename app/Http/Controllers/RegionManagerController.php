<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User; 
use App\Models\Mission;
use App\Models\InspectionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ✅ Import Auth facade
use Illuminate\Support\Facades\Log;
use App\Models\MissionApproval; 
class RegionManagerController extends Controller
{


    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('signin.form')->with('error', 'Please log in first.');
        }

        $user = Auth::user();
        $userType = optional($user->userType)->name ?? 'Control';

        // 👇 Fetch assigned location (if applicable)
        $location = in_array(strtolower($userType), ['city_manager', 'city_supervisor'])
            ? $user->assignedLocations->first()
            : null;

        $locationData = $location ? [
            'id' => $location->id,
            'name' => $location->name
        ] : null;

        // ✅ Get region IDs the current user has access to
        $regionIds = optional(Auth::user())->regions()->pluck('regions.id');

        

        // ✅ Fetch pilots assigned to those regions
        $pilots = \App\Models\User::whereHas('regions', function ($query) use ($regionIds) {
            $query->whereIn('regions.id', $regionIds);
        })->whereHas('userType', function ($q) {
            $q->where('name', 'pilot');
        })->get();

        return view('missions.index', compact('userType', 'locationData', 'pilots'));
    }




    // public function index()
    // {
    //     if (!Auth::check()) {
    //         return redirect()->route('signin.form')->with('error', 'Please log in first.');
    //     }

    //     $user = Auth::user();
    //     $userType = optional($user->userType)->name ?? 'Control';

    //     // 👇 Fetch assigned location (if applicable)
    //     $location = in_array(strtolower($userType), ['city_manager', 'city_supervisor'])
    //         ? $user->assignedLocations->first() // returns a Location model or null
    //         : null;

    //     // Pass both name and id if available
    //     $locationData = $location ? [
    //         'id' => $location->id,
    //         'name' => $location->name
    //     ] : null;

    //     return view('missions.index', compact('userType', 'locationData'));
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
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    }

    $user = Auth::user();

    $regionIds = $user instanceof User
        ? $user->regions()->pluck('regions.id')
        : collect();

    $missions = Mission::whereIn('region_id', $regionIds)
        ->with([
            'inspectionTypes:id,name',
            'locations:id,name',
            'approvals:id,mission_id,city_manager_approved,region_manager_approved,modon_admin_approved'
        ])
        ->get();

    $missions = $missions->map(function ($mission) {
        $mission->approval_status = [
            'city_manager_approved' => $mission->approvals->city_manager_approved ?? null,
            'region_manager_approved' => $mission->approvals->region_manager_approved ?? null,
            'modon_admin_approved' => $mission->approvals->modon_admin_approved ?? null,
        ];
        unset($mission->approvals);
        return $mission;
    });

    return response()->json([
        'missions' => $missions
    ]);
}

    // public function getmanagermissions()
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //     }
    
    //     $user = Auth::user();
        
    //     // ✅ Get all assigned region IDs (support multiple regions)
    //     $regionIds = $user instanceof User
    //         ? $user->regions()->pluck('regions.id')
    //         : collect();
    
    //     // ✅ Fetch all missions for the user's regions
    //     $missions = Mission::whereIn('region_id', $regionIds)
    //         ->with(['inspectionTypes:id,name', 'locations:id,name']) // Eager load related data
    //         ->get();
    
    //     return response()->json([
    //         'missions' => $missions
    //     ]);
    // }
    
    
    

    /**
     * Store a new mission.
     */
    public function storeMission(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $request->validate([
            'inspection_type' => 'required|exists:inspection_types,id',
            'mission_date' => ['required', 'date', 'after_or_equal:today'],
            'note' => 'nullable|string',
            'locations' => 'required|array',
            'locations.*' => 'exists:locations,id',
        ], [
            'mission_date.after_or_equal' => 'The mission date cannot be in the past.',
        ]);
    
        try {
            $user = Auth::user();
            $regionIds = $user instanceof User ? $user->regions()->pluck('regions.id') : collect();
            $regionId = $regionIds->first(); // Adjust if needed
            $userId = Auth::id();
    
            // ✅ Create mission
            $mission = Mission::create([
                'mission_date' => $request->mission_date,
                'note' => $request->note,
                'region_id' => $regionId,
                'user_id' => $userId,
                'pilot_id' => $request->pilot_id,
            ]);
    
            // ✅ Sync relationships
            $mission->inspectionTypes()->sync([$request->inspection_type]);
            $mission->locations()->sync($request->locations);
    
            // ✅ Create related approval record
            MissionApproval::create([
                'mission_id' => $mission->id,
                'city_manager_approved' => false,
                'region_manager_approved' => false,
                'modon_admin_approved' => false,
                'is_fully_approved' => false,
            ]);
    
            return response()->json([
                'message' => 'Mission created successfully!',
                'mission' => [
                    'id' => $mission->id,
                    'inspection_type' => [
                        'id' => $request->inspection_type,
                        'name' => InspectionType::find($request->inspection_type)?->name
                    ],
                    'mission_date' => $mission->mission_date,
                    'locations' => $mission->locations->map(fn($loc) => ['id' => $loc->id, 'name' => $loc->name]),
                ]
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create mission.',
                'message' => $e->getMessage()
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
    //         'mission_date' => 'required|date',
    //         'note' => 'nullable|string',
    //         'locations' => 'required|array',
    //         'locations.*' => 'exists:locations,id',
    //     ]);
    
    //     try {
    //         $user = Auth::user();
    //         $regionIds = $user instanceof User ? $user->regions()->pluck('regions.id') : collect();
    //         $regionId = $regionIds->first(); // Pick one or handle differently
    //         $userId = Auth::id(); // 👈 Get the current user's ID
    
    //         $mission = Mission::create([
    //             'mission_date' => $request->mission_date,
    //             'note' => $request->note,
    //             'region_id' => $regionId,
    //             'user_id' => $userId, // ✅ Add this!
    //         ]);
    
    //         $mission->inspectionTypes()->sync([$request->inspection_type]);
    //         $mission->locations()->sync($request->locations);
    
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
    public function destroyMission($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $user = Auth::user();
    
        // ✅ Get all region IDs this user is assigned to
        $regionIds = $user instanceof \App\Models\User
            ? $user->regions()->pluck('regions.id')->toArray()
            : [];
    
        // ✅ Find mission with approvals
        $mission = Mission::with('approvals')->findOrFail($id);
    
        // ✅ Ensure user is assigned to this mission's region
        if (!in_array($mission->region_id, $regionIds)) {
            return response()->json(['error' => 'You are not authorized to delete this mission.'], 403);
        }
    
        // ✅ Get the approval record
        $approval = $mission->approvals;
    
        $hasBeenApproved = $approval && (
            $approval->city_manager_approved ||
            $approval->region_manager_approved ||
            $approval->modon_manager_approved
        );
    
        // ✅ If any approvals exist...
        if ($hasBeenApproved) {
            // Check if user is region manager of this mission's region
            $isRegionManager = $user->type === 'region_manager';
    
            if (!$isRegionManager) {
                return response()->json([
                    'error' => '❌ This mission has already been approved. Only the region manager can delete it now.'
                ], 403);
            }
        }
    
        // ✅ Passed all checks — soft delete
        $mission->delete();
    
        return response()->json(['message' => '✅ Mission deleted successfully!']);
    }
    
  
    //  public function destroyMission($id)
    //  {
    //      if (!Auth::check()) {
    //          return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //      }
     
    //      $user = Auth::user();
     
    //      // ✅ Get all region IDs this user is assigned to (via user_region pivot)
    //      $regionIds = $user instanceof \App\Models\User
    //          ? $user->regions()->pluck('regions.id')->toArray()
    //          : [];
     
    //      // ✅ Find the mission
    //      $mission = Mission::findOrFail($id);
     
    //      // ✅ Ensure the mission belongs to one of the user's regions
    //      if (!in_array($mission->region_id, $regionIds)) {
    //          return response()->json(['error' => 'You are not authorized to delete this mission.'], 403);
    //      }
     
    //      // ✅ Delete the mission
    //      $mission->delete();
     
    //      return response()->json(['message' => '✅ Mission deleted successfully!']);
    //  }
     

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
    Log::info("🚀 Incoming Mission Update Request", ['data' => $request->all()]);

    // ✅ Validate input
    $request->validate([
        'mission_id' => 'required|exists:missions,id',
        'inspection_type' => 'required|exists:inspection_types,id',
        'mission_date' => 'required|date',
        'note' => 'nullable|string',
        'locations' => 'required|array',
        'locations.*' => 'exists:locations,id',
        'pilot_id' => 'required|exists:users,id', // ✅ Validate pilot_id
    ]);

    // ✅ Find mission
    $mission = Mission::findOrFail($request->mission_id);

    // ✅ Update mission fields
    $mission->mission_date = $request->mission_date;
    $mission->note = $request->note ?? "";
    $mission->pilot_id = $request->pilot_id; // ✅ Update pilot
    $mission->save();

    // ✅ Sync inspection type (only one now)
    $mission->inspectionTypes()->sync([$request->inspection_type]);

    // ✅ Sync locations
    $mission->locations()->sync($request->locations);

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
    

