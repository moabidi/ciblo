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
        this._chart = {};

        this._init = function () {
            Highcharts.setOptions({
                lang: this._lang
            });
            this._chart = Highcharts.chart(this._container, {
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
                title: {
                    text: this._title
                },
                subtitle: {
                    text: this._subtitle
                },
                xAxis: {
                    categories: this._xAxis,
                    title: {
                        text: ''
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: this._mesure
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle'
                },
                plotOptions: {
                    series: {
                        label: {
                            connectorAllowed: false,
                            enabled: false
                        },
                        //pointStart: 2010
                    }
                },
                series: this._data,
                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom'
                            }
                        }
                    }]
                }
            });
        }
    }
});
