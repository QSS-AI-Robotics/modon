<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserType;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show the signup form.
     */
    public function showSignupForm()
    {
        $userTypes = UserType::all();
        $regions = Region::all();

        return view('auth.signup', compact('userTypes', 'regions'));
    }

    /**
     * Handle AJAX signup request.
     */
    public function registerUser(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'user_type' => 'required|exists:user_types,id',
            'region' => 'required|exists:regions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the user
        $user = User::create([
            'name' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => $request->user_type,
            'region_id' => $request->region,
        ]);

        return response()->json(['message' => 'User registered successfully!'], 201);
    }
    /**
     * Show the signin form.
     */
    public function showSigninForm()
    {
        return view('auth.signin');
    }

    /**
     * Handle AJAX login request.
     */
    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['message' => 'Login successful', 'redirect' => route('dashboard')], 200);
        } else {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }
    }

    /**
     * Show the dashboard.
     */
    public function dashboard()
    {
        return view('dashboard', ['user' => Auth::user()]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return response()->json(['message' => 'Logged out successfully', 'redirect' => route('signin.form')]);
    }
}
