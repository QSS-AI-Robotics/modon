<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User; 
use App\Models\Mission;
use App\Models\InspectionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ✅ Import Auth facade

class RegionManagerController extends Controller
{
    /**
     * Display the locations page with all locations for the authenticated user's region.
     */
    public function index()
    {
        // ✅ Ensure the user is authenticated
        if (!Auth::check()) {
            return redirect()->route('signin.form')->with('error', 'Please log in first.');
        }

        $regionId = Auth::user()->region_id;
        $locations = Location::where('region_id', $regionId)->get(); // ✅ Show only locations of user's region

        return view('region_manager.locations', compact('locations'));
    }

    /**
     * Store a new location, assigning it to the authenticated user's region.
     */
    public function store(Request $request)
    {
        // ✅ Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'map_url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        // ✅ Get region_id from authenticated user
        $regionId = Auth::user()->region_id;

        // ✅ Create location with region_id
        $location = Location::create([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'map_url' => $request->map_url,
            'description' => $request->description,
            'region_id' => $regionId, // ✅ Assign region_id
        ]);

        return response()->json(['message' => 'Location added successfully!', 'location' => $location]);
    }

    /**
     * Fetch a location for editing.
     */
    public function edit($id)
    {
        // ✅ Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $location = Location::find($id);

        // ✅ Check if the location exists
        if (!$location) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        // ✅ Ensure the user can only edit locations from their region
        if ($location->region_id !== Auth::user()->region_id) {
            return response()->json(['error' => 'You are not authorized to edit this location.'], 403);
        }

        return response()->json($location);
    }

    /**
     * Update an existing location.
     */
    public function update(Request $request, $id)
    {
        // ✅ Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $location = Location::findOrFail($id);

        // ✅ Ensure the user can only update locations from their region
        if ($location->region_id !== Auth::user()->region_id) {
            return response()->json(['error' => 'You are not authorized to update this location.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'map_url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $location->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'map_url' => $request->map_url,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Location updated successfully!']);
    }

    /**
     * Delete a location.
     */
    public function destroy($id)
    {
        // ✅ Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $location = Location::findOrFail($id);

        // ✅ Ensure the user can only delete locations from their region
        if ($location->region_id !== Auth::user()->region_id) {
            return response()->json(['error' => 'You are not authorized to delete this location.'], 403);
        }

        $location->delete();

        return response()->json(['message' => 'Location deleted successfully!']);
    }



       /**
     * Display the missions page for the authenticated user's region.
     */
    public function missions()
    {
        if (!Auth::check()) {
            return redirect()->route('signin.form')->with('error', 'Please log in first.');
        }
    
        $regionId = Auth::user()->region_id;
    
        // Get all missions for the region and include relationships
        $missions = Mission::where('region_id', $regionId)
            ->with(['inspectionTypes', 'locations']) // ✅ Ensure we load inspectionTypes
            ->get();
    
        // Get locations for the region
        $locations = Location::where('region_id', $regionId)->get();
    
        // Get all available inspection types
        $inspectionTypes = InspectionType::all();
    
        return view('region_manager.missions', compact('missions', 'locations', 'inspectionTypes'));
    }
    

    /**
     * Store a new mission.
     */
    public function storeMission(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $request->validate([
            'inspection_types' => 'required|array',
            'inspection_types.*' => 'exists:inspection_types,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'note' => 'nullable|string',
            'locations' => 'required|array',
            'locations.*' => 'exists:locations,id',
        ]);
    
        try {
            $regionId = Auth::user()->region_id;
    
            // Create Mission (without inspection_type_id)
            $mission = Mission::create([
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'note' => $request->note,
                'region_id' => $regionId,
            ]);
    
            // Attach multiple inspection types
            $mission->inspectionTypes()->sync($request->inspection_types); // ✅ Uses pivot table
    
            // Attach multiple locations
            $mission->locations()->sync($request->locations);
    
            return response()->json([
                'message' => 'Mission created successfully!',
                'mission' => [
                    'id' => $mission->id,
                    'inspection_types' => $mission->inspectionTypes->map(fn($type) => ['id' => $type->id, 'name' => $type->name]),
                    'start_datetime' => $mission->start_datetime,
                    'end_datetime' => $mission->end_datetime,
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
    
    

    /**
     * Delete a mission.
     */
    public function destroyMission($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $mission = Mission::findOrFail($id);

        if ($mission->region_id !== Auth::user()->region_id) {
            return response()->json(['error' => 'You are not authorized to delete this mission.'], 403);
        }

        $mission->delete();

        return response()->json(['message' => 'Mission deleted successfully!']);
    }
}
    

