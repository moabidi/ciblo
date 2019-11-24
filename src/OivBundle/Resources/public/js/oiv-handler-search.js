/**
 * Created by abidi on 16/11/18.
 */
$(function($){

    $.handleSearch = new function() {

        this._locale;
        this._g1;
        this._g2;
        this._g3;
        this._g4;
        this._g5;
        this._g6;
        this._g7;
        this._g8;
        this._g9;

        this._uri;
        this._dataSort = '';
        this._dataFilter;
        this._dataTableFilter;
        this._listType = ['prod', 'export','import','consumption','indovcons'];

        this._sendRequest = function(view, method, data, top, changeHeader) {
            $('.card').loading({circles: 3, overlay: true, width:275, top: top});
            $.ajax({
                'url': $.handleSearch._uri + view,
                'method': method,
                'data': data
            }).done(function(response){
                $.handleSearch._handleSuccess(response, view, changeHeader);
                setTimeout(function(){
                    $('.card').loading({destroy: true});
                }, 200);
            }).fail(function(error){
                setTimeout(function(){
                    $('.card').loading({destroy: true});
                }, 200);
                alert($.handleSearch._trans.error_response);
                console.log('error response', error);
            });
        };

        this._handleSuccess = function(response, view, changeHeader) {
            switch (view) {
                case 'global': $.handleSearch._refreshGlobalView(response, changeHeader);break;
                case 'info-naming': $.handleSearch._refreshPopupView(response);break;
                case 'graph': $.handleSearch._refreshGraphView(response);break;
                case 'global-country': $.handleSearch._refreshDataCountryView(response, changeHeader);break;
            }
        };

        /**
         *
         * @param error
         * @private
         */
        this._handleFailure = function(error) {
            alert($.handleSearch._trans.error_response);
            console.log('error response');
        };

        /**
         *
         * @param response
         * @param changeHeader
         * @private
         */
        this._refreshGlobalView = function(response, changeHeader) {
            $.handleSearch._refreshTableResult(response,'resultStats', changeHeader);
        };

        /**
         *
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
                html = $.handleSearch._trans.no_result_found;
            }
            $('#popup div').html(html);
            $('#popup').modal();
        };

        this._refreshGraphView = function(response) {

        };

        /**
         *
         * @param response
         * @param changeHeader
         * @private
         */
        this._refreshDataCountryView = function(response, changeHeader) {
            $.handleSearch._refreshTableResult(response,'resultStats', changeHeader);
        };

        /**
         *
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
                            var info = key=='codeVivc' ? ' <span class="glyphicon glyphicon-info-sign" aria-hidden="false" data-toggle="tooltip" title="'+$.handleSearch._trans.infoCodeVivc+'"></span>':'';
                            hearder += '<th data-sort="' + key + '" class="sorting">' + val + info + '</th>';
                            hearderFilter += '<th><input name="'+key+'" class="filter-col" type="text"><i class="glyphicon glyphicon-remove"></i><i class="glyphicon glyphicon-search"></i></th>';
                        }
                    });
                    hearder = '<tr>' + hearder + '</tr><tr>' + hearderFilter + '</tr>';
                }
                $.each(content.data, function (key, items) {
                    body += '<tr data-dbType="'+content.dbType+'" data-id="'+items.id+'" data-country="'+items.countryCode+'">';
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

                if (content.total < '2') {
                    $('#'+idTable).parents().eq(1).find('.pagination').removeClass('show').addClass('hide');
                } else {
                    $('#'+idTable).parents().eq(1).find('.pagination').removeClass('hide');
                    $('#first-pg-'+idTable).removeClass('hide');
                    $('#prev-pg-'+idTable).removeClass('hide');
                    $('#next-pg-'+idTable).removeClass('hide');
                    $('#last-pg-'+idTable).removeClass('hide');
                    if (content.current == '1') {
                        $('#first-pg-'+idTable).addClass('hide');
                        $('#prev-pg-'+idTable).addClass('hide');
                    } else if (content.current == '2') {
                        $('#prev-pg-'+idTable).addClass('hide');
                    }
                    if (content.total == content.current) {
                        $('#next-pg-'+idTable).addClass('hide');
                        $('#last-pg-'+idTable).addClass('hide');
                    } else if (content.total == (content.current + 1)) {
                        $('#next-pg-'+idTable).addClass('hide');
                    }
                    $('#total-result').removeClass('hide');
                    $('#prev-pg-'+idTable).attr('data-offset', content.prev);
                    $('#current-pg-'+idTable).text(content.current+'/'+content.total);
                    $('#next-pg-'+idTable).attr('data-offset', content.next);
                    $('#last-pg-'+idTable).attr('data-offset', content.last);
                }
                $('#total-result span').text(content.count);
                if (hearder != '') {
                    $('#'+idTable).html('<thead>'+hearder+'</thead><tbody>'+body+'</tbody>');
                } else {
                    $('#' + idTable + ' tbody').html(body);
                }
                //$('#'+idTable).html('<thead>'+hearder+'</thead><tbody>'+body+'</tbody>');
            }else{
                body = '<tr><td class="text-center">'+$.handleSearch._trans.no_result_search+'</td></tr>';
                $('#'+idTable).parents().eq(1).find('.pagination').removeClass('show').addClass('hide');
                $('#'+idTable+' tbody').html(body);
                $('#total-result').addClass('hide');
            }
            $('#'+idTable).parents().eq(1).removeClass('hide').addClass('show');
        };

        this._initSearchButton = function(btn, view) {
            $(btn).on('click', function() {
                var data = $.handleSearch._getFiltersData($(this),view);
                if (!data) return false;
                var posLoader = $(this).offset().top;
                $.handleSearch._sendRequest(view, 'POST', data, posLoader, true);
                $('#container-graph').removeClass('show').addClass('hide');
                $('.result-search').removeClass('hide').addClass('show');
                return false;
            });
        };

        this._getFiltersData = function (btn,view) {
            var db = $(btn).attr('data-dbType');
            if (db == '') {
                alert('Veuillez sélectionner une base de données');
                return false;
            }
            var data = 'dbType='+db+'&countryCode='+$('#country').val();
            data += '&view=tab1';

            $.handleSearch._dataFilter = data;
            $.handleSearch._dataSort = '';
            $.handleSearch._dataTableFilter = '';
            if (view == 'global-country') {
                data += '&limit='+$('#limit-pg-resultStats').val();
            }
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
                if (indexType != '-1' && $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item').length > 1) {
                    $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item').not('.highcharts-legend-item-hidden').trigger('click');
                    $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item:eq('+indexType+')').trigger('click');
                }
                $('.result-search').removeClass('show').addClass('hide');
                $('#container-graph').removeClass('hide').addClass('show');
                return false;
            });

            $('.product').on('click', function(){
                var containerGraph = $(this).attr('data-graph');
                $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item.highcharts-legend-item-hidden').trigger('click');
                $('.result-search').removeClass('show').addClass('hide');
                $('#container-graph').removeClass('hide').addClass('show');
                return false;
            });
        };

        /**
         * Change year table stat
         * @private
         */
        this._changeYearStat = function () {
            $('#nextYear,#prevYear').on('click', function () {
               if ( $(this).attr('data-year')) {
                   var year = parseInt($(this).attr('data-year'));
                   if (year > $.handleSearch._lastStatYear || year < 1995) {
                       alert ($.handleSearch._trans.data_not_available);
                       return false;
                   }
                   $('#nextYear .year').html(year+1);
                   $('#currentYear').html(year);
                   $('#prevYear .year').html(year-1);

                   $('#country-name').attr('data-statYear',year);
                   $('#nextYear').attr('data-year',year+1);
                   $('#prevYear').attr('data-year',year-1);
                   $('.products tbody tr').each(function(){
                       var product = $(this).attr('id');
                       var data = {'prod':'','export':'','import':'','consumption':'','indovcons':''};
                       var indexYear = -1;
                       var dataProduct;
                       var dataProductCapita;

                       switch (product) {
                           case 'rfresh':
                               indexYear = $.handleSearch._g1._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g1._data;
                               dataProductCapita = $.handleSearch._g6._data;break;
                           case 'rin':
                               indexYear = $.handleSearch._g2._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g2._data;
                               dataProductCapita = $.handleSearch._g7._data;break;
                           case 'rtable':
                               indexYear = $.handleSearch._g3._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g3._data;
                               dataProductCapita = $.handleSearch._g8._data;break;
                           case 'rsec':
                               indexYear = $.handleSearch._g4._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g4._data;
                               dataProductCapita = $.handleSearch._g9._data;break;
                           case 'area':
                               indexYear = $.handleSearch._g5._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g5._data;break;
                       }
                       //console.log(product,indexYear,dataProduct);
                       data.prod = typeof dataProduct[0] != 'undefined' && typeof dataProduct[0].data[indexYear] != 'undefined'? dataProduct[0].data[indexYear]:'0';
                       if (product !='area') {
                           data.export = typeof dataProduct[1] != 'undefined' && typeof dataProduct[1].data[indexYear] != 'undefined' ? dataProduct[1].data[indexYear] : '0';
                           data.import = typeof dataProduct[2] != 'undefined' && typeof dataProduct[2].data[indexYear] != 'undefined' ? dataProduct[2].data[indexYear] : '0';
                           data.consumption = typeof dataProduct[3] != 'undefined' && typeof dataProduct[3].data[indexYear] != 'undefined' ? dataProduct[3].data[indexYear] : '0';
                           data.indovcons = typeof dataProductCapita[0] != 'undefined' && typeof dataProductCapita[0].data[indexYear] != 'undefined' ? dataProductCapita[0].data[indexYear] : '0';
                       }
                       //console.log(data);
                       $('#'+product+' .stat-type').each(function () {
                           var statType = $(this).attr('data-statType');
                            $(this).parent().find('.valStatType').text($.handleSearch._formatNumber(data[statType]));
                           if (data[statType]==0) {
                               $(this).parent().addClass('hide');
                           } else {
                               $(this).parent().removeClass('hide');
                           }
                       });
                   });
               }
                return false;
            });

            /** Change year filter */
            $('#year').on('changed.bs.select',function (e, clickedIndex, isSelected, previousValue) {
                if (isSelected) {
                    var top = $(this).offset().top;
                    $('.card').loading({circles: 3, overlay: true, width:275, top: top});
                    $('#prevYear').attr('data-year', $(this).val());
                    $('#prevYear').trigger('click');
                    setTimeout(function(){
                        $('.card').loading({destroy: true});
                    }, 500);
                }
            });
        };

        /**
         * refrech filter switch selected database
         * @private
         */
        this._initRefreshFilter = function() {
            $('#advancedSearch').on('click', function(e) {
                e.preventDefault();
                var countryCode = $('#country').val();
                var year = $('#year').val();
                var href = window.location.href.split('?');
                href = href[0];
                href = href.endsWith('/') ? href+'recherche':href+'/recherche';
                window.location.href = href + '?year=' + year + '&countryCode=' + countryCode;
                return false;
            });
        };

        this._initChangeCountry = function () {
            $('#country').on('changed.bs.select',function (e, clickedIndex, isSelected, previousValue) {
                //console.log(clickedIndex, isSelected, previousValue,$(this).val());
                if (isSelected) {
                    var countryCode = $(this).val();
                    var year = $('#year').val();
                    var href = window.location.href.split('?');
                    window.location.href = href[0] + '?year=' + year + '&countryCode=' + countryCode;
                }
            });
            $('#simpleSearch').on('click',function () {
               $('.fiche-country').removeClass('hide').addClass('show');
            });
        };

        this._initGetInfoNaming = function () {
            $('body').on('click','td .info-naming' ,function () {
                var posLoader = $(this).offset().top;
                var data = 'appellationName=' + $(this).attr('data-appellationName');
                data += $(this).attr('data-fieldname') == 'referenceName' ? '&isCtg=0':'&isCtg=1';
                $.handleSearch._sendRequest('info-naming', 'POST', data, posLoader, false);
            });
        }

        this._initHandlePagination = function() {
            $('.pagination a').on('click', function () {
                var view = $(this).attr('data-view');
                var data = $.handleSearch._dataFilter;
                var posLoader = $(this).offset().top;
                var offset = $(this).attr('data-offset');
                var limit = $(this).parents().eq(3).find('.offset-pg').val();
                data += $.handleSearch._dataTableFilter;
                data += '&offset='+offset+'&limit='+limit+$.handleSearch._dataSort;
                $.handleSearch._sendRequest(view, 'POST', data, posLoader, false);
            });
        }

        this._initHandlePagePagination = function() {
            $('select.offset-pg ').on('change', function () {
                var view = $(this).attr('data-view');
                var data = $.handleSearch._dataFilter;
                var posLoader = $(this).offset().top;
                var offset = '0';
                var limit = $(this).val();
                data += $.handleSearch._dataTableFilter;
                data += '&offset='+offset+'&limit='+limit+$.handleSearch._dataSort;
                $.handleSearch._sendRequest(view, 'POST', data, posLoader, false);
            });
        }


        this._initSortData = function() {
            $('body').on('click','th.sorting, th.sorting_asc, th.sorting_desc', function () {
                var view = 'global';
                var data = $.handleSearch._dataFilter;
                var offset = '0';
                var limit = $('#limit-pg-resultStats').val();
                var sort = $(this).attr('data-sort');
                var order = $(this).hasClass('sorting_asc') ? 'DESC':'ASC';
                $.handleSearch._dataSort = '&sort='+sort+'&order='+order;
                /** Set default filter */
                data = data == undefined ? 'dbType=stat&countryCode=oiv&year='+$.handleSearch._lastStatYear+'&view=tab1':data;
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
            $('body').on('click','#resultStats thead .glyphicon-search', function () {
                var values = '';
                $('body').find('#resultStats thead input.filter-col').each(function(){
                    if ($(this).val()) {
                        values += '&tableFilters['+$(this).attr('name')+']='+$(this).val();
                    }
                });
                $.handleSearch._dataTableFilter = values;
                if ($.handleSearch._dataTableFilter) {
                    var view = 'global';
                    var data = $.handleSearch._dataFilter;
                    var offset = '0';
                    var limit = $('#limit-pg-resultStats').val();
                    /** Set default filter */
                    data = data == undefined ? 'dbType=stat&countryCode=oiv&view=tab1' : data;
                    data += $.handleSearch._dataTableFilter;
                    data += '&offset=' + offset + '&limit=' + limit + $.handleSearch._dataSort;
                    $.handleSearch._sendRequest(view, 'POST', data, false);
                }
            });
        };

        this._initResetTable = function() {
            $('body').on('click','#resultStats thead .glyphicon-remove', function () {
                if ($(this).parent().find('input').val()) {
                    $(this).parent().find('input').val('');
                    var values = '';
                    $('body').find('#resultStats thead input.filter-col').each(function(){
                        if ($(this).val()) {
                            values += '&tableFilters['+$(this).attr('name')+']='+$(this).val();
                        }
                    });
                    $.handleSearch._dataTableFilter = values;
                    var view = 'global';
                    var data = $.handleSearch._dataFilter;
                    var offset = '0';
                    var limit = $('#limit-pg-resultStats').val();
                    /** Set default filter */
                    data = data == undefined ? 'dbType=stat&countryCode=oiv&view=tab1' : data;
                    data += $.handleSearch._dataTableFilter;
                    data += '&offset=' + offset + '&limit=' + limit + $.handleSearch._dataSort;
                    $.handleSearch._sendRequest(view, 'POST', data, false);
                }
            });
        };

        /**
         * Search on keyup
         */
        this._initKeypSearch = function() {
            $(document).on("keyup", ".filter-col", function () {
                if ($('.pagination').hasClass('hide')) {
                    var value = $(this).val().toLowerCase();
                    var index = $(this).parent().index() + 1;
                    var table = $(this).parents().eq(3);
                    $(table).find("tbody tr").filter(function () {
                        $(this).toggle($(this).find('td:nth-child(' + index + ')').text().toLowerCase().indexOf(value) > -1)
                    });
                    $('#total-result span').text($(table).find("tbody tr:visible").length);
                }
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

    $(document).ready(function() {
        $.handleSearch._initSearchButton('.btn-global-country-search','global-country');
        $.handleSearch._initStatButton();
        $.handleSearch._initStatTypeButton();
        $.handleSearch._changeYearStat();
        $.handleSearch._initRefreshFilter();
        $.handleSearch._initKeypSearch();
        $.handleSearch._initChangeCountry();
        $.handleSearch._initHandlePagination();
        $.handleSearch._initHandlePagePagination();
        $.handleSearch._initSortData();
        $.handleSearch._initSearchTable();
        $.handleSearch._initResetTable();
        $.handleSearch._initGetInfoNaming();
        $('.selectpicker').selectpicker();
        $('.selectpicker').trigger('change');
    });
});