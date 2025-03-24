<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Mission;
use App\Models\PilotReport;
use App\Models\PilotReportImage;
use App\Models\InspectionType;
use App\Models\Location;
use Illuminate\Support\Facades\Log; // ✅ Import Log Facade
use Illuminate\Support\Facades\Storage;


class PilotController extends Controller
{
    /**
     * Display the pilot's mission page.
     */
    public function index()
    {
        return view('pilot.pilot'); // ✅ Loads `pilot.blade.php`
    }

    /**
     * Fetch missions assigned to the pilot's region.
     */
    public function getMissions()
    {
        // ✅ Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        // ✅ Get the authenticated pilot's region
        $user = Auth::user();
        $regionId = $user->region_id;
    
        Log::info("Fetching Missions for Region ID:", ['region_id' => $regionId, 'user_id' => $user->id]);
    
        // ✅ Fetch only missions for the pilot's region
        $missions = Mission::where('region_id', $regionId)
            ->with(['inspectionTypes:id,name', 'locations:id,name'])
            ->get();
    
        return response()->json(['missions' => $missions]);
    }
    
    //this belong to show all missions 
    // public function getMissions()
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //     }

    //     $regionId = Auth::user()->region_id;

    //     $missions = Mission::where('region_id', $regionId)
    //         ->with(['inspectionTypes:id,name', 'locations:id,name'])
    //         ->get();

    //     return response()->json(['missions' => $missions]);
    // }

    /**
     * Fetch pilot reports assigned to missions in the pilot's region.
     */
    // public function getReports()
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
    //     }
    
    //     $regionId = Auth::user()->region_id;
    
    //     $reports = PilotReport::whereHas('mission', function ($query) use ($regionId) {
    //         $query->where('region_id', $regionId);
    //     })->with('mission', 'images')->get();
    
    //     return response()->json(['reports' => $reports]);
    // }
    public function getReports(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $regionId = Auth::user()->region_id;
        $missionId = $request->input('mission_id');
    
        $reports = PilotReport::whereHas('mission', function ($query) use ($regionId, $missionId) {
            $query->where('region_id', $regionId);
            if ($missionId) {
                $query->where('id', $missionId);
            }
        })
        ->with([
            'mission',
            'images.inspectionType', // 👈 eager load inspection type
            'images.location'        // 👈 eager load location
        ])
        ->get();
    
        return response()->json(['reports' => $reports]);
    }
    


    /**
     * Store a new pilot report.
     */
    

 

public function storeReport(Request $request)
{
    Log::info("🚀 Incoming Report Submission:", $request->all());

    // ✅ Validate the request
    $request->validate([
        'mission_id' => 'required|exists:missions,id',
        'start_datetime' => 'required|date',
        'end_datetime' => 'required|date|after:start_datetime',
        'video_url' => 'nullable|url',
        'description' => 'nullable|string',

        'inspection_id' => 'required|array',
        'inspection_id.*' => 'required|exists:inspection_types,id',

        'location_id' => 'required|array',
        'location_id.*' => 'required|exists:locations,id',

        'inspectiondescrption.*' => 'nullable|string',

        'images_*.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:102048',
    ]);

    // ✅ Generate a unique report reference
    $reportReference = 'REP-' . Str::random(8);

    // ✅ Create the report
    $report = PilotReport::create([
        'report_reference' => $reportReference,
        'mission_id' => $request->mission_id,
        'start_datetime' => $request->start_datetime,
        'end_datetime' => $request->end_datetime,
        'video_url' => $request->video_url,
        'description' => $request->description,
    ]);

    Log::info("✅ Report Created Successfully", ['report_id' => $report->id]);

    // ✅ Process each incident (inspection, location, description)
    foreach ($request->inspection_id as $index => $inspectionId) {
        $locationId = $request->location_id[$index] ?? null;
        $inspectionDescription = $request->inspectiondescrption[$index] ?? '';

        Log::info("📌 Processing Incident #$index", [
            'inspection_id' => $inspectionId,
            'location_id' => $locationId,
            'description' => $inspectionDescription
        ]);

        // ✅ Process images for this inspection-location pair
        $imageField = "images_{$index}";
        if ($request->hasFile($imageField)) {
            foreach ($request->file($imageField) as $image) {
                $path = $image->store('reports', 'public');

                // ✅ Ensure the correct values are logged
                Log::info("📸 Saving Image for Incident #$index", [
                    'inspection_id' => $inspectionId,
                    'location_id' => $locationId,
                    'description' => $inspectionDescription,
                    'image_path' => "storage/$path"
                ]);

                // ✅ Insert into database
                PilotReportImage::create([
                    'pilot_report_id' => $report->id,
                    'inspection_type_id' => $inspectionId, // ✅ Ensure this is set
                    'location_id' => $locationId, // ✅ Ensure this is set
                    'description' => $inspectionDescription, // ✅ Ensure this is set
                    'image_path' => "storage/$path",
                ]);
            }
        } else {
            Log::warning("⚠ No images found for Incident #$index");
        }
    }

    // ✅ Update the mission's status and report submission
    Mission::where('id', $request->mission_id)->update([
        'report_submitted' => 1,
        'status' => 'Completed'
    ]);

    Log::info("✅ Mission Updated as Completed", ['mission_id' => $request->mission_id]);

    return response()->json([
        'message' => '✅ Report created successfully!',
        'report' => $report
    ]);
}

    


