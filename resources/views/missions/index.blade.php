@extends('layouts.app') <!-- or whatever your layout is -->

@section('content')


        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1">

            <!-- Left Column (Mission Control & Reports) -->
            <div class="col-lg-9 d-flex flex-column h-100">
                
                <!-- Mission Control Header -->
                <div class="row">
                    <div class="col-lg-12 p-2 bg-section d-flex flex-column align-items-start">
                        <p class="bluishtext" data-lang-key="{{ $userType }}" id="userTypeFront"> {{ ucwords(str_replace('_', ' ', $userType)) }}</p>
                        <h3 class="fw-bold" data-lang-key="missionControl">Mission Control</h3>
                    </div>
                </div>

                <!-- Reports List -->
                <div class="row h-100">
                    <div class="col-lg-12 col-xl-12 col-md-12 flex-grow-1 d-flex flex-column overflow-hidden bg-section mt-2">
                        
                        <!-- Reports Header -->
                        <div class="border-bottom-qss p-2">
                            <div class="row d-flex justify-content-between">
                                <div class="col-lg-4">
                                    <h5><span  data-lang-key="missionList">Mission List</span> <span id="mifu" class="d-none">{{ $userType }}</span></h5>
                                </div>
                                <div class="col-lg-4 d-flex justify-content-lg-end justify-content-start">


                                    <input type="date" placeholder="" class="dateInput" id="filterMission">
                                    <img src="./images/refresh.png" class="img-fluid mx-1 p-1  imghover custImg2 refreshIcon">
                                </div>
                                <div class="col-lg-5">
                                    <div class="row py-2 gap-2">
                                        <div class="col-lg-2 col-2">
                                            <span class="badge p-2  mstatus activeStatus" data-lang-key="all" id="allMissions">All </span>
                                        </div>
                                        <div class="col-lg-2 col-2">
                                            <span class="badge p-2  mstatus" data-lang-key="pending" id="pending">Pending</span>
                                        </div>
                                        <div class="col-lg-2 col-2">
                                            <span class="badge p-2  mstatus" data-lang-key="rejected" id="rejected">Rejected</span>
                                        </div>
                                        <div class="col-lg-2 col-2">
                                            <span class="badge p-2  mstatus" data-lang-key="completed" id="completed">Completed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="flex-grow 1 " style="">
                            {{-- <div class="flex-grow 1  overflow-y-auto" style="max-height: 50vh;overflow-x:hidden;"> --}}
                            <div class="row fw-bold custom-bborder  label-text w-100  px-3 py-2 justify-content-between">
                                <div class="col-3" data-lang-key="inspectionType">Inspection Type</div>
                                <div class="col-2"data-lang-key="missionDate">Mission Date</div>
                                <div class="col-3 text-center" data-lang-key="location">Location</div>
                                <div class="col-2 text-center" data-lang-key="status">Status</div>
                                <div class="col-2 text-center" data-lang-key="actions">Actions</div>
                            </div>
                            <div class="accordion " id="missionsAccordion"></div>
                        </div>
                        <div id="paginationWrapper" class=""></div>
                    </div>
                </div>

            </div> <!-- End Left Column -->
 
            <!-- Right Column (Mission Analytics & Create New Mission sd) -->
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
                            <h6 class="form-title form-title-mission" data-lang-key="create_new_Mission">Create New Mission</h6>
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
                                <label class="form-label label-text" data-lang-key="selectProgram">Select Program</label>
                                <div class="row"   id="inspectionTypesContainer">

                                </div>
                            </div>
                     
                            <!-- Date Inputs -->
                            <div class="col-md-6 col-sm-6 py-1 pt-3">
                                <label class="form-label label-text" data-lang-key="missionDate">Mission Date</label>
                                <input type="date" class="form-control dateInput" id="mission_date" name="mission_date" required>
                            </div>

                            <div class="col-md-6 col-sm-6 py-1 pt-3">
                                <label for="region_id" class="form-label label-text" data-lang-key="region">Region</label>
                            
                                @if($regions->isNotEmpty())
                                    @php
                                        $selectedRegionId = old('region_id', $locationData['region_id'] ?? $regions->first()->id);
                                    @endphp
                            
                                    <select name="region_id" id="region_id" class="form-select mx-1 dateInput"
                                            @if($regions->count() === 1) disabled @endif required>
                                        @foreach($regions as $reg)
                                            <option value="{{ $reg->id }}"
                                                @if($selectedRegionId == $reg->id) selected @endif>
                                                {{ $reg->name }}
                                            </option>
                                        @endforeach
                                    </select>
                            
                                    @if($regions->count() === 1)
                                        {{-- preserve value even when disabled --}}
                                        <input type="hidden" name="region_id" value="{{ $regions->first()->id }}">
                                    @endif
                                @else
                                    <input type="text"
                                           class="form-control dateInput mx-1"
                                           value="No regions available"
                                           disabled>
                                @endif
                            </div>
                            
                            
                            
                      

                            <div class="col-md-6 col-sm-12 p-2">
                                <label class="form-check-label label-text py-1" data-lang-key="location">Location</label>
                            
                                @if($userType === 'region_manager' || $userType === 'qss_admin' || $userType === 'modon_admin'|| $userType === 'general_manager')
                                    @if($locations->isNotEmpty())
                                        @php
                                            $selectedLocationId = old('location_id', $locationData['id'] ?? $locations->first()->id);
                                        @endphp
                            
                                        <select class="form-select mx-1 dateInput" name="location_id" id="location_id"
                                            @if($locations->count() === 1 || $locationData) disabled @endif required>
                            
                                            @foreach($locations as $loc)
                                                @php
                                                    $region = $loc->locationAssignments->pluck('region')->filter()->first();
                                                @endphp
                                                <option 
                                                    value="{{ $loc->id }}" 
                                                    data-region-id="{{ $region?->id }}" 
                                                    data-region-name="{{ $region?->name }}"
                                                    @if($selectedLocationId == $loc->id) selected @endif>
                                                    {{ $loc->name }}
                                                </option>
                                            @endforeach
                                        </select>
                            
                                        @if($locations->count() === 1 || $locationData)
                                            {{-- preserve selected value if select is disabled --}}
                                            <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
                                        @endif
                            
                                    @else
                                        <input type="text"
                                            class="form-control dateInput mx-1"
                                            value="No locations found for this region"
                                            disabled>
                                    @endif
                            
                                @elseif($locationData)
                                    {{-- ✅ For city-level users (1 fixed location, disabled select) --}}
                                    <select class="form-select mx-1 dateInput" name="location_id" id="location_id" disabled required>
                                        <option value="{{ $locationData['id'] }}" selected>{{ $locationData['name'] }}</option>
                                    </select>
                                    <input type="hidden" name="location_id" value="{{ $locationData['id'] }}">
                                
                                @else
                                    {{-- ❌ Fallback for any other users --}}
                                    <input type="text"
                                        class="form-control dateInput mx-1"
                                        value="No location found"
                                        disabled>
                                @endif
                            </div>
                            

                        
                                


                            <div class="col-md-6 col-sm-12 p-2">
                                <label for="pilot_id" class="form-check-label label-text py-1" data-lang-key="pilot"> Pilot</label>
                            
                                @if($pilots->count() === 1)
                                    <!-- Only one pilot: auto-selected and disabled -->
                                    <select name="pilot_id" id="pilot_id" class="form-select  form-control dateInput mx-1" disabled>
                                        <option value="{{ $pilots[0]->id }}" selected>{{ $pilots[0]->name }}</option>
                                    </select>
                            
                                    <!-- Hidden input to still submit the selected pilot -->
                                    <input type="hidden" name="pilot_id" value="{{ $pilots[0]->id }}">
                                
                                @elseif($pilots->count() > 1)
                                    <!-- Multiple pilots: allow user to select, and make it required -->
                                    <select name="pilot_id" id="pilot_id" class="form-select  form-control dateInput mx-1" required>
                                        <option value="">Select Pilot</option>
                                        @foreach($pilots as $pilot)
                                            <option value="{{ $pilot->id }}">{{ $pilot->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <!-- No pilot available -->
                                    <select class="form-select form-control dateInput mx-1" disabled required>
                                        <option class="text-danger" >No pilots available for this region.</option>
                                    </select>
                                @endif
                            </div>
                            
                            <div class="col-md-6 col-sm-6">
                                <label class="form-label label-text" data-lang-key="latitude">Latitude</label>
                                <input type="text" class="form-control dateInput" id="latitude" name="latitude" value="2" required>
                            </div>
                            <div class="col-md-6 col-sm-6 ">
                                <label class="form-label label-text" data-lang-key="longitude">Longitude</label>
                                <input type="text" class="form-control dateInput" id="longitude" name="longitude" value="2" required>
                            </div>
                            {{-- notes textarea --}}
                            <div class="col-md-12 col-sm-12">
                                <label class="form-check-label label-text py-2" data-lang-key="notes">Notes</label>
                                <textarea id="note" name="note" class="form-control notes-textarea flex-grow-1 mx-1" rows="3" ></textarea>

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
                                            <button class="btn mission-btn btn-sm d-flex align-items-center gap-1 w-100 " id="CreateMissionBtn" type="submit">
                                               
                                                <span data-lang-key="createMission">Create Mission</span>
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
        <div class="modal fade" id="viewMissionReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content bg-modal">
                    <div class="modal-header border-0">
                        <h6 class="modal-title" id="addReportModalLabel" data-lang-key="reportDetail">Report Detail</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="vireReportForm">
                            @csrf
                            <input type="hidden" class="form-control" id="mission_id" name="mission_id" required>
        
        
                            <div>
                                <div class="row">
                                   
                                    <div class="col-lg-12 ">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="start_datetime" class="form-label " data-lang-key="missionCreatedBy">Mission Created By</label>
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
                                                    <label for="start_datetime" class="form-label " data-lang-key="geoCoordinates">Geo Coordinates</label>
                                                    <p id="viewgeoInfo" class="whiteText text-capitalize"></p>
                                                    <p id="viewmissionDateInfo" class="whiteText text-capitalize d-none"></p>
                                                </div>
                                            </div>
                                        </div>
        
                                    </div>
                                    <div class="col-lg-6">
                                        
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
                                            <label for="description" class="form-label" data-lang-key="description">Description</label>
                                            <div  class="form-control  text-white" id="description" name="description"  style="background: none;border:1px solid #FFFFFF33;min-height:220px;overflow-y:auto;max-height:220px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="description" class="form-label"  data-lang-key="images">Images</label>
                                            <div id="missionReportImages" class="missionReportImages d-flex flex-wrap gap-2"></div>
        
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
                                    <div class="col-lg-12 text-end" >
                                        <button  class="btn btn-info downloadReportbtn mt-2"><img src="../images/downloads.png" alt=""></button>
                                       
                                    </div>
                                    
                                </div>
                            </div>
                           
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
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
            {{-- <script src="{{ asset('js/lang.js') }}"></script> --}}
            <script src="{{ asset('js/missions.js') }}"></script>

        @endpush