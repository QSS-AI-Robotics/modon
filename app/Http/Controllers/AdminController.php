<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserType;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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

}
