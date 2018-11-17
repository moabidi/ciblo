/**
 * Created by abidi on 16/11/18.
 */
$(function($){

    $.handleSearch = new function() {

        _sendRequest = function(uri, method, data, view) {
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
        }

        _handleSuccess = function(response, view) {
            switch (view) {
                case 'global': this._refreshGlobalView(response);break;
                case 'stat': this._refreshStatView(response);break;
                case 'graph': this._refreshGraphView(response);break;
                case 'variety': this._refreshVarietyView(response);break;
            }
        }

        _handleFailure = function(error) {
            alert('error response');
            console.log('error response');
        }

        _refreshGlobalView = function(response) {
            this._refreshTableResult(response,'resultSearch');
        }
        _refreshStatView = function(response) {

        }

        _refreshGraphView = function(response) {

        }

        _refreshVarietyView = function(response) {
            this._refreshTableResult(response,'resultStats');
        }

        _refreshTableResult = function (idTable,content) {
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
        }
        _initSearchButton = function(btn, view) {
            $('#'+btn).on('click', function() {
                var uri = '/statistics/'+view;
                var data = $.handleSearch._getFiltersData(view);
                this._sendRequest(uri, method, data,view)
            });
        }

        _getFiltersData = function (view) {
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
    }

    $(document).ready(function(){
        $.handleSearch._initSearchButton('btn-global-search','global');
        $.handleSearch._initSearchButton('btn-stat-search','stat');
        $.handleSearch._initSearchButton('btn-graph-search','graph');
        $.handleSearch._initSearchButton('btn-variety-search','variety');
    });
})