<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Drone;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DroneController extends Controller
{
    public function index()
    {
        $user = Auth::user();
    
        $isAdmin = in_array(strtolower($user->userType->name), ['qss_admin', 'modon_admin']);
    
        if ($isAdmin) {
            $drones = Drone::with('user:id,name')->get();
        } else {
            $drones = Drone::with('user:id,name')
                           ->where('user_id', $user->id)
                           ->get();
        }
    
        // Fetch all pilots
        $pilots = User::whereHas('userType', function ($query) {
            $query->where('name', 'Pilot');
        })->select('id', 'name')->get();
    
        return view('drone.index', compact('drones', 'pilots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'sr_no' => 'required|string|unique:drones,sr_no',
            'user_id' => 'required|exists:users,id',
        ]);

        Drone::create([
            'model' => $request->model,
            'sr_no' => $request->sr_no,
            'user_id' => $request->user_id,
        ]);

        return response()->json(['message' => 'Drone created successfully']);
    }
    public function destroy($id)
    {
        $drone = Drone::find($id);
    
        if (!$drone) {
            return response()->json(['message' => 'Drone not found.'], 404);
        }
    
        $drone->delete();
    
        return response()->json(['message' => 'Drone deleted successfully.']);
    }
    
}
