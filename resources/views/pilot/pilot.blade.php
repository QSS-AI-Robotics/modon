<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilot Missions & Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Custom CSS -->
        <link rel="stylesheet" href="{{ asset('css/modon.css') }}">
        <link rel="stylesheet" href="{{ asset('css/missions.css') }}">

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
    <div class="container-fluid vh-100 d-flex flex-column padded-container">
        <!-- Header -->
        <div class="row header shadows bg-section p-1 mb-2 align-items-center">
            <div class="col-2 d-flex align-items-center">
                <img src="{{ asset('images/qss.png') }}" alt="Logo" class="w-50">
            </div>
            <div class="col-7 d-flex">
                <button class="btn cont-btn selected mx-1">Overview</button>
                <button class="btn cont-btn mx-1"><a href="/missions">Missions</a></button>
                <button class="btn cont-btn mx-1"><a href="/locations">Locations</a></button>
                <button class="btn cont-btn mx-1"><a href="/pilot">Pilot</a></button>
                <button class="btn cont-btn mx-1">Reports</button>
            </div>
            <div class="col-3 d-flex justify-content-end">
                <div class="dropdown">
                    <img src="{{ asset('images/user.png') }}" alt="Profile" class="img-fluid rounded-circle" style="max-height: 50px; cursor: pointer;">
                </div>
            </div>
        </div>
        <!-- End Header -->

                <!-- Main Panel -->
                <div class="row shadows mainPanel p-0 flex-grow-1">

                    <!-- Left Column (Mission Control & Reports) -->
                    <div class="col-lg-12 d-flex flex-column h-100">
                        
                        <!-- Mission Control Header -->
                        <div class="row">
                            <div class="col-lg-12 p-3 bg-section d-flex flex-column align-items-start">
                                <p class="gray-text">Control Panel</p>
                                <h3 class="fw-bold">Pilot Missions</h3>
                            </div>
                        </div>
        
                        <!-- Reports List -->
                        <div class="row h-100">
                            <div class="col-lg-12 col-xl-12 col-md-12 flex-grow-1 d-flex flex-column overflow-hidden bg-section mt-2">
                                
                                <!-- Reports Header -->
                                <div class="border-bottom-qss p-2">
                                    <div class="row d-flex justify-content-between">
                                        <div class="col-lg-4">
                                            <p>Detailed Summery</p>
                                        </div>
                                        <div class="col-lg-4 text-end search-container">
                                            <img src="./images/search.png" alt="Search" class="img-fluid search-icon">
                                            <input type="search" placeholder="Search Reports Here" class="search-input">
                                        </div>
                                    </div>
                                </div>
        
                                <!-- Missions Table -->
                                <div class="table-responsive flex-grow-1 overflow-auto">
                                    <table class="table table-text">
                                        <thead>
                                            <tr>
                                                <th>Inspection Types</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Locations</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="missionTableBody">
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Loading missions...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
        
                    </div> <!-- End Left Column -->
        

                    
                </div> 
                <!-- End Main Panel -->


    </div>


{{-- <div class="container mt-5">
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
    
</div> --}}


<!-- Bootstrap Modal for Show Report -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-modal">
            <div class="modal-header border-0">
                <h6 class="modal-title" id="addReportModalLabel">Detailed Report</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
       
               <div class="row">
                <div class="col-lg-7">
                    <div class="table-responsive flex-grow-1 overflow-auto">
                        <table class="table table-text">
    
                            <tbody id="reportTableBody">
                                <tr>
                                   <td colspan="8" class="text-center text-muted">Loading reports...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-5">
                    {{-- <video src="" autoplay muted class="pilot_video" controls width="100%" height="auto"></video> --}}
                    <iframe id="pilotVideo" width="100%" height="315" frameborder="0" ></iframe>


                </div>
               </div>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap Modal for Adding Reports -->

<div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-modal">
            <div class="modal-header border-0">
                <h6 class="modal-title" id="addReportModalLabel">Submit a New Report</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addReportForm">
                    @csrf
                    <input type="hidden" class="form-control" id="mission_id" name="mission_id" required>


                    <div>
                        <div class="row">
                           
                            <div class="col-lg-6">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="start_datetime" class="form-label ">Start Date & Time</label>
                                        <input type="datetime-local" class="form-control dateInput form-control-lg" id="start_datetime" name="start_datetime" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="end_datetime" class="form-label">End Date & Time</label>
                                        <input type="datetime-local" class="form-control dateInput form-control-lg"  id="end_datetime" name="end_datetime" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <!-- Upload Video URL -->
    
                                    <div class="mb-3">
                                        <label for="video_url" class="form-label">Video URL</label>
                                        <input type="url" class="form-control dateInput  form-control-lg" id="video_url" name="video_url" placeholder="Enter video link" value="http://localhost:8080/phpmyadmin/index.php?route=/sql&db=modon&table=pilot_report_images&pos=0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Notes</label>
                                    <textarea type="text" class="form-control notes-textarea " id="description" name="description" rows="11"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grouped Select Fields -->
                    <div class="col-lg-12 col-md-12" style="border: 1px solid #FFFFFF33; padding: 10px;border-radius: 10px;">
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="form-label">Incident Detail</label>
                            </div>
                            <div class="col-lg-6 text-end pb-1">
                                <button type="button" class="btn updForm addInspectionRow">+</button>
                                <button type="button" class="btn updForm removeInspectionRow">-</button>
                            </div>
                            <div class="col-lg-12">
                                <div class="inspectionLocationWrapper overflow-auto">
                                    <div class="d-flex flex-nowrap gap-3 inspectionLocationGroup" id="inspectionLocationGroup">
                                        <!-- Each inspection-location-item -->
                                        <div class="col-lg-3 col-md-3 col-sm-6 mb-3 inspection-location-item">
                                            <div class="row">
                                                <div class="col-12 mb-2">
                                                    <select class="form-select inspection_id dateInput  dateInput form-control-lg" name="inspection_id[]" id="inspection_id" required></select> 
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <select class="form-select location_id dateInput  dateInput form-control-lg" name="location_id[]" id="location_id" required></select> 
                                                </div>
                                                <div class="col-12 mb-2 ">
                                                    <div class="image-upload-box  border-secondary rounded p-3 text-center text-white" style="" onclick="this.querySelector('input[type=file]').click()">
                                                        <p class="mb-2">Click to Upload Images</p>
                                                        <div class="image-preview d-flex flex-wrap gap-2 justify-content-start"></div>
                                                        <input type="file" class="form-control d-none images" name="images_0[]" multiple accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <input type="text" class="form-control inspectiondescrption dateInput text-white form-control-lg" name="inspectiondescrption[]" placeholder="Inspection Description">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                  
                        </div>
                    </div>
                   
                        <button type="submit" class="btn btn-primary w-25 mt-2">Submit Report</button>
                   
                    
                </form>
            </div>
        </div>
    </div>
</div>






<!-- Bootstrap Modal for Updating Reports -->

<!-- Bootstrap Modal for Editing Reports -->
<div class="modal fade" id="editReportModal" tabindex="-1" aria-labelledby="editReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editReportModalLabel">Edit Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editReportForm">
                    @csrf
                    <input type="hidden" class="form-control" id="edit_report_id" name="report_id" required>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_start_datetime" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" class="form-control" id="edit_start_datetime" name="start_datetime" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_end_datetime" class="form-label">End Date & Time</label>
                                <input type="datetime-local" class="form-control" id="edit_end_datetime" name="end_datetime" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control" id="edit_video_url" name="video_url">
                            </div>
                        </div>
                    </div>

                    <!-- Incident Details -->
                    <div id="editInspectionLocationGroup"></div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_description" name="editdescription" rows="3"></textarea>
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
<script>
    // Image preview logic
    $(document).on('change', '.images', function () {
        const previewContainer = $(this).closest('.image-upload-box').find('.image-preview');
        previewContainer.empty();

        const files = this.files;
        if (files.length > 0) {
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = $('<img>').attr('src', e.target.result).css({ width: '100px', height: '100px', objectFit: 'cover', borderRadius: '8px' });
                    previewContainer.append(img);
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>
</body>
</html>
