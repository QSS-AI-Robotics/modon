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
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $regionId = Auth::user()->region_id;

        $missions = Mission::where('region_id', $regionId)
            ->with(['inspectionTypes:id,name', 'locations:id,name'])
            ->get();

        return response()->json(['missions' => $missions]);
    }

    /**
     * Fetch pilot reports assigned to missions in the pilot's region.
     */
    public function getReports()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }
    
        $regionId = Auth::user()->region_id;
    
        $reports = PilotReport::whereHas('mission', function ($query) use ($regionId) {
            $query->where('region_id', $regionId);
        })->with('mission', 'images')->get();
    
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
    
//      public function editReport($id)
// {
//     $report = PilotReport::with('images')->findOrFail($id);

//     // ✅ Fetch associated incidents with images
//     $incidents = $report->images->map(function ($image) {
//         return [
//             'id' => $image->id,
//             'inspection_type_id' => $image->inspection_type_id,
//             'location_id' => $image->location_id,
//             'description' => $image->description,
//             'images' => [$image->image_path], // You can extend this if multiple images per incident
//         ];
//     });

//     return response()->json([
//         'mission_id' => $report->mission_id,
//         'start_datetime' => $report->start_datetime,
//         'end_datetime' => $report->end_datetime,
//         'video_url' => $report->video_url,
//         'description' => $report->description,
//         'incidents' => $incidents,
//         'inspections' => InspectionType::all(['id', 'name']), // Send all available inspections
//         'locations' => Location::all(['id', 'name']), // Send all available locations
//     ]);
// }



    /**
     * Update an existing report.
     */
    public function updateReport(Request $request, $reportId)
{
    Log::info("🚀 Incoming Report Update Request", ['report_id' => $reportId, 'data' => $request->all()]);

    // ✅ Decode JSON from `data`
    $requestData = json_decode($request->input('data'), true);

    if (!$requestData) {
        return response()->json(['error' => 'Invalid JSON format'], 400);
    }

    // ✅ Fetch Existing Report
    $report = PilotReport::find($reportId);

    if (!$report) {
        return response()->json(['error' => 'Report not found'], 404);
    }

    // ✅ Update Report Fields
    $report->start_datetime = $requestData['start_datetime'];
    $report->end_datetime = $requestData['end_datetime'];
    $report->video_url = $requestData['video_url'] ?? null;
    $report->description = $requestData['description'] ?? '';
    $report->save();
    Log::info("✅ Updated Main Report", ['report' => $report]);

    // ✅ Fetch Existing Images From Database
    $existingImages = PilotReportImage::where('pilot_report_id', $report->id)->get();
    Log::info("📌 Existing Images Before Update", ['images' => $existingImages]);

    // ✅ Extract Incoming Image IDs
    $incomingImageIds = collect($requestData['pilot_report_images'])->pluck('id')->filter()->toArray();

    // ✅ Delete Images That Are Not in the Incoming Data
    foreach ($existingImages as $existingImage) {
        if (!in_array($existingImage->id, $incomingImageIds)) {
            Log::info("🗑️ Deleting Removed Image", ['image_id' => $existingImage->id, 'path' => $existingImage->image_path]);

            // Delete the file from storage (optional)
            if (Storage::exists(str_replace("storage/", "", $existingImage->image_path))) {
                Storage::delete(str_replace("storage/", "", $existingImage->image_path));
            }

            // Delete record from the database
            $existingImage->delete();
        }
    }

    // ✅ Process Incoming Images
    foreach ($requestData['pilot_report_images'] as $index => $imageData) {
        if (!isset($imageData['inspection_id']) || !isset($imageData['location_id'])) {
            Log::warning("⚠️ Missing data for image entry", ['index' => $index]);
            continue;
        }

        if (!empty($imageData['id'])) {
            // ✅ Update Existing Image
            $imageRecord = PilotReportImage::find($imageData['id']);
            if ($imageRecord) {
                $imageRecord->inspection_type_id = $imageData['inspection_id'];
                $imageRecord->location_id = $imageData['location_id'];
                $imageRecord->description = $imageData['description'] ?? '';

                // ✅ Handle New Image Uploads
                if (!empty($imageData['new_images']) && $request->hasFile("images_{$index}")) {
                    foreach ($request->file("images_{$index}") as $image) {
                        $path = $image->store('reports', 'public');
                        $imageRecord->image_path = "storage/$path";
                        Log::info("🔄 Updated Image Path", ['image_path' => "storage/$path"]);
                    }
                }

                $imageRecord->save();
                Log::info("🔄 Updated Image Record", ['image' => $imageRecord]);
            }
        } else {
            // ✅ Add New Image
            if (!empty($imageData['new_images']) && $request->hasFile("images_{$index}")) {
                foreach ($request->file("images_{$index}") as $image) {
                    $path = $image->store('reports', 'public');
                    PilotReportImage::create([
                        'pilot_report_id' => $report->id,
                        'inspection_type_id' => $imageData['inspection_id'],
                        'location_id' => $imageData['location_id'],
                        'description' => $imageData['description'] ?? '',
                        'image_path' => "storage/$path",
                    ]);
                    Log::info("✅ Created New Image", ['image_path' => "storage/$path"]);
                }
            }
        }
    }

    return response()->json(['message' => '✅ Report updated successfully!']);
}

