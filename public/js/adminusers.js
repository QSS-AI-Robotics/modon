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