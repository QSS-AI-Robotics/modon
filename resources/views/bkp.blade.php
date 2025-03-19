

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flex Layout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/modon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/missions.css') }}">

</head>
<body>
    <div class="container-fluid vh-100 d-flex flex-column padded-container">
        <div class="row header shadows bg-section p-1 mb-2 align-items-center ">
            <div class="col-2 d-flex align-items-center">
                <img src="{{ asset('images/qss.png') }}" alt="Logo" class="w-50" style="">
            </div>
            <div class="col-7 d-flex ">
                <button class="btn cont-btn selected mx-1"> Overview</button>
                <button class="btn cont-btn mx-1">Map View</button>
                <button class="btn cont-btn mx-1">All Drones</button>
                <button class="btn cont-btn mx-1">Controller</button>
                <button class="btn cont-btn mx-1">Reports</button>
            </div>
            <div class="col-3 d-flex justify-content-end">
                <div class="dropdown">
                    <img src="{{ asset('images/user.png') }}" alt="Profile" class="img-fluid rounded-circle" style="max-height: 50px; cursor: pointer;">
                  
                </div>
            </div>
        </div>

        <div class="row shadows mainPanel p-0 flex-grow-1 ">  
            <div class="col-lg-3 d-flex p-0 flex-column">
                <!-- Mission Analytics: Height based on content -->
                <div class="bg-section mb-2">
                    <div class="row g-0 ">
                        <div class="col-lg-6 label-text col-md-6 p-3">
                            <h6>Mission Analytics</h6>
                        </div>
                        <div class="col-lg-6 col-md-6 label-text p-3 text-end">
                            <p>Last 7 Days</p>
                        </div>
                        <div class="col-lg-12 p-2">
                            <div class="d-flex justify-content-between align-items-center label-text p-1">
                                <label class="form-check-label label-text mb-0" for="exampleCheck1">Pending Missions</label>
                                <p class="mb-0 fw-bold">75%</p>
                            </div>
                            <div class="progress" role="progressbar">
                                <div class="progress-bar text-bg-danger" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="col-lg-12 p-2">
                            <div class="d-flex justify-content-between align-items-center label-text p-1">
                                <label class="form-check-label label-text mb-0" for="exampleCheck1">Finished Missions</label>
                                <p class="mb-0 fw-bold">25%</p>
                            </div>
                            <div class="progress" role="progressbar">
                                <div class="progress-bar text-bg-success" style="width: 25%"></div>
                            </div>
                        </div>
                        <div class="col-lg-12 p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center label-text p-1">
                                <label class="form-check-label label-text mb-0" for="exampleCheck1">Total Missions</label>
                                <p class="mb-0 fw-bold">102</p>
                            </div>
                            <div class="progress" role="progressbar">
                                <div class="progress-bar text-bg-warning text-white" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <!-- Create New Mission: Occupies Remaining Space -->
                <div class="d-flex flex-column bg-section  p-3 flex-grow-1">
                    <h6 class="text-left pt-2">Create New Mission</h6>
                    <label class="form-check-label label-text mb-2">Select Inspection</label>
                
                    <div class="p-2">
                        <div class="row">
                            <!-- Checkboxes -->
                            <div class="col-6 col-md-6 col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" id="trafficAnalysis" class="form-check-input">
                                    <label class="form-check-label checkbox-text" for="trafficAnalysis">Gas Leaks</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" id="thermalAnomalies" class="form-check-input">
                                    <label class="form-check-label checkbox-text" for="thermalAnomalies">Road Safety</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" id="gasLeaks" class="form-check-input">
                                    <label class="form-check-label checkbox-text" for="gasLeaks">Traffic Analysis</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" id="roadCracks" class="form-check-input">
                                    <label class="form-check-label checkbox-text" for="roadCracks">Road Cracks</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" id="roadSafety" class="form-check-input">
                                    <label class="form-check-label checkbox-text" for="roadSafety">Thermal Anomalies</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-check">
                                    <input type="checkbox" id="storageArea" class="form-check-input">
                                    <label class="form-check-label checkbox-text" for="storageArea">Storage Area</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" id="outdoorViolation" class="form-check-input">
                                    <label class="form-check-label checkbox-text" for="outdoorViolation">Outdoor Violation</label>
                                </div>
                            </div>
                
                            <!-- Start & End Date -->
                            <div class="col-md-6 col-sm-12">
                                <label for="startDate" class="form-label label-text">Start Date</label>
                                <input type="date" id="startDate" class="form-control dateInput">
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <label for="endDate" class="form-label label-text">End Date</label>
                                <input type="date" id="endDate" class="form-control dateInput">
                            </div>
                
                            <!-- Location & Additional Notes -->
                            <div class="col-md-6 col-sm-12 p-2">
                                <div class="row p-2">
                                    <label class="form-check-label label-text py-2">Select Locations</label>
                                    <div class="col-12 scroll-container">
                                        <div class="form-check">
                                            <input type="checkbox" id="location1" class="form-check-input">
                                            <label class="form-check-label checkbox-text" for="location1">A</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="location2" class="form-check-input">
                                            <label class="form-check-label checkbox-text" for="location2">B</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 p-2">
                                <div class="row p-2">
                                    <label class="form-check-label label-text py-2">Notes</label>
                                    <div class="col-12">
                                        <textarea id="additionalNotes" class="form-control notes-textarea" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                
                            <!-- New Mission Button -->
                            <div class="col-md-5 col-sm-12">
                                <div class="btn mission-btn btn-sm d-flex align-items-center gap-1 w-100">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 12V8.7241C19 8.25623 18.836 7.80316 18.5364 7.44373L14.5997 2.71963C14.2197 2.26365 13.6568 2 13.0633 2H11H7C4.79086 2 3 3.79086 3 6V18C3 20.2091 4.79086 22 7 22H12" stroke="#101625" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M16 19H22" stroke="#101625" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19 16L19 22" stroke="#101625" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M14 2.5V6C14 7.10457 14.8954 8 16 8H18.5" stroke="#101625" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <span>New Mission</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            




            
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
