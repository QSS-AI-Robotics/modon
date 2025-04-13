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

        return response()->json([
            'message' => '✅ Drone added successfully!'
        ]);
    }

    public function updatedrone(Request $request, $id)
    {
        $drone = Drone::findOrFail($id);

        $validated = $request->validate([
            'model' => 'required|string|max:255',
            'sr_no' => 'required|string|unique:drones,sr_no,' . $drone->id,
            'user_id' => 'required|exists:users,id'
        ]);

        $drone->update($validated);

        return response()->json([
            'message' => '✅ Drone updated successfully!'
        ]);
    }


    public function destroy($id)
    {
        // ✅ Optional: Only allow authenticated users
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
    
        $drone = Drone::find($id);
    
        if (!$drone) {
            return response()->json(['message' => 'Drone not found.'], 404);
        }
    
        // ✅ Optional: Add user permission check (e.g., only admin or drone creator can delete)
        $user = Auth::user();
        if ($user->userType->name !== 'qss_admin') {
            return response()->json(['message' => 'You are not authorized to delete this drone.'], 403);
        }
    
        $drone->delete();
    
        return response()->json(['message' => '✅ Drone deleted successfully.']);
    }
    
    
}
