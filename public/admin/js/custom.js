$(document).ajaxComplete(loadCustom);
$(function () {loadCustom();});

function loadCustom() {
    // Initialize select2 on selects
    var selects = $('.spet select');
    if (selects.length > 0)
        selects.select2();

    // DateTimepicker
    var datetimepicker = $('.b-datetimepicker');
    var datetimepicker_input = $('.b-datetimepicker input');
    if (datetimepicker.length) {
        datetimepicker.datetimepicker({
            format: 'dd.MM.yyyy hh:mm',
            pickSeconds: false,
            language: 'ru'
        });

        // MASKED INPUT PLUGIN
        datetimepicker_input.mask("99.99.9999 99:99", {placeholder: "00.00.0000 00:00"});
    }

    var datepicker = $('.b-datepicker');
    var datepicker_input = $('.b-datepicker input');
    if (datepicker.length) {
        datepicker.datetimepicker({
            format: 'dd.MM.yyyy',
            pickTime: false,
            language: 'ru'
        });

        // MASKED INPUT PLUGIN
        datepicker_input.mask("99.99.9999", {placeholder: "00.00.0000"});
    }

    var timepicker = $('.b-timepicker');
    var timepicker_input = $('.b-timepicker input');
    if (timepicker.length) {
        timepicker.datetimepicker({
            format: 'hh:mm',
            pickDate: false,
            pickSeconds: false
        });

        // MASKED INPUT PLUGIN
        timepicker_input.mask("99:99", {placeholder: "00:00"});
    }

    // Report's plot of visitings
    var plot = $('#b-plot_container'), yAxisInt = [];
    if(plot[0]) {
        var xAxis = plot.attr('data-xaxis').split(',');
        var yAxis = plot.attr('data-yaxis').split(',');
        yAxis.map(function (val) {
            return yAxisInt.push(parseInt(val));
        });

        Highcharts.chart('b-plot_container', {
            title: {
                text: 'Посещаемость страницы заведений',
                x: -20 //center
            },
            subtitle: {
                //text: 'Source: WorldClimate.com',
                x: -20
            },
            xAxis: {
                //categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                categories: xAxis
            },
            yAxis: {
                title: {
                    text: 'Кол-во посещений'
                },
                plotLines: [{
                    value: 0,
                    width: 2,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: ' посещений'
            },
            legend: {
                //layout: 'vertical',
                //align: 'right',
                //verticalAlign: 'middle',
                //borderWidth: 0
            },
            series: [{
                name: ' ',
                data: yAxisInt
                //}, {
                //    name: 'New York',
                //    data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
            }]
        })
    }
}