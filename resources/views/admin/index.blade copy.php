<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flex Layout</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/modon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/missions.css') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="container-fluid vh-100 d-flex flex-column padded-container">
        
        <!-- Header -->
        <div class="row header shadows bg-section p-1 mb-2 align-items-center">
            <div class="col-2 d-flex align-items-center">
                <img src="{{ asset('images/qss.png') }}" alt="Logo" class="w-50">
            </div>
            <div class="col-7 d-flex">
                <button class="btn cont-btn selected mx-1">Overview</button>
                <button class="btn cont-btn mx-1"><a href="/missions">Missions</a></button>
                <button class="btn cont-btn mx-1"><a href="/locations">Locations</a></button>
                <button class="btn cont-btn mx-1"><a href="/pilot">Pilot</a></button>
                <button class="btn cont-btn mx-1">Reports</button>
            </div>
            <div class="col-3 d-flex justify-content-end">
                <div class="dropdown">
                    <img src="{{ asset('images/user.png') }}" alt="Profile" class="img-fluid  rounded-circle" style="max-height: 50px; cursor: pointer;">
                </div>
            </div>
        </div>

        <!-- Main Panel -->
        <div class="row shadows mainPanel p-0 flex-grow-1">

            <!-- Left Column (Mission Control & Reports) -->
            <div class="col-lg-9 d-flex flex-column h-100">
                
                <!-- Mission Control Header -->
                <div class="row">
                    <div class="col-lg-12 p-3 bg-section d-flex flex-column align-items-start">
                        <p class="gray-text">Control Panel</p>
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
                                    <h5>Reports List</h5>
                                </div>
                                <div class="col-lg-4 text-end search-container">
                                    <img src="./images/search.png" alt="Search" class="img-fluid search-icon">
                                    <input type="search" placeholder="Search Reports Here" class="search-input dateInput">
                                </div>
                            </div>
                        </div>

                        <!-- Reports Table -->
                        <div class="table-responsive flex-grow-1 overflow-auto">
                            <table class="table table-text">
                                <thead>
                                    <tr>
                                        
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Type</th> 
                                        <th>Region</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="userTableBody" class="">

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
                            <button type="button" class="btn btn-danger cancel-btn btn-sm d-none p-1">
                                ✖
                            </button>
                        </div>
                    </div>
                    <form id="signupForm">
                        @csrf
                        <div class="row">


                            <div class="col-md-6">
                                <input type="hidden" name="locationId" id="locationId">
                            </div>
                            <!-- Date Inputs -->
                            <div class="col-md-12 col-sm-12">
                                <label class="form-label label-text">Full Name</label>
                                <input type="text" class="form-control dateInput" id="fullname" name="fullname" value="z" required>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label class="form-label label-text">Email</label>
                                <input type="email" class="form-control dateInput"  id="email" name="email" value="z@gmail.com" required>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <label class="form-label label-text">Password</label>
                                <input type="password" class="form-control dateInput" id="password" name="password" value="admin1234" required>
                            </div>
                            <div class="col-md-12 col-sm-12">
                               
                                <label for="region" class="form-label">Region</label>
                                <select class="form-select  dateInput" id="region" name="region" required>
                                    <option value="">Select Region</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger" id="region_error"></span>
                            </div>



                            {{-- notes textarea --}}
                            <div class="col-md-12 col-sm-12">
                                <label for="user_type" class="form-label">User Type</label>
                                <select class="form-select dateInput" id="user_type" name="user_type" required>
                                    <option value="">Select User Type</option>
                                    @foreach($userTypes as $userType)
                                        <option value="{{ $userType->id }}">{{ $userType->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger" id="user_type_error"></span>

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
            
        </div> <!-- End Main Panel -->
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/modon.js') }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>

</body>
</html>
