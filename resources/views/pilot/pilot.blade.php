
@extends('layouts.app')

@section('title', 'Pilot Dashboard')

@section('content')
                <!-- Main Panel -->
                <div class="row shadows mainPanel p-0 flex-grow-1">

                    <!-- Left Column (Mission Control & Reports) -->
                    <div class="col-lg-12 d-flex flex-column h-100">
                        
                        <!-- Mission Control Header -->
                        <div class="row">
                            <div class="col-lg-12 p-3 bg-section d-flex flex-column align-items-start">
                                <p class="gray-text">{{ ucwords(str_replace('_', ' ', $userType)) }} Panel</p>
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
                                            <input type="search" placeholder="Search Reports Here" class="search-input dateInput">
                                        </div>
                                    </div>
                                </div>
        

                                <div class="flex-grow 1">
                                    <div class="row fw-bold custom-bborder  label-text w-100  px-3 py-2 justify-content-between">
                                        <div class="col-3 ">Inspection Type</div>
                                        <div class="col-2 ">Mission Date</div>
                                        <div class="col-3 text-center">Location</div>
                                        <div class="col-2 text-center">Status</div>
                                        <div class="col-2 text-center">Actions</div>
                                    </div>
                         
                                    <div class="accordion flex-grow-1 overflow-auto" id="pilotTableBody" style="max-height: 58vh;">
                                        <!-- Dynamic rows will go here -->
                                    </div>
                                    
                                </div>


                            </div>
                        </div>
        
                    </div> <!-- End Left Column -->
        

                    
                </div> 
                <!-- End Main Panel -->





<!-- Bootstrap Modal for Show Report -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-modal">
            <div class="modal-header border-0">
                <h6 class="modal-title" id="addReportModalLabel">Report Detail</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="vireReportForm">
                    @csrf
                    <input type="hidden" class="form-control" id="mission_id" name="mission_id" required>


                    <div>
                        <div class="row">
                           
                            <div class="col-lg-6">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="start_datetime" class="form-label ">Program</label>
                                        <p id="viewprogramInfo" class="whiteText text-capitlaize"></p>
                                        <label for="start_datetime" class="form-label ">Region</label>
                                        <p id="viewregionInfo" class="whiteText text-capitalize"></p>
                                        <label for="start_datetime" class="form-label ">Location</label>
                                        <p id="viewlocationInfo" class="whiteText text-capitalize"></p>

                                    </div>
                                </div>

                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <div  class="form-control  " id="description" name="description" rows="8" style="background: none;border:1px solid #FFFFFF33;min-height:150px"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grouped Select Fields -->
                    <div class="col-lg-12 col-md-12" style="border: 1px solid #FFFFFF33; padding: 10px;border-radius: 10px;">
                        <div class="row">

                           
                            <div class="col-lg-6  h-100 video-section">
        
                                    
                                <iframe id="pilotVideo" width="100%" frameborder="0"></iframe>
        
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Images</label>
                                    <div id="missionReportImages" class="d-flex flex-wrap gap-2"></div>

                                    <!-- Fullscreen Modal -->
                                    <div id="fullscreenImageModal" class="fullscreen-image-modal d-none">
                                        <span class="close-btn">&times;</span>
                                        <img id="fullscreenImage" src="" alt="Full Image">
                                    </div>
                                </div>
                            </div>
                            
                            
                  
                        </div>
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-8">
                                    <button  class="btn btn-danger deleteReportbtn mt-2"><img src="../images/delete.png" alt=""></button>
                                    <button  class="btn btn-warning editReportbtn mt-2"><img src="../images/edit.png" alt=""></button>
                                </div>
                                <div class="col-lg-4">
                                    <div class=" my-1 d-none text-danger " id="report-validation-errors" >
                                        All fields are required.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                        
                   
                    
                </form>
            </div>
        </div>
    </div>
</div>
{{-- <div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
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
</div> --}}

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
                                        <label for="start_datetime" class="form-label ">Program</label>
                                        <p id="programInfo" class="whiteText text-capitlaize"></p>
                                        <label for="start_datetime" class="form-label ">Region</label>
                                        <p id="regionInfo" class="whiteText text-capitalize"></p>
                                        <label for="start_datetime" class="form-label ">Location</label>
                                        <p id="locationInfo" class="whiteText text-capitalize"></p>

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
                                
                                <div class=" mb-2 ">
                                    <div class="image-upload-box  border-secondary rounded p-3 text-center text-white" style="" onclick="this.querySelector('input[type=file]').click()">
                                        <p class="mb-2">Click to Upload Images</p>
                                        <div class="image-preview d-flex flex-wrap gap-2 justify-content-start"></div>
                                        <input type="file" class="form-control d-none images" name="images_0[]" multiple accept="image/*">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grouped Select Fields -->
                    <div class="col-lg-12 col-md-12" style="border: 1px solid #FFFFFF33; padding: 10px;border-radius: 10px;">
                        <div class="row">

                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Notes</label>
                                    <textarea type="text" class="form-control notes-textarea " id="description" name="description" rows="11">its is new notes aboutnew mission</textarea>
                                </div>
                         
                            </div>
                            
                            
                  
                        </div>
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-8">
                                    <button type="submit" class="btn submitReportbtn w-25 mt-2">Submit Report</button>
                                </div>
                                <div class="col-lg-4">
                                    <div class=" my-1 d-none text-danger " id="report-validation-errors" >
                                        All fields are required.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                        
                   
                    
                </form>
            </div>
        </div>
    </div>
</div>

{{-- <div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
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
                                        <input type="datetime-local" class="form-control dateInput form-control-lg" id="start_datetime" name="start_datetime" >
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="end_datetime" class="form-label">End Date & Time</label>
                                        <input type="datetime-local" class="form-control dateInput form-control-lg"  id="end_datetime" name="end_datetime" >
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
                                                    <select class="form-select inspection_id dateInput  dateInput form-control-lg" name="inspection_id[]" id="inspection_id" ></select> 
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <select class="form-select location_id dateInput  dateInput form-control-lg" name="location_id[]" id="location_id" ></select> 
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
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-8">
                                    <button type="submit" class="btn btn-primary w-25 mt-2">Submit Report</button>
                                </div>
                                <div class="col-lg-4">
                                    <div class=" my-1 d-none text-danger" id="report-validation-errors" >
                                        All fields are required.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                        
                   
                    
                </form>
            </div>
        </div>
    </div>
</div> --}}






<!-- Bootstrap Modal for Updating Reports -->

<!-- Bootstrap Modal for Editing Reports -->
{{-- <div class="modal fade" id="updateReportModal" tabindex="-1" aria-labelledby="updateReportModalLabel" aria-hidden="true">
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
                                        <input type="url" class="form-control dateInput form-control-lg" id="edit_video_url" name="video_url" placeholder="Enter video link" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Notes</label>
                                    <textarea class="form-control notes-textarea" id="edit_description" name="description" rows="11" required></textarea>
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
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-8">
                                    <button type="submit" class="btn btn-success w-25 mt-2">Update Report</button>
                                </div>
                                <div class="col-lg-4">
                                    <div class=" my-1 d-none text-danger" id="updatereport-validation-errors" >
                                        All fields are required.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                   
                </form>
            </div>
        </div>
    </div>
</div> --}}

  <!-- End Main Panel -->

@endsection
@push('scripts')
<script src="{{ asset('js/pilot.js') }}"></script>
@endpush