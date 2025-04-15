<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegionManagerController;
use App\Http\Controllers\PilotController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DroneController;
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
    Route::get('/users', [UserController::class, 'index'])->name('dashboard'); // Dashboard
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // User Management (Only for authenticated users)
    Route::get('/users/{id}/edit', [UserController::class, 'edit']);
    Route::post('/users/{id}/update', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);


});


// ✅ Protected Routes for Region Manager


Route::middleware(['auth', 'checkUserType:city_supervisor,qss_admin,city_manager,region_manager'])->group(function () {

    
    Route::get('/missions', [RegionManagerController::class, 'index'])->name('missions.index');
    Route::get('/getmanagermissions', [RegionManagerController::class, 'getmanagermissions'])->name('missions.getmanagermissions');
    Route::post('/missions/store', [RegionManagerController::class, 'storeMission'])->name('missions.store'); 
    Route::post('/missions/{id}', [RegionManagerController::class, 'destroyMission'])->name('missions.destroy');
    Route::get('/missions/{id}/edit', [RegionManagerController::class, 'editMission'])->name('missions.edit');
    Route::post('/missions/update', [RegionManagerController::class, 'updateMission'])->name('missions.update');
    Route::get('/missions/stats', [RegionManagerController::class, 'getMissionStats'])->name('missions.stats');
   // web.php or a controller group
    Route::get('/missions/inspection-data', [RegionManagerController::class, 'getInspectionTypes'])->name('missions.inspectionTypes');
    Route::get('/missions/location-data', [RegionManagerController::class, 'getLocations'])->name('missions.locations');

});

// Pilot Routes
Route::middleware(['auth', 'checkUserType:pilot'])->group(function () {
    Route::get('/pilot', [PilotController::class, 'index'])->name('pilot.index');
    Route::get('/pilot/missions', [PilotController::class, 'getMissions'])->name('pilot.missions');
    Route::get('/pilot/reports', [PilotController::class, 'getReports'])->name('pilot.reports');
    Route::post('/pilot/reports/store', [PilotController::class, 'storeReport'])->name('pilot.reports.store');

    // Edit & Update Report
    Route::get('/pilot/reports/{id}/edit', [PilotController::class, 'editReport'])->name('pilot.reports.edit');
    Route::post('/pilot/reports/{id}/update', [PilotController::class, 'updateReport'])->name('pilot.reports.update');

    // Delete Report
    Route::post('/pilot/reports/{id}', [PilotController::class, 'destroyReport'])->name('pilot.reports.delete');

    //update mission status
    Route::post('/pilot/missions/update-status', [PilotController::class, 'updateMissionStatus']);

});

// Admin Routes
Route::middleware(['auth', 'checkUserType:qss_admin'])->group(function () {

    Route::get('/admin/users', [AdminController::class, 'adminusers'])->name('admin.adminusers');
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/dashboard/users', [AdminController::class, 'getAllUsers'])->name('admin.getUsers');
    Route::post('/dashboard/user/{id}', [AdminController::class, 'deleteUser'])->name('admin.deleteUser');
    Route::post('/dashboard/users/storeuser', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/dashboard/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::get('/missions-by-region', [AdminController::class, 'missionsByRegion'])->name('missions.by.region');
    Route::get('/inspections-by-region', [AdminController::class, 'inspectionsByRegion'])->name('inspections.by.region');
    Route::get('/pilot-mission-summary', [AdminController::class, 'pilotMissionSummary']);
    Route::get('/latest-inspections', [AdminController::class, 'latestInspections']);
    Route::get('/latest-missions', [AdminController::class, 'latestMissions'])->name('missions.latest');

    Route::get('/locations', [AdminController::class, 'locations'])->name('locations.locations');
    Route::get('/get-locations', [AdminController::class, 'fetchLocations'])->name('locations.get');
    Route::post('/locations/store', [AdminController::class, 'store'])->name('locations.store');
    Route::get('/locations/{id}/edit', [AdminController::class, 'edit'])->name('locations.edit');
    Route::post('/locations/{id}/update', [AdminController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{id}', [AdminController::class, 'destroy'])->name('locations.destroy');
    
    
    Route::get('/drones', [DroneController::class, 'index'])->name('drones.index');
    Route::post('/drones', [DroneController::class, 'store'])->name('drones.store');
    Route::put('/update-drone/{id}/update', [DroneController::class, 'updatedrone'])->name('drone.updatedrone');
    Route::post('/delete-drone/{id}', [DroneController::class, 'destroy'])->name('drone.delete');

});
Route::middleware(['auth'])->group(function () {


});  
