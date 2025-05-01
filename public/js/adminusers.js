
$(document).ready(function () {



    let currentLang = localStorage.getItem("selectedLang") || "en";
    const regionChartMissions = document.getElementById('regionMissionChart').getContext('2d');

    const regionChart = new Chart(regionChartMissions, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#FFE500', '#81FF76', '#AD2727'],
                borderColor: '#121212',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            cutout: '50%',
            plugins: {
                // title: {
                //     display: true,
                //     text: 'Missions by Region',
                //     color: '#FFFFFF',
                //     font: {
                //         size: 18,
                //         weight: 'bold'
                //     }
                // },
                legend: {
                    display: false,
                    position: 'bottom',
                    labels: {
                        color: '#FFFFFF'
                    }
                },
                datalabels: {
                    color: 'white',
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    formatter: (value, context) => {
                        const label = context.chart.data.labels[context.dataIndex];
                        return `${label}: ${value}`;
                    }
                }
            }
        },
        plugins: [ChartDataLabels] // 👈 Register the plugin
    });
    
    

      // const regionChartMissions = document.getElementById('regionMissionChart').getContext('2d');
    // const regionChart = new Chart(regionChartMissions, {
    //     type: 'line',
    //     data: {
    //         labels: [],
    //         datasets: [{
    //             // label: 'Missions Completed',
    //             data: [],
    //             fill: false,
    //             tension: 0.4,
    //             pointBorderColor: '#FFFFFF',
    //             pointBackgroundColor: '#fff',
    //             pointHoverBackgroundColor: '#FFFFFF',
    //             pointHoverBorderColor: '#FFFFFF'
    //         }]
    //     },
    //     options: {
    //         responsive: true,
    //         plugins: {
    //             title: {
    //                 display: true,
    //                 color: '#FFFFFF',
    //                 font: {
    //                     size: 18,
    //                     weight: 'bold'
    //                 }
    //             },
    //             legend: {
    //                 display: false,
    //                 position: 'bottom',
    //                 labels: {
    //                     color: '#FFFFFF'
    //                 }
    //             }
    //         },
    //         scales: {
    //             y: {
    //                 beginAtZero: true,
    //                 ticks: {
    //                     color: '#FFFFFF',
    //                     stepSize: 1, // 👈 This forces step increments
    //                     precision: 0  // 👈 Removes decimal values
    //                 }
    //             },
    //             x: {
    //                 title: {
    //                     display: true,
    //                     text: '',
    //                     color: '#FFFFFF'
    //                 },
    //                 ticks: {
    //                     color: '#FFFFFF'
    //                 }
    //             }
    //         }
    //     }
    // });

    $('.datePanel-input').on('change', function () {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
    
        // If start is selected and end is empty → focus end date
        if ($(this).attr('id') === 'start-date' && !endDate) {
            $('#end-date').focus();
            return; // Wait until both are filled before proceeding
        }
    
        // If both dates are selected, validate
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
    
            if (start > end) {
                alert('Start date cannot be after end date.');
                return;
            }
        }
    
        // Fetch chart data regardless of date presence
        fetchMissionsByRegion(regionChart);
        // fetchInspectionsByRegion(regionBarChart);
        fetchPilotMissionSummary();
    });
    
    



    // ✅ Call after chart is created
    // fetchMissionsByRegion();
    fetchMissionsByRegion(regionChart);

    function fetchMissionsByRegion(chartInstance) {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
    
        // Validate: Start should not be after End
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert("Start date cannot be after end date.");
            return;
        }
    
        // Log the selected date range

    
        $.ajax({
            url: '/missions-by-region',
            type: 'GET',
            data: {
                start_date: startDate || null,
                end_date: endDate || null
            },
            dataType: 'json',
            success: function (data) {
                console.log('✅ Missions by Region:', data);
                updateChart(chartInstance, data);
                const regionData = data.data.filter(item => item.region !== 'all');

                // Set mission values to corresponding elements
                let totalMissions = 0;
                
                regionData.forEach(item => {
                    let regionKey = item.region;
                    const missionCount = item.missions;
                
                    totalMissions += missionCount;
                
                    if (regionKey === 'central') {
                        $('#centremissionVal').text(missionCount);
                    } else if (regionKey === 'eastern') {
                        $('#eastmissionVal').text(missionCount);
                    } else if (regionKey === 'western') {
                        $('#westmissionVal').text(missionCount);
                    }
                });
                
                // Set total
                $('#totalmissionVal').text(totalMissions);
                
                // Sort by missions count descending
                const sorted = [...regionData].sort((a, b) => b.missions - a.missions);
                
                // Check if all mission values are equal
                const allSame = sorted.every(item => item.missions === sorted[0].missions);
                
                // Assign color based on logic
                const colorMap = {};
                

                if (allSame) {
                    regionData.forEach(item => {
                        colorMap[item.region] = 'green';
                    });
                } else {
                    const values = sorted.map(item => item.missions);
                    const [first, second, third] = values;
                
                    sorted.forEach(item => {
                        const count = item.missions;
                
                        if (count === first && count === second && count === third) {
                            colorMap[item.region] = 'green'; // all equal
                        } else if (count === first && first !== second) {
                            colorMap[item.region] = 'red'; // only one highest
                        } else if (count === first && first === second && second !== third) {
                            colorMap[item.region] = 'red'; // two tied for highest
                        } else if (count === second && second === third && first !== second) {
                            colorMap[item.region] = 'green'; // two tied for lowest
                        } else if (count === second && first !== second && second !== third) {
                            colorMap[item.region] = 'orange'; // true middle
                        } else {
                            colorMap[item.region] = 'green';
                        }
                    });
                }
                
                // Build result array like ['centerred', 'eastorange', 'westgreen']
                const colorValues = regionData.map(item => {
                    let regionKey = item.region;
                
                    // Normalize region to match image IDs
                    if (regionKey === 'central') regionKey = 'center';
                    else if (regionKey === 'eastern') regionKey = 'east';
                    else if (regionKey === 'western') regionKey = 'west';
                
                    const color = colorMap[item.region];
                    return `${regionKey}${color}`;
                });
                
                console.log('🎨 Region Color Values:', colorValues);
                
                // Pass to map update function
                updateRegionMapFromValues(colorValues);
                
                // Store in data attributes for later use
                $('.selectRegion').attr('data-centercolorcode', colorValues.find(v => v.startsWith('center')) || '');
                $('.selectRegion').attr('data-eastcolorcode', colorValues.find(v => v.startsWith('east')) || '');
                $('.selectRegion').attr('data-westcolorcode', colorValues.find(v => v.startsWith('west')) || '');
                $('.selectRegion').attr('data-allcolorcode', colorValues.join(','));
                
                
            },
            error: function (xhr, status, error) {
                console.error('❌ Error fetching missions by region:', error);
            }
        });
    }
    
    
    function updateChart(chart, response) {
        const chartData = (response.data || []).filter(item => item.region !== 'all');
    
        const labels = chartData.map(item =>
            item.region.charAt(0).toUpperCase() + item.region.slice(1).toLowerCase()
        );
        
        const values = chartData.map(item => item.missions);
    
        const totalMissions = values.reduce((sum, val) => sum + val, 0);
        $("#totalMissions").text(totalMissions);
        console.log(`📊 Total Missions: ${totalMissions}`);
    
        const hasData = values.some(value => value > 0);
    
        // Assign colors based on region names
        const colorMap = {
            'eastern': '#AD2727',
            'western': '#C6B40D',
            'central': '#80FE76'
        };
    
        const backgroundColors = labels.map(region => colorMap[region.toLowerCase()] || '#ccc');
    
        // Update chart
        chart.data.labels = labels;
        chart.data.datasets[0].data = values;
        chart.data.datasets[0].backgroundColor = backgroundColors;
    
        chart.update();
    
        // Show/hide no data message
        if (hasData) {
            $('#noDataMessage').addClass('d-none');
            $('#regionMissionChart').removeClass('d-none');
        } else {
            $('#noDataMessage').removeClass('d-none');
            $('#regionMissionChart').addClass('d-none');
        }
    }
    
    


    
    

    // fetchInspectionsByRegion(regionBarChart);


    function fetchInspectionsByRegion(chartInstance) {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
    
        // Optional: validate dates here if you want
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert("Start date cannot be after end date.");
            return;
        }

        $.ajax({
            url: '/inspections-by-region',
            type: 'GET',
            data: {
                start_date: startDate || null,
                end_date: endDate || null
            },
            dataType: 'json',
            success: function (data) {
                console.log("✅ Inspections by Region:", data);
                updateInspectionChart(chartInstance, data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching inspections:', error);
            }
        });
    }


function updateInspectionChart(chart, response) {
    const chartData = response.data || [];

    const labels = chartData.map(item => item.region);
    const rawValues = [8,5,1,3];
    // const rawValues = chartData.map(item => item.inspections);
  
    // Used only for visual display
    const visualValues = rawValues.map(value => value === 0 ? 0.2 : value);

    // Determine max real value (not 0.2)
    const maxValue = Math.max(...rawValues);
    const barColors = rawValues.map(value =>
        value === maxValue && value > 0 ? '#AD2727' : '#0A415B'
    );

    const hasData = labels.length > 0;

    if (hasData) {
        $('#noregionDataMessage').addClass('d-none');

        chart.data.labels = labels;
        chart.data.datasets[0].data = visualValues;
        chart.data.datasets[0].backgroundColor = barColors;

        chart.options.plugins.datalabels.formatter = function (value, context) {
            return rawValues[context.dataIndex]; // Always show 0, not 0.2
        };

        // Only show Y-axis if any real data > 0
        const anyRealData = rawValues.some(v => v > 0);
        chart.options.scales.y.ticks.display = anyRealData;
        chart.options.scales.y.grid.display = anyRealData;

        chart.update();
    } else {
        chart.data.labels = [];
        chart.data.datasets[0].data = [];
        chart.update();
        $('#noregionDataMessage').removeClass('d-none');
    }

    // Optional logging
    if (response.filtered) {
        console.log(`📅 Filtered inspections from ${response.from} to ${response.to}`);
    } else {
        console.log("📊 Showing all inspections (no filter)");
    }
}

  
    fetchPilotMissionSummary();

    function fetchPilotMissionSummary() {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
    
        // Validate dates
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert("Start date cannot be after end date.");
            return;
        }
    
        $.ajax({
            url: '/pilot-mission-summary',
            method: 'GET',
            dataType: 'json',
            data: {
                start_date: startDate || null,
                end_date: endDate || null
            },
            success: function (response) {
                const data = response.data || [];
    
                console.log("✅ Pilot Mission Summary:", data);
                
                if (data.length > 0) {
                    $('#missionsPanel').empty(); 
                }else{
                    $('#missionsPanel').empty(); 
                    $('#missionsPanel')
                    .text("No Pilot Data Found...")
                    .css({
                        'text-align': 'center',
                        'display': 'flex',
                        'justify-content': 'center',
                        'color': '#999' // optional styling
                    });
                }
               
    
                data.forEach(pilot => {
                    const total = pilot.total_missions || 0;
                    const completed = pilot.completed_missions || 0;
                    const pending = pilot.pending_missions || 0;
                    const rejected = pilot.rejected_missions || 0;
    
                    const pendingPercent = total ? Math.round((pending / total) * 100) : 0;
                    const completedPercent = total ? Math.round((completed / total) * 100) : 0;
                    const rejectedPercent  = total ? Math.round((rejected / total) * 100) : 0;
                    const card = `
                    <div class="col-lg-12 h-100 rounded">
                        <div class="bg-modon h-100 d-flex flex-column p-2 me-2">
                            <div class="d-flex align-items-end mb-2">
                                <img src="/storage/users/${pilot.image}" alt="Search" class="imghover rounded" style="width:50px; height:50px">
                                <div>
                                    <p class="px-2 mb-0 lh-1 text-capitalize" id="pilotname">${pilot.name}</p>
                                    <small 
                                        class="cont-btn px-2 mb-0 lh-1 text-capitalize text-truncate"
      
                                    >
                                        ${pilot.region.split(',')}
                                    </small>
                                </div>
                            </div>
                
                            <!-- Row: Pending, Finished, Rejected -->
                            <div class="d-flex justify-content-between gap-2 py-3 mb-1">
                                <!-- Pending -->
                                <div class="flex-fill p-2">
                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                        <label class="form-check-label mb-0" data-lang-key="pending">Pending</label>
                                        <p class="mb-0 fw-bold">${pending}</p>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: ${pendingPercent}%"></div>
                                    </div>
                                </div>
                
                                <!-- Finished -->
                                <div class="flex-fill p-2">
                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                        <label class="form-check-label mb-0" data-lang-key="finished">Finished</label>
                                        <p class="mb-0 fw-bold">${completed}</p>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: ${completedPercent}%"></div>
                                    </div>
                                </div>
                
                                <!-- Rejected -->
                                <div class="flex-fill p-2">
                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                        <label class="form-check-label mb-0" data-lang-key="rejected">Rejected</label>
                                        <p class="mb-0 fw-bold">${rejected}</p>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: ${rejectedPercent}%"></div>
                                    </div>
                                </div>
                            </div>
                
                            <!-- Row: Total Missions -->
                            <div class="p-2 ">
                                <div class="d-flex justify-content-between align-items-center label-text p-1">
                                    <label class="form-check-label mb-0" data-lang-key="totalMissions">Total Missions</label>
                                    <p class="mb-0 fw-bold">${total}</p>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-info text-white" style="width: ${total ? '100%' : '0%'}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                   
                    $('#missionsPanel').append(card);
                    // updateLanguageTexts(currentLang);
                });
            },
            error: function (xhr, status, error) {
                console.error('Failed to load pilot mission summary:', error);
            }
        });
    }
 
    fetchLatestInspections();
    function fetchLatestInspections() {
        $.ajax({
            url: '/latest-inspections',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
               console.log("Latest Inspections:", data);
    
                const container = $('.IncidentPanel');
                container.empty(); // clear previous data
    
                if (data.length === 0) {
                    container.append('<p class="text-center ">No inspections found.</p>');
                    return;
                }
    
                data.forEach(item => {
                    const html = `
                        <div class="incidentDiv p-2 my-2">
                            <div class="row align-items-center">
                                <div class="col-2 d-flex justify-content-center align-items-center">
                                    <img src="${item.image_path}" class="img-fluid rounded-circle" style="height: 30px; width:30px">
                                </div>
                                <div class="col-10 d-flex flex-column justify-content-center">
                                    <h6 class="mb-0">${item.inspection_name}</h6>
                                    <p class="mb-0">Region: <span class="text-capitalize">${item.region_name}</span > <br> <span class="text-capitalize">${item.location}</span></p>
                                </div>
                            </div>
                        </div>
                    `;
    
                    container.append(html);
                });
            },
            error: function(err) {
                console.error('Failed to load inspections:', err);
            }
        });
    }

    $(".refreshIcon").on('click', function() {
        window.location.reload();
    });
    fetchLatestMissions()
    function fetchLatestMissions() {
        $.ajax({
            url: '/latest-missions',
            method: 'GET',
            dataType: 'json',
            success: function (missions) {
                console.log("🚀 Latest Missions:", missions);
    
                let html = '';
                missions.forEach(mission => {
                    html += `

                        <div class="incidentDiv p-2 my-2"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-custom-class="custom-tooltip"
                           data-title="${mission.note}" 
                            
                            <div class="row align-items-center">
                                <div class="col-10 d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-truncate heartbeat text-capitalize">
                                        ${mission.note.split(' ')[0] || 'No title'}
                                    </h6>
                                    <p class="mb-0 text-capitalize">Region: ${mission.region} | Status: ${mission.status}</p>
                                </div>
                            </div>
                        </div>


                    `;
                });
    
                $('.latestMissionPanel').html(html);

                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');

                tooltipTriggerList.forEach(el => {
                    const content = el.getAttribute('data-title'); // Get content from custom attr
                    new bootstrap.Tooltip(el, {
                        html: true,
                        title: `<strong class="text-dark">Mission Description:</strong><br>${content}`,
                        customClass: 'custom-tooltip'
                    });
                });
                
            },
            error: function (err) {
                console.error("❌ Failed to fetch missions:", err);
            }
        });
    }




    function updateRegionMapFromValues(values) {
        const regionImages = ['center', 'east', 'west'];

        // Hide all images first
        regionImages.forEach(r => $(`#${r}`).hide());

        // Loop over the provided values (like "centergreen")
        values.forEach(val => {
            const match = val.match(/(center|east|west)(green|red|orange)/);
            if (match) {
                const region = match[1];
                const color = match[2];

                // Show the matched region image
                $(`#${region}`).show();

                // Update the src for that region's image
                const newSrc = `./images/map/heatmap/${region}${color}.png`;
                $(`#${region}`).attr('src', newSrc);
            }
        });

        // If it's a reset (3 values), we assume full map view
        if (values.length > 1) {
            $('#mainBgmap').attr('src', './images/map/map.jpg');
        } else if (values.length === 1) {
            const match = values[0].match(/(center|east|west)/);
            if (match) {
                const region = match[1];
                $('#mainBgmap').attr('src', `./images/map/${region}map.jpg`);
            }
        }
    }

    $('.selectRegion').on('click', function () {
        const region = $(this).data('region'); 
    
        // Get value from the mapData element
        const rawValue =  $(this).data(`${region}colorcode`);
    
        // Convert to array
        const values = rawValue ? rawValue.toString().split(',') : [];
    
        console.log(values);
    
        if (region === "reset") {
            $("#mainBgmap").attr('src', `./images/map/map.jpg`);
        } else {
            $("#mainBgmap").attr('src', `./images/map/${region}map.jpg`);
        }
    
        updateRegionMapFromValues(values);
    });
    
});