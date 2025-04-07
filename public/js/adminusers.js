
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

    // ✅ Call after chart is created
    fetchMissionsByRegion(regionChart);

    function fetchMissionsByRegion(chartInstance) {
        $.ajax({
            url: '/missions-by-region',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                console.log('Missions by Region:', data);
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

   

});