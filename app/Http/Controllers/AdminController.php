<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserType;
use App\Models\Region;
use App\Models\Drone;
use App\Models\Mission;
use App\Models\Location;
use App\Models\PilotReport;
use App\Models\PilotReportImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
    
        return view('admin.adminusers', [
            'userTypes' => $userTypes,
            'regions' => $regions
        ]);
    }
    public function getAllUsers()
    {
        $user = Auth::user();

        // Check if the userType exists and equals 'qss_admin'
        if ($user && $user->userType && $user->userType->name === 'qss_admin') {
            $users = User::with(['userType', 'region'])->get();
        } else {
            $users = collect(); // Return empty collection for non-admins
        }

        return response()->json([
            'status' => 'success',
            'users' => $users
        ]);
    }

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

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => '✅ User deleted successfully.'
        ]);
    }









public function storeUser(Request $request)
{
    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->region_id = $request->region_id;
    $user->user_type_id = $request->user_type_id;

    // ✅ Handle image upload
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('users', $imageName, 'public'); // stored in storage/app/public/users
        $user->image = $imageName;
    }

    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => '✅ User created successfully.',
    ]);
}


public function updateUser(Request $request, $id)
{
    $user = User::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'password' => 'nullable|string|min:6',
        'region_id' => 'required|exists:regions,id',
        'user_type_id' => 'required|exists:user_types,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
        ], 422);
    }

    // Update basic fields
    $user->name = $request->name;
    $user->email = $request->email;
    $user->region_id = $request->region_id;
    $user->user_type_id = $request->user_type_id;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    // ✅ If a new image is uploaded
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('users', $imageName, 'public');

        // Delete old image (optional)
        if ($user->image && Storage::disk('public')->exists('users/' . $user->image)) {
            Storage::disk('public')->delete('users/' . $user->image);
        }
        
        $user->image = $imageName;
    }

    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => '✅ User updated successfully.',
    ]);
}


    // public function updateUser(Request $request, $id)
    // {
    //     $user = User::findOrFail($id);

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email,' . $id,
    //         'password' => 'nullable|string|min:6',
    //         'region_id' => 'required|exists:regions,id',
    //         'user_type_id' => 'required|exists:user_types,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $validator->errors()->first(),
    //         ], 422);
    //     }

    //     $user->update([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
    //         'region_id' => $request->region_id,
    //         'user_type_id' => $request->user_type_id,
    //     ]);

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
    

    
    // public function inspectionsByRegion()
    // {
    //     $data = DB::table('regions')
    //         ->leftJoin('missions', function($join) {
    //             $join->on('regions.id', '=', 'missions.region_id')
    //                  ->where('missions.status', 'Completed'); // ✅ only completed missions
    //         })
    //         ->leftJoin('pilot_reports', 'missions.id', '=', 'pilot_reports.mission_id')
    //         ->leftJoin('pilot_report_images', 'pilot_reports.id', '=', 'pilot_report_images.pilot_report_id')
    //         ->select('regions.name as region', DB::raw('COUNT(pilot_report_images.id) as inspections'))
    //         ->groupBy('regions.id', 'regions.name')
    //         ->get();
    
    //     return response()->json($data);
    // }


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
            ->select('id', 'name', 'region_id')
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


}
