@extends('layouts.app') <!-- or whatever your layout is -->

@section('content')


        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1">

            <!-- Left Column (Mission Control & Reports) -->
            <div class="col-lg-9 d-flex flex-column h-100">
                
                <!-- Mission Control Header -->
                <div class="row">
                    <div class="col-lg-12 p-3 bg-section d-flex flex-column align-items-start">
                        <p class="gray-text">{{ ucwords(str_replace('_', ' ', $userType)) }} Panel</p>
                        <h3 class="fw-bold">Mission Control</h3>
                    </div>
                </div>

                <!-- Reports List -->
                <div class="row h-100">
                    <div class="col-lg-12 col-xl-12 col-md-12 flex-grow-1 d-flex flex-column overflow-hidden bg-section mt-2">
                        
                        <!-- Reports Header -->
                        <div class="border-bottom-qss p-2">
                            <div class="row d-flex justify-content-between">
                                <div class="col-lg-4">
                                    <h5>Reports List</h5>
                                </div>
                                <div class="col-lg-4 text-end search-container">
                                    <img src="./images/search.png" alt="Search" class="img-fluid search-icon">
                                    <input type="search" placeholder="Search Reports Here" class="search-input">
                                </div>
                            </div>
                        </div>

                        <!-- Reports Table -->
                        <div class="table-responsive flex-grow-1 overflow-auto">
                            <table class="table table-text">
                                <thead>
                                    <tr>
                                        <th>Inspection Type</th>
                                        <th>Mission Date</th>
                                        
                                        <th>Location</th>
                                        <th>Note</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="missionTableBody" class="align-items-center">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Loading missions...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> <!-- End Left Column -->

            <!-- Right Column (Mission Analytics & Create New Mission) -->
            <div class="col-lg-3 d-flex p-0 flex-column">
                
                <!-- Mission Analytics -->
                {{-- <div class="mx-2">
                    <div class="row g-0 bg-section">
                        <div class="col-lg-6 label-text col-md-6 p-3">
                            <h6>Mission Analytics</h6>
                        </div>
                        <div class="col-lg-6 col-md-6 label-text p-3 text-end">
                            <p>Last 7 Days</p>
                        </div>
                        <div class="col-lg-12 p-2">
                            <div class="d-flex justify-content-between align-items-center label-text p-1">
                                <label class="form-check-label label-text mb-0">Pending Missions</label>
                                <p class="mb-0 fw-bold" id="pendingMissions">0</p>
                            </div>
                            <div class="progress">
                                <div class="progress-bar text-bg-danger" id="pendingMissionsBar" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="col-lg-12 p-2">
                            <div class="d-flex justify-content-between align-items-center label-text p-1">
                                <label class="form-check-label label-text mb-0">Finished Missions</label>
                                <p class="mb-0 fw-bold" id="completedMissions">0</p>
                            </div>
                            <div class="progress">
                                <div class="progress-bar text-bg-success" id="completedMissionsBar" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="col-lg-12 p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center label-text p-1">
                                <label class="form-check-label label-text mb-0">Total Missions</label>
                                <p class="mb-0 fw-bold" id="totalMissions">0</p>
                            </div>
                            <div class="progress">
                                <div class="progress-bar text-bg-warning text-white" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div> --}}

                <!-- Create New Mission -->
                <div class="d-flex flex-column bg-section p-3 flex-grow-1 mx-2 my-1">
                    
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <h6 class="form-title">Create New Mission</h6>
                        </div>
                        <div class="col-lg-4  text-end">
                            <button type="button" class="btn btn-danger cancel-btn btn-sm d-none p-1">
                                ✖
                            </button>
                        </div>
                    </div>
                    <form id="addMissionForm">
                        @csrf
                        <div class="row">

                            <div class="col-md-12">
                                <label class="form-label label-text">Select Programs</label>
                                <div class="row"   id="inspectionTypesContainer">

                                </div>
                            </div>
                     
                            <!-- Date Inputs -->
                            <div class="col-md-12 col-sm-12 py-1 pt-3">
                                <label class="form-label label-text">Mission Date</label>
                                <input type="date" class="form-control dateInput" id="mission_date" name="mission_date" required>
                            </div>
                      

                            <div class="col-md-12 col-sm-12 p-2">
                                <label class="form-check-label label-text py-1">Location</label>
                            
                                @if($locationData)
                                    <input type="text" class="form-control dateInput"  name="location_id" id="location_id" data-location-id="{{ $locationData['id'] }}"  value="{{ $locationData['name'] }}" disabled>
                                 @endif
                            </div>

                            {{-- notes textarea --}}
                            <div class="col-md-12 col-sm-12">
                                <label class="form-check-label label-text py-2">Notes</label>
                                <textarea id="note" name="note" class="form-control notes-textarea flex-grow-1" rows="3"></textarea>

                            </div>

                           
                               <!-- Button (Update or Create) -->
                                <div class="col-lg-6 pt-2 d-flex align-items-end text-center">
                                    
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="col-12 my-1 d-none text-danger" id="mission-validation-errors" >
                                                All fields are required.
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <button class="btn mission-btn btn-sm d-flex align-items-center gap-1 w-100 " type="submit">
                                               
                                                <span>Create Mission</span>
                                            </button>
                                        </div>    
                                    </div>
                                    
                                   
                                </div>

                           
                        </div>
                    </form>
                </div>

            </div> <!-- End Right Column -->
            
        </div> 
        <!-- End Main Panel -->

        <div class="modal fade" id="missionReportModal" tabindex="-1" aria-labelledby="missionReportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content bg-modal">
                    <div class="modal-header border-0">
                        <h6 class="modal-title" id="missionReportModalLabel">Detailed Report</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row h-100">
                            <!-- LEFT COLUMN -->
                            <div class="col-lg-7 h-100 report-modal-content">
                                <div class="table-responsive report-table-container">
                                    <table class="table table-text mb-0">
                                        <tbody id="missionReportTableBody">
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


        @endsection
        @push('scripts')
            <script src="{{ asset('js/missions.js') }}"></script>

        @endpush