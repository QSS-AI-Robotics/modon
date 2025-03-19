
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
        <div class="row header shadows bg-section p-2 mb-2 align-items-center">
            <div class="col-2 d-flex align-items-center">
                <img src="{{ asset('images/qss.png') }}" alt="Logo" class="" style="">
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

        <div class="row shadows mainPanel p-0 flex-grow-1">  
            <div class="col-lg-3 gap3 flex-column-full">
                <div class="row flex-grow-1 pe-2">
                    <div class="col-lg-12 d-flex mb-2  p-0 flex-column">
                        <div class=" flex-grow-1   bg-section">
                          <div class="row g-0 ">
                            <div class="col-lg-6 col-md-6 p-3  ">
                                <h6>Mission Analytics</h6>
                            </div>
                            <div class="col-lg-6 col-md-6  p-3 text-end">
                                <p>Last 7 Days</p>
                            </div>
                            <div class="col-lg-12 p-2 ">
                                <div class="d-flex justify-content-between align-items-center  label-text p-1">
                                    <label class="form-check-label label-text mb-0" for="exampleCheck1">Pending Missions</label>
                                    <p class="mb-0 fw-bold">75%</p>
                                </div>
                                
                                <div class="progress" role="progressbar" aria-label="Warning example" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar text-bg-danger" style="width: 75%"></div>
                                </div>
                            </div>
                            <div class="col-lg-12 p-2">
                                <div class="d-flex justify-content-between align-items-center  label-text p-1">
                                    <label class="form-check-label label-text mb-0" for="exampleCheck1">Finished Missions</label>
                                    <p class="mb-0 fw-bold">25%</p>
                                </div>
                                <div class="progress" role="progressbar" aria-label="Warning example" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar text-bg-success" style="width: 25%"></div>
                                </div>
                            </div>
                            <div class="col-lg-12 p-2">
                                <div class="d-flex justify-content-between align-items-center  label-text p-1">
                                    <label class="form-check-label label-text mb-0" for="exampleCheck1">Total Missions</label>
                                    <p class="mb-0 fw-bold">102</p>
                                </div>
                                <div class="progress " role="progressbar" aria-label="Warning example" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar text-bg-warning text-white" style="width: 100%"></div>
                                </div>
                            </div>
                          </div>
                        </div>
                       
                    </div>
            
                    <div class="col-lg-12 d-flex flex-column bg-section recent-alerts">
                        <h6 class="text-left pt-1">Recent Alerts</h6>
                            <div class="alertpanel d-flex justify-content-between mb-2 p-3">
                                <div class="col-lg-1 d-flex justify-content-center align-items-center  text-center">
                                    <img src="./images/danger.png" alt="" class="w-75">
                                </div>
                                <div class="col-lg-10 d-flex flex-column justify-content-center">
                                    <h6 class="m-0">No2 Emission Detected</h6>
                                    <p class="m-0 txt">Sector A- Drone Q12</p>
                                </div>
                                
                            </div>
                            <div class="alertpanel d-flex justify-content-between mb-2 p-3">
                                <div class="col-lg-1 d-flex justify-content-center align-items-center  text-center">
                                    <img src="../images/danger.png" alt="" class="w-75">
                                </div>
                                <div class="col-lg-10 d-flex flex-column justify-content-center">
                                    <h6 class="m-0">Broken Equipement</h6>
                                    <p class="m-0 txt">Sector B- Drone Q12</p>
                                </div>
                                
                            </div>
                            <div class="alertpanel d-flex justify-content-between mb-2 p-3 ">
                                <div class="col-lg-1 d-flex justify-content-center align-items-center  text-center">
                                    <img src="../images/danger.png" alt="" class="w-75">
                                </div>
                                <div class="col-lg-10 d-flex flex-column justify-content-center">
                                    <h6 class="m-0">No2 Emission Detected</h6>
                                    <p class="m-0 txt">Sector A- Drone Q12</p>
                                </div>
                                
                            </div>
                    </div>
                </div>
            </div>
            

            <div class="col-lg-9 gap3 flex-column-full">
                <div class="row flex-grow-1">
                   
                    <div class="col-lg-6 gap3 flex-column-full">
                        <div class="row flex-grow-1 pe-2">
                            <div class="col-lg-12 d-flex align-items-center justify-content-center flex-grow-1 bg-section bg-sect" style="margin-bottom: 10px;">
                                <img src="../images/cam1.png" alt="" class="w-90">
                            </div>
                            <div class="col-lg-12 d-flex align-items-center justify-content-center flex-grow-1 bg-section bg-sect">
                                <img src="../images/cam2.png" alt="" class="w-90">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 gap3 flex-column-full">
                        <div class="row flex-grow-1">
                            <div class="col-lg-12 d-flex align-items-center justify-content-center flex-grow-1 bg-section bg-sect" style="margin-bottom: 10px;">
                                <img src="../images/cam3.png" alt="" class="w-90">
                            </div>
                            <div class="col-lg-12 d-flex align-items-center justify-content-center flex-grow-1 bg-section bg-sect">
                                <img src="../images/cam4.png" alt="" class="w-90">
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
