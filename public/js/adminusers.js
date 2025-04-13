
$(document).ready(function () {



    const regionChartMissions = document.getElementById('regionMissionChart').getContext('2d');

    const regionChart = new Chart(regionChartMissions, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#C6B40D', '#E9393B', '#78EC70'],
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
                    color: '#FFFFFF',
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
    const regionBarChartInstance = document.getElementById('regionBarChart').getContext('2d');

    const regionBarChart = new Chart(regionBarChartInstance, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [],
                borderRadius: 5,
                barThickness: 30,
                maxBarThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    display: false,
                    grid: {
                        display: false
                    }
                },
                x: {
                    ticks: { color: 'white' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                }
            },
            plugins: {
                legend: { display: false },
                datalabels: {
                    color: 'white',
                    anchor: 'end',
                    align: 'start',
                    offset: -3,
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    formatter: value => value
                }
            }
        },
        plugins: [ChartDataLabels]
    });

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
        fetchInspectionsByRegion(regionBarChart);
        fetchPilotMissionSummary();
    });
    
    



    // ✅ Call after chart is created
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
            'eastern': '#78EC70',
            'western': '#E9393B',
            'central': '#C6B40D'
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
        } else {
            $('#noDataMessage').removeClass('d-none');
        }
    }
    
    


    
    

    fetchInspectionsByRegion(regionBarChart);


    function fetchInspectionsByRegion(chartInstance) {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
    
        // Optional: validate dates here if you want
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            alert("Start date cannot be after end date.");
            return;
        }
        // if (startDate && endDate) {
        //     console.log(`📅 Fetching missions from ${startDate} to ${endDate}`);
        // } else if (startDate) {
        //     console.log(`📅 Fetching missions from ${startDate} onwards`);
        // } else if (endDate) {
        //     console.log(`📅 Fetching missions until ${endDate}`);
        // } else {
        //     console.log("📊 Fetching missions without date filter");
        // }
        $.ajax({
            url: '/inspections-by-region',
            type: 'GET',
            data: {
                start_date: startDate || null,
                end_date: endDate || null
            },
            dataType: 'json',
            success: function (data) {
                // console.log("✅ Inspections by Region:", data);
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
    const rawValues = chartData.map(item => item.inspections);

    // Used only for visual display
    const visualValues = rawValues.map(value => value === 0 ? 0.2 : value);

    // Determine max real value (not 0.2)
    const maxValue = Math.max(...rawValues);
    const barColors = rawValues.map(value =>
        value === maxValue && value > 0 ? 'red' : 'black'
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
    
                $('#missionsPanel').empty(); // Clear existing cards
    
                data.forEach(pilot => {
                    const total = pilot.total_missions || 0;
                    const completed = pilot.completed_missions || 0;
                    const pending = pilot.pending_missions || 0;
    
                    const pendingPercent = total ? Math.round((pending / total) * 100) : 0;
                    const completedPercent = total ? Math.round((completed / total) * 100) : 0;
    
                    const card = `
                        <div class="col-lg-4  h-100 rounded">
                            <div class="bg-modon h-100 d-flex flex-column p-2 me-2">
                               
                                <div class="d-flex align-items-end mb-2">
                                    <img src="${pilot.image}" alt="Search" class="imghover rounded" style="width:50px; height:50px">
                                    <div>
                                        <p class="px-2 mb-0 lh-1 text-capitalize" id="pilotname">${pilot.name}</p>
                                        <small class="cont-btn px-2 mb-0 lh-1  text-capitalize">${pilot.region}</small>
                                    </div>
                                </div>    
                                <div class="p-2">
                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                        <label class="form-check-label mb-0">Pending</label>
                                        <p class="mb-0 fw-bold">${pending}</p>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: ${pendingPercent}%"></div>
                                    </div>
                                </div>
    
                                <div class="p-2">
                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                        <label class="form-check-label mb-0">Finished</label>
                                        <p class="mb-0 fw-bold">${completed}</p>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: ${completedPercent}%"></div>
                                    </div>
                                </div>
    
                                <div class="p-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                        <label class="form-check-label mb-0">Total Missions</label>
                                        <p class="mb-0 fw-bold">${total}</p>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning text-white" style="width: ${total ? '100%' : '0%'}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
    
                    $('#missionsPanel').append(card);
                });
            },
            error: function (xhr, status, error) {
                console.error('Failed to load pilot mission summary:', error);
            }
        });
    }
    // function fetchPilotMissionSummary() {
    //     $.ajax({
    //         url: '/pilot-mission-summary',
    //         method: 'GET',
    //         dataType: 'json',
    //         success: function (data) {
    //             // console.log("Pilot Mission Summary:");
    //             $('#missionsPanel').empty(); // Clear any existing cards
    
    //             data.forEach(pilot => {
    //                 // console.log(`${pilot.name} → Total: ${pilot.total_missions}, Completed: ${pilot.completed_missions}, Pending: ${pilot.pending_missions}`);
    
    //                 const total = pilot.total_missions || 0;
    //                 const completed = pilot.completed_missions || 0;
    //                 const pending = pilot.pending_missions || 0;
    
    //                 // % calculations
    //                 const pendingPercent = total ? Math.round((pending / total) * 100) : 0;
    //                 const completedPercent = total ? Math.round((completed / total) * 100) : 0;
    
    //                 const card = `
    //                     <div class="col-lg-4 h-100  rounded">
    //                         <div class="bg-modon h-100 d-flex flex-column p-2 me-2">
    //                             <p class="pt-2  text-capitalize px-2 fw-bold">${pilot.name}</p>
    
    //                             <div class="p-2">
    //                                 <div class="d-flex justify-content-between align-items-center label-text p-1">
    //                                     <label class="form-check-label mb-0">Pending </label>
    //                                     <p class="mb-0 fw-bold">${pending}</p>
    //                                 </div>
    //                                 <div class="progress">
    //                                     <div class="progress-bar bg-danger" style="width: ${pendingPercent}%"></div>
    //                                 </div>
    //                             </div>
    
    //                             <div class="p-2">
    //                                 <div class="d-flex justify-content-between align-items-center label-text p-1">
    //                                     <label class="form-check-label mb-0">Finished </label>
    //                                     <p class="mb-0 fw-bold">${completed}</p>
    //                                 </div>
    //                                 <div class="progress">
    //                                     <div class="progress-bar bg-success" style="width: ${completedPercent}%"></div>
    //                                 </div>
    //                             </div>
    
    //                             <div class="p-2 mb-2">
    //                                 <div class="d-flex justify-content-between align-items-center label-text p-1">
    //                                     <label class="form-check-label mb-0">Total Missions</label>
    //                                     <p class="mb-0 fw-bold">${total}</p>
    //                                 </div>
    //                                 <div class="progress">
    //                                     <div class="progress-bar bg-warning text-white" style="width: 100%"></div>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                     </div>
    //                 `;
    
    //                 $('#missionsPanel').append(card);
    //             });
    //         },
    //         error: function (xhr, status, error) {
    //             console.error('Failed to load pilot mission summary:', error);
    //         }
    //     });
    // }
    fetchLatestInspections();
    function fetchLatestInspections() {
        $.ajax({
            url: '/latest-inspections',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // console.log("Latest Inspections:", data);
    
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
                                    <h6 class="mb-0">${item.description}</h6>
                                    <p class="mb-0">Region: <span class="text-capitalize">${item.region_name}</span > - <span class="text-capitalize">${item.location}</span></p>
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
    
});