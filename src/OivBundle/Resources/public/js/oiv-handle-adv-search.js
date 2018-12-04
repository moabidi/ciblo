$(function($){

    $.handleSearch = new function() {

        this._g1;
        this._g2;
        this._g3;
        this._g4;

        this._dataFilter;
        this._listType = ['prod', 'consumption','export','import'];

        /**
         * Ajax request with loader
         * @param uri
         * @param method
         * @param data
         * @param view
         * @param top
         * @private
         */
        this._sendRequest = function(uri, method, data, view, top) {
            global.blockUI({
                target: '',
                animate: true,
                overlayColor: '#000'
            });
            $.ajax({
                'url':uri,
                'method': method,
                'data': data
            }).done(function(response){
                $.handleSearch._handleSuccess(response, view);
                setTimeout(function(){
                    global.unblockUI('');
                }, 500);
            }).fail(function(error){
                setTimeout(function(){
                    global.unblockUI('');
                }, 500);
                alert('error response');
                console.log('error response', error);
            });
        };

        /**
         * Handle sucess response
         * @param response
         * @param view
         * @private
         */
        this._handleSuccess = function(response, view) {
            switch (view) {
                case 'global': $.handleSearch._refreshTableResult(response, 'datatable_orders');break;
                case 'generate-export': $.handleSearch._refreshExport(response);break;
                case 'stattype-countries': $.handleSearch._refreshGraphView(response);break;
            }
        };

        /**
         * Handle error response
         * @param error
         * @private
         */
        this._handleFailure = function(error) {
            alert('error response');
            console.log('error response');
        };

        /**
         * Handle export response
         * @param response
         * @private
         */
        this._refreshExport = function(response) {
            if (typeof response.href != 'undefined') {
                window.open(response.href);
            } else {
                alert('Pas de données à exporter');
            }

        };

        this._refreshGraphView = function(response) {
            $.handleSearch._g1._xAxis = response.xAxis;
            $.handleSearch._g1._data = response.yAxis[response.label];
            $.handleSearch._g1._title = response.label;
            $.handleSearch._g1._mesure = '1000 QX';
            $.handleSearch._g1._init();
        };

        /**
         * Refresh Result table
         * @param content
         * @param idTable
         * @private
         */
        this._refreshTableResult = function (content, idTable) {
            var hearder = '';
            var hearderFilter = '';
            var body = '';
            //console.log(content, content.length);
            if (typeof content.data != 'undefined') {
                $.each(content.labelfields, function (key, val) {
                    hearder += '<th>' + val + '</th>';
                    hearderFilter += '<td rowspan="1" colspan="1"><input class="form-control form-filter input-sm filter-col" type="text"></td>';
                });
                hearder = '<tr role="row" class="heading">'+hearder+'</tr><tr role="row" class="filter">'+hearderFilter+'</tr>';
                $.each(content.data, function (key, items) {
                    classCSS = key%2 ? 'odd':'even';
                    body += '<tr role="row '+classCSS+'">';
                    $.each(items, function (key, val) {
                        body += '<td>' + val + '</td>';
                    });
                    body += '</tr>';
                });
                $('#count-result').text(content.count);
                $('#count-page').text(content.total);
                if (content.total < '2') {
                    $('#pagination-result .pagination').removeClass('show').addClass('hide');
                } else {
                    $('#pagination-result .pagination').removeClass('hide');
                    $('#prev-pg').removeClass('hide');
                    $('#next-pg').removeClass('hide');
                    if (content.current == '1') {
                        $('#prev-pg').addClass('hide');
                    }
                    if (content.total == content.current) {
                        $('#next-pg').addClass('hide');
                    }

                    $('#prev-pg').attr('data-offset', content.prev);
                    $('#current-pg').val(content.current);
                    $('#next-pg').attr('data-offset', content.next);
                }
            }else{
                hearder = '<tr><th class="text-center">Aucune résulat touvée pour votre recherche</th></tr>';
                body = '<tr><td></td></tr>';
                $('#'+idTable).parents().eq(1).find('.pagination').removeClass('show').addClass('hide');
            }
            $('#'+idTable).html('<thead>'+hearder+'</thead><tbody>'+body+'</tbody>');
            $('#'+idTable).parents().eq(1).removeClass('hide').addClass('show');
        };

        /**
         *
         * @param btn
         * @param view
         * @private
         */
        this._initSearchButton = function(btn, view) {
            $(document).on('click',btn, function() {
                var uri = '/fr/statistiques/';
                var data = $.handleSearch._getFiltersData($(this),view);
                if (!data) return false;
                var posLoader = $(this).offset().top;
                $.handleSearch._sendRequest(uri+view, 'POST', data,view, posLoader);
                return false;
            });
        };

        /**
         *
         * @param btn
         * @param view
         * @returns {*}
         * @private
         */
        this._getFiltersData = function (btn,view) {
            var db = $(btn).attr('data-dbType');
            if (db == '') {
                alert('Veuillez sélectionner une base de données');
                return false;
            }
            var data = 'dbType='+db+'&countryCode='+$('#country').val()+'&limit='+$('#limit-pg').val();
            if(db == 'stat') {
                data += '&yearMin='+ $('#yearMin').val();
                data += '&yearMax='+ $('#yearMax').val();
            }else{
                data += '&year='+ $('#year').val();
            }
            if (view == 'stattype-countries') {
                data += '&statType='+$(btn).val()+'&view=tab2';
            } else if (view == 'global') {
                data +='&view=tab1';
                var slectedFilters = '#' + db + ' ' + '[data-view=' + db + ']';
                $(slectedFilters).each(function () {
                    //console.log($(this));
                    if ($(this).val()) {
                        data += '&'+$(this).attr('name') + '=' + $(this).val();
                    }
                });
            }
            $.handleSearch._dataFilter = data;
            return data;
        };

        this._initStatButton = function() {
            $('a.product').on('click', function(){
                $('.graph').removeClass('show').addClass('hide');
                $('#'+$(this).attr('data-graph')).addClass('show');
                return false;
            });
        };

        /**
         * Change Graph stat
         * @private
         */
        this._initStatTypeButton = function() {

            $('a.stat-type').on('click', function(){
                var containerGraph = $(this).attr('data-graph');
                var indexType = $.handleSearch._listType.indexOf($(this).attr('data-statType'));
                $('.graph').removeClass('show').addClass('hide');
                $('#'+containerGraph).addClass('show');
                if (indexType != '-1') {
                    $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item').not('.highcharts-legend-item-hidden').trigger('click');
                    $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item:eq('+indexType+')').trigger('click');
                }
                return false;
            });

            $('.product').on('click', function(){
                var containerGraph = $(this).attr('data-graph');
                $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item.highcharts-legend-item-hidden').trigger('click');
                return false;
            });
        };

        /**
         * Change year filter
         * @private
         */
        this._changeYearStat = function () {
            $('#yearMin,#yearMax').on('change', function () {
                var html = '';
                if ($('#yearMin').val() == $('#yearMax').val()) {
                    html = '<span id="fl-year-min" class="label label-md label-warning" style="font-size: 14px;">'+$(this).val()+'</span>';
                } else{
                    html = '<span class="label label-md label-warning" style="font-size: 14px;">'+$("#yearMin").val()+'</span>';
                    html += ' à <span class="label label-md label-warning" style="font-size: 14px;">'+$("#yearMax").val()+'</span>';
                }
                $('#selected-year p:nth-child(2)').html(html);
            });
            var html = '';
            if ($('#yearMin').val() == $('#yearMax').val()) {
                html = '<span id="fl-year-min" class="label label-md label-warning" style="font-size: 14px;">'+$('#yearMin').val()+'</span>';
            } else{
                html = '<span class="label label-md label-warning" style="font-size: 14px;">'+$("#yearMin").val()+'</span>';
                html += ' à <span class="label label-md label-warning" style="font-size: 14px;">'+$("#yearMax").val()+'</span>';
            }
            $('#selected-year p:nth-child(2)').html(html);
        };

        /**
         * Change result pagination whene number page is changed
         * @private
         */
        this._initHandlePagination = function() {
            $('#pagination-result a').on('click', function () {
                var view = $(this).attr('data-view');
                var uri = '/fr/statistiques/'+view;
                var data = $.handleSearch._dataFilter;
                var posLoader = $(this).offset().top;
                var offset = $(this).attr('data-offset');
                var limit = $('#limit-pg').val();
                data += '&offset='+offset+'&limit='+limit;
                $.handleSearch._sendRequest(uri, 'POST', data,view, posLoader);
            });
        };

        /**
         * Change result pagination whene limit page is changed
         * @private
         */
        this._initHandlePagePagination = function() {
            $('select#limit-pg').on('change', function () {
                var view = $(this).attr('data-view');
                var uri = '/fr/statistiques/'+view;
                var data = $.handleSearch._dataFilter;
                var posLoader = $(this).offset().top;
                var offset = '0';
                var limit = $(this).val();
                data += '&offset='+offset+'&limit='+limit;
                $.handleSearch._sendRequest(uri, 'POST', data,view, posLoader);
            });
        };

        /**
         * Export data into pdf or csv file via ajax request
         * @private
         */
        this._initExportButton = function() {
            $('.export-btn').on('click', function () {
                var exportType = $(this).attr('data-export');
                if ( exportType == 'csv' || exportType =='pdf' ) {
                    var view = 'generate-export';
                    var uri = '/fr/statistiques/'+view;
                    var data = $.handleSearch._dataFilter + '&exportType='+exportType;
                    var posLoader = $(this).offset().top;
                    $.handleSearch._sendRequest(uri, 'POST', data,view, posLoader);
                } else {
                    alert('Export type not available')
                }
            });
        };

        this._initHandleResetFilters = function() {
          $('.reset-filter').on('click', function(){
            $(this).parents().eq(1).find('select, input').val('');
            $(this).parents().eq(1).find('select').trigger('change');
          });
        };

        this._initShowSelectedStatType = function() {
            $('#StatData-statType').on('change', function(){
                $('#selected-statType p:nth-child(2)').html('');
                if ($(this).val()) {
                    $.each($(this).val(), function (k, v) {
                        $('#selected-statType p:nth-child(2)').append(
                            '<button data-dbType="stat" value="' + v + '" class="btn btn-sm yellow table-group-action-submit">' +
                            '<i class="fa fa-check"></i> <i class="fa fa-times"></i> ' + v +
                            '</button>'
                        );
                    });
                }
            });
            $('#selected-statType a').on('click', function () {
                $('#StatData-statType').selectpicker($(this).attr('data-action'));
                $('#StatData-statType').trigger('change');
            })
        };

        /**
         * Search on keyup
         */
        this._initKeypSearch = function() {
            $(document).on("keyup", ".filter-col", function () {
                var value = $(this).val().toLowerCase();
                var index = $(this).parent().index() + 1;
                var table = $(this).parents().eq(3);
                $(table).find("tbody tr").filter(function () {
                    $(this).toggle($(this).find('td:nth-child('+index+')').text().toLowerCase().indexOf(value) > -1)
                });
            });
        };

        /**
         * format long number by add space between thousands
         * @param nStr
         * @returns {string}
         * @private
         */
        this._formatNumber = function (nStr) {
            nStr += '';
            nStr = (Math.round( nStr * 100 )/100 ).toString();
            nStr = Number.parseFloat(nStr);
            nStr = nStr.toFixed(1);
            var x = nStr.split('.');
            var x1 = x[0];
            var x2 = x.length > 1 && x[1]!=0 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ' ' + '$2');
            }
            return x1 + x2;
        }
    };

    $(document).ready(function(){
        global.init(); // init global core components
        Layout.init(); // init current layout
        QuickSidebar.init(); // init quick sidebar

        $.handleSearch._initSearchButton('.filter-submit','global');
        $.handleSearch._initSearchButton('#selected-statType p:nth-child(2) button','stattype-countries');
        $.handleSearch._initExportButton();
        $.handleSearch._initStatButton();
        $.handleSearch._initStatTypeButton();
        $.handleSearch._changeYearStat();
        $.handleSearch._initKeypSearch();
        $.handleSearch._initHandlePagination();
        $.handleSearch._initHandlePagePagination();
        $.handleSearch._initHandleResetFilters();
        $.handleSearch._initShowSelectedStatType();
        $('.multi-select').multiSelect();
        $('.bs-select').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });
        $('.select2me').select2({
            placeholder: "Select",
            allowClear: true
        });
    });
});