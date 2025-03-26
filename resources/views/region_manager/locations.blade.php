@extends('layouts.app') <!-- or whatever your layout is -->

@section('content')

        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1">

            <!-- Left Column (Mission Control & Reports) -->
            <div class="col-lg-9 d-flex flex-column h-100">
                
                <!-- Mission Control Header -->
                <div class="row">
                    <div class="col-lg-12 p-3 bg-section d-flex flex-column align-items-start">
                        <p class="gray-text">Control Panel</p>
                        <h3 class="fw-bold">Locations Control</h3>
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
                                        <th>Locations</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Region</th> 
                                        <th>Map</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="locationTableBody" class="align-items-center">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> <!-- End Left Column -->

            <!-- Right Column (Mission Analytics & Create New Mission) -->
            <div class="col-lg-3 d-flex p-0 flex-column">
                
                <!-- Mission Analytics -->


                <!-- Create New Mission -->
                <div class="d-flex flex-column bg-section p-3 flex-grow-1 mx-2 my-1">
                    
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <h6 class="form-title">Create New Location</h6>
                        </div>
                        <div class="col-lg-4  text-end">
                            <button type="button" class="btn btn-danger cancel-btn btn-sm d-none p-1">
                                ✖
                            </button>
                        </div>
                    </div>
                    <form id="locationForm">
                        @csrf
                        <div class="row">


                            <div class="col-md-6">
                                <input type="hidden" name="locationId" id="locationId">
                            </div>
                            <!-- Date Inputs -->
                            <div class="col-md-12 col-sm-12">
                                <label class="form-label label-text">Location Name</label>
                                <input type="text" class="form-control dateInput" id="name" name="start_datetime" >
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-label label-text">Latitude</label>
                                <input type="Number" class="form-control dateInput" id="latitude" name="latitude" >
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <label class="form-label label-text">Longitude</label>
                                <input type="Number" class="form-control dateInput" id="longitude" name="longitude" >
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label class="form-label label-text">Map</label>
                                <input type="text" class="form-control dateInput" id="map_url" name="map_url" placeholder="google map url" >
                            </div>



                            {{-- notes textarea --}}
                            <div class="col-md-12 col-sm-12">
                                <label class="form-check-label label-text py-2">Description</label>
                                <textarea id="description" name="description" class="form-control notes-textarea flex-grow-1" rows="5"></textarea>

                            </div>
                            <div class="col-12 my-1  text-danger  d-none" id="location-validation-errors" >
                                    All fields are required.
                            </div>
                               <!-- Button (Update or Create) -->
                                <div class="col-lg-6 d-flex  align-items-end text-center mt-4">
                                    <button class="btn mission-btn btn-sm d-flex align-items-center gap-1 w-100 " type="submit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19 12V8.7241C19 8.25623 18.836 7.80316 18.5364 7.44373L14.5997 2.71963C14.2197 2.26365 13.6568 2 13.0633 2H11H7C4.79086 2 3 3.79086 3 6V18C3 20.2091 4.79086 22 7 22H12" stroke="#101625" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M16 19H22" stroke="#101625" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M19 16L19 22" stroke="#101625" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M14 2.5V6C14 7.10457 14.8954 8 16 8H18.5" stroke="#101625" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        <span>New Location</span>
                                    </button>
                                </div>

                           
                        </div>
                    </form>
                </div>

            </div> <!-- End Right Column -->
            
        </div> <!-- End Main Panel -->

@endsection

@push('scripts')
<script src="{{ asset('js/locations.js') }}"></script>
@endpush