<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserType;
use App\Models\Region;
use App\Models\Drone;
use App\Models\Mission;
use App\Models\Location;
use App\Models\UserLocation;
use App\Models\PilotReport;
use App\Models\PilotReportImage;
use App\Models\Pilot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\LocationAssignment;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;


class AdminController extends Controller
{

    public function index()
    {

        $pilot = User::whereHas('userType', function ($query) {
            $query->where('name', 'Pilot');
        })->count();
        $drones = Drone::count();
        $missions = Mission::count();
        $locations = Location::count();
        $regions = Region::count();
    
        return view('admin.index', [
            'pilot' => $pilot,
            'regions' => $regions,
            'locations' => $locations,
            'missions' => $missions,
            'drones' => $drones
        ]);
    }

    public function adminusers()
    {
        $userTypes = UserType::all();
        $regions = Region::all();
        
            // ✅ Fetch locations with their region info via LocationAssignment
        $locations = Location::with(['locationAssignments.region'])
        ->get()
        ->map(function ($location) {
            $regionNames = $location->locationAssignments->map(function ($assignment) {
                return $assignment->region->name ?? null;
            })->filter()->unique()->values()->all();

            return [
                'id' => $location->id,
                'name' => $location->name,
                'regions' => $regionNames,
            ];
        });

        return view('admin.adminusers', [
            'userTypes' => $userTypes,
            'regions' => $regions,
            'locations' => $locations,
        ]);
    }

    public function getAllUsers()
{
    $authUser = Auth::user();

    if ($authUser && $authUser->userType && $authUser->userType->name === 'qss_admin') {
        $users = User::with(['userType', 'regions', 'pilot', 'assignedLocations'])->get()->map(function ($user) {
            $userType = strtolower($user->userType?->name);

            $region = $userType === 'pilot'
                ? $user->regions->pluck('name')->toArray()
                : optional($user->regions->first())->name;

            $locations = in_array($userType, ['city_supervisor', 'city_manager'])
                ? $user->assignedLocations->map(function ($loc) {
                    return [
                        'id' => $loc->id,
                        'name' => $loc->name,
                    ];
                })->toArray()
                : [];

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'region' => $region,
                'locations' => $locations, // ✅ Includes id + name now
                'user_type' => $user->userType?->name,
                'image' => $user->image,
                'license_no' => $userType === 'pilot' ? $user->pilot?->license_no : null,
                'license_expiry' => $userType === 'pilot' ? $user->pilot?->license_expiry : null,
            ];
        });
    } else {
        $users = collect(); // Empty collection for non-admins
    }

