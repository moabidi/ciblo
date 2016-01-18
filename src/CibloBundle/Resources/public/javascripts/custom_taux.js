/**
 * Created with JetBrains PhpStorm.
 * User: moabidi
 * Date: 18/01/16
 * Time: 19:00
 * To change this template use File | Settings | File Templates.
 */

$(function () {
    $('#container').highcharts({
        title: {
            text: title,
            x: max //center
        },
        subtitle: {
            text: 'Source: WorldClimate.com',
            x: 0
        },
        xAxis: {
            categories: days
        },
        yAxis: {
            title: {
                text: axeName
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: suffix
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: list
    });
});
