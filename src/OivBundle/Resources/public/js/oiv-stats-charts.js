/**
 * Created by abidi on 17/11/18.
 */
$(function ($) {

    $.statCharts = new function () {

        this._xAxis = [];
        this._container = '';
        this._data = '';
        this._title = '';
        this._subtitle = '';
        this._mesure = '';

        this._init = function () {

            Highcharts.chart(this._container, {
                chart: {
                    type: 'column'
                },
                title: {
                    text: this._title
                },
                subtitle: {
                    text: this._subtitle
                },
                xAxis: {
                    categories: this._xAxis,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Mesures ('+this._mesure+')'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y:.1f} '+this._mesure+'</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.1,
                        borderWidth: 10
                    }
                },
                series: this._data
            });
        }
    }
});
