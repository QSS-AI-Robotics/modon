const ctx = document.getElementById('regionLineChart').getContext('2d');
const regionChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Region 1', 'Region 2', 'Region 3', 'Region 4', 'Region 5'],
        datasets: [{
            label: 'Missions Completed',
            data: [12, 19, 7, 15, 10],
            fill: false,
            // color: '#FFFFFF',
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
                // text: 'Missions Completed per Region',
                color: '#FFFFFF', // dark text
                font: {
                    size: 18,
                    weight: 'bold'
                }
            },
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    color: '#343a40' // legend text color
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    // text: 'Number of Missions',
                    color: '#FFFFFF'
                },
                ticks: {
                    color: '#FFFFFF' // y-axis label color
                },
              
            },
            x: {
                title: {
                    display: true,
                    text: 'Regions',
                    color: '#FFFFFF'
                },
                ticks: {
                    color: '#FFFFFF' // x-axis label color
                },
             
            }
        }
    }
});


// Get the canvas context
const ctx2 = document.getElementById('regionBarChart').getContext('2d');

// Data setup
const labels = ['Region A', 'Region B', 'Region C', 'Region D', 'Region E'];
const values = [12, 19, 7, 14, 10];

// Determine the max value and index
const maxValue = Math.max(...values);
const maxIndex = values.indexOf(maxValue);

// Color each bar: red for max, black for the rest
const barColors = values.map((value, index) =>
    index === maxIndex ? 'red' : 'black'
);

// Create the Chart
const regionBarChart = new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Missions',
            data: values,
            backgroundColor: barColors,
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
                display: false, // Hide Y axis ticks and grid
                grid: {
                    display: false
                }
            },
            x: {
                ticks: {
                    color: 'white' // X-axis labels color
                },
                grid: {
                    color: 'rgba(255,255,255,0.05)'
                }
            }
        },
        plugins: {
            legend: {
                display: false // Hide legend
            },
            datalabels: {
                color: 'white',          // Label color
                anchor: 'end',           // Position at end of bar
                align: 'start',          // Align above the bar
                offset: -10,             // Space above the bar
                font: {
                    weight: 'bold',
                    size: 12
                },
                formatter: function(value) {
                    return value;       // Show raw value
                }
            }
        }
    },
    plugins: [ChartDataLabels] // Register the DataLabels plugin
});

