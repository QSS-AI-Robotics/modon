<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserType;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