    return response()->json([
        'status' => 'success',
        'users' => $users
    ]);
}

    // public function getAllUsers()
    // {
    //     $authUser = Auth::user();
    
    //     if ($authUser && $authUser->userType && $authUser->userType->name === 'qss_admin') {
    //         $users = User::with(['userType', 'regions', 'pilot', 'assignedLocations'])->get()->map(function ($user) {
    //             $userType = strtolower($user->userType?->name);
    
    //             $region = $userType === 'pilot'
    //                 ? $user->regions->pluck('name')->toArray()
    //                 : optional($user->regions->first())->name;
    
    //             $locations = in_array($userType, ['city_supervisor', 'city_manager'])
    //                 ? $user->assignedLocations->pluck('name')->toArray()
    //                 : [];
    
    //             return [
    //                 'id' => $user->id,
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'region' => $region,
    //                 'locations' => $locations,
    //                 'user_type' => $user->userType?->name,
    //                 'image' => $user->image,
    //                 'license_no' => $userType === 'pilot' ? $user->pilot?->license_no : null,
    //                 'license_expiry' => $userType === 'pilot' ? $user->pilot?->license_expiry : null,
    //             ];
    //         });
    //     } else {
    //         $users = collect(); // Empty collection for non-admins
    //     }
    
    //     return response()->json([
    //         'status' => 'success',
    //         'users' => $users
    //     ]);
    // }
    

    
    public function deleteUser($id)
    {
        $authUser = Auth::user();
    
        // Optional: Allow only qss_admins to delete
        if ($authUser->userType->name !== 'qss_admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized action.'
            ], 403);
        }
    
        $user = User::find($id);
    
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }
    
        // Detach regions from pivot table before deleting
        $user->regions()->detach();
    
        // ✅ Remove assigned locations if city roles
        $userTypeName = strtolower(optional($user->userType)->name);
        if (in_array($userTypeName, ['city_supervisor', 'city_manager'])) {
            \App\Models\UserLocation::where('user_id', $user->id)->delete();
        }
    
        // Delete pilot license info if exists
        if ($userTypeName === 'pilot') {
            $user->pilot()?->delete();
        }
    
        // Delete image from storage if exists
        if ($user->image && Storage::disk('public')->exists('users/' . $user->image)) {
            Storage::disk('public')->delete('users/' . $user->image);
        }
    
        // Delete user
        $user->delete();
    
        return response()->json([
            'status' => 'success',
            'message' => '✅ User deleted successfully.'
        ]);
    }
    
    // public function deleteUser($id)
    // {
    //     $authUser = Auth::user();
    
    //     // Optional: Allow only qss_admins to delete
    //     if ($authUser->userType->name !== 'qss_admin') {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Unauthorized action.'
    //         ], 403);
    //     }
    
    //     $user = User::find($id);
    
    //     if (!$user) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'User not found.'
    //         ], 404);
    //     }
    
    //     // Detach regions from pivot table before deleting
    //     $user->regions()->detach();
    
    //     // Optional: delete pilot license info if exists
    //     if (strtolower($user->userType->name) === 'pilot') {
    //         $user->pilot()?->delete();
    //     }
    
    //     // Optional: delete image from storage
    //     if ($user->image && Storage::disk('public')->exists('users/' . $user->image)) {
    //         Storage::disk('public')->delete('users/' . $user->image);
    //     }
    
    //     // Delete user
    //     $user->delete();
    
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => '✅ User deleted successfully.'
    //     ]);
    // }
    


    public function storeUser(Request $request)
    {
        $userType = UserType::find($request->user_type_id);
        $userTypeName = strtolower($userType?->name);
        $isPilot = $userTypeName === 'pilot';
        $userType = UserType::find($request->user_type_id);
        $isCityLevel = in_array(strtolower($userType?->name), ['city_supervisor', 'city_manager']);
    
        // ✅ Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'user_type_id' => 'required|exists:user_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'assigned_regions' => $isPilot ? 'required|array|min:1' : 'required|array|size:1',
        ];
    
        if ($isPilot) {
            $rules['license_no'] = 'required|string';
            $rules['license_expiry'] = 'required|date';
        }
    
        if ($isCityLevel) {
            $rules['location_id'] = 'required|exists:locations,id';
        }
    
        $validated = $request->validate($rules);
    
        // ✅ Create the user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->user_type_id = $request->user_type_id;
    
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('users', $imageName, 'public');
            $user->image = $imageName;
        }
    
        $user->save();
    
        // ✅ Save pilot license info
        if ($isPilot) {
            Pilot::create([
                'user_id' => $user->id,
                'license_no' => $request->license_no,
                'license_expiry' => $request->license_expiry,
            ]);
        }
    
        // ✅ Attach region(s)
        $user->regions()->attach($request->assigned_regions);
    
        // ✅ If city-level role, save location mapping
        if ($isCityLevel) {
            
            UserLocation::create([
                'user_id' => $user->id,
                'location_id' => $request->location_id,
            ]);
            Log::info("Saving user location for user_id: {$user->id}, location_id: {$request->location_id}");

        }

        return response()->json([
            'status' => 'success',
            'message' => '✅ User created successfully.',
        ]);
    }
    



