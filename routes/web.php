<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegionManagerController;
use App\Http\Controllers\PilotController;
// ✅ Public Routes (Accessible Without Authentication)
Route::get('/', [AuthController::class, 'showSigninForm'])->name('signin.form'); // Sign-in Page
Route::post('/signin', [AuthController::class, 'loginUser'])->name('signin.store'); // Login
Route::get('/signup', [AuthController::class, 'showSignupForm'])->name('signup.form'); // Signup Page
Route::post('/signup', [AuthController::class, 'registerUser'])->name('signup.store'); // Register

// ✅ Alias for Laravel’s default login route (Fixes "Route [login] not defined.")
Route::get('/login', function () {
    return redirect()->route('signin.form'); // Redirect to "/"
})->name('login'); // ✅ Now "Route [login] exists"

// ✅ Protected Routes (Requires Authentication)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserController::class, 'index'])->name('dashboard'); // Dashboard

    // User Management (Only for authenticated users)
    Route::get('/users/{id}/edit', [UserController::class, 'edit']);
    Route::post('/users/{id}/update', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


// ✅ Protected Routes for Region Manager


Route::middleware(['auth'])->group(function () {
    Route::get('/locations', [RegionManagerController::class, 'index'])->name('locations.index');
    Route::post('/locations/store', [RegionManagerController::class, 'store'])->name('locations.store');
    Route::get('/locations/{id}/edit', [RegionManagerController::class, 'edit'])->name('locations.edit');
    Route::post('/locations/{id}/update', [RegionManagerController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{id}', [RegionManagerController::class, 'destroy'])->name('locations.destroy');
    
        Route::get('/missions', [RegionManagerController::class, 'missions'])->name('missions.index');
        Route::post('/missions/store', [RegionManagerController::class, 'storeMission'])->name('missions.store'); // ✅ Ensure this is POST
        Route::delete('/missions/{id}', [RegionManagerController::class, 'destroyMission'])->name('missions.destroy');
   
});

// Pilot Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/pilot', [PilotController::class, 'index'])->name('pilot.index');
    Route::get('/pilot/missions', [PilotController::class, 'getMissions'])->name('pilot.missions');
    Route::get('/pilot/reports', [PilotController::class, 'getReports'])->name('pilot.reports');
    Route::post('/pilot/reports/store', [PilotController::class, 'storeReport'])->name('pilot.reports.store');

    // Edit & Update Report
    Route::get('/pilot/reports/{id}/edit', [PilotController::class, 'editReport'])->name('pilot.reports.edit');
    Route::post('/pilot/reports/{id}/update', [PilotController::class, 'updateReport'])->name('pilot.reports.update');

    // Delete Report
    Route::post('/pilot/reports/{id}', [PilotController::class, 'destroyReport'])->name('pilot.reports.delete');


});