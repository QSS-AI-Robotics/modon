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
class AdminController extends Controller
{
    // Show the main admin dashboard/index page
    public function index()
    {
        $userTypes = UserType::all();
        $regions = Region::all();
    
        return view('admin.index', [
            'userTypes' => $userTypes,
            'regions' => $regions
        ]);
    }

    public function adminusers()
    {
        $pilot = User::whereHas('userType', function ($query) {
            $query->where('name', 'Pilot');
        })->count();
        $drones = Drone::count();
        $missions = Mission::count();
        $locations = Location::count();
        $regions = Region::count();
    
        return view('admin.adminusers', [
            'pilot' => $pilot,
            'regions' => $regions,
            'locations' => $locations,
            'missions' => $missions,
            'drones' => $drones
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'region_id' => 'required|exists:regions,id',
            'user_type_id' => 'required|exists:user_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'region_id' => $request->region_id,
            'user_type_id' => $request->user_type_id,
        ]);

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            'region_id' => $request->region_id,
            'user_type_id' => $request->user_type_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✅ User updated successfully.',
        ]);
    }


    public function missionsByRegion()
    {
        $data = Region::withCount('missions')
            ->get()
            ->map(function ($region) {
                return [
                    'region' => $region->name,
                    'missions' => $region->missions_count,
                ];
            });
    
        return response()->json($data);
    }
    
    public function inspectionsByRegion()
    {
        $data = DB::table('regions')
            ->leftJoin('missions', function($join) {
                $join->on('regions.id', '=', 'missions.region_id')
                     ->where('missions.status', 'Completed'); // ✅ only completed missions
            })
            ->leftJoin('pilot_reports', 'missions.id', '=', 'pilot_reports.mission_id')
            ->leftJoin('pilot_report_images', 'pilot_reports.id', '=', 'pilot_report_images.pilot_report_id')
            ->select('regions.name as region', DB::raw('COUNT(pilot_report_images.id) as inspections'))
            ->groupBy('regions.id', 'regions.name')
            ->get();
    
        return response()->json($data);
    }
    public function pilotMissionSummary()
    {
        $pilots = User::whereHas('userType', function ($query) {
            $query->where('name', 'Pilot');
        })
        ->select('id', 'name', 'region_id')
        ->get()
        ->map(function ($pilot) {
            $total = DB::table('missions')
                ->where('region_id', $pilot->region_id)
                ->count();

            $completed = DB::table('missions')
                ->where('region_id', $pilot->region_id)
                ->where('status', 'Completed')
                ->count();

            $pending = DB::table('missions')
                ->where('region_id', $pilot->region_id)
                ->where('status', 'Pending')
                ->count();

            return [
                'name' => $pilot->name,
                'total_missions' => $total,
                'completed_missions' => $completed,
                'pending_missions' => $pending,
            ];
        });

        return response()->json($pilots);
    }

    
    
    // public function latestInspections()
    // {
    //     $images = PilotReportImage::with([
    //         'report.mission.region'
    //     ])->get();
    
    //     $result = $images->map(function ($image) {
    //         $report  = $image->report;
    //         $mission = $report?->mission;
    //         $region  = $mission?->region;
    
    //         return [
    //             'pilot_report_image_id' => $image->id,
    //             'pilot_report_id'       => $report?->id,
    //             'mission_id'            => $mission?->id,
    //             'region_id'             => $region?->id,
    //             'region_name'           => $region?->name ?? 'N/A',
    //         ];
    //     });
    
    //     return response()->json($result);
    // }

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



}
