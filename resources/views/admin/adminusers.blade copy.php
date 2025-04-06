@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1 ">
            
            
            <!-- first Column (Mission Analytics & Create New Mission) -->
            <div class="col-lg-3 d-flex p-0 flex-column h-100 border">
                
         
                <div class="row flex-grow-1 mx-2 my-1 h-100 ">
                    <div class="col-lg-12 border border-danger">
                        <div class="row">
                            <div class="col-lg-6 righttransparentBorder ">
                                <div class="row bg-section">
                                 <div class="col-lg-12 p-2 d-flex align-items-center">
                                     <img src="{{ asset('images/pilot.png') }}" class="img-fluid" style="height: 24px;">
                                     <h6 class="ps-2 mb-0">Pilots</h6>
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
                                         <h6 class="ps-2 mb-0">Drones</h6>
                                     </div>
                                  <div class="col-lg-12 text-end">
                                      <h1 class="" id="totaldrones">5</h1>
                                  </div>
                                 </div>
                              </div>
                        </div>
                    </div>

                     <div class="col-lg-12 border ">
                        <div class="row bg-section">
                            <div class="col-lg-12 p-2 d-flex align-items-center">
                                <img src="{{ asset('images/danger.png') }}" class="img-fluid" style="height: 24px;">
                                <h6 class="ps-2 mb-0">Missions</h6>
                            </div>
                            <div class="col-lg-12 text-end">
                                <h1 class=""  id="totalMissions">5</h1>
                            </div>
                           </div>
                     </div>

                     <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6 righttransparentBorder">
                                <div class="row bg-section">
                                 <div class="col-lg-12 p-2 d-flex align-items-center">
                                     <img src="{{ asset('images/regions.png') }}" class="img-fluid" style="height: 24px;">
                                     <h6 class="ps-2 mb-0">Regions</h6>
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
                                         <h6 class="ps-2 mb-0">Locations</h6>
                                     </div>
                                  <div class="col-lg-12 text-end">
                                      <h1 class="" id="totalLocations">5</h1>
                                  </div>
                                 </div>
                              </div>
                        </div>
                     </div>

                      <div class="col-lg-12  d-flex flex-column flex-grow-1 ">
                        <div class="row bg-section flex-grow-1">
                            <div class="col-lg-12 p-2 d-flex align-items-center">
                                <img src="{{ asset('images/danger.png') }}" class="img-fluid" style="height: 24px;">
                                <h6 class="ps-2 mb-0">Missions</h6>
                            </div>
                            <div class="col-lg-12 text-end">
                                <h1 class=""  id="totalMissions">5</h1>
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