public function updateUser(Request $request, $id)
{
    $user = User::findOrFail($id);
    $userTypeName = strtolower(UserType::find($request->user_type_id)?->name);
    $isPilot = $userTypeName === 'pilot';
    $isCityLevel = in_array($userTypeName, ['city_supervisor', 'city_manager']);

    // ✅ Validation
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'password' => 'nullable|string|min:6',
        'user_type_id' => 'required|exists:user_types,id',
        'assigned_regions' => 'required|array|min:1',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ];

    if ($isPilot) {
        $rules['license_no'] = 'required|string';
        $rules['license_expiry'] = 'required|date';
    }

    if ($isCityLevel) {
        $rules['location_id'] = 'required|exists:locations,id';
    }

    $validated = $request->validate($rules);

    // ✅ Update base user info
    $user->name = $request->name;
    $user->email = $request->email;
    $user->user_type_id = $request->user_type_id;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    // ✅ Update image
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('users', $imageName, 'public');

        if ($user->image && Storage::disk('public')->exists('users/' . $user->image)) {
            Storage::disk('public')->delete('users/' . $user->image);
        }

        $user->image = $imageName;
    }

    $user->save();

    // ✅ Handle pilot fields
    if ($isPilot) {
        Pilot::updateOrCreate(
            ['user_id' => $user->id],
            [
                'license_no' => $request->license_no,
                'license_expiry' => $request->license_expiry,
            ]
        );
    }

    // ✅ Sync regions
    $submittedRegionIds = $request->assigned_regions ?? [];
    $existingRegionIds = $user->regions()->pluck('regions.id')->toArray();

    if (array_diff($submittedRegionIds, $existingRegionIds) || array_diff($existingRegionIds, $submittedRegionIds)) {
        $user->regions()->sync($submittedRegionIds);
    }

    // ✅ Handle city-level location
    if ($isCityLevel) {
        // If record exists, update; otherwise, create
        UserLocation::updateOrCreate(
            ['user_id' => $user->id],
            ['location_id' => $request->location_id]
        );
    } else {
        // ❌ Not city-level anymore, remove any assigned location
        UserLocation::where('user_id', $user->id)->delete();
    }

    return response()->json([
        'status' => 'success',
        'message' => '✅ User updated successfully.',
    ]);
}


    // public function updateUser(Request $request, $id)
    // {
    //     $user = User::findOrFail($id);
    
    //     $userTypeName = strtolower(UserType::find($request->user_type_id)?->name);
    //     $isPilot = $userTypeName === 'pilot';
    
    //     $rules = [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email,' . $id,
    //         'password' => 'nullable|string|min:6',
    //         'user_type_id' => 'required|exists:user_types,id',
    //         'assigned_regions' => 'required|array|min:1',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ];
    
    //     if ($isPilot) {
    //         $rules['license_no'] = 'required|string';
    //         $rules['license_expiry'] = 'required|date';
    //     }
    
    //     $validated = $request->validate($rules);
    
    //     // ✅ Update basic user info
    //     $user->name = $request->name;
    //     $user->email = $request->email;
    //     $user->user_type_id = $request->user_type_id;
    
    //     if ($request->filled('password')) {
    //         $user->password = Hash::make($request->password);
    //     }
    
    //     // ✅ Handle image
    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $imageName = time() . '_' . $image->getClientOriginalName();
    //         $image->storeAs('users', $imageName, 'public');
    
    //         if ($user->image && Storage::disk('public')->exists('users/' . $user->image)) {
    //             Storage::disk('public')->delete('users/' . $user->image);
    //         }
    
    //         $user->image = $imageName;
    //     }
    
    //     $user->save();
    
    //     // ✅ Handle pilot-specific data
    //     if ($isPilot) {
    //         Pilot::updateOrCreate(
    //             ['user_id' => $user->id],
    //             [
    //                 'license_no' => $request->license_no,
    //                 'license_expiry' => $request->license_expiry,
    //             ]
    //         );
    //     }
    
    //     // ✅ Sync regions for all users
    //     $submittedRegionIds = $request->assigned_regions ?? [];
    //     $existingRegionIds = $user->regions()->pluck('regions.id')->toArray();
    
    //     if (array_diff($submittedRegionIds, $existingRegionIds) || array_diff($existingRegionIds, $submittedRegionIds)) {
    //         $user->regions()->sync($submittedRegionIds);
    //     }
    
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => '✅ User updated successfully.',
    //     ]);
    // }
    
    
    
    
   
   

    

 
    public function missionsByRegion(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');
    
        $regions = Region::with(['missions' => function ($query) use ($start, $end) {
            if ($start) {
                $query->whereDate('start_datetime', '>=', Carbon::parse($start));
            }
            if ($end) {
                $query->whereDate('start_datetime', '<=', Carbon::parse($end));
            }
        }])->get();
    
        $data = $regions->map(function ($region) {
            return [
                'region'   => $region->name,
                'missions' => $region->missions->count(),
            ];
        });
    
        return response()->json([
            'from' => $start ?? null,
            'to' => $end ?? null,
            'filtered' => (bool)($start || $end),
            'data' => $data
        ]);
    }
    

    


    public function inspectionsByRegion(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');
    
        $query = DB::table('regions')
            ->leftJoin('missions', function ($join) use ($start, $end) {
                $join->on('regions.id', '=', 'missions.region_id')
                     ->where('missions.status', 'Completed');
    
                if ($start) {
                    $join->whereDate('missions.start_datetime', '>=', Carbon::parse($start));
                }
    
                if ($end) {
                    $join->whereDate('missions.end_datetime', '<=', Carbon::parse($end));
                }
            })
            ->leftJoin('pilot_reports', 'missions.id', '=', 'pilot_reports.mission_id')
            ->leftJoin('pilot_report_images', 'pilot_reports.id', '=', 'pilot_report_images.pilot_report_id')
            ->select(
                'regions.name as region',
                DB::raw('COUNT(pilot_report_images.id) as inspections')
            )
            ->groupBy('regions.id', 'regions.name')
            ->get();
    
        return response()->json([
            'from' => $start ?? null,
            'to' => $end ?? null,
            'filtered' => (bool)($start || $end),
            'data' => $query
        ]);
    }



    public function pilotMissionSummary(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');
    
        $pilots = User::whereHas('userType', function ($query) {
                $query->where('name', 'Pilot');
            })
            ->with('region') // Eager load region relation
            ->select('id', 'name', 'region_id', 'image')
            ->get()
            ->map(function ($pilot) use ($start, $end) {
                $missionsQuery = DB::table('missions')
                    ->where('region_id', $pilot->region_id);
    
                if ($start) {
                    $missionsQuery->whereDate('start_datetime', '>=', Carbon::parse($start));
                }
    
                if ($end) {
                    $missionsQuery->whereDate('start_datetime', '<=', Carbon::parse($end));
                }
    
                $total = (clone $missionsQuery)->count();
                $completed = (clone $missionsQuery)->where('status', 'Completed')->count();
                $pending = (clone $missionsQuery)->where('status', 'Pending')->count();
    
                return [
                    'name' => $pilot->name,
                    'region' => optional($pilot->region)->name ?? 'N/A',
                    'image' => $pilot->image_url ?? asset('images/default-user.png'),
                    'total_missions' => $total,
                    'completed_missions' => $completed,
                    'pending_missions' => $pending,
                ];
            });
    
        return response()->json([
            'from' => $start,
            'to' => $end,
            'filtered' => (bool)($start || $end),
            'data' => $pilots
        ]);
    }
    
    
    public function latestMissions()
    {
        $missions = Mission::with('region:id,name')
            ->orderBy('created_at', 'desc') // ✅ Sort by created_at
            ->take(2)
            ->get()
            ->map(function ($mission) {
                return [
                    'id'             => $mission->id,
                    'note'           => $mission->note,
                    'start_datetime' => $mission->start_datetime,
                    'end_datetime'   => $mission->end_datetime,
                    'region'         => $mission->region->name ?? 'N/A',
                    'status'         => $mission->status,
                    'created_at'     => $mission->created_at->format('Y-m-d H:i')
                ];
            });

        return response()->json($missions);
    }
    
    


    public function latestInspections()
    {
        $images = PilotReportImage::with([
            'location:id,name',
            'report.mission.region:id,name'
        ])->get();
    
        $result = $images->map(function ($image) {
            $report  = $image->report;
            $mission = $report?->mission;
            $region  = $mission?->region;
    
            return [
                'region_name'  => $region?->name ?? 'N/A',
                'location'     => $image->location?->name ?? 'N/A',
                'description'  => $image->description ?? '',
                'image_path'   => asset($image->image_path),
            ];
        });
    
        return response()->json($result);
    }
    //drones functions
    public function adddrone(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        // Ensure user is a pilot
        if ($user->userType->name !== 'Pilot') {
            return back()->with('error', 'Only pilot users can be assigned a drone.');
        }

        // Save drone
        Drone::create([
            'model' => $request->model,
            'sr_no' => $request->sr_no,
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Drone added successfully.');
    }

    public function drones()
    {
        $user = Auth::user();

        // Check if user is admin (either QSS or Modon)
        $isAdmin = in_array(strtolower($user->userType->name), ['qss_admin', 'modon_admin']);

        if ($isAdmin) {
            // Show all drones
            $drones = Drone::with('user:id,name')->get();
        } else {
            // Show only drones assigned to this user
            $drones = Drone::with('user:id,name')
                ->where('user_id', $user->id)
                ->get();
        }

        return response()->json($drones);
    }


    // locations functions
    public function locations()
    {
        if (!Auth::check()) {
            return redirect()->route('signin.form')->with('error', 'Please log in first.');
        }

        // ✅ Get all regions to pass to view (for filters, selects, etc.)
        $regions = \App\Models\Region::select('id', 'name')->get();

        return view('region_manager.locations', compact('regions'));
    }


    // get all locations
    public function fetchLocations()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $user = Auth::user();
        $userType = strtolower($user->userType->name ?? '');
    
        if ($userType === 'qss_admin') {
            // ✅ Load each location along with its assignment's region
            $locations = Location::with(['locationAssignments.region'])->get();
    
            // Format data
            $formatted = $locations->map(function ($location) {
                $assignment = $location->locationAssignments->first(); // assuming one assignment per location
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'map_url' => $location->map_url,
                    'description' => $location->description,
                    'region' => $assignment?->region?->name ?? 'N/A',
                ];
            });
    
            return response()->json([
                'locations' => $formatted
            ]);
        }
    
        return response()->json([
            'locations' => []
        ]);
    }
    
    

        /**
     * Store a new location, assigning it to the authenticated user's region.
     */


    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'map_url' => 'nullable|url',
            'description' => 'nullable|string',
            'region_id' => 'required|exists:regions,id' // ✅ Now passed from frontend
        ]);
    
        // ✅ Create the location (no region_id here anymore)
        $location = Location::create([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'map_url' => $request->map_url,
            'description' => $request->description,
        ]);
    
        // ✅ Assign location to user & region
        LocationAssignment::create([
            'user_id' => Auth::id(),
            'location_id' => $location->id,
            'region_id' => $request->region_id
        ]);
    
        return response()->json([
            'message' => '✅ Location added successfully!',
            'location' => $location
        ]);
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
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $user = Auth::user();
        $location = Location::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'map_url' => 'nullable|url',
            'description' => 'nullable|string',
            'region_id' => 'required|exists:regions,id',
        ]);

        // ✅ Get existing assignment record (user + location combo)
        $assignment = LocationAssignment::where('location_id', $location->id)->first();

        // ✅ Optional: check if user is allowed to edit based on assignment (unless admin)
        $isAdmin = strtolower($user->userType->name ?? '') === 'qss_admin';
        if (!$isAdmin && (!$assignment || $assignment->user_id !== $user->id)) {
            return response()->json(['error' => 'You are not authorized to update this location.'], 403);
        }

        // ✅ Update the location basic fields
        $location->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'map_url' => $request->map_url,
            'description' => $request->description,
        ]);

        // ✅ Update region assignment if changed
        if ($assignment && $assignment->region_id !== (int) $request->region_id) {
            $assignment->update(['region_id' => $request->region_id]);
        }

        return response()->json(['message' => '✅ Location updated successfully!']);
    }



    /**
     * Delete a location.
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $user = Auth::user();
        $isAdmin = strtolower($user->userType->name ?? '') === 'qss_admin';
    
        $location = Location::findOrFail($id);
    
        // ✅ Get the location assignment (region + creator info)
        $assignment = \App\Models\LocationAssignment::where('location_id', $location->id)->first();
    
        // ✅ Ensure the user can delete (admins OR the user who created it)
        if (!$isAdmin && (!$assignment || $assignment->user_id !== $user->id)) {
            return response()->json(['error' => 'You are not authorized to delete this location.'], 403);
        }
    
        // ✅ Delete assignment first (foreign key constraints)
        if ($assignment) {
            $assignment->delete();
        }
    
        $location->delete();
    
        return response()->json(['message' => '✅ Location deleted successfully!']);
    }
    
    

}
