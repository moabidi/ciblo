$(function($){

    $.handleSearch = new function() {

        this._g1;
        this._uri = '/fr/statistiques/';
        this._dataFilter = '';
        this._dataTableFilter ='';
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
            var options = {
                'url': $.handleSearch._uri + view,
                'method': method,
                'data': data
            }
            if (view == 'backoffice/import-data') {
                options = {
                    'url': $.handleSearch._uri + view,
                    'method': method,
                    'data': data,
                    processData: false,
                    contentType: false,
                    cache: false,
                }
            }

            $.ajax(options)
                .done(function(response){
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
                $.handleSearch._handleFailure(error);
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
                case 'backoffice/generate-export-bo': $.handleSearch._refreshExport(response);break;
                case 'backoffice/import-data': $.handleSearch._refreshImport(response);break;
                case 'backoffice/create-data': $.handleSearch._refreshCreate(response);break;
                case 'backoffice/edit-data': $.handleSearch._refreshEdit(response);break;
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
            if (typeof error.responseJSON.response != 'undefined') {
                alert(error.responseJSON.response);
            } else {
                alert('error response');
            }
            console.log(error);
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
         * Handle save data response
         * @param response
         * @private
         */
        this._refreshCreate = function(response) {
            if ( typeof response.response != 'undefined' && response.response == 'success') {
                alert('Les données sont enregistrées avec succès');
            } else {
                alert('Porbleme d\'enregistrement des données');
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
            $.handleSearch._g1._data = response.yAxis[response.statType];
            $.handleSearch._g1._title = response.label;
            $.handleSearch._g1._mesure = response.mesure;
            $.handleSearch._g1._init();
            $('a[href="#tab_graph"]').trigger('click');
        };

        this._refreshEdit = function (content) {
            if (typeof content.data != 'undefined' && typeof content.dbType != 'undefined' && $('#form-' + content.dbType).length) {
                $.each(content.data, function(key, val){
                    var input = $('#form-'+content.dbType).find('[name='+key+']')[0];
                    if ($(input).length) {
                        $(input).val(val);
                        if ($(input).attr('type') == 'checkbox' && val != '' && val != '0') {
                            $(input).prop('checked', true);
                        }
                    }
                });
                $('#form-' + content.dbType).find('select').trigger('change');
                $('#form-' + content.dbType).find('input[type=checkbox]').uniform('update');
                $('a[href=#tab_add]').trigger('click');
            } else {
                alert('Un probleme est survenu sur le formulaire d\'édition');
            }
        };

        this._refreshImport = function (content) {
            if (typeof content.data != 'undefined') {
                var header = '';
                var body = '';
                $.each(content.labelfields, function (key, val) {
                    header += '<th aria-controls="datatable_import" rowspan="1" colspan="1" >' + val + '</th>';
                });
                $.each(content.data, function (key, items) {
                    classCSS = key % 2 ? 'odd' : 'even';
                    body += '<tr role="row ' + classCSS + '">';
                    $.each(items, function (key, val) {
                        body += '<td>' + val + '</td>';
                    });
                    body += '</tr>';
                });
                $('#datatable_import').html('<thead><tr class="heading">'+header+'</tr></thead><tbody>'+body+'</tbody>');
            } else if (typeof content.save != 'undefined') {
                if ( content.save == true) {
                    alert('Les données sont enregistrées avec succès');
                    $('#datatable_import').html('');
                } else {
                    alert('Porbleme d\'enregistrement des données');
                }
            }else if (typeof content.response != 'undefined') {
                alert(content.response);
            }
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
            if (typeof content.data != 'undefined') {
                if (changeHeader) {
                    $.each(content.labelfields, function (key, val) {
                        if (val != 'id') {
                            hearder += '<th data-sort="' + key + '" class="sorting" tabindex="0" aria-controls="datatable_orders" rowspan="1" colspan="1" >' + val + '</th>';
                            hearderFilter += '<td rowspan="1" colspan="1"><a><input name="'+ key +'" class="form-control form-filter input-sm filter-col" type="text"><i class="fa fa-remove"></i><i class="icon-magnifier"></i></a></td>';
                        }
                    });
                    hearder += '<th>Actions</th>';
                    hearderFilter += '<td><button class="btn btn-sm yellow filter-submit margin-bottom"><i class="fa fa-search"></i> Rechercher</button></td>';
                    hearder = '<tr role="row" class="heading">' + hearder + '</tr><tr role="row" class="filter">' + hearderFilter + '</tr>';
                }
                $.each(content.data, function (key, items) {
                    classCSS = key%2 ? 'odd':'even';
                    body += '<tr role="row '+classCSS+'">';
                    $.each(items, function (key, val) {
                        if (key != 'id') {
                            if (key == 'url' || key == 'internetAdress') {
                                val = '<a target="_blank" href="'+val+'">'+content.textViewMore+'</a>';
                            }
                            body += '<td>' + val + '</td>';
                        }
                    });
                    body += '<td><button data-dbtype="'+content.dbType+'" class="btn-edit-data btn btn-sm yellow margin-bottom" value="'+items.id+'">'
                        +'<i class="fa fa-edit"></i> Editer</button></td></tr>';
                });
                $('#count-result').text(content.count);
                $('#count-page').text(content.total);
                $('#pagination-result #info-pg').removeClass('hide');
                if (content.total < '2') {
                    $('#pagination-result .pagination.dataTables_length').removeClass('hide');
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

        this._editData = function() {
            $('body').on('click','.btn-edit-data', function(e) {
                e.preventDefault();
                if(Math.floor($(this).val()) == $(this).val() && $.isNumeric($(this).val())) {
                    var data = 'dbType='+$(this).attr('data-dbtype')+'&id='+$(this).val();
                    $.handleSearch._sendRequest('backoffice/edit-data', 'POST', data, true);
                }else{
                    alert('Données n\'esixte pas');
                }
            });
        };


        this._createData = function() {
            $('.btn-create-data').on('click', function(e) {
                e.preventDefault();
                var isValid = true;
                var that = this;
                var data = 'dbType='+$(this).attr('data-dbtype');
                $.each($(that).parents().eq(3).find('input.db-field, select.db-field'), function(){
                    if ($(this).val() == '') {
                        alert('Tous les champs sont obligatoires');
                        console.log($(this));
                        return isValid = false;
                    }
                    if ($(this).attr('type') == 'checkbox' && $(this).attr('checked')) {
                        data += '&' + $(this).attr('name') + '=' + $(this).val();
                    }else if ($(this).attr('type') != 'checkbox'){
                        data += '&' + $(this).attr('name') + '=' + $(this).val();
                    }
                });
                if (isValid) {
                    $.handleSearch._sendRequest('backoffice/create-data', 'POST', data, true);
                }
            });
            return false;
        };

        this._importData = function() {
            $('.btn-upload-data, .btn-import-data').on('click', function(e) {
                e.preventDefault();
                var isValid = true;
                var blocImport = $(this).parents().eq(3)[0];
                if ($(blocImport).find('input[name=dataFile]').val() == '') {
                    alert('Veuillez sélectionner un fichier');
                    return isValid = false;
                }
                if (isValid) {
                    data = new FormData($(blocImport).get(0));
                    data.append('dbType', $(this).attr('data-dbtype'));
                    if ($(this).hasClass('btn-import-data')) {
                        data.append('save', '1');
                    }
                    $.handleSearch._sendRequest('backoffice/import-data', 'POST', data, true);
                    if ($('#datatable_import tbody tr').length) {
                        if ($(this).hasClass('btn-upload-data')) {
                            $(this).removeClass('show').addClass('hide');
                            $(blocImport).find('.btn-import-data').removeClass('hide').addClass('show');
                        } else {
                            $(this).removeClass('show').addClass('hide');
                            $(blocImport).find('.btn-upload-data').removeClass('hide').addClass('show');
                        }
                    }

                }
                return false;
            });

            $('.reset-upload').on('click', function(e) {
                e.preventDefault();
                var blocImport = $(this).parents().eq(3)[0];
                $(blocImport).find('input[type=file]').val('');
                $(blocImport).find('.btn-import-data').removeClass('show').addClass('hide');
                $('#datatable_import').html('');
            });
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
                            val = val ? val:'Tout';
                            $('#selected-filters').append('<p>' +
                                '<span class="caption-subject font-red-sunglo bold">'+ $(this).find('a.filter-name').text()+'</span>' +
                                '<span class="label label-md label-warning">'+val+'</span>' +
                                '</p>');
                        });
                    }
                }
                $.handleSearch._sendRequest(view, 'POST', data, true);
                $('.caption-db').text($(blockDBFilter).find('.title').text());
                $('#tab_add form').removeClass('show').addClass('hide');
                $('#tab_import form').removeClass('show').addClass('hide');
                $('#form-'+$(this).attr('data-dbtype')).removeClass('hide').addClass('show');
                $('#import-'+$(this).attr('data-dbtype')).removeClass('hide').addClass('show');
                $('#datatable_import').html('');
                $('.btn-import-data').removeClass('show').addClass('hide');
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
                if ($('#memberShip:checked').length) {
                    data += '&memberShip=1';
                }
            }
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
            data += '&view=tab3';
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
                data = (data == undefined || data == '') ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab3':data;
                if ($.handleSearch._dataTableFilter != undefined) {
                    data += $.handleSearch._dataTableFilter;
                }
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
                data = (data == undefined || data == '') ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab3':data;
                if ($.handleSearch._dataTableFilter != undefined) {
                    data += $.handleSearch._dataTableFilter;
                }
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
                data = data == undefined ? 'dbType=stat&countryCode=oiv&year='+((new Date()).getFullYear()-2)+'&view=tab3':data;
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
                    data = data == undefined ? 'dbType=stat&countryCode=oiv&year=' + ((new Date()).getFullYear() - 2) + '&view=tab3' : data;
                    data += $.handleSearch._dataTableFilter;
                    data += '&offset=' + offset + '&limit=' + limit + $.handleSearch._dataSort;
                    $.handleSearch._sendRequest(view, 'POST', data, false);
                }
            });
        };

        /**
         * Reset filter table
         * @private
         */
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
                    $.handleSearch._sendRequest('backoffice/generate-export-bo', 'POST', data, false);
                } else {
                    alert('Export type not available')
                }
            });
        };

        /**
         * Reset Input, Select Fields
         * @private
         */
        this._initHandleResetFilters = function() {
            $('.reset-filter').on('click', function(){
                $(this).parents().eq(1).find('select, input').val('');
                if ($(this).parents().eq(1).find('select#yearMin').length) {
                    $(this).parents().eq(1).find('select#yearMin').val($('select#yearMin option:nth-child(1)').val());
                    $(this).parents().eq(1).find('select#yearMax').val($('select#yearMin option:nth-child(1)').val());
                }
                $(this).parents().eq(1).find('select').trigger('change');
                $(this).parents().eq(1).find('select.bs-select').selectpicker('refresh');
                $('#datatable_orders tbody').html('');
                $('#pagination-result .pagination').addClass('hide');
                $('#pagination-result #info-pg').addClass('hide');
            });
            $('.reset-form').on('click', function(){
                $(this).parents().eq(3).find('select, input[type=text],input[type=hidden]').val('');
                $(this).parents().eq(3).find('select').trigger('change');
                $(this).parents().eq(3).find('input[type=checkbox]').prop('checked', false);
                $(this).parents().eq(3).find('input[type=checkbox]').uniform('update');
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
            });
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
        $.handleSearch._createData();
        $.handleSearch._editData();
        $.handleSearch._importData();
        $.handleSearch._checkFilterYear();
        //$('.multi-select').multiSelect();
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
        setTimeout(function() {
            $('#StatData-statType').trigger('change');
        },500);
        $('.select2me').select2({
            placeholder: "Select",
            allowClear: true
        });
    });
});