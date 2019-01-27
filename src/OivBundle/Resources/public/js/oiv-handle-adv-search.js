$(function($){

    $.handleSearch = new function() {

        this._g1;
        this._uri;
        this._dataFilter ='';
        this._dataTableFilter = '';
        this._dataSort = '';
        this._listType = ['prod', 'consumption','export','import'];

        /**
         * Ajax request with loader
         * @param view
         * @param method
         * @param data
         * @private
         */
        this._sendRequest = function(view, method, data, changeHeader) {
            global.blockUI({
                target: '',
                animate: true,
                overlayColor: '#000'
            });
            $.ajax({
                'url': $.handleSearch._uri + view,
                'method': method,
                'data': data
            }).done(function(response){
                $.handleSearch._handleSuccess(response, view, changeHeader);
                setTimeout(function(){
                    global.unblockUI('');
                    $('.scroll-to-top').trigger('click');
                }, 500);
            }).fail(function(error){
                setTimeout(function(){
                    global.unblockUI('');
                    $('.scroll-to-top').trigger('click');
                }, 500);
                alert($.handleSearch._trans.error_response);
                console.log('error response', error);
            });
        };

        /**
         * Handle sucess response
         * @param response
         * @param view
         * @private
         */
        this._handleSuccess = function(response, view, changeHeader) {
            switch (view) {
                case 'global': $.handleSearch._refreshTableResult(response, 'datatable_orders',changeHeader);break;
                case 'generate-export': $.handleSearch._refreshExport(response);break;
                case 'stattype-countries': $.handleSearch._refreshGraphView(response);break;
                case 'info-naming': $.handleSearch._refreshPopupView(response);break;
            }
        };

        /**
         * Handle error response
         * @param error
         * @private
         */
        this._handleFailure = function(error) {
            alert($.handleSearch._trans.error_response);
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
                alert($.handleSearch._trans.no_result_export);
            }

        };

        /**
         * Refresh popup info (categorie and reference namingData)
         * @param response
         * @private
         */
        this._refreshPopupView = function(response) {
            var html = '';
            if (response.data != 'undefined') {
                if (response.isCtg == '1') {
                    var prevCtg = '';
                    $.each(response.data, function (key, items) {
                        if (prevCtg != items.productType) {
                            html += prevCtg != '' ? '</p></a>' : '';
                            html += '<a href="javascript:;" class="list-group-item">';
                            html += '<h4 class="list-group-item-heading">' + items.productType + '</h4>';
                            html += '<p class="list-group-item-text">';
                        }
                        html += '<button class="btn btn-primary" type="button">' + items.productCategoryName + '</button>';
                        prevCtg = items.productType;
                    });
                    html += '</p></a>';
                } else {
                    $.each(response.data, function (key, items) {
                        if(items.url) {
                            html += '<a href="' + items.url + '" target="_blank" class="list-group-item">';
                        } else {
                            html += '<a href="javascript:;" class="list-group-item">';
                        }
                        html += '<h4 class="list-group-item-heading">' + items.referenceName + '</h4>';
                        html += '</a>';
                    });
                }
            }
            $('#popup h3').html(response.appellationName);
            if (html == '') {
                html = $.handleSearch._trans.no_result_found;
            }
            $('#popup div').html(html);
            $('#popup').modal();
        };

        /**
         *
         * @param response
         * @private
         */
        this._refreshGraphView = function(response) {
            $.handleSearch._g1._xAxis = response.xAxis;
            $.handleSearch._g1._data = response.yAxis[response.statType];
            $.handleSearch._g1._title = response.label;
            $.handleSearch._g1._mesure = response.mesure;
            $.handleSearch._g1._init();
            $('a[href="#tab_graph"]').trigger('click');
        };

        /**
         * Refresh Result table
         * @param content
         * @param idTable
         * @param changeHeader
         * @private
         */
        this._refreshTableResult = function (content, idTable, changeHeader) {
            var hearder = '';
            var hearderFilter = '';
            var body = '';
            //console.log(content, content.length);
            if (typeof content.data != 'undefined') {
                if (changeHeader) {
                    $.each(content.labelfields, function (key, val) {
                        if (val != 'id') {
                            hearder += '<th data-sort="' + key + '" class="sorting" tabindex="0" aria-controls="datatable_orders" rowspan="1" colspan="1" >' + val + '</th>';
                            hearderFilter += '<td rowspan="1" colspan="1"><a><input name="'+ key +'" class="form-control form-filter input-sm filter-col" type="text"><i class="fa fa-remove"></i><i class="icon-magnifier"></i></a></td>';
                        }
                    });
                    hearder = '<tr role="row" class="heading">' + hearder + '</tr><tr role="row" class="filter">' + hearderFilter + '</tr>';
                }
                $.each(content.data, function (key, items) {
                    classCSS = key%2 ? 'odd':'even';
                    body += '<tr role="row '+classCSS+'">';
                    $.each(items, function (key, val) {
                        if (key != 'id') {
                            if (key == 'productCategoryName' || key =='productType' || key=='referenceName') {
                                val = '<a class="info-naming" data-appellationName="'+items.appellationName+'" data-fieldName="'+key+'">' + val + ' ' +content.textView +'</a>';
                            } else if (key == 'url' && val) {
                                val = '<a target="_blank" href="'+val+'">'+content.textViewMore+'</a>';
                            }
                            body += '<td>' + val + '</td>';
                        }
                    });
                    body += '</tr>';
                });
                $('#count-result').text(content.count);
                $('#count-page').text(content.total);
                if (content.total < '2') {
                    $('#pagination-result .pagination.dataTables_paginate').removeClass('show').addClass('hide');
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
                if (hearder != '') {
                    $('#'+idTable).html('<thead>'+hearder+'</thead><tbody>'+body+'</tbody>');
                } else {
                    $('#' + idTable + ' tbody').html(body);
                }
            }else{
                $('#'+idTable).parents().eq(1).find('.pagination.dataTables_paginate').removeClass('show').addClass('hide');
                $('#count-result').text(0);
                $('#'+idTable+' tbody').html('<tr><td colspan="4">'+$.handleSearch._trans.no_result_search+'</td></tr>');
            }
            $('#'+idTable).parents().eq(1).removeClass('hide').addClass('show');
            $('a[href="#tab_table"]').trigger('click');
        };

        /**
         *
         * @param btn
         * @param view
         * @private
         */
        this._initSearchButton = function(btn, view) {
            $(document).on('click',btn, function() {
                var data = $.handleSearch._getFiltersData($(this),view);
                if (!data) return false;
                var blockDBFilter;
                if ($(this).hasClass('btn')) {
                    blockDBFilter = $(this).parents().eq(2);
                } else {
                    blockDBFilter = $(this).parent();
                }
                if ($(this).hasClass('filter-submit')) {
                    if ($(this).hasClass('db-link')){
                        $(this).parents().eq(1).find('li.db').removeClass('active');
                        $(this).parent().addClass('active open');
                        $('.current-db-search').text($(this).find('span.title').text());
                    } else{
                        $(this).parents().eq(3).find('li.db').removeClass('active');
                        $(this).parents().eq(2).addClass('active open');
                        $('.current-db-search').text($(this).parents().eq(2).find('span.title').text());
                    }
                    $('#tab_graph').removeClass('active');
                    $('a[href=#tab_graph]').removeClass('show').addClass('hide');
                    if ($(this).attr('data-dbtype') == 'stat') {
                        $('#selected-filters').removeClass('show').addClass('hide');
                        $('#selected-statType').removeClass('hide').addClass('show');
                        $('a[href=#tab_graph]').removeClass('hide').addClass('show');
                    } else {
                        $('#selected-statType').removeClass('show').addClass('hide');
                        $('#selected-filters').removeClass('hide').addClass('show');
                        $('#selected-filters').html('');
                        $.each($(blockDBFilter).find('li.filter'), function() {
                            var val = $(this).find('select').val();
                            val = val ? val:$.handleSearch._trans.text_all;
                            $('#selected-filters').append('<p>' +
                                '<span class="caption-subject font-red-sunglo bold">'+ $(this).find('a.filter-name').text()+'</span>' +
                                '<span class="label label-md label-warning">'+val+'</span>' +
                                '</p>');
                        });
                    }
                }
                $.handleSearch._sendRequest(view, 'POST', data, true);
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
            if (!$('#country').val()) {
                alert($.handleSearch._trans.select_country);
                return false;
            }
            var data = 'dbType='+db+'&countryCode='+$('#country').val()+'&limit='+$('#limit-pg').val();
            if(db == 'stat') {
                data += '&yearMin='+ $('#yearMin').val();
                data += '&yearMax='+ $('#yearMax').val();
            }
            //else{
            //    data += '&year='+ $('#year').val();
            //}
            if (view == 'stattype-countries') {
                data += '&statType='+$(btn).val();
            } else if (view == 'global') {
                var slectedFilters = '[data-view=' + db + ']';
                $(slectedFilters).each(function () {
                    //console.log($(this));
                    if ($(this).val()) {
                        data += '&'+$(this).attr('name') + '=' + $(this).val();
                    }
                });
            }
            data += '&view=tab2';
            $.handleSearch._dataFilter = data;
            $.handleSearch._dataTableFilter = '';
            $.handleSearch._dataSort = '';
            return data;
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
                var data = $.handleSearch._dataFilter;
                var offset = $(this).attr('data-offset');
                var limit = $('#limit-pg').val();
                /** Set default filter */
                data = data == undefined ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab2':data;
                data += $.handleSearch._dataTableFilter;
                data += '&offset='+offset+'&limit='+limit+$.handleSearch._dataSort;
                $.handleSearch._sendRequest(view, 'POST', data,false);
            });
        };

        /**
         * Change result pagination whene limit page is changed
         * @private
         */
        this._initHandlePagePagination = function() {
            $('select#limit-pg').on('change', function () {
                var view = $(this).attr('data-view');
                var data = $.handleSearch._dataFilter;
                var offset = '0';
                var limit = $(this).val();
                /** Set default filter */
                data = data == undefined ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab2':data;
                data += $.handleSearch._dataTableFilter;
                data += '&offset='+offset+'&limit='+limit+$.handleSearch._dataSort;
                $.handleSearch._sendRequest(view, 'POST', data, false);
            });
        };

        this._initSortData = function() {
            $('body').on('click','th.sorting, th.sorting_asc, th.sorting_desc', function () {
                var view = 'global';
                var data = $.handleSearch._dataFilter;
                var offset = '0';
                var limit = $('#limit-pg').val();
                var sort = $(this).attr('data-sort');
                var order = $(this).hasClass('sorting_asc') ? 'DESC':'ASC';
                $.handleSearch._dataSort = '&sort='+sort+'&order='+order;
                /** Set default filter */
                data = data == undefined ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab2':data;
                data += $.handleSearch._dataTableFilter;
                data += '&offset='+offset+'&limit='+limit+$.handleSearch._dataSort;
                $.handleSearch._sendRequest(view, 'POST', data, false);
                if ($(this).hasClass('sorting_asc')) {
                    $(this).parent().find('th').removeClass('sorting_asc').removeClass('sorting_desc').addClass('sorting');
                    $(this).removeClass('sorting_asc').addClass('sorting_desc');
                } else {
                    $(this).parent().find('th').removeClass('sorting_asc').removeClass('sorting_desc').addClass('sorting');
                    $(this).removeClass('sorting_desc').addClass('sorting_asc');
                }
            });
        };

        /**
         * Search with filter table
         * @private
         */
        this._initSearchTable = function() {
            $('body').on('click','#datatable_orders tr.filter .icon-magnifier', function () {
                var values = '';
                $('body').find('#datatable_orders tr.filter input').each(function(){
                    if ($(this).val()) {
                        values += '&tableFilters['+$(this).attr('name')+']='+$(this).val();
                    }
                });
                $.handleSearch._dataTableFilter = values;
                if ($.handleSearch._dataTableFilter) {
                    var view = 'global';
                    var data = $.handleSearch._dataFilter;
                    var offset = '0';
                    var limit = $('#limit-pg').val();
                    /** Set default filter */
                    data = data == undefined ? 'dbType=stat&countryCode=oiv&year=' + ((new Date()).getFullYear() - 2) + '&view=tab2' : data;
                    data += $.handleSearch._dataTableFilter;
                    data += '&offset=' + offset + '&limit=' + limit + $.handleSearch._dataSort;
                    $.handleSearch._sendRequest(view, 'POST', data, false);
                }
            });
        };

        this._initResetTable = function() {
            $('body').on('click','#datatable_orders tr.filter .fa-remove', function () {
                if ($(this).parent().find('input').val()) {
                    $(this).parent().find('input').val('');
                    var values = '';
                    $('body').find('#datatable_orders tr.filter input').each(function(){
                        if ($(this).val()) {
                            values += '&tableFilters['+$(this).attr('name')+']='+$(this).val();
                        }
                    });
                    $.handleSearch._dataTableFilter = values;
                    var view = 'global';
                    var data = $.handleSearch._dataFilter;
                    var offset = '0';
                    var limit = $('#limit-pg').val();
                    /** Set default filter */
                    data = data == undefined ? 'dbType=stat&countryCode=oiv&year=' + ((new Date()).getFullYear() - 2) + '&view=tab2' : data;
                    data += $.handleSearch._dataTableFilter;
                    data += '&offset=' + offset + '&limit=' + limit + $.handleSearch._dataSort;
                    $.handleSearch._sendRequest(view, 'POST', data, false);
                }
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
                    var data = $.handleSearch._dataFilter + '&exportType='+exportType;
                    $.handleSearch._sendRequest('generate-export', 'POST', data, false);
                } else {
                    alert($.handleSearch._trans.no_type_export);
                }
            });
        };

        /**
         *
         * @private
         */
        this._initHandleResetFilters = function() {
          $('.reset-filter').on('click', function(){
            $(this).parents().eq(1).find('select, input').val('');
            $(this).parents().eq(1).find('select').trigger('change');
          });
        };

        /**
         * Show selected StatType
         * @private
         */
        this._initShowSelectedStatType = function() {
            $('#StatData-statType').on('change', function(){
                $('#selected-statType p.list-product span.list-stat').html('');
                $('#selected-statType p.list-product').removeClass('show').addClass('hide');
                if ($(this).val()) {
                    $.each($(this).val(), function (k, v) {
                        var statType = $('#StatData-statType option[value="'+v+'"]');
                        var product = $('#StatData-statType option[value="'+v+'"]').parent();
                        var selectedProduct = $('#selected-statType').find('#product-'+$(product).attr('id'))[0];
                        $(selectedProduct).removeClass('hide');
                        $(selectedProduct).find('span.list-stat').removeClass('hide');
                        $(selectedProduct).find('span.list-stat').append(
                            '<button data-dbType="stat" value="' + v + '" class="btn btn-sm yellow table-group-action-submit">' +
                            '<i class="fa fa-check"></i>' + $(statType).text() +
                            '</button>'
                        );
                    });
                }
            });
            $('#selected-statType a').on('click', function () {
                $('#StatData-statType').selectpicker($(this).attr('data-action'));
                $('#StatData-statType').trigger('change');
            });
            $('body').on('click','#selected-statType button', function () {
                $('body').find('#selected-statType button').removeClass('active');
                $(this).addClass('active');
            });

            $('#selectAllCountry').on('click', function() {
                $('body').find('#ms-country .ms-optgroup-label').eq(2).trigger('click');
            })
        };

        /**
         * Search on keyup on the data table
         */
        this._initKeypSearch = function() {
            $(document).on("keyup", "thead input.form-filter", function () {
                if ($('.pagination.dataTables_length').hasClass('hide')) {
                    var value = $(this).val().toLowerCase();
                    var index = $(this).parents().eq(1).index() + 1;
                    var table = $(this).parents().eq(4);
                    $(table).find("tbody tr").filter(function () {
                        $(this).toggle($(this).find('td:nth-child(' + index + ')').text().toLowerCase().indexOf(value) > -1)
                    });
                    $('#count-result').text($(table).find("tbody tr:visible").length);
                }
            });
        };

        /**
         *
         * @private
         */
        this._initGetInfoNaming = function () {
            $('body').on('click','td .info-naming' ,function () {
                var data = 'appellationName=' + $(this).attr('data-appellationName');
                data += $(this).attr('data-fieldname') == 'referenceName' ? '&isCtg=0':'&isCtg=1';
                $.handleSearch._sendRequest('info-naming', 'POST', data, false);
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

        this._checkFilterYear = function () {
            $('#yearMax, #yearMin').on('change', function(e) {
               if ($('#yearMax').val() < $('#yearMin').val()) {
                   alert($.handleSearch._trans.error_year);
               }
            });
        }
    };

    $(document).ready(function(){
        global.init(); // init global core components
        Layout.init(); // init current layout
        QuickSidebar.init(); // init quick sidebar

        $.handleSearch._initSearchButton('.filter-submit','global');
        $.handleSearch._initSearchButton('#selected-statType span.list-stat button','stattype-countries');
        $.handleSearch._initExportButton();
        $.handleSearch._changeYearStat();
        $.handleSearch._initKeypSearch();
        $.handleSearch._initHandlePagination();
        $.handleSearch._initHandlePagePagination();
        $.handleSearch._initSortData();
        $.handleSearch._initSearchTable();
        $.handleSearch._initResetTable();
        $.handleSearch._initHandleResetFilters();
        $.handleSearch._initShowSelectedStatType();
        $.handleSearch._initGetInfoNaming();
        $.handleSearch._checkFilterYear();
        $('.multi-select').multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder=''>",
            afterInit: function(ms){
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function(e){
                        if (e.which === 40){
                            that.$selectableUl.focus();
                            return false;
                        }
                    });
            },
            afterSelect: function(){
                this.qs1.cache();
            },
            afterDeselect: function(){
                this.qs1.cache();
            }
        });
        $('.bs-select').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });
        $('.bs-select').selectpicker('refresh');

        $('.select2me').select2({
            placeholder: "Select",
            allowClear: true
        });
    });
});