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
                                            <h1 id="totalPilots">5</h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 lefttransparentBorder h-100">
                                    <div class="row bg-section h-100 d-flex flex-column justify-content-between">
                                        <div class="col-lg-12 p-2 d-flex align-items-center">
                                            <img src="{{ asset('images/drones.png') }}" class="img-fluid" style="height: 24px;">
                                            <p class="ps-2 mb-0">Drones</p>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <h1 id="totaldrones">5</h1>
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
                                            <h1 id="totalMissions">12</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Regions & Locations (25%) -->
                            <div class="row mb-2" style="flex-grow: 1;">
                                <div class="col-lg-6 righttransparentBorder">
                                    <div class="row bg-section  h-100 d-flex flex-column justify-content-between">
                                        <div class="col-lg-12 p-2 d-flex align-items-center">
                                            <img src="{{ asset('images/regions.png') }}" class="img-fluid" style="height: 24px;">
                                            <p class="ps-2 mb-0">Regions</p>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <h1 id="totalRegions">5</h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 lefttransparentBorder">
                                    <div class="row bg-section  h-100 d-flex flex-column justify-content-between">
                                        <div class="col-lg-12 p-2 d-flex align-items-center">
                                            <img src="{{ asset('images/locations.png') }}" class="img-fluid" style="height: 24px;">
                                            <p class="ps-2 mb-0">Locations</p>
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <h1 id="totalLocations">5</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 4: Chart (25%) -->
                            <div class="row" style="flex-grow: 1; min-height: 0;">
                                <div class="col-lg-12 d-flex flex-column h-100">
                                    <div class="row bg-section flex-grow-1">
                                        <div class="col-lg-12 py-3 d-flex align-items-center justify-content-between">
                                            <p class="mb-0">Missions Vs Regions</p>
                                            <small class="mb-0">last 7 days</small>
                                        </div>
                                        <div class="col-lg-12 flex-grow-1">
                                            <canvas id="regionLineChart" class="w-100 h-100"></canvas>
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
            <div class="col-lg-3 d-flex p-0 flex-column h-100">
   
                <div class="row flex-grow-1 mx-2 my-1 bg-section d-flex flex-column h-100">
           
                    <!-- Header: stays fixed -->
                    <div class="col-lg-12 py-3 d-flex justify-content-between">
                        <p class="mb-0">Latest Incidents</p>
               
         
                    </div>

                    <!-- This will grow to fill remaining space -->
                    <div class="col-lg-12 flex-grow-1 d-flex flex-column overflow-auto IncidentPanel" style="height: 40vh; min-height: 0;">
                        @for ($i = 0; $i <8; $i++)
                            <div class="incidentDiv p-2 my-2">
                                <div class="row align-items-center">
                                    <div class="col-2 d-flex justify-content-center align-items-center">
                                        <img src="{{ asset('images/warning.png') }}" class="img-fluid" style="height: 20px;">
                                    </div>
                                    <div class="col-10 d-flex flex-column justify-content-center">
                                        <h6 class="mb-0">No2 Emission Detected</h6>
                                        <p class="mb-0">Region A - Drone A12</p>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

             <!-- End second Column -->

             
            <!-- third Column (Mission Control & Reports) -->
            <div class="col-lg-6 d-flex p-0 flex-column h-100">
                <div class="row flex-grow-1 mx-2 my-1 bg-section d-flex flex-column h-100">
                    
                    <!-- First half -->
                    <div class="col-lg-12 flex-grow-1">
                            <div class="row">
                                <div class="col-lg-12 py-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0">Pilot Tracking</p>
                                    </div>
                                    <div class="d-flex  align-items-center">
                                        <img src="{{ asset('images/calendar.png') }}" 
                                        class="img-fluid me-1 p-1 imghover" 
                                        style="height: 28px; background: #101625; border-radius: 6px;">
                                   
                                        <button class="btn btn-sm modonbtn mx-1">Today</button>
                                        <button class="btn btn-sm modonbtn mx-1">Region:All</button>
                                    </div>
                                </div>
                                <div class="col-lg-12 ">
                                    <div class="row">
                                        <div class="col-lg-4 ">
                                                <div class="row g-0 bg-modon  p-2">
                                                    <div class="col-lg-12">
                                                       <p class="pt-2 px-2">Pilot Name</p>
                                                    </div>
                                                    <div class="col-lg-12 p-2">
                                                        <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                            <label class="form-check-label label-text mb-0">Pending Missions</label>
                                                            <p class="mb-0 fw-bold" id="pendingMissions">2</p>
                                                        </div>
                                                        <div class="progress">
                                                            <div class="progress-bar text-bg-danger" id="pendingMissionsBar" style="width: 10%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12 p-2">
                                                        <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                            <label class="form-check-label label-text mb-0">Finished Missions</label>
                                                            <p class="mb-0 fw-bold" id="completedMissions">3</p>
                                                        </div>
                                                        <div class="progress">
                                                            <div class="progress-bar text-bg-success" id="completedMissionsBar" style="width: 50%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12 p-2 mb-2">
                                                        <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                            <label class="form-check-label label-text mb-0">Total Missions</label>
                                                            <p class="mb-0 fw-bold" id="totalMissions">5</p>
                                                        </div>
                                                        <div class="progress">
                                                            <div class="progress-bar text-bg-warning text-white" style="width: 100%"></div>
                                                        </div>
                                                    </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
            
                    <!-- Second half -->
                    <div class="col-lg-12  flex-grow-1">
                        <!-- Content here -->
                        <p class="text-center py-3 text-primary">Bottom Half</p>
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