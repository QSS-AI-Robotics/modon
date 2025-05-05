@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1 ">
            
            

            <!-- First Column -->
            <div class="col-lg-3 d-flex p-0 flex-column ">
                <div class="row flex-grow-1  h-100">
                    <div class="col-lg-12 d-flex flex-column h-100">

                        <!-- Container for the stack -->
                        <div class="d-flex flex-column flex-grow-1">

                            <!-- Row 1: Pilots & Drones (25%) -->
                            <div class="row  ">
  
                                <div class="col-12 col-sm-6 ">
                                    <div class="card h-100  py-1">
                                        <div class="card-body d-flex flex-column justify-content-between p-2 bg-section">
                                            <div class="d-flex align-items-center mb-2">
                                                <img src="{{ asset('images/pilot-hat.png') }}" class="img-fluid" style="height: 22px;">
                                                <p class="ps-2 mb-0" data-lang-key="pilots">Pilots</p>
                                            </div>
                                            <div class="text-end">
                                                <h2 id="totalPilots" class="mb-0">{{ $pilot }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div class="col-12 col-sm-6 ps-0">
                                    <div class="card h-100  py-1">
                                        <div class="card-body d-flex flex-column justify-content-between p-2  bg-section">
                                            <div class="d-flex align-items-center mb-2">
                                                <img src="{{ asset('images/drones.png') }}" class="img-fluid" style="height: 22px;">
                                                <p class="ps-2 mb-0" data-lang-key="drones">Drones</p>
                                            </div>
                                            <div class="text-end">
                                                <h2 id="totaldrones" class="mb-0">{{ $drones }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                


                            </div>
                            

                            <!-- Row 2: Missions (25%) -->
                            <div class="row my-1" >

                                <div class="col-12 col-sm-12 ">
                                    <div class="card h-100 ">
                                        <div class="card-body d-flex flex-column justify-content-between p-2 py-3 bg-section">
                                            <div class="d-flex align-items-center mb-2">
                                                <img src="{{ asset('images/missions.png') }}" class="img-fluid" style="height: 24px;">
                                                <p class="ps-2 mb-0"data-lang-key="missions">Missions</p>
                                            </div>
                                            <div class="text-end">
                                                <h2 id="totalMissions" class="mb-0">{{ $missions }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                            </div>

                            <!-- Row 3: Regions & Locations (25%) -->
                            <div class="row " >


                                <div class="col-12 col-sm-6 mb-1">
                                    <div class="card h-100  py-1">
                                        <div class="card-body d-flex flex-column justify-content-between p-2 bg-section">
                                            <div class="d-flex align-items-center mb-2">
                                                <img src="{{ asset('images/drones.png') }}" class="img-fluid" style="height: 20px;">
                                                <p class="ps-2 mb-0"data-lang-key="reigons">Reigons</p>
                                            </div>
                                            <div class="text-end">
                                                <h2 id="totalRegions" class="mb-0">{{ $regions-1 }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-6 mb-1 ps-0">
                                    <div class="card h-100  py-1">
                                        <div class="card-body d-flex flex-column justify-content-between p-2 bg-section">
                                            <div class="d-flex align-items-center mb-2">
                                                <img src="{{ asset('images/drones.png') }}" class="img-fluid" style="height: 20px;">
                                                <p class="ps-2 mb-0"data-lang-key="locations">Locations</p>
                                            </div>
                                            <div class="text-end">
                                                <h2 id="totalLocations" class="mb-0">{{ $locations }}</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Row 4: Chart (25%) -->
                            <div class="row" style="flex-grow: 1; min-height: 0;">
                                <div class="col-lg-12 chartSection d-flex flex-column h-100">
                                    <div class="card bg-section flex-grow-1">
                                       
                                        <p class="mb-0 card-title p-2"data-lang-key="missionAnaltyics">Missions Analytics</p>
                                        
                                        <div class="card-body flex-grow-1 position-relative d-flex justify-content-center text-center" style="height: 30vh" >
                                            <canvas id="regionMissionChart" class="text-capitalize " ></canvas>
                                            <div id="noDataMessage" class="position-absolute top-50 start-50 translate-middle text-white fw-bold d-none" data-lang-key="noDataFound">
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


            
            <div class="col-lg-3 d-flex flex-column p-0">
                <div class="d-flex flex-column flex-grow-1 mx-2 ">
              
                  <!-- Fixed-height header (Latest Missions) -->
                  {{-- <div class="bg-section mb-2" style="flex: 0 0 auto;">
                    <p class="mb-2 pt-2 p-2"data-lang-key="latestMissions">Latest Missions</p>
                    <div class="latestMissionPanel p-2" style="max-height: 150px; overflow-y:auto;">

                        <div class="incidentDiv p-2 my-2" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-title="d" <div="">
                            <div class="col-10 d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-truncate heartbeat text-capitalize">
                                   Loading...
                                </h6>
                                <p class="mb-0 text-capitalize">Region:  Loading... | Status:  Loading...</p>
                            </div>
                        </div>
                        <div class="incidentDiv p-2 my-2" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-title="d" <div="">
                            <div class="col-10 d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-truncate heartbeat text-capitalize" data-lang-key="loading...">
                                    Loading...
                                </h6>
                                <p class="mb-0 text-capitalize">Region:  Loading... | Status:  Loading...</p>
                            </div>
                        </div>

                    </div>
                  </div> --}}
                  <div class="card bg-section mb-1">
                    <div class="card-body p-2">
                        <p class="mb-2 pt-2 ps-2" data-lang-key="latestMissions">Latest Missions</p>
                
                        <div class="latestMissionPanel p-2" style="overflow-y: auto;">
                            <div class="incidentDiv p-2 my-2" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-title="d">
                                <div class="col-10 d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-truncate heartbeat text-capitalize">
                                        Loading...
                                    </h6>
                                    <p class="mb-0 text-capitalize">Region: Loading... | Status: Loading...</p>
                                </div>
                            </div>
                
                            <div class="incidentDiv p-2 my-2" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-title="d">
                                <div class="col-10 d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-truncate heartbeat text-capitalize" data-lang-key="loading...">
                                        Loading...
                                    </h6>
                                    <p class="mb-0 text-capitalize">Region: Loading... | Status: Loading...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                
              
                    <!-- Flexible and scrollable body -->

                    <div class="card bg-section flex-grow-1 d-flex flex-column" style="min-height: 0; overflow: hidden;">
                        <div class="card-body d-flex flex-column p-2" style="min-height: 0; overflow: hidden;">
                            <p class="mb-2 pt-2 ps-2" data-lang-key="latestIncidents">Latest Incidents</p>
                            <div class="flex-grow-1 IncidentPanel overflow-y-auto px-2" style=""> </div>
                        </div>
                    </div>
                
                
                </div>
              </div>
              
            

             <!-- End second Column -->


                         {{-- third column start --}}
            <div class="col-lg-6 d-flex p-0 flex-column h-lg-100  mobile-65vh">
                <div class="d-flex flex-column  flex-lg-grow-1 ">
            
                
                    <!-- === Pilot Tracking Card === -->
                            <div class="mb-1">
                                <div class="card bg-section d-flex flex-column">
                                    <div class="card-body d-flex flex-column p-2">
                                        <!-- Header -->
                                        <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                                            <p class="mb-0" data-lang-key="completedMissions">Completed Missions</p>

                                            <div class="col-lg-4 text-end datePanel-container">
                                                <div class="date-fields-wrapper">
                                                    <div class="date-wrapper">
                                                        <label for="start-date" class="date-label" data-lang-key="startDate">Start Date</label>
                                                        <input type="date" id="start-date" class="datePanel-input start-date">
                                                    </div>
                                                    <div class="date-wrapper">
                                                        <label for="end-date" class="date-label" data-lang-key="endDate">End Date</label>
                                                        <input type="date" id="end-date" class="datePanel-input end-date">
                                                    </div>
                                                </div>

                                                <img src="./images/calendar.png" alt="Search" class="img-fluid datePanel-icon pt-2 imghover">
                                                <img src="{{ asset('images/refresh.png') }}" class="img-fluid mx-1 p-1 mt-2 imghover custImg refreshIcon">
                                            </div>
                                        </div>

                                        <!-- Pilot Grid -->
                                        {{-- <div class="px-2 pb-2 ">

                                            <div class="row">
                                                <div class="col-lg-12 ">
                                                    <div class="row gy-2 gx-3  flex-nowrap overflow-x-auto" style="white-space: nowrap;" id="locationsAnalytics">
                                                        @foreach($regionNames as $region)
                                                            @if(strtolower($region) !== 'all')
                                                                <div class="col-lg-2 col-md-4 col-sm-6" style="min-width: 110px;">
                                                                    <button class="btn btn-sm modon-btn w-100 text-capitalize" style="font-size:12px">{{ $region }}</button>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row flex-nowrap overflow-auto  py-2 px-1" style="white-space: nowrap; min-height: 0;">
                                                
                                                <div class="col-lg-3 col-md-3 col-sm-4 col-6">
                                                    <div class="card shadow-sm p-2 text-center   " style="background-color: #0A415B; color: #d1d5db; border-radius: 8px;border-bottom:2px solid #25D366">
                                                        <div class="d-flex flex-column justify-content-center align-items-center gap-2 " style="min-height: 150px;">
                                                            <!-- Number -->
                                                            <div class="fw-bold text-white p-3 rounded" style="background: linear-gradient(to bottom, #105A7E, #082D3F); font-size: 15px;">
                                                                259
                                                            </div>
                                                        <div>
                                                           <small>Jeddah Second <br>Industrial City</small>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                
                                                <!-- Repeat for more cards -->
                                            </div>
                                            
                                        </div> --}}
                                        <div class="px-2 pb-2">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row gy-2 gx-3 flex-nowrap overflow-x-auto " style="white-space: nowrap;" id="locationsAnalytics">
                                                       
                                                        @foreach($regionNames as $region)
                                                            @if(strtolower($region->name) !== 'all')
                                                                <div class="col-lg-2 col-md-4 col-sm-6" style="min-width: 110px;">
                                                                    <button 
                                                                        class="btn btn-sm modon-btn w-100 text-capitalize region-button" 
                                                                        style="font-size:12px" 
                                                                        data-region-id="{{ $region->id }}" 
                                                                    >
                                                                        {{ $region->name }} 
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                  
                                             
                                                </div>
                                            </div>
                                            {{-- <div class="row flex-nowrap overflow-auto py-2 px-1" style="white-space: nowrap; min-height: 0;" id="cityCards"> --}}
                                                <div class="slider-wrapper">
                                                    <div class="slider-container"  id="cityCards">
                                                        <div class="slider-box">
                                                            <div class="card shadow-sm p-2 text-center" style="background-color: #0A415B; color: #d1d5db; border-radius: 8px; border-bottom: 2px solid #25D366;">
                                                                <div class="d-flex flex-column justify-content-center align-items-center gap-2" style="min-height: 150px;">
                                                                    <div class="fw-bold text-white p-3 rounded" style="background: linear-gradient(to bottom, #105A7E, #082D3F); font-size: 15px;">
                                                                        0
                                                                    </div>
                                                                    <div>
                                                                        <small class="text-wrap text-truncate" style="max-width: 100px;">Loading...</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {{-- </div> --}}
                                        </div>

                                    </div> <!-- card-body -->
                                </div> <!-- card -->
                            </div> <!-- mb-3 -->

            
                    <!-- === Second Half: Incident Chart === -->
                    
                    <div class=" flex-grow-1 d-flex gap-2">


                        <div class="d-flex flex-grow-0 flex-lg-grow-1 px-2 pb-2 bg-section gap-2 stack-on-mobile w-50 firstDiv" style="min-height: 0;">
                            <div class="card-body d-flex flex-column p-2 " style="min-height: 0; overflow: hidden;">  
                                <p class="mb-2 py-2" data-lang-key="pilotTracking">Pilot Tracking</p>   
                                <div  class="flex-nowrap overflow-auto" id="missionsPanel" style="white-space: nowrap; min-height: 0;">
                                    <div class="col-lg-12 h-100 pt-2 rounded">
                                        <div class="bg-modon d-flex flex-column p-2 me-2 rounded h-100 ">
                                            <div class="d-flex align-items-end mb-2">
                                                <img src="./images/default-user.png" alt="Pilot" class="imghover rounded" style="width:50px; height:50px;">
                                                <div>
                                                    <p class="px-2 mb-0 lh-1" id="pilotname" data-lang-key="loading...">Loading...</p>
                                                    <small class="cont-btn px-2 mb-0 lh-1" data-lang-key="loading">Loading</small>
                                                </div>
                                            </div>
                                        
                                            <!-- Row: Pending, Finished, Rejected -->
                                            <div class="justify-content-between gap-2">
                                                <!-- Pending -->
                                                <div class="flex-fill p-2">
                                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                        <label class="form-check-label mb-0" data-lang-key="pending">Pending</label>
                                                        <p class="mb-0 fw-bold" id="pendingCount">0</p>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-danger" style="width: 0%" id="pendingProgress"></div>
                                                    </div>
                                                </div>
                                        
                                                <!-- Finished -->
                                                <div class="flex-fill p-2">
                                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                        <label class="form-check-label mb-0" data-lang-key="finished">Finished</label>
                                                        <p class="mb-0 fw-bold" id="finishedCount">0</p>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" style="width: 0%" id="finishedProgress"></div>
                                                    </div>
                                                </div>
                                        
                                                <!-- Rejected -->
                                                <div class="flex-fill p-2">
                                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                        <label class="form-check-label mb-0" data-lang-key="rejected">Rejected</label>
                                                        <p class="mb-0 fw-bold" id="rejectedCount">0</p>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-secondary" style="width: 0%" id="rejectedProgress"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Row: Total Missions -->
                                            <div class="p-2 mt-2">
                                                <div class="d-flex justify-content-between align-items-center label-text p-1">
                                                    <label class="form-check-label mb-0" data-lang-key="totalMissions">Total Missions</label>
                                                    <p class="mb-0 fw-bold" id="totalCount">0</p>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning text-white" style="width: 100%" id="totalProgress"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>                           
                                
                            </div>
                        </div>

{{-- 
                        <div class="d-flex flex-grow-0 flex-lg-grow-1 px-2 pb-2 bg-section gap-2 stack-on-mobile w-50 firstDiv" style="min-height: 0;">
                            <div class="card-body d-flex flex-column p-2 " style="min-height: 0; overflow: hidden;">                                
                                <!-- Header Row -->
                                <div class=" justify-content-between align-items-center px-2 mb-2 ">
                                    <p class="mb-0 py-2" data-lang-key="Missions">Missions </p>
                                    <div class="row gy-2 gx-3  flex-nowrap overflow-x-auto" style="white-space: nowrap;" id="locationsAnalytics">
                                        @foreach($regionNames as $region)
                                            @if(strtolower($region) !== 'all')
                                                <div class="col-lg-3 col-md-4 col-sm-6" style="min-width: 110px;">
                                                    <button class="btn btn-sm modon-btn w-100 text-capitalize" style="font-size:12px">{{ $region }}</button>
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                    </div>
                                </div>                    
                                <!-- Content: Regions and Map -->
                                <div class="d-flex flex-grow-1 px-2 pb-2 gap-2 border" style="min-height: 0;">     
                                </div>
                            </div>
                        </div> --}}
                        <div class="d-flex flex-grow-0 flex-lg-grow-1 px-2 pb-2 bg-section gap-2 stack-on-mobile w-50 secondDiv" style="min-height: 0;">
                            <div class="card-body d-flex flex-column p-2 " style="min-height: 0; overflow: hidden;">                                
                                <!-- Header Row -->
                                <div class="d-flex justify-content-between align-items-center px-2 mb-2">
                                    <p class="mb-0 py-2" data-lang-key="map">Map</p>
                                </div>                    
                                <!-- Content: Regions and Map -->
                                <div class="d-flex flex-grow-1 px-2 pb-2 gap-2" style="min-height: 0;">                   
                                    <!-- Map Column -->
                                    <div class="d-flex flex-grow-1 bg-modon rounded p-2 overflow-hidden" style="min-height: 0;">
                                        <div class="map-wrapper position-relative w-100 h-100">
                                            <img src="{{ asset('images/map/map.jpg') }}" id="mainBgmap" alt="Saudi Map" class="img-fluid w-100 h-100">
                                            <img src="" alt="Center" id="center" class="position-absolute">
                                            <img src="" alt="West" id="west" class="position-absolute">
                                            <img src="" alt="East" id="east" class="position-absolute">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    

                    
            
                </div>
            </div>
            
            <!-- End  third Column -->
            
        </div> 
        <!-- End Main Panel -->
        <script>

        </script>
@endsection
@push('scripts')

<script src="{{ asset('js/adminusers.js') }}"></script>


@endpush