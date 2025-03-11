<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Mission;
use App\Models\PilotReport;
use App\Models\PilotReportImage;
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
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $request->validate([
            'mission_id' => 'required|exists:missions,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'video_url' => 'nullable|url',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Generate unique report reference
        $reportReference = 'REP-' . Str::random(8);

        $report = PilotReport::create([
            'report_reference' => $reportReference,
            'mission_id' => $request->mission_id,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'video_url' => $request->video_url,
            'description' => $request->description,
        ]);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reports', 'public'); // ✅ Store in 'public' disk
        
                PilotReportImage::create([
                    'pilot_report_id' => $report->id,
                    'image_path' => "storage/$path", // ✅ Save correct path
                ]);
            }
        }
        

        return response()->json(['message' => 'Report created successfully!', 'report' => $report]);
    }
    /**
     * Fetch a single report for editing.
     */
    public function editReport($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
        }

        $report = PilotReport::with('mission')->find($id);

        if (!$report) {
            return response()->json(['error' => 'Report not found.'], 404);
        }

        return response()->json($report);
    }


    /**
     * Update an existing report.
     */


     public function updateReport(Request $request, $id)
     {
         if (!Auth::check()) {
             return response()->json(['error' => 'Unauthorized access. Please log in.'], 401);
         }
     
         $request->validate([
             'start_datetime' => 'required|date',
             'end_datetime' => 'required|date|after:start_datetime',
             'description' => 'nullable|string',
             'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
         ]);
     
         // ✅ Find the report
         $report = PilotReport::findOrFail($id);
     
         // ✅ Update report details
         $report->update([
             'start_datetime' => $request->start_datetime,
             'end_datetime' => $request->end_datetime,
             'description' => $request->description,
         ]);
     
         // ✅ Get existing images from frontend
         $existingImages = json_decode($request->existing_images, true) ?? [];
         
         // 🔥 Fix path inconsistencies
         $existingImages = array_map(fn($img) => ltrim($img, "/"), $existingImages);
         Log::info("🔍 Existing Images from Frontend (Cleaned Paths):", $existingImages);
     
         // ✅ Fetch all current images from the database (without `/storage/`)
         $currentImages = PilotReportImage::where('pilot_report_id', $report->id)
             ->pluck('image_path')
             ->map(fn($img) => ltrim($img, "/"))
             ->toArray();
         Log::info("📂 Current Images in Database (Cleaned Paths):", $currentImages);
     
         // ✅ Delete only images that were removed by the user
         $imagesToDelete = array_diff($currentImages, $existingImages);
         Log::info("🗑️ Images to be Deleted:", $imagesToDelete);
     
         foreach ($imagesToDelete as $imagePath) {
             Storage::disk('public')->delete(str_replace("storage/", "", $imagePath)); // ✅ Delete from storage
             PilotReportImage::where('image_path', $imagePath)->delete(); // ✅ Remove record from DB
         }
     
         // ✅ Handle new images (avoid duplication)
         if ($request->hasFile('images')) {
             $newImages = [];
             foreach ($request->file('images') as $image) {
                 $path = $image->store('reports', 'public'); 
                 $imagePath = "storage/$path";
     
                 // ✅ Check if this file already exists in the database before adding
                 $imageExists = PilotReportImage::where('pilot_report_id', $report->id)
                     ->where('image_path', $imagePath)
                     ->exists();
     
                 if (!$imageExists) { // Only insert if the image is NOT already in the DB
                     PilotReportImage::create([
                         'pilot_report_id' => $report->id,
                         'image_path' => $imagePath,
                     ]);
     
                     $newImages[] = $imagePath; // Track uploaded images
                     $existingImages[] = ltrim($imagePath, "/"); // 🔥 Update existing images list
                 }
             }
             Log::info("📸 New Images Uploaded (Without Duplicates):", $newImages);
         }
     
         // ✅ Debugging Response
         $finalImages = PilotReportImage::where('pilot_report_id', $report->id)->pluck('image_path')->toArray();
         Log::info("✅ Final Existing Images after Update:", $finalImages);
     
         return response()->json([
             'message' => 'Report updated successfully!',
             'final_images' => $finalImages
         ]);
     }
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
    
        // ✅ Delete the report
        $report->delete();
    
        // ✅ Confirm the report was deleted
        file_put_contents(storage_path('logs/debug_reportlog.txt'), "Report ID $id and its images successfully deleted.\n", FILE_APPEND);
    
        return response()->json([
            'message' => 'Report and relevant images deleted successfully.',
            'status' => 'success'
        ], 200);
    }
    
    
    
    
    
    
    
    
    

}
