/**
 * Created by abidi on 16/11/18.
 */
$(function($){

    $.handleSearch = new function() {

        this._g1;
        this._g2;
        this._g3;
        this._g4;

        this._listType = ['prod', 'consumption','export','import'];

        this._sendRequest = function(uri, method, data, view) {
            $.ajax({
                'uri':uri,
                'method': method,
                'data': data
            }).done(function(response){
                this._handleSuccess(response, view);
            }).fail(function(error){
                alert('error response');
                console.log('error response');
            });
        };

        this._handleSuccess = function(response, view) {
            switch (view) {
                case 'global': this._refreshGlobalView(response);break;
                case 'stat': this._refreshStatView(response);break;
                case 'graph': this._refreshGraphView(response);break;
                case 'variety': this._refreshVarietyView(response);break;
            }
        };

        this._handleFailure = function(error) {
            alert('error response');
            console.log('error response');
        };

        this._refreshGlobalView = function(response) {
            this._refreshTableResult(response,'resultSearch');
        };

        this._refreshStatView = function(response) {

        };

        this._refreshGraphView = function(response) {

        };

        this._refreshVarietyView = function(response) {
            this._refreshTableResult(response,'resultStats');
        };

        this._refreshTableResult = function (idTable,content) {
            var hearder = '';
            var body = '';
            $.each(content.result.labelfields, function(key, val){
                hearder += '<th>'+val+'</th>';
            });
            $.each(response.result.data, function(key, items){
                body += '<tr>';
                $.each(items, function(key, val){
                    body += '<td>'+val+'</td>';
                });
                body += '</tr>';
            });
            $('#'+idTable).html('<thead><tr>'+hearder+'</tr></thead><tbody>'+body+'</tbody>');
        };

        this._initSearchButton = function(btn, view) {
            $('#'+btn).on('click', function() {
                var uri = '/statistics/'+view;
                var data = $.handleSearch._getFiltersData(view);
                $.handleSearch._sendRequest(uri, method, data,view)
            });
        };

        this._getFiltersData = function (view) {
            var data = 'dbType='+$('#dbType').val();
            var slectedFilters = '[data-view='+view+']';
            if (view == 'global') {
                slectedFilters = '#'+$('#dbType').val()+' '+slectedFilters;
            }
            $(slectedFilters).each(function(){
                if ($(this).val()) {
                    data += $(this).attr('name') + '=' + $(this).val() + '&';
                }
            });
            return data;
        }

        this._initStatButton = function() {
            $('a.product').on('click', function(){
                $('.graph').removeClass('show').addClass('hide');
                $('#'+$(this).attr('data-graph')).addClass('show');
                console.log($(this).attr('data-graph'));
                return false;
            });
        }

        this._initStatTypeButton = function() {

            $('a.stat-type').on('click', function(){
                var containerGraph = $(this).attr('data-graph');
                var indexType = $.handleSearch._listType.indexOf($(this).attr('data-statType'));
                $('.graph').removeClass('show').addClass('hide');
                $('#'+containerGraph).addClass('show');
                if (indexType != '-1') {
                    $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item').trigger('click');
                    $('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item:eq('+indexType+')').trigger('click');
                }
                console.log($(this).attr('data-graph'));
                console.log($(this).attr('data-statType'));
                console.log(indexType);
                console.log($('#' + containerGraph + ' .highcharts-legend .highcharts-legend-item:eq('+indexType+')'));
                return false;
            });
        }

        this._changeYearStat = function () {
            $('#nextYear,#prevYear').on('click', function () {
               if ( $(this).attr('data-year')) {
                   var year = parseInt($(this).attr('data-year'));
                   //console.log(g1);console.log(g2);
                   $('#nextYear .year').html(year+1);
                   $('#prevYear .year').html(year-1);
                   $('#nextYear').attr('data-year',year+1);
                   $('#prevYear').attr('data-year',year-1);
                   $('#products tbody tr').each(function(){
                       var product = $(this).attr('id');
                       //console.log(product);console.log( $('#'+product+' .stat-type'));
                       var measure = $(this).attr('data-measure');
                       var data = {'prod':'','consumption':'','export':'','import':''};
                       var indexYear = -1;
                       var dataProduct;
                       console.log(year,product,$.handleSearch._g1);
                       switch (product) {
                           case 'rfresh':
                               indexYear = $.handleSearch._g1._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g1._data;
                           case 'rin':
                               indexYear = $.handleSearch._g2._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g2._data;
                           case 'rtable':
                               indexYear = $.handleSearch._g3._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g3._data;
                           case 'rsec':
                               indexYear = $.handleSearch._g4._xAxis.indexOf(year);
                               dataProduct = $.handleSearch._g4._data;
                       }
                       console.log(indexYear);
                       console.log(dataProduct);
                       data.prod = typeof dataProduct[0].data[indexYear] != 'undefined'? dataProduct[0].data[indexYear]:'0';
                       data.consumption = typeof dataProduct[1].data[indexYear] != 'undefined'? dataProduct[1].data[indexYear]:'0';
                       data.export = typeof dataProduct[2].data[indexYear] != 'undefined'? dataProduct[2].data[indexYear]:'0';
                       data.import = typeof dataProduct[3].data[indexYear] != 'undefined'? dataProduct[3].data[indexYear]:'0';
                       console.log(data);
                       $('#'+product+' .stat-type').each(function () {
                           var statType = $(this).attr('data-statType');
                            $(this).parent().find('.valStatType').parent().html('<span class="valStatType">'+data[statType]+'</span> '+measure );
                       });
                   });
               }
                return false;
            });
        }
    }

    $(document).ready(function(){
        $.handleSearch._initSearchButton('btn-global-search','global');
        $.handleSearch._initSearchButton('btn-country-search','country');
        $.handleSearch._initSearchButton('btn-product-search','country-statistic');
        $.handleSearch._initSearchButton('btn-global-country-search','global-country');
        $.handleSearch._initStatButton();
        $.handleSearch._initStatTypeButton();
        $.handleSearch._changeYearStat();
    });
})