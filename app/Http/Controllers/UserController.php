<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserType;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
class UserController extends Controller
{
    /**
     * Show the dashboard and users list (only for qss_admin)
     */
    public function index()
    {
        $user = Auth::user();

        // Only show users table if the logged-in user is qss_admin
        $users = ($user->userType->name === 'qss_admin') ? User::with(['userType', 'region'])->get() : [];

        return view('dashboard', compact('user', 'users'));
    }

    /**
     * Fetch a user for editing (AJAX request).
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $userTypes = UserType::all();
        $regions = Region::all();

        return response()->json([
            'user' => $user,
            'userTypes' => $userTypes,
            'regions' => $regions
        ]);
    }

    /**
     * Update user details.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'region' => 'required|exists:regions,id',
            'user_type' => 'required|exists:user_types,id',
        ]);

        $user->update([
            'name' => $request->fullname,
            'email' => $request->email,
            'region_id' => $request->region,
            'user_type_id' => $request->user_type,
        ]);

        return response()->json(['message' => 'User updated successfully!']);
    }

    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    
        return response()->json(['message' => 'User deleted successfully!']);
    }
    public function resetPassword(Request $request)
    {
        Log::info('Password reset request received', ['user_id' => Auth::id()]);

        try {
            // Validate the request
            $request->validate([
                'currentPassword' => 'required',
                'newPassword' => 'required|min:8|confirmed', // Ensure newPassword and confirmNewPassword match
            ]);
            Log::info('Validation passed', ['user_id' => Auth::id()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors and return them as a JSON response
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        }

        $user = User::findOrFail(Auth::id());

        // Check if the current password is correct
        if (!Hash::check($request->currentPassword, $user->password)) {
            Log::warning('Current password is incorrect', ['user_id' => $user->id]);
            return response()->json(['error' => 'Current password is incorrect.'], 400);
        }

        // Update the user's password
        Log::info('Updating password for user', ['user_id' => $user->id]);
        $user->password = Hash::make($request->newPassword);
        $user->force_password_reset = false; // Disable forced password reset
        $user->save();

        Log::info('Password updated successfully', ['user_id' => $user->id]);

        return response()->json(['message' => 'Password updated successfully!']);
    
    }
}
