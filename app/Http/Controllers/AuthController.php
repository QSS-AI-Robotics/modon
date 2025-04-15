<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserType;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
         // Validate incoming request
         $validator = Validator::make($request->all(), [
             'email'    => 'required|email',
             'password' => 'required',
         ]);
     
         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
         }
     
         // Attempt authentication
         if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
             $user = Auth::user();
     
             // Get user type name from related table
             $userType = UserType::find($user->user_type_id);
             $userTypeName = $userType ? $userType->name : 'unknown';
     
             // Log the login event
             Log::info('User logged in', [
                 'user_id'    => $user->id,
                 'email'      => $user->email,
                 'user_type'  => $userTypeName,
                 'ip_address' => $request->ip(),
                 'timestamp'  => now()->toDateTimeString(),
             ]);
     
             // Redirect based on user type
             switch ($userTypeName) {
                 case 'qss_admin':
                 case 'modon_admin':
                     $redirect = route('admin.index');
                     break;
     
                 case 'pilot':
                     $redirect = route('pilot.index');
                     break;
     
                 case 'region_manager':
                     $redirect = route('missions.index');
                     break;
                 case 'region_manager':
                 case 'city_manager':
                 case 'city_supervisor':
                     $redirect = route('missions.index');
                     break;
     
                 default:
                     $redirect = '/';
                     break;
             }
     
             return response()->json([
                 'message' => 'Login successful',
                 'redirect' => $redirect
             ]);
         }
     
         // Authentication failed
         return response()->json([
             'error' => 'Invalid email or password'
         ], 401);
     }

     
    // public function loginUser(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);
    
    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }
    
    //     if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
    //         return response()->json(['message' => 'Login successful', 'redirect' => route('admin.index')], 200);
    //     } else {
    //         return response()->json(['error' => 'Invalid email or password'], 401);
    //     }
    // }

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
        return response()->json([
            'message' => 'Logged out successfully',
            'redirect' => route('signin.form') . '/'
        ]);
    }
}
