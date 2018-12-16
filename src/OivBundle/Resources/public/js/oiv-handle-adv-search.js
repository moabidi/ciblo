$(function($){

    $.handleSearch = new function() {

        this._g1;
        this._g2;
        this._g3;
        this._g4;
        this._uri = '/fr/statistiques/';
        this._dataFilter;
        this._listType = ['prod', 'consumption','export','import'];

        /**
         * Ajax request with loader
         * @param view
         * @param method
         * @param data
         * @private
         */
        this._sendRequest = function(view, method, data) {
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
                $.handleSearch._handleSuccess(response, view);
                setTimeout(function(){
                    global.unblockUI('');
                    $('.scroll-to-top').trigger('click');
                }, 500);
            }).fail(function(error){
                setTimeout(function(){
                    global.unblockUI('');
                    $('.scroll-to-top').trigger('click');
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
                case 'info-naming': $.handleSearch._refreshPopupView(response);break;
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
                        if (prevCtg != items.productCategoryName) {
                            html += prevCtg != '' ? '</p></a>' : '';
                            html += '<a href="javascript:;" class="list-group-item">';
                            html += '<h4 class="list-group-item-heading">' + items.productCategoryName + '</h4>';
                            html += '<p class="list-group-item-text">';
                        }
                        html += '<button class="btn btn-primary" type="button">' + items.productType + '</button>';
                        prevCtg = items.productCategoryName;
                    });
                    html += '</p></a>';
                } else {
                    $.each(response.data, function (key, items) {
                        html += '<a href="javascript:;" class="list-group-item">';
                        html += '<h4 class="list-group-item-heading">' + items.referenceName + '</h4>';
                        html += '</a>';
                    });
                }
            }
            $('#popup h3').html(response.appellationName);
            if (html == '') {
                html = 'Aucun résultat trouvé !';
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
                    if (val != 'id') {
                        hearder += '<th>' + val + '</th>';
                        hearderFilter += '<td rowspan="1" colspan="1"><input class="form-control form-filter input-sm filter-col" type="text"></td>';
                    }
                });
                hearder = '<tr role="row" class="heading">'+hearder+'</tr><tr role="row" class="filter">'+hearderFilter+'</tr>';
                $.each(content.data, function (key, items) {
                    classCSS = key%2 ? 'odd':'even';
                    body += '<tr role="row '+classCSS+'">';
                    $.each(items, function (key, val) {
                        if (key != 'id') {
                            if (key == 'productCategoryName' || key =='productType' || key=='referenceName') {
                                val = '<a class="info-naming" data-appellationName="'+items.appellationName+'" data-fieldName="'+key+'">' + val + ' ' +content.textView +'</a>';
                            } else if (key == 'url') {
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
                $('#count-result').text(0);
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
                var data = $.handleSearch._getFiltersData($(this),view);
                if (!data) return false;
                if ($(this).hasClass('filter-submit')) {
                    $(this).parents().eq(3).find('li.db').removeClass('active');
                    $(this).parents().eq(2).addClass('active');
                    $('.current-db-search').text($(this).parents().eq(2).find('span.title').text());
                    if ($(this).attr('data-dbtype') == 'stat') {
                        $('#selected-filters').removeClass('show').addClass('hide');
                        $('#selected-statType').removeClass('hide').addClass('show');
                    } else {
                        $('#selected-statType').removeClass('show').addClass('hide');
                        $('#selected-filters').removeClass('hide').addClass('show');
                        $('#selected-filters').html('');
                        $.each($(this).parents().eq(2).find('li.filter'), function() {
                            var val = $(this).find('select').val();
                            val = val ? val:'Tout';
                            $('#selected-filters').append('<p>' +
                                '<span class="caption-subject font-red-sunglo bold uppercase">'+ $(this).find('a.filter-name').text()+'</span>' +
                                '<span class="label label-md label-warning">'+val+'</span>' +
                                '</p>');
                        });
                    }
                }
                $.handleSearch._sendRequest(view, 'POST', data);
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
                alert('Veuillez sélectionner au moins un pays');
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
                var slectedFilters = '[data-view=' + db + ']';
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
                data = data == undefined ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab1':data;
                data += '&offset='+offset+'&limit='+limit;
                $.handleSearch._sendRequest(view, 'POST', data);
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
                data = data == undefined ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab1':data;
                data += '&offset='+offset+'&limit='+limit;
                $.handleSearch._sendRequest(view, 'POST', data);
            });
        };

        this._initSortData = function() {
            $('th.sorting, th.sorting_asc, th.sorting_desc').on('click', function () {
                var view = 'global';
                var data = $.handleSearch._dataFilter;
                var offset = '0';
                var limit = $('#limit-pg').val();
                var sort = $(this).attr('data-sort');
                var order = $(this).hasClass('sorting_asc') ? 'DESC':'ASC';
                /** Set default filter */
                data = data == undefined ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab1':data;
                data += '&offset='+offset+'&limit='+limit+'&sort='+sort+'&order='+order;
                $.handleSearch._sendRequest(view, 'POST', data);
                if ($(this).hasClass('sorting_asc')) {
                    $(this).removeClass('sorting_asc').addClass('sorting_desc');
                } else {
                    $(this).removeClass('sorting_desc').addClass('sorting_asc');
                }
            });
        }

        /**
         * Export data into pdf or csv file via ajax request
         * @private
         */
        this._initExportButton = function() {
            $('.export-btn').on('click', function () {
                var exportType = $(this).attr('data-export');
                if ( exportType == 'csv' || exportType =='pdf' ) {
                    var data = $.handleSearch._dataFilter + '&exportType='+exportType;
                    $.handleSearch._sendRequest('generate-export', 'POST', data);
                } else {
                    alert('Export type not available')
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
                $('#selected-statType p:nth-child(2)').html('');
                if ($(this).val()) {
                    $.each($(this).val(), function (k, v) {
                        $('#selected-statType p:nth-child(2)').append(
                            '<button data-dbType="stat" value="' + v + '" class="btn btn-sm yellow table-group-action-submit">' +
                            '<i class="fa fa-check"></i>' + v +
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
        };

        /**
         * Search on keyup on the data table
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
         *
         * @private
         */
        this._initGetInfoNaming = function () {
            $('body').on('click','td .info-naming' ,function () {
                var data = 'appellationName=' + $(this).attr('data-appellationName');
                data += $(this).attr('data-fieldname') == 'referenceName' ? '&isCtg=0':'&isCtg=1';
                $.handleSearch._sendRequest('info-naming', 'POST', data);
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
        $.handleSearch._changeYearStat();
        $.handleSearch._initKeypSearch();
        $.handleSearch._initHandlePagination();
        $.handleSearch._initHandlePagePagination();
        $.handleSearch._initSortData();
        $.handleSearch._initHandleResetFilters();
        $.handleSearch._initShowSelectedStatType();
        $.handleSearch._initGetInfoNaming();
        //$('.multi-select').multiSelect();
        $('.multi-select').multiSelect({
            selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder=''>",
            selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder=''>",
            afterInit: function(ms){
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#selectedFilter .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function(e){
                        if (e.which === 40){
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function(e){
                        if (e.which == 40){
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },
            afterSelect: function(){
                this.qs1.cache();
                this.qs2.cache();
            },
            afterDeselect: function(){
                this.qs1.cache();
                this.qs2.cache();
            }
        });
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