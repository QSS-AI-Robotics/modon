<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilot Missions & Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .image-preview {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .image-preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between">
        <h2>Missions Assigned to Your Region</h2>
        {{-- <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReportModal">Add Report</button> --}}
        <button id="addReportBtn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReportModal">
            Add Report
        </button>
    </div>

    <!-- Missions Table -->
    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>Inspection Types</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Locations</th>
            </tr>
        </thead>
        <tbody id="missionTableBody">
            <tr>
                <td colspan="4" class="text-center text-muted">Loading missions...</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="container mt-5">
    <h2>Submitted Reports</h2>

    <!-- Reports Table -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Mission</th>
                <th>Report Reference</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Video</th>
                <th>Images</th>
                <th>Description</th>
                <th>Actions</th> <!-- ✅ Added -->
            </tr>
        </thead>
        <tbody id="reportTableBody">
            <tr>
                <td colspan="8" class="text-center text-muted">Loading reports...</td>
            </tr>
        </tbody>
    </table>
    
</div>

<!-- Bootstrap Modal for Adding Reports -->
<div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReportModalLabel">Submit a New Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addReportForm">
                    @csrf

                    <div class="mb-3">
                        <label for="mission_id" class="form-label">Select Mission</label>
                        <select class="form-select" id="mission_id" name="mission_id" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="start_datetime" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required>
                    </div>

                    <div class="mb-3">
                        <label for="end_datetime" class="form-label">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
                    </div>
                    <!-- Upload Video URL -->
                    <div class="mb-3">
                        <label for="video_url" class="form-label">Video URL (Optional)</label>
                        <input type="url" class="form-control" id="video_url" name="video_url" placeholder="Enter video link">
                    </div>

                    <div class="mb-3">
                        <label for="images" class="form-label">Upload Images</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                        <div class="image-preview mt-2" id="imagePreview"></div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Submit Report</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Bootstrap Modal for Updating Reports -->
<div class="modal fade" id="editReportModal" tabindex="-1" aria-labelledby="editReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editReportForm">
                    @csrf
                    <input type="hidden" id="edit_report_id" name="report_id"> <!-- ✅ Added name attribute -->
                
                    <div class="mb-3">
                        <label for="edit_start_datetime" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="edit_start_datetime" name="start_datetime" required> <!-- ✅ Added name -->
                    </div>
                
                    <div class="mb-3">
                        <label for="edit_end_datetime" class="form-label">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="edit_end_datetime" name="end_datetime" required> <!-- ✅ Added name -->
                    </div>
                
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description"></textarea> <!-- ✅ Added name -->
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">Current Images</label>
                        <div class="image-preview" id="editImagePreview"></div>
                    </div>
                
                    <div class="mb-3">
                        <label for="edit_images" class="form-label">Upload New Images</label>
                        <input type="file" class="form-control" id="edit_images" name="images[]" multiple accept="image/*"> <!-- ✅ Added name -->
                    </div>
                
                    <button type="submit" class="btn btn-primary w-100">Update Report</button>
                </form>
                
            </div>
        </div>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/pilot.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