    /**
     * Fetch a single report for editing.
     */
    public function editReport($id)
    {
        $report = PilotReport::with('images')->findOrFail($id);
    
        // ✅ Fetch associated incidents with images
        $incidents = $report->images->map(function ($image) {
            return [
                'id' => $image->id,
                'inspection_type_id' => $image->inspection_type_id,
                'location_id' => $image->location_id,
                'description' => $image->description,
                'images' => [$image->image_path], // Extend this if multiple images per incident
            ];
        });
    
        // ✅ Fetch only inspections from `mission_inspection_type`
        $inspections = InspectionType::whereIn('id', function ($query) use ($report) {
            $query->select('inspection_type_id')
                ->from('mission_inspection_type')
                ->where('mission_id', $report->mission_id);
        })->get(['id', 'name']);
    
        // ✅ Fetch only locations from `mission_location`
        $locations = Location::whereIn('id', function ($query) use ($report) {
            $query->select('location_id')
                ->from('mission_location')
                ->where('mission_id', $report->mission_id);
        })->get(['id', 'name']);
    
        return response()->json([
            'mission_id' => $report->mission_id,
            'start_datetime' => $report->start_datetime,
            'end_datetime' => $report->end_datetime,
            'video_url' => $report->video_url,
            'description' => $report->description,
            'incidents' => $incidents,
            'inspections' => $inspections,  // ✅ Only relevant inspections from `mission_inspection_type`
            'locations' => $locations,      // ✅ Only relevant locations from `mission_location`
        ]);
    }
    




    /**
     * Update an existing report.
     */


     public function updateReport(Request $request, $reportId)
{
    Log::info("🚀 Incoming Report Update Request", [
        'report_id' => $reportId,
        'request_raw' => $request->all()
    ]);

    $requestData = json_decode($request->input('data'), true);

    if (!$requestData) {
        return response()->json(['error' => 'Invalid JSON format'], 400);
    }

    Log::info("📦 Parsed Structured Data", ['data' => $requestData]);

    $report = PilotReport::find($reportId);
    if (!$report) {
        return response()->json(['error' => 'Report not found'], 404);
    }

    $report->update([
        'start_datetime' => $requestData['start_datetime'],
        'end_datetime' => $requestData['end_datetime'],
        'video_url' => $requestData['video_url'] ?? null,
        'description' => $requestData['description'] ?? '',
    ]);

    Log::info("✅ Report fields updated", ['report_id' => $reportId]);

    // Fetch existing images from DB
    $existingImages = PilotReportImage::where('pilot_report_id', $reportId)->get()->keyBy('image_path');
    Log::info("🔍 Existing DB Images", ['images' => $existingImages->keys()]);

    // Keep track of all image paths from frontend
    $incomingPaths = [];

    foreach ($requestData['pilot_report_images'] as $item) {
        $existingPath = ltrim($item['previous_image'] ?? '', '/');
        $incomingPaths[] = $existingPath;

        // If image already exists in DB (update case)
        if ($existingPath && $existingPath !== 'N/A' && $existingImages->has($existingPath)) {
            $image = $existingImages->get($existingPath);

            $image->update([
                'inspection_type_id' => $item['inspection_id'],
                'location_id' => $item['location_id'],
                'description' => $item['description'] ?? '',
            ]);
            Log::info("🔄 Updated Image Record", ['image_path' => $existingPath]);

            // Replace image if new one uploaded
            if (!empty($item['new_images']) && $request->hasFile("images_{$item['index']}")) {
                foreach ($request->file("images_{$item['index']}") as $file) {
                    $path = $file->store('reports', 'public');

                    if ($image->image_path !== "storage/$path") {
                        // Optionally delete old image file from storage
                        if (Storage::exists(str_replace('storage/', '', $image->image_path))) {
                            Storage::delete(str_replace('storage/', '', $image->image_path));
                        }

                        $image->update(['image_path' => "storage/$path"]);
                        Log::info("🖼️ Replaced Image File", ['new_path' => "storage/$path"]);
                    }
                }
            }
        }
        // New Image Case
        elseif (!empty($item['new_images']) && $request->hasFile("images_{$item['index']}")) {
            foreach ($request->file("images_{$item['index']}") as $file) {
                $path = $file->store('reports', 'public');

                PilotReportImage::create([
                    'pilot_report_id' => $report->id,
                    'inspection_type_id' => $item['inspection_id'],
                    'location_id' => $item['location_id'],
                    'description' => $item['description'] ?? '',
                    'image_path' => "storage/$path",
                ]);

                $incomingPaths[] = "storage/$path";
                Log::info("🆕 New Image Added", ['path' => "storage/$path"]);
            }
        }
    }

    // Delete records not present in request anymore
    foreach ($existingImages as $path => $image) {
        if (!in_array($path, $incomingPaths)) {
            if (Storage::exists(str_replace('storage/', '', $path))) {
                Storage::delete(str_replace('storage/', '', $path));
            }
            $image->delete();
            Log::info("🗑️ Deleted Removed Image", ['image_path' => $path]);
        }
    }

    return response()->json(['message' => '✅ Report and images updated successfully']);
}

    //  this is the original code which update the report images but not removing the image record
    // public function updateReport(Request $request, $reportId)
    // {
    //     Log::info("🚀 Incoming Report Update Request", [
    //         'report_id' => $reportId,
    //         'request_raw' => $request->all()
    //     ]);
    
    //     $requestData = json_decode($request->input('data'), true);
    
    //     if (!$requestData) {
    //         return response()->json(['error' => 'Invalid JSON format'], 400);
    //     }
    
    //     Log::info("📦 Parsed Structured Data", ['data' => $requestData]);
    
    //     $report = PilotReport::find($reportId);
    
    //     if (!$report) {
    //         return response()->json(['error' => 'Report not found'], 404);
    //     }
    
    //     $report->update([
    //         'start_datetime' => $requestData['start_datetime'],
    //         'end_datetime' => $requestData['end_datetime'],
    //         'video_url' => $requestData['video_url'] ?? null,
    //         'description' => $requestData['description'] ?? '',
    //     ]);
    
    //     Log::info("✅ Report fields updated", ['report_id' => $reportId]);
    
    //     foreach ($requestData['pilot_report_images'] as $item) {
    //         $index = $item['index'];
    //         $isNew = $item['previous_image'] === "N/A";
    //         $newImageCount = (int) $item['new_images'];
    
    //         if (!$isNew) {
    //             $previousImagePath = ltrim($item['previous_image'], '/');
    //             $image = PilotReportImage::where('pilot_report_id', $reportId)
    //                 ->where('image_path', $previousImagePath)
    //                 ->first();
    
    //             if ($image) {
    //                 $image->update([
    //                     'inspection_type_id' => $item['inspection_id'],
    //                     'location_id' => $item['location_id'],
    //                     'description' => $item['description'],
    //                 ]);
    //                 Log::info("🔄 Updated Image Metadata", ['id' => $image->id]);
    
    //                 // Check for new image file to replace existing
    //                 if ($newImageCount > 0 && $request->hasFile("images_{$index}")) {
    //                     foreach ($request->file("images_{$index}") as $newFile) {
    //                         $path = $newFile->store('reports', 'public');
    //                         $newFullPath = "storage/$path";
    
    //                         if ($image->image_path !== $newFullPath) {
    //                             // Optionally delete old image from storage
    //                             if (Storage::exists(str_replace('storage/', '', $image->image_path))) {
    //                                 Storage::delete(str_replace('storage/', '', $image->image_path));
    //                             }
    
    //                             $image->update(['image_path' => $newFullPath]);
    //                             Log::info("🖼️ Replaced Existing Image", ['id' => $image->id, 'new_path' => $newFullPath]);
    //                         } else {
    //                             Log::warning("⚠️ Skipped replacing with identical image", ['path' => $newFullPath]);
    //                         }
    //                     }
    //                 }
    //             } else {
    //                 Log::warning("❌ Existing image not found by path", ['path' => $previousImagePath]);
    //             }
    //         } else {
    //             // Completely new image (no previous)
    //             if ($newImageCount > 0 && $request->hasFile("images_{$index}")) {
    //                 foreach ($request->file("images_{$index}") as $newFile) {
    //                     $path = $newFile->store('reports', 'public');
    //                     $newPath = "storage/$path";
    
    //                     PilotReportImage::create([
    //                         'pilot_report_id' => $report->id,
    //                         'inspection_type_id' => $item['inspection_id'],
    //                         'location_id' => $item['location_id'],
    //                         'description' => $item['description'],
    //                         'image_path' => $newPath,
    //                     ]);
    
    //                     Log::info("🆕 Created New Image", ['image_path' => $newPath]);
    //                 }
    //             } else {
    //                 Log::warning("⚠️ Skipped new image creation: No file uploaded", ['index' => $index]);
    //             }
    //         }
    //     }
    
    //     return response()->json(['message' => '✅ Report and images updated successfully!']);
    // }
    
    
     
 




    
    /**
     * Delete a report.
     */

    public function destroyReport($id)
    {
        // ✅ Write a log before processing the deletion
        file_put_contents(storage_path('logs/debug_reportlog.txt'), "DELETE request received for report ID: $id\n", FILE_APPEND);

        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $report = PilotReport::find($id);

        if (!$report) {
            return response()->json(['error' => 'Report not found.'], 404);
        }

        // ✅ Log deletion attempt
        file_put_contents(storage_path('logs/debug_reportlog.txt'), "Deleting report ID: $id\n", FILE_APPEND);

        // ✅ Delete images from storage and database
        $images = PilotReportImage::where('pilot_report_id', $report->id)->get();
        foreach ($images as $image) {
            $imagePath = public_path($image->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath); // ✅ Delete image from storage
            }
            $image->delete(); // ✅ Delete image record from database
        }

        // ✅ Store mission ID before deleting the report
        $missionId = $report->mission_id;

        // ✅ Delete the report
        $report->delete();

        // ✅ Check if there are still reports for this mission
        $remainingReports = PilotReport::where('mission_id', $missionId)->exists();

        // ✅ If no reports remain, update `report_submitted` to 0
        if (!$remainingReports) {
            // Mission::where('id', $missionId)->update(['report_submitted' => 0]);
            Mission::where('id', $missionId)->update([
                'report_submitted' => 0,
                'status' => 'Pending' // ✅ Also update status to 'Completed'
            ]);
            
            file_put_contents(storage_path('logs/debug_reportlog.txt'), "Mission ID $missionId updated: report_submitted = 0\n", FILE_APPEND);
        }

        // ✅ Confirm the report was deleted
        file_put_contents(storage_path('logs/debug_reportlog.txt'), "Report ID $id and its images successfully deleted.\n", FILE_APPEND);

        return response()->json([
            'message' => 'Report and relevant images deleted successfully. Mission updated if needed.',
            'status' => 'success'
        ], 200);
    }

    
        /**
     * Update mission status.
     */
    public function updateMissionStatus(Request $request)
    {
        $request->validate([
            'mission_id' => 'required|exists:missions,id',
            'status' => 'required|string'
        ]);

        $mission = Mission::findOrFail($request->mission_id);
        $mission->status = $request->status;
        $mission->save();

        return response()->json([
            'message' => 'Mission status updated successfully.',
            'mission' => $mission
        ]);
    }
    
    
    
    
    
    

}
