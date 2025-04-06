@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1 ">
            
            
            <!-- first Column (Mission Analytics & Create New Mission) -->
            <div class="col-lg-3 d-flex p-0 flex-column h-100">        <!-- main column -->
                <div class="row flex-grow-1 mx-2 my-1 h-100">            <!-- inside row -->
                  <div class="col-lg-12 d-flex flex-column h-100">  
                        <div class="row mb-2">
                            <div class="col-lg-6 righttransparentBorder ">
                                <div class="row bg-section">
                                 <div class="col-lg-12 p-2 d-flex align-items-center">
                                     <img src="{{ asset('images/pilot.png') }}" class="img-fluid" style="height: 24px;">
                                     <p class="ps-2 mb-0">Pilots</p>
                                 </div>
                                 <div class="col-lg-12 text-end">
                                     <h1 class=""  id="totalPilots">5</h1>
                                 </div>
                                </div>
                             </div>
                             <div class="col-lg-6 lefttransparentBorder ">
                                 <div class="row  bg-section">
                                     <div class="col-lg-12 p-2 d-flex align-items-center">
                                         <img src="{{ asset('images/drones.png') }}" class="img-fluid" style="height: 24px;">
                                         <p class="ps-2 mb-0">Drones</p>
                                     </div>
                                  <div class="col-lg-12 text-end">
                                      <h1 class="" id="totaldrones">5</h1>
                                  </div>
                                 </div>
                              </div>
                        </div>
                        <div class="row mb-2">
                           
                             <div class="col-lg-12 ">
                                 <div class="row  bg-section">
                                     <div class="col-lg-12 p-2 d-flex align-items-center">
                                         <img src="{{ asset('images/missions.png') }}" class="img-fluid" style="height: 24px;">
                                         <p class="ps-2 mb-0">Missions</p>
                                     </div>
                                  <div class="col-lg-12 text-end">
                                      <h1 class="" id="totalMissions">12</h1>
                                  </div>
                                 </div>
                              </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-lg-6 righttransparentBorder ">
                                <div class="row bg-section">
                                 <div class="col-lg-12 p-2 d-flex align-items-center">
                                     <img src="{{ asset('images/regions.png') }}" class="img-fluid" style="height: 24px;">
                                     <p class="ps-2 mb-0">Regions</p>
                                 </div>
                                 <div class="col-lg-12 text-end">
                                     <h1 class=""  id="totalRegions">5</h1>
                                 </div>
                                </div>
                             </div>
                             <div class="col-lg-6 lefttransparentBorder ">
                                 <div class="row  bg-section">
                                     <div class="col-lg-12 p-2 d-flex align-items-center">
                                         <img src="{{ asset('images/locations.png') }}" class="img-fluid" style="height: 24px;">
                                         <p class="ps-2 mb-0">Locations</p>
                                     </div>
                                  <div class="col-lg-12 text-end">
                                      <h1 class="" id="totalLocations">5</h1>
                                  </div>
                                 </div>
                              </div>
                        </div>
                        <div class="row flex-grow-1 ">
                           
                            <div class="col-lg-12 ">
                                <div class="row bg-section">
                                    <div class="col-lg-12 py-3 d-flex align-items-center justify-content-between">
                                       
                                        <p class="mb-0">Missions Vs Regions</p>
                                        <small class="mb-0 " >last 7 days</small>
                                    </div>
                                 <div class="col-lg-12 ">
                                    <canvas id="regionLineChart" width="100%" height="73"></canvas>
                                 </div>
                                </div>
                             </div>
                       </div>
                    </div>                     
                </div>
            </div>
             <!-- first Right Column -->



            <!-- second Column (Mission Analytics & Create New Mission) -->
            <div class="col-lg-3 d-flex p-0 flex-column">
                
         


                <!-- Create New Mission -->
                <div class="d-flex flex-column bg-section p-3 flex-grow-1 mx-2 my-1">
                    

                </div>

            </div>
             <!-- End second Column -->

             
            <!-- third Column (Mission Control & Reports) -->
            <div class="col-lg-6 d-flex p-0 flex-column">
                
         


                <!-- Create New Mission -->
                <div class="d-flex flex-column bg-section p-3 flex-grow-1 mx-2 my-1">
                    

                </div>

            </div>
            <!-- End  third Column -->
            
        </div> 
        <!-- End Main Panel -->

@endsection
@push('scripts')
<script src="{{ asset('js/adminusers.js') }}"></script>
@endpush