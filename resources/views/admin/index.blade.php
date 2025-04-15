@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1 ">
            
            

            <!-- First Column -->
            <div class="col-lg-3 d-flex p-0 flex-column h-100">
                <div class="row flex-grow-1 mx-2 my-1 h-100">
                    <div class="col-lg-12 d-flex flex-column h-100">

                        <!-- Container for the stack -->
                        <div class="d-flex flex-column flex-grow-1">

                            <!-- Row 1: Pilots & Drones (25%) -->
                            <div class="row mb-2 " style="flex-grow: 1;">
                                <div class="col-lg-6 righttransparentBorder h-100">
                                    <div class="row bg-section h-100 d-flex flex-column justify-content-between">
                                        <div class="col-lg-12 p-2 d-flex align-items-center">
                                            <img src="{{ asset('images/pilot.png') }}" class="img-fluid" style="height: 24px;">
                                            <p class="ps-2 mb-0">Pilots</p>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <h1 id="totalPilots">{{ $pilot }}</h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 lefttransparentBorder h-100">
                                    <div class="row bg-section h-100 d-flex flex-column justify-content-between">
                                        <div class="col-lg-12 p-2 d-flex align-items-center">
                                            <img src="{{ asset('images/drones.png') }}" class="img-fluid" style="height: 20px;">
                                            <p class="ps-2 mb-0">Drones</p>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <h1 id="totaldrones">{{ $drones }}</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            

                            <!-- Row 2: Missions (25%) -->
                            <div class="row mb-2" style="flex-grow: 1;">
                                <div class="col-lg-12">
                                    <div class="row bg-section  h-100 d-flex flex-column justify-content-between">
                                        <div class="col-lg-12 p-2 d-flex align-items-center">
                                            <img src="{{ asset('images/missions.png') }}" class="img-fluid" style="height: 24px;">
                                            <p class="ps-2 mb-0">Missions</p>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <h1 id="totalMissions">{{ $missions }}</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Regions & Locations (25%) -->
                            <div class="row mb-2" style="flex-grow: 1;">
                                <div class="col-lg-6 righttransparentBorder">
                                    <div class="row bg-section  h-100 d-flex flex-column justify-content-between">
                                        <div class="col-lg-12 p-2 d-flex align-items-center">
                                            <img src="{{ asset('images/regions.png') }}" class="img-fluid" style="height: 20px;">
                                            <p class="ps-2 mb-0">Regions</p>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <h1 id="totalRegions">{{ $regions }}</h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 lefttransparentBorder">
                                    <div class="row bg-section  h-100 d-flex flex-column justify-content-between">
                                        <div class="col-lg-12 p-2 d-flex align-items-center">
                                            <img src="{{ asset('images/locations.png') }}" class="img-fluid" style="height: 20px;">
                                            <p class="ps-2 mb-0">Locations</p>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <h1 id="totalLocations">{{ $locations }}</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 4: Chart (25%) -->
                            <div class="row" style="flex-grow: 1; min-height: 0;">
                                <div class="col-lg-12 d-flex flex-column h-100">
                                    <div class="row bg-section flex-grow-1">
                                        <div class="col-lg-12 py-3 d-flex align-items-center justify-content-between">
                                            <p class="mb-0">Missions Analytics</p>
                                            {{-- <small class="mb-0">last 7 days</small> --}}
                                        </div>
                                        
                                        <div class="col-lg-12 flex-grow-1 position-relative d-flex justify-content-center text-center" style="height: 35vh" >
                                            <canvas id="regionMissionChart" class="text-capitalize " ></canvas>
                                            <div id="noDataMessage" class="position-absolute top-50 start-50 translate-middle text-white fw-bold d-none">
                                                No data found
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- End stack -->
                    </div>
                </div>
            </div>
             <!-- first Right Column -->


            <!-- second Column (Mission Analytics & Create New Mission) -->
            <div class="col-lg-3 d-flex p-0 flex-column h-100 ">
                <div class="row flex-grow-1 mx-2 my-1 d-flex flex-column  h-100">
            

                    <div class="col-12 bg-section " style="height:35%;">
                        <p class="mb-4 pt-2" style="height:10%">Latest Missions</p>
                        <div class="latestMissionPanel" style="overflow-y:auto;height:80%;overflow-x:hidden">
                            
                            
                        </div>
                    </div>
                    <div class="col-12 bg-section mt-3 " style="height:62%;">
                        <p class="mb-0 pt-3"  style="height:10%">Latest Incidents</p>
                        <div class="IncidentPanel" style="overflow-y:auto;height:85%;overflow-x:hidden">
                        </div>
                    </div>
                    
                    
                </div>
            </div>
            

             <!-- End second Column -->
            {{-- third column start --}}
            <div class="col-lg-6 d-flex p-0 flex-column h-100">
                <div class="d-flex flex-column flex-grow-1 mx-2 my-1">
            
                    <!-- === First Half: Pilot Tracking === -->
                    <div class="flex-grow-1 mb-1">
                        <div class="bg-section d-flex flex-column h-100">
                            <!-- Header -->
                            <div class="py-3 px-3 d-flex align-items-center justify-content-between">
                                <p class="mb-0">Pilot Tracking</p>
                                <div class="col-lg-4 text-end datePanel-container">
                                    <div class="date-fields-wrapper">
                                        <div class="date-wrapper">
                                            <label for="start-date" class="date-label">Start Date</label>
                                            <input type="date" id="start-date" class="datePanel-input date start-date">
                                        </div>
                                        <div class="date-wrapper">
                                            <label for="end-date" class="date-label">End Date</label>
                                            <input type="date" id="end-date" class="datePanel-input end-date">
                                        </div>
                                    </div>
                                
                                    <img src="./images/calendar.png" alt="Search" class="img-fluid datePanel-icon pt-2 imghover">
                                    <img src="{{ asset('images/refresh.png') }}" class="img-fluid mx-1 p-1 mt-2 imghover custImg refreshIcon" >
                                </div>

                            </div>
            
                            <!-- Pilot Grid -->
                            <div class="px-3 pb-3 flex-grow-1 overflow-auto">
                                {{-- <div class="row h-100" id="missionsPanel"> --}}
                                    <div class="row flex-nowrap overflow-auto h-100" id="missionsPanel" style="white-space: nowrap;">

                                  
                                        
                                        <div class="col-lg-4 h-100 pb-1 rounded">
                                            <div class="bg-modon h-100 d-flex flex-column p-2 me-2">
                                                <div class="d-flex align-items-end mb-2">
                                                    <img src="./images/default-user.png" alt="Search" class="imghover rounded" style="width:50px; height:50px">
                                                    <div>
                                                        <p class="px-2 mb-0 lh-1" id="pilotname">Loading...</p>
                                                        <small class="cont-btn px-2 mb-0 lh-1">Loading</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="p-2">
                                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                        <label class="form-check-label mb-0">Pending</label>
                                                        
                                                        <p class="mb-0 fw-bold">0</p>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-danger" style="width: 0%"></div>
                                                    </div>
                                                </div>
            
                                                <div class="p-2">
                                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                        <label class="form-check-label mb-0">Finished</label>
                                                        <p class="mb-0 fw-bold">0</p>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" style="width: 0%"></div>
                                                    </div>
                                                </div>
            
                                                <div class="p-2 mb-2">
                                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                        <label class="form-check-label mb-0">Total Missions</label>
                                                        <p class="mb-0 fw-bold">0</p>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-warning text-white" style="width: 100%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                  
                                    
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <!-- === Second Half: Incident Chart === -->
                    {{-- <div class="flex-grow-1 mt-2">
                        <div class="bg-section d-flex flex-column h-100">
                            <!-- Header -->
                            <div class="py-3 px-3 d-flex justify-content-between">
                                <p class="mb-0">Incident Chart</p>
                                
                            </div>
            
                            <!-- Chart -->
                            <div class="flex-grow-1 px-3 pb-3 d-none">
                                <canvas id="regionBarChart" class="w-100" style="height: 30vh"></canvas>
                                <div id="noregionDataMessage" class="position-absolute top-50 start-50 translate-middle text-white fw-bold d-none">
                                    No data found
                                </div>
                            </div>
                            <div class="flex-grow-1 px-3">
                                <img src="{{ asset('images/headmap.png') }}" class="img-fluid  object-fit-cover" >
                            </div>
                        </div>
                    </div> --}}
                    <div class="flex-grow-1  pb-1">
                        <div class="bg-section d-flex flex-column h-100">
                            <!-- Header Row -->
                            <div class="d-flex justify-content-between px-2">
                                <p class="mb-0 py-2">Incident Chart</p>
                                <p class="mb-0 py-2">Heat Map</p>
                            </div>
                    
                            <!-- Content Row: Chart & Image -->
                            <div class="d-flex flex-grow-1 px-2 pb-2 gap-2" style="min-height: 200px;">
                                <!-- Chart Column -->
                                <div class="flex-grow-1 w-50 h-100 d-flex flex-column">
                                    <canvas id="regionBarChart" class="w-100 h-100"></canvas>
                                    <div id="noregionDataMessage"
                                         class="position-absolute top-50 start-50 translate-middle text-white fw-bold d-none">
                                        No data found
                                    </div>
                                </div>
                    
                                <!-- Image Column -->
                                <div class="flex-grow-1 w-50 h-100 d-flex p-2">
                                    <img src="{{ asset('images/headmap.png') }}"
                                         class="w-100 h-100 object-fit-cover rounded"
                                         alt="Heatmap">
                                </div>
                            </div>
                        </div>
                    </div>
                    
            
                </div>
            </div>
            
            <!-- End  third Column -->            
        </div> 
        <!-- End Main Panel -->

@endsection
@push('scripts')
<script src="{{ asset('js/adminusers.js') }}"></script>
@endpush