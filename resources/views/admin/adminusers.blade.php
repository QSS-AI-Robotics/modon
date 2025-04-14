@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1">

            <!-- Left Column (Mission Control & Reports) -->
            <div class="col-lg-9 d-flex flex-column h-100">
                
                <!-- Mission Control Header -->
                <div class="row">
                    <div class="col-lg-12 p-3 bg-section d-flex flex-column align-items-start">
                        <p class="gray-text">Qss Panel</p>
                        <h3 class="fw-bold">Admin Control</h3>
                    </div>
                </div>

                <!-- Reports List -->
                <div class="row h-100">
                    <div class="col-lg-12 col-xl-12 col-md-12 flex-grow-1 d-flex flex-column overflow-hidden bg-section mt-2">
                        
                        <!-- Reports Header -->
                        <div class="border-bottom-qss p-2">
                            <div class="row d-flex justify-content-between">
                                <div class="col-lg-4">
                                    <h5>Users</h5>
                                </div>
                                <div class="col-lg-4 text-end search-container">
                                    <img src="../images/search.png" alt="Search" class="img-fluid search-icon">
                                    <input type="search" placeholder="Search Reports Here" class="search-input dateInput">
                                </div>
                            </div>
                        </div>

                        <!-- Reports Table -->
                        <div class="table-responsive flex-grow-1 overflow-auto">
                            <table class="table table-text">
                                <thead>
                                    <tr>
                                        
                                        <th>Avatar</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Type</th> 
                                        <th>Region</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="userTableBody" class="text-left">

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
                            <h6 class="form-title">Create New User</h6>
                        </div>
                        <div class="col-lg-4  text-end">
                            <button type="button" class="btn btn-danger cancel-btn btn-sm d-none px-2">
                                ✖
                            </button>
                        </div>
                    </div>
                    <form id="userStoreForm">
                        @csrf
                        <div class="row">


                            <div class="col-md-6">
                                <input type="hidden" name="userId" id="userId">
                            </div>
                            <!-- Date Inputs -->
                            <div class="col-md-12 col-sm-12">
                                <label class="form-label label-text pt-2">Full Name</label>
                                <input type="text" class="form-control dateInput" id="name" name="name" value="z" >


                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-label label-text pt-2">Email</label>
                                <input type="email" class="form-control dateInput"  id="email" name="email" value="z@gmail.com" >
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <label class="form-label label-text pt-2">Password</label>
                                <input type="password" class="form-control dateInput" id="password" name="password" value="admin1234" >
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label for="user_type_id" class="form-label label-text pt-2">Type</label>
                                <select class="form-select dateInput" id="user_type_id" name="user_type_id" >
                                    <option value="">Select User Type</option>
                                    @foreach($userTypes as $userType)
                                        <option value="{{ $userType->id }}" class="text-capitalize">{{ ucwords(str_replace('_', ' ', $userType->name)) }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger" id="user_type_error"></span>

                            </div>

                            {{-- select regions start--}}
 
                            
                            <div class="col-md-12 col-sm-12">
                                <label class="form-label pt-2 label-text">Assigned Region(s)</label>
                                <div id="regionCheckboxes" class="d-flex flex-wrap gap-2">
                                    @foreach($regions as $region)
                                        <div class="form-check">
                                            <input class="form-check-input region-checkbox" type="checkbox" 
                                                   value="{{ $region->id }}" id="region_{{ $region->id }}">
                                            <label class="form-check-label text-capitalize label-text" for="region_{{ $region->id }}">
                                                {{ $region->name === 'all' ? 'Headquarter' : ucwords(str_replace('_', ' ', $region->name)) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            

                            
                            <div  class="col-md-12 d-none" id="pilotFields">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <label class="form-label label-text pt-2">License No</label>
                                        <input type="text" class="form-control dateInput"  id="license_no" name="license_no" value="JHS456734" >
                                    </div>
        
                                    <div class="col-md-6 col-sm-12">
                                        <label class="form-label label-text pt-2">Expiray Date</label>
                                        <input type="date" class="form-control dateInput" id="license_expiry" name="license_expiry" >
                                    </div>
                                </div>
                            </div>
                            <div  class="col-md-12 d-none" id="LocationsFields">
                                <div class="col-md-12 col-sm-12">
                                    <label for="location_id" class="form-label label-text pt-2">Type</label>
                                    <select class="form-select dateInput" id="location_id" name="location_id">
                                        <option value="">Select Location</option>
                                        @foreach($locations as $location)
                                            @php
                                                $regionNames = $location['regions'] ?? [];
                                                $regionLabel = implode(', ', array_map(function ($r) {
                                                    return ucwords(str_replace('_', ' ', $r));
                                                }, $regionNames));
                                                $dataRegionAttr = implode(',', $regionNames);
                                            @endphp
                                    
                                            <option value="{{ $location['id'] }}" data-region="{{ $dataRegionAttr }}">
                                                {{ $location['name'] }} 
                                                @if($regionLabel)
                                                    ({{ $regionLabel }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    
                                    <span class="text-danger" id="user_type_error"></span>
    
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <label for="image" class="form-label pt-2">Profile Image</label>
                                <input type="file" class="form-control dateInput" id="user_image" name="image" accept="image/*">
                                <img id="imagePreview" src="#" alt="Preview" class="img-fluid mt-2 d-none" style="max-height: 100px;">
                            </div>

                            {{-- notes textarea --}}



                            <div class="col-12 my-1  text-danger  d-none" id="users-validation-errors" >
                                All fields are required.
                            </div>
                               <!-- Button (Update or Create) -->
                                <div class="col-lg-6 d-flex  align-items-end text-center mt-4">
                                    <button class="btn mission-btn btn-sm d-flex align-items-center " type="submit">
                                        Create User
                                    </button>
                                </div>

                           
                        </div>
                    </form>
                </div>

            </div> <!-- End Right Column -->
            
        </div> 
        <!-- End Main Panel -->

@endsection
@push('scripts')
<script src="{{ asset('js/script.js') }}"></script>
@endpush