//     public function updateReport(Request $request, $reportId)
// {
//     Log::info("🚀 Incoming Report Update Request", ['report_id' => $reportId, 'data' => $request->all()]);

//     // ✅ Decode JSON from `data`
//     $requestData = json_decode($request->input('data'), true);

//     if (!$requestData) {
//         return response()->json(['error' => 'Invalid JSON format'], 400);
//     }

//     // ✅ Ensure required fields are present
//     if (!isset($requestData['report_id']) || !isset($requestData['start_datetime']) || !isset($requestData['end_datetime'])) {
//         return response()->json(['error' => 'Missing required fields'], 400);
//     }

//     // ✅ Find and Update Main Report
//     $report = PilotReport::find($reportId);

//     if (!$report) {
//         return response()->json(['error' => 'Report not found'], 404);
//     }

//     $report->start_datetime = $requestData['start_datetime'];
//     $report->end_datetime = $requestData['end_datetime'];
//     $report->video_url = $requestData['video_url'] ?? null;
//     $report->description = $requestData['description'] ?? '';
//     $report->save();

//     Log::info("✅ Updated Main Report", ['report' => $report]);

//     // ✅ Process Pilot Report Images
//     foreach ($requestData['pilot_report_images'] as $index => $imageData) {
//         // ✅ Ensure required fields exist
//         if (!isset($imageData['inspection_id']) || !isset($imageData['location_id'])) {
//             Log::warning("⚠️ Missing data for image entry", ['index' => $index]);
//             continue;
//         }

//         // ✅ Check if the image record exists
//         if (!empty($imageData['id'])) {
//             $imageRecord = PilotReportImage::find($imageData['id']);
//             if ($imageRecord) {
//                 // ✅ Update Existing Image Record
//                 $imageRecord->inspection_type_id = $imageData['inspection_id'];
//                 $imageRecord->location_id = $imageData['location_id'];
//                 $imageRecord->description = $imageData['description'] ?? '';

//                 // ✅ Handle New Image Uploads
//                 if (!empty($imageData['new_images']) && $request->hasFile("images_{$index}")) {
//                     foreach ($request->file("images_{$index}") as $image) {
//                         $path = $image->store('reports', 'public');
//                         $imageRecord->image_path = "storage/$path";
//                         Log::info("🔄 Updated Image Path", ['image_path' => "storage/$path"]);
//                     }
//                 }

//                 $imageRecord->save();
//                 Log::info("🔄 Updated Image Record", ['image' => $imageRecord]);
//             }
//         } else {
//             // ✅ Create New Image Record if Needed
//             if (!empty($imageData['new_images']) && $request->hasFile("images_{$index}")) {
//                 foreach ($request->file("images_{$index}") as $image) {
//                     $path = $image->store('reports', 'public');
//                     PilotReportImage::create([
//                         'pilot_report_id' => $report->id,
//                         'inspection_type_id' => $imageData['inspection_id'],
//                         'location_id' => $imageData['location_id'],
//                         'description' => $imageData['description'] ?? '',
//                         'image_path' => "storage/$path",
//                     ]);
//                     Log::info("✅ Created New Image", ['image_path' => "storage/$path"]);
//                 }
//             }
//         }
//     }

//     return response()->json(['message' => '✅ Report updated successfully!']);
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
