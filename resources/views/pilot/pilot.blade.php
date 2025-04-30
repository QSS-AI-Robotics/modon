
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
                         
                                    <div class="accordion flex-grow-1 " id="pilotTableBody">
                                        <!-- Dynamic rows will go here -->
                                    </div>
                                    
                                </div>
                                <div id="paginationWrapper" class=""></div>


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
                <form id="viewReportForm">
                    @csrf
                    <input type="hidden" class="form-control" id="mission_id" name="mission_id" required>


                    <div>
                        <div class="row">
                           
                            {{-- <div class="col-lg-6">
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

                            </div> --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_datetime" class="form-label " data-lang-key="missioncreated">Mission Created By</label>
                                    <p id="viewOwnerInfo" class="whiteText text-capitlaize"></p>
                                    <label for="start_datetime" class="form-label" data-lang-key="pilot">Pilot</label>
                                    <p id="viewpilotInfo" class="whiteText text-capitalize"></p>
                                    
                                    <label for="start_datetime" class="form-label" data-lang-key="region">Region</label>
                                    <p id="viewregionInfo" class="whiteText text-capitalize"></p>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_datetime" class="form-label " data-lang-key="program">Program</label>
                                    <p id="viewprogramInfo" class="whiteText text-capitlaize"></p>
                                    <label for="start_datetime" class="form-label " data-lang-key="location">Location</label>
                                    <p id="viewlocationInfo" class="whiteText text-capitalize"></p>
                                    <label for="start_datetime" class="form-label " data-lang-key="geo">Geo Coordinated</label>
                                    <p id="viewgeoInfo" class="whiteText text-capitalize"></p>
                                    <p id="viewmissionDateInfo" class="whiteText text-capitalize d-none"></p>

                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grouped Select Fields -->
                    <div class="col-lg-12 col-md-12" style="border: 1px solid #FFFFFF33; padding: 10px;border-radius: 10px;">
                        <div class="row">

                           
                            <div class="col-lg-6  h-100 video-section d-none">
        
                                    
                                <iframe id="pilotVideo" width="100%" frameborder="0"></iframe>
        
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <div  class="form-control  " id="description" name="description" style="background: none;border:1px solid #FFFFFF33;min-height:220px;overflow-y:auto;max-height:220px;"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Images</label>
                                    <div id="missionReportImages" class="d-flex flex-wrap gap-2 missionReportImages"></div>

                                    <!-- Fullscreen Modal -->
                                    <div id="fullscreenImageModal" class="fullscreen-image-modal d-none">
                                        <span class="close-btn">&times;</span>
                                        <img id="fullscreenImage" src="" alt="Full Image">
                                    </div>
                                </div>
                            </div>
                            
                            
                  
                        </div>

                    </div>
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-8">
                                <button  class="btn btn-danger deleteReportbtn mt-2" data-report-id=""><img src="../images/delete.png" alt=""></button>
                                <button  class="btn btn-warning editReportbtn mt-2" data-report-id=""><img src="../images/edit.png" alt=""></button>
                                <button  class="btn btn-info downloadReportPilot mt-2"><img src="../images/downloads.png" alt=""></button>
                            </div>
                           
                        </div>
                    </div>
                </form>
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
                                        <label for="start_datetime" class="form-label ">Program</label>
                                        <p id="programInfo" class="whiteText text-capitlaize"></p>
                                        <label for="start_datetime" class="form-label ">Region</label>
                                        <p id="regionInfo" class="whiteText text-capitalize"></p>
                                        <label for="start_datetime" class="form-label ">Location</label>
                                        <p id="locationInfo" class="whiteText text-capitalize"></p>
                                        {{-- hidden --}}
                                        <input type="hidden" id="missionCreatefInfos" class="whiteText text-capitalize"></input>
                                        <input type="hidden" id="dateInfos" class="whiteText text-capitalize"></input>
                                        <input type="hidden" id="pilotInfos" class="whiteText text-capitalize"></input>
                                        <input type="hidden" id="geolocationinfos" class="whiteText text-capitalize"></input>

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








<!-- Bootstrap Modal for Updating Reports -->

<!-- Bootstrap Modal for Editing Reports -->
<div class="modal fade" id="editReportModal" tabindex="-1" aria-labelledby="editReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-modal">
            <div class="modal-header border-0">
                <h6 class="modal-title" id="editReportModalLabel">Edit Report</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editReportForm">
                    @csrf
                    <input type="hidden" name="report_id" id="edit_report_id">
                    <input type="hidden" name="mission_id" id="edit_mission_id">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Program</label>
                                <p id="editProgramInfo" class="whiteText text-capitalize"></p>
                                <label class="form-label">Region</label>
                                <p id="editRegionInfo" class="whiteText text-capitalize"></p>
                                <label class="form-label">Location</label>
                                <p id="editLocationInfo" class="whiteText text-capitalize"></p>
                            </div>
                            <div class="mb-3">
                                <label for="edit_video_url" class="form-label">Video URL</label>
                                <input type="url" class="form-control dateInput" id="edit_video_url" name="video_url">
                            </div>
                        </div>
                        <div class="col-lg-6 ">
                            <div class="mb-3 " >
                                <label for="edit_description" class="form-label">Notes</label>
                                <textarea class="form-control " id="edit_description" name="description" rows="8" style="background: none;color: white;border: 1px solid #FFFFFF33;"></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                       
                        <div class="col-lg-6">
                            
                            <label class="form-label">Upload New Images</label>
                            <div class="editimage-upload-box border-secondary rounded p-3 text-center text-white" onclick="this.querySelector('input[type=file]').click()">
                                <p class="mb-2">Click to Upload Images</p>
                                <div class="new-image-preview d-flex flex-wrap gap-2 justify-content-start"></div>
                                <input type="file" class="form-control d-none" name="new_images[]" multiple accept="image/*">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Current Images</label>
                            <div id="editCurrentImages" class="d-flex flex-wrap gap-2 mb-3 missionReportImages"></div>

                        </div>
                    </div>

                    

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-danger d-none" id="edit-validation-errors">All fields are required.</div>
                        <button type="submit" class="btn btn-primary updateReportBtn">Update Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


  <!-- End Main Panel -->

@endsection
@push('scripts')
<script src="{{ asset('js/pilot.js') }}"></script>
@endpush