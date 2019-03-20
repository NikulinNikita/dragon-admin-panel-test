$(function () {
    updateChart('registeredUsersCount', $('#date_from').val(), $('#date_to').val());
    $('body').on('click', '.ajax_send', function(e) {
        e.preventDefault();
        let type = $('#select_type').val();
        let date_from = $('#date_from').val();
        let date_to = $('#date_to').val();

        updateChart(type, date_from, date_to, true);
    });
});

function updateChart(type, date_from, date_to, update) {
    $.ajax({method: 'GET', url: '/admin_panel/ajax/getChartData', data: {type: type, date_from: date_from, date_to: date_to} }).done(function(resp) {
        let chartData = JSON.parse(resp);
        let chartDataX = Object.keys(chartData);
        let chartDataY = Object.values(chartData);
        let maxValue = Math.max.apply(Math, chartDataY.length ? chartDataY : [9]);
        let data ={
            labels: chartDataX,
            datasets: [{
                data: chartDataY,
                borderWidth: 2
            }]
        };
        let options = {
            scales: {
                yAxes: [{
                    ticks: {
                        stepSize: Math.ceil((maxValue + maxValue / 10) / 5),
                        beginAtZero:true,
                        suggestedMax: Math.ceil(maxValue + maxValue / 10)
                    }
                }]
            },
            legend: {
                display: false,
            }
        };

        let chartDiv = $(".chart");
        if(update) {
            chartDiv.empty();
        }
        chartDiv.append(
            '<canvas id="lineChart" style="height:250px"></canvas>'
        );
        let canvas = $("#lineChart");
        // let canvas = document.getElementById('lineChart');
        // // if(update) {
        // //     let context = canvas.getContext('2d');
        // //     context.clearRect(0, 0, canvas.width, canvas.height)
        // // }
        let myLineChart = new Chart(canvas, {
            type: 'line',
            data: data,
            options: options
        });
    });
}