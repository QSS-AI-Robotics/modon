@extends('layouts.app') <!-- or whatever your layout is -->

@section('content')


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


   



        <!-- Bootstrap Modal for Show Report -->
        <div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content bg-modal">
                    <div class="modal-header border-0">
                        <h6 class="modal-title" id="viewReportModalLabel">Detailed Report</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row h-100">
                            <!-- LEFT COLUMN -->
                            <div class="col-lg-7 h-100 report-modal-content">
                                <div class="table-responsive report-table-container">
                                    <table class="table table-text mb-0">
                                        <tbody id="reportTableBody">
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">Loading reports...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- RIGHT COLUMN -->
                            <div class="col-lg-5 h-100 video-section">
                                <h6 class="pb-2">Video Feed</h6>
                                <iframe id="pilotVideo" width="100%" frameborder="0"></iframe>

                                <h6 class="my-2">Mission Notes</h6>
                                <textarea class="pilot_note notes-textarea w-100" disabled></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                                                <input type="url" class="form-control dateInput  form-control-lg" id="video_url" name="video_url" placeholder="Enter video link" value="https://www.youtube.com/watch?v=CyORBodMwzI">
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
        <div class="modal fade" id="updateReportModal" tabindex="-1" aria-labelledby="updateReportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content bg-modal">
                    <div class="modal-header border-0">
                        <h6 class="modal-title" id="updateReportModalLabel">Update Report</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="updateReportForm">
                            @csrf
                            <input type="hidden" class="form-control" id="edit_report_id" name="report_id">

                            <div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="edit_start_datetime" class="form-label">Start Date & Time</label>
                                                <input type="datetime-local" class="form-control dateInput form-control-lg" id="edit_start_datetime" name="start_datetime" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="edit_end_datetime" class="form-label">End Date & Time</label>
                                                <input type="datetime-local" class="form-control dateInput form-control-lg" id="edit_end_datetime" name="end_datetime" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="edit_video_url" class="form-label">Video URL</label>
                                                <input type="url" class="form-control dateInput form-control-lg" id="edit_video_url" name="video_url" placeholder="Enter video link">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="edit_description" class="form-label">Notes</label>
                                            <textarea class="form-control notes-textarea" id="edit_description" name="description" rows="11"></textarea>
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
                                        <button type="button" class="btn updForm updaddInspectionRow">+</button>
                                        <button type="button" class="btn updForm updremoveInspectionRow">-</button>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="inspectionLocationWrapper overflow-auto">
                                            <div class="d-flex flex-nowrap gap-3 updateInspectionLocationGroup" id="updateInspectionLocationGroup">
                                                <!-- JS will dynamically populate this -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-25 mt-2">Update Report</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>



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

        @endsection
