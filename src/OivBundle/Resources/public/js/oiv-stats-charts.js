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
        this._lang = {};

        this._init = function () {
            Highcharts.setOptions({
                lang: this._lang
            });
            Highcharts.chart(this._container, {
                credits: {
                    enabled: true,
                    text: 'Copyright OIV',
                    href:'http://www.oiv.int'
                },
                exporting: {
                    buttons: {
                        contextButton: {
                            menuItems: ["downloadPNG", "downloadJPEG", "downloadPDF", "downloadSVG","downloadCSV","downloadXLS"]
                        }
                    }
                },
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
                    allowDecimals: false,
                    min: 0,
                    title: {
                        text: this._mesure
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true,
                    valueDecimals: 0
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.1,
                        borderWidth: 2
                    }
                },
                series: this._data
            });
        }
    }
});
