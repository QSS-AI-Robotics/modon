@extends('layouts.app')

@section('title', 'Drones')

@section('content')
        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1">

            <!-- Left Column (Mission Control & Reports) -->
            <div class="col-lg-9 d-flex flex-column h-100">
                
                <!-- Mission Control Header -->
                <div class="row">
                    <div class="col-lg-12 p-3 bg-section d-flex flex-column align-items-start">
                        <p class="gray-text" data-lang-key="qssAdminPanel">Qss Admin Panel</p>
                        <h3 class="fw-bold" data-lang-key="droneControl">Drone Control</h3>
                    </div>
                </div>

                <!-- Reports List -->
                <div class="row h-100">
                    <div class="col-lg-12 col-xl-12 col-md-12 flex-grow-1 d-flex flex-column overflow-hidden bg-section mt-2">
                        
                        <!-- Reports Header -->
                        <div class="border-bottom-qss p-2">
                            <div class="row d-flex justify-content-between">
                                <div class="col-lg-4">
                                    <h5 data-lang-key="drones">Drones</h5>
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
                                        <th>#</th> <!-- Serial number column -->
                                        <th data-lang-key="model">Model</th>
                                        <th data-lang-key="serialNumber">Serial No</th>
                                        <th data-lang-key="assignedTo">Assigned To</th> 
                                        <th data-lang-key="actions">Actions</th> 
                                    </tr>
                                </thead>
                                <tbody id="DroneTableBody">
                                    @forelse($drones as $drone)
                                    <tr id="drone-row-{{ $drone->id }}">
                                        <td>{{ $loop->iteration }}</td> <!-- Serial number -->
                                        <td class="drone-model">{{ $drone->model }}</td>
                                        <td class="drone-serial">{{ $drone->sr_no }}</td>
                                        <td class="drone-user" data-user-id="{{ $drone->user_id }}">{{ $drone->user->name ?? 'N/A' }}</td>
                                        <td>
                                            <img src="{{ asset('images/edit.png') }}" alt="Edit" class="edit-drone img-fluid actions" data-id="{{ $drone->id }}">
                                            <img src="{{ asset('images/delete.png') }}" alt="Delete" class="delete-drone img-fluid actions" data-id="{{ $drone->id }}">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="no-drones-row">
                                        <td colspan="5" class="text-center text-light">No drones found.</td>
                                    </tr>
                                    @endforelse
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
                            <h6 class="form-title" data-lang-key="addNewDrone">Add New Drone</h6>
                        </div>
                        <div class="col-lg-4 rtl-align-start">
                            <button type="button" class="btn btn-danger cancel-btn btn-sm d-none px-2">
                                ✖
                            </button>
                        </div>
                    </div>
                    <form id="addDroneForm">
                        @csrf
                        <div class="row">


                            <div class="col-md-6">
                                <input type="hidden" name="droneId" id="droneId">
                            </div>
                            <!-- Date Inputs -->
                            <div class="col-md-12 col-sm-12">
                                <label class="form-label label-text pt-2" data-lang-key="model">Model</label>
                                <input type="text" class="form-control dateInput" id="modal" name="modal" value="Dji Mavic 4" disabled>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-label label-text pt-2" data-lang-key="serialNumber">Serial No</label>
                                <input type="text" class="form-control dateInput"  id="srno" name="srno"  >
                            </div>

                           
                            {{-- notes textarea --}}
                            <div class="col-md-12 col-sm-12">
                                <label for="user_type" class="form-label pt-2" data-lang-key="selectPilot">Select Pilot</label>
                                <select class="form-select dateInput" id="user_type" name="user_type">
                                    <option value="">Select Pilot</option>
                                    @foreach($pilots as $pilot)
                                        <option value="{{ $pilot->id }}">{{ $pilot->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger" id="user_type_error"></span>
                            </div>
                            <div class="col-12 my-1  text-danger  d-none" id="users-validation-errors" >
                                All fields are required.
                            </div>
                               <!-- Button (Update or Create) -->
                                <div class="col-lg-6 d-flex  align-items-end text-center mt-4">
                                    <button class="btn mission-btn btn-sm d-flex align-items-center" data-lang-key="addDrone" id="submitDroneBtn" type="submit">
                                        Add Drone
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
<script src="{{ asset('js/drones.js') }}"></script>
@endpush