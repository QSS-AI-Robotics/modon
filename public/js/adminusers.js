
$(document).ready(function () {
    const regionChartInstance = document.getElementById('regionLineChart').getContext('2d');
    const regionChart = new Chart(regionChartInstance, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                // label: 'Missions Completed',
                data: [],
                fill: false,
                tension: 0.4,
                pointBorderColor: '#FFFFFF',
                pointBackgroundColor: '#fff',
                pointHoverBackgroundColor: '#FFFFFF',
                pointHoverBorderColor: '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    color: '#FFFFFF',
                    font: {
                        size: 18,
                        weight: 'bold'
                    }
                },
                legend: {
                    display: false,
                    position: 'bottom',
                    labels: {
                        color: '#FFFFFF'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#FFFFFF'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Regions',
                        color: '#FFFFFF'
                    },
                    ticks: {
                        color: '#FFFFFF'
                    }
                }
            }
        }
    });
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
                maxBarThickness: 40
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
                    offset: -0,
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


    // ✅ Call after chart is created
    fetchMissionsByRegion(regionChart);

    function fetchMissionsByRegion(chartInstance) {
        $.ajax({
            url: '/missions-by-region',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                // console.log('Missions by Region:', data);
                updateChart(chartInstance, data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching missions by region:', error);
            }
        });
    }

    function updateChart(chart, data) {
        const labels = data.map(item => item.region);
        const values = data.map(item => item.missions);

        const hasData = values.length > 0 && values.some(value => value > 0);

        if (hasData) {
            $('#noDataMessage').addClass('d-none');
            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
            chart.update();
        } else {
            chart.data.labels = [];
            chart.data.datasets[0].data = [];
            chart.update();
            $('#noDataMessage').removeClass('d-none');
        }
    }


    fetchInspectionsByRegion(regionBarChart);

    function fetchInspectionsByRegion(chartInstance) {
        $.ajax({
            url: '/inspections-by-region',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                // console.log("Inspection counts by region:");
                // data.forEach(item => {
                //     console.log(`${item.region}: ${item.inspections}`);
                // });
    
                updateInspectionChart(chartInstance, data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching inspections:', error);
            }
        });
    }
    function updateInspectionChart(chart, data) {
        const labels = data.map(item => item.region);
        const rawValues = data.map(item => item.inspections);
    
        // Replace zeros with a tiny number so bars are still visible
        const values = rawValues.map(value => value === 0 ? 0.2 : value);
    
        const maxValue = Math.max(...rawValues);
        const barColors = rawValues.map(value => value === maxValue ? 'red' : 'black');
    
        const hasData = labels.length > 0;
    
        if (hasData) {
            $('#noregionDataMessage').addClass('d-none');
    
            chart.data.labels = labels;
            chart.data.datasets[0].data = values;
            chart.data.datasets[0].backgroundColor = barColors;
    
            // 👇 Ensure we display 0 label even if value is visually small (0.2)
            chart.options.plugins.datalabels.formatter = function (value, context) {
                return rawValues[context.dataIndex]; // show original 0, 10, etc.
            };
    
            chart.update();
        } else {
            chart.data.labels = [];
            chart.data.datasets[0].data = [];
            chart.update();
            $('#noregionDataMessage').removeClass('d-none');
        }
    }
    fetchPilotMissionSummary();
    function fetchPilotMissionSummary() {
        $.ajax({
            url: '/pilot-mission-summary',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                // console.log("Pilot Mission Summary:");
                $('#missionsPanel').empty(); // Clear any existing cards
    
                data.forEach(pilot => {
                    // console.log(`${pilot.name} → Total: ${pilot.total_missions}, Completed: ${pilot.completed_missions}, Pending: ${pilot.pending_missions}`);
    
                    const total = pilot.total_missions || 0;
                    const completed = pilot.completed_missions || 0;
                    const pending = pilot.pending_missions || 0;
    
                    // % calculations
                    const pendingPercent = total ? Math.round((pending / total) * 100) : 0;
                    const completedPercent = total ? Math.round((completed / total) * 100) : 0;
    
                    const card = `
                        <div class="col-lg-4 h-100 shadow-lg rounded">
                            <div class="bg-modon h-100 d-flex flex-column p-2 me-2">
                                <p class="pt-2  text-capitalize px-2 fw-bold">${pilot.name}</p>
    
                                <div class="p-2">
                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                        <label class="form-check-label mb-0">Pending </label>
                                        <p class="mb-0 fw-bold">${pending}</p>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: ${pendingPercent}%"></div>
                                    </div>
                                </div>
    
                                <div class="p-2">
                                    <div class="d-flex justify-content-between align-items-center label-text p-1">
                                        <label class="form-check-label mb-0">Finished </label>
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
                                        <div class="progress-bar bg-warning text-white" style="width: 100%"></div>
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
    fetchLatestInspections();
    function fetchLatestInspections() {
        $.ajax({
            url: '/latest-inspections',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log("Latest Inspections:");
                data.forEach(item => {
                    console.log(`${item.inspection_type} - ${item.location} - ${item.region}`);
                });
            },
            error: function(err) {
                console.error('Failed to load inspections:', err);
            }
        });
    }

    
});