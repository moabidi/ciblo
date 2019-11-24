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
            .done(function(response, textStatus, xhr){
                if (xhr.status == '401') {
                    window.location.reload();
                }
                $.handleSearch._handleSuccess(response, view, changeHeader);
                setTimeout(function(){
                    global.unblockUI('');
                    $('.scroll-to-top').trigger('click');
                }, 500);
            }).fail(function(error, textStatus, xhr){
                if (error.status == '401') {
                    window.location.reload();
                }
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
                case 'backoffice/edit-parameters': $.handleSearch._refreshParametersView(response);break;
                case 'backoffice/calculate-stat': $.handleSearch._refrechCalculateStat(response);break;
                case 'backoffice/linked-naming-country': $.handleSearch._refrechFormNaming(response);break;
            }
        };

        /**
         * Handle error response
         * @param error
         * @private
         */
        this._handleFailure = function(error) {
            if (typeof error.responseJSON.response != 'undefined') {
                var msg ='';
                console.log(error.responseJSON.messages);
                if(typeof error.responseJSON.messages != 'undefined') {
                    $.each(error.responseJSON.messages, function(key,message){
                        msg += message+'\n';
                        $('#error-'+key).html(message);
                        $('#error-'+key).parents().eq(1).addClass('has-error');
                    });
                }else{
                    msg = error.responseJSON.response;
                }
                alert(msg);
            } else {
                alert('error response');
            }
        };

        /**
         *
         * @param response
         * @private
         */
        this._refrechFormNaming = function(response) {
            if (typeof response.response != 'undefined') {
                var currentForm = $('#form-edit-naming');
                if ($('#tab_add.active #form-naming').length) {
                    currentForm = $('#form-naming');
                }
                var optionAppellationName = '';
                var optionTypeNationalCode = '';
                var optionProductCategory = '';
                $.each(response.response.countryAppellationCode, function(key, val){
                    optionAppellationName += '<option value="'+val+'" data-code="'+key+'">'+val+'</option>';
                });
                $.each(response.response.countryTypeNationalCode, function(key, val){
                    optionTypeNationalCode += '<option value="'+val+'">'+val+'</option>';
                });
                $.each(response.response.productCategory, function(key, val){
                	optionProductCategory += '<option value="'+key+'">'+val+'</option>';
                });
                $(currentForm).find('select[name="appellationName"]').html(optionAppellationName);
                $(currentForm).find('select[name="parentName"]').html(optionAppellationName);
                $(currentForm).find('select[name="typeNationalCode"]').html(optionTypeNationalCode);
                $.each($(currentForm).find('select[name="categories[]"]'), function() {
                	$(this).html(optionProductCategory);
                	$(this).selectpicker('refresh');
                });
                $(currentForm).find('select[name="appellationName"]').selectpicker('refresh');
                $(currentForm).find('select[name="parentName"]').selectpicker('refresh');
                $(currentForm).find('select[name="typeNationalCode"]').selectpicker('refresh');
                /** set selected value when mode is editable */
                $(currentForm).find('select[name="appellationName"]').val($(currentForm).find('select[name="appellationName"]').attr('data-val'));
                $(currentForm).find('select[name="parentName"]').val($(currentForm).find('select[name="parentName"]').attr('data-val'));
                $(currentForm).find('select[name="typeNationalCode"]').val($(currentForm).find('select[name="typeNationalCode"]').attr('data-val'));
                $.each($(currentForm).find('select[name="categories[]"]'), function(){
                	$(this).val($(this).attr('data-val'));
                	$(this).trigger('change');
                });
                $(currentForm).find('select[name="appellationName"]').trigger('change');
                $(currentForm).find('select[name="parentName"]').trigger('change');
                $(currentForm).find('select[name="typeNationalCode"]').trigger('change');

            }
        };

        /**
         *
         * @param response
         * @private
         */
        this._refreshParametersView = function(response) {
            if (typeof response.response != 'undefined' && response.response == 'success') {
                alert('Les modifications sont bien enregistrées');
            }
        };

        /**
         *
         * @param response
         * @private
         */
        this._refrechCalculateStat = function(response) {
            if (typeof response.response != 'undefined' && response.response == 'success') {
                alert('Les statistiques calculées sont en cours de mis à jour, vous recevrez un mail de recap pour chaque statistique.');
            }
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

                $.each($('#'+response.idForm+' .help-block'), function () {
                    $(this).html('');
                    $(this).parents().eq(1).removeClass('has-error');
                });
                $('#'+response.idForm+' .reset-form').trigger('click');
                $('#'+response.idForm+' [name="versioning"]').val($('#'+response.idForm+' [name="versioning"]').attr('data-version'));
                alert('Les données sont enregistrées avec succès');
            } else if (typeof response.response != 'undefined' && typeof response.messages != 'undefined'){
                $.each($('#'+response.idForm+' .help-block'), function () {
                    $(this).html('');
                    $(this).parents().eq(1).removeClass('has-error');
                });
                $.each(response.messages, function (key, message) {
                    $('#error-'+key).html(message);
                    $('#error-'+key).parents().eq(1).addClass('has-error');
                });
                alert('Porblème d\'enregistrement des données ');
            } else {
                alert('Porblème d\'enregistrement des données');
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
                        if (prevCtg == '' || prevCtg == items.productType) {
                            html += prevCtg == '' ? '<a href="javascript:;" class="list-group-item">' : ', ';
                            html += '<span class="list-group-item-heading">' + items.productCategoryName + '</span>';
                        } else {
                            html += '<p class="list-group-item-text">';
                            html += '<button class="btn btn-primary" type="button">' + prevCtg + '</button>';
                            html += '</p></a>';
                            html += '<a href="javascript:;" class="list-group-item">';
                            html += '<span class="list-group-item-heading">' + items.productCategoryName + '</span>';
                        }
                        prevCtg = items.productType;
                    });
                    if (html !='') {
                        html += '<p class="list-group-item-text">';
                        html += '<button class="btn btn-primary" type="button">' + prevCtg + '</button>';
                        html += '</p></a>';
                    }
                } else {
                    $.each(response.data, function (key, items) {
                        if(items.url) {
                            html += '<a href="' + items.url + '" target="_blank" class="list-group-item">';
                        } else {
                            html += '<a href="javascript:;" class="list-group-item">';
                        }
                        html += '<h4 class="list-group-item-heading">' + items.url + '</h4>';
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
            $.handleSearch._g1._axisName = 'Année';
            $.handleSearch._g1._init();
            $('a[href="#tab_graph"]').trigger('click');
        };

        this._hideSelectedFilters = function() {
            $('#selectedFilter').removeClass('show').addClass('hide');
        };

        this._showSelectedFilters = function() {
            $('#selectedFilter').removeClass('hide').addClass('show');
        };

        this._hideSelectedFiltersOnClick = function() {
            $('a[href="#tab_add"],a[href="#tab_edit"],a[href="#tab_import"]').on('click',function(e){
                $.handleSearch._hideSelectedFilters();
                $('.scroll-to-top').trigger('click');
                if ($(this).attr('href') == '#tab_add' && $('li.db.active a[data-dbtype="naming"]').length ) {
                    var data = 'countryCode='+$('#form-naming select[name="countryCode"]').val();
                    $.handleSearch._sendRequest('backoffice/linked-naming-country', 'POST', data, true);
                } else if ($(this).attr('href') == '#tab_edit' && $('li.db.active a[data-dbtype="naming"]').length ) {
                    var data = 'countryCode='+$('#form-edit-naming select[name="countryCode"]').val();
                    $.handleSearch._sendRequest('backoffice/linked-naming-country', 'POST', data, true);
                }
            });
        };

        this._showSelectedFiltersOnClick = function() {
            $('a[href="#tab_table"],a[href="#tab_graph"],#label-selectedFilter').on('click',function(e){
                if ($(this).attr('id')=='label-selectedFilter' && $('#selectedFilter').hasClass('show')) {
                    $.handleSearch._hideSelectedFilters();
                } else {
                    $.handleSearch._showSelectedFilters();
                }
                $('.scroll-to-top').trigger('click');
            });
        };

        this._refreshEdit = function (content) {
            if (typeof content.data != 'undefined' && typeof content.dbType != 'undefined' && $('#form-edit-' + content.dbType).length) {
                $.each(content.data, function(key, val){
                    var input = $('#form-edit-'+content.dbType).find('[name='+key+']')[0];
                    if ($(input).length) {
                        $(input).val(val);
                        $(input).attr('data-val',val);
                        if ($(input).attr('type') == 'checkbox' ) {
                            $(input).val('1');
                            if(val != '' && val != '0') {
                                $(input).prop('checked', true);
                            }else{
                                $(input).prop('checked', false);
                            }
                        }
                    }
                });
                if (typeof content.namingProducts != 'undefined') {
                    var elm = $('#naming-products-edit table tbody tr').first().clone();
                    $(elm).find('div.bs-select').remove();
                    $('#naming-products-edit table tbody').html('');
                    $.each(content.namingProducts, function(key, val){
                        if ($('#naming-products-edit table tbody tr').eq(key).length == 0) {
                            $('#naming-products-edit table tbody').append('<tr>'+$(elm).html()+'</tr>');
                        }
                        var editElm = $('body').find('#naming-products-edit table tbody tr').eq(key);
                        $(editElm).find('input[name="products[]"]').val(val.productCategoryName);
                        $(editElm).find('select[name="categories[]"]').val(val.productType);
                        $(editElm).find('select[name="categories[]"]').attr('data-val',val.productType);
                        $(editElm).find('select[name="categories[]"]').selectpicker('refresh');
                    });
                }
                if (typeof content.namingReferences != 'undefined') {
                    var elm = $('#naming-references-edit table tbody tr').first();
                    $('#naming-references-edit table tbody').html('');
                    $.each(content.namingReferences, function(key, val){
                        if ($('#naming-references-edit table tbody tr').eq(key).length == 0) {
                            $('#naming-references-edit table tbody').append('<tr>'+$(elm).html()+'</tr>');
                        }
                        var editElm = $('body').find('#naming-references-edit table tbody tr').eq(key);
                        $(editElm).find('input[name="references[]"]').val(val.referenceName);
                        $(editElm).find('input[name="urls[]"]').val(val.url);
                    });
                }
                $('#form-edit-' + content.dbType).find('select').trigger('change');
                $('#form-edit-' + content.dbType).find('input[type=checkbox]').uniform('update');
                $('a[href=#tab_edit]').trigger('click');

            } else {
                alert('Un probleme est survenu sur le formulaire d\'édition');
            }
        };

        this._refreshImport = function (content) {
            if (typeof content.data != 'undefined') {
                var header = '';
                var body = '';
                $.each(content.labelfields, function (key, val) {
                    header += '<th aria-controls="datatable_import" rowspan="1" colspan="1" ><span>' + val + '</span></th>';
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
                if ($('#datatable_import tbody tr').length) {
                    var blocImport = $('#tab_import form:visible')[0];
                    $(blocImport).find('.btn-upload-data').removeClass('show').addClass('hide');
                    if ($(blocImport).find('#btn-import-file-product').length) {
                        if ($(blocImport).find('.upload-product.hide').length) {
                            $(blocImport).find('.upload-product').removeClass('hide');
                            $('#btn-import-file-product').removeClass('show').addClass('hide');
                            $(blocImport).find('.upload-naming').removeClass('show').addClass('hide');
                        } else {
                            $(blocImport).find('#btn-import-file-product').removeClass('show').addClass('hide');
                            $(blocImport).find('.btn-import-data').removeClass('hide');
                        }
                    } else {
                        $(blocImport).find('.btn-import-data').removeClass('hide');
                    }
                }
                if (typeof content.nbReplacement != 'undefined' && typeof content.nbNew != 'undefined') {
                	alert('Information : '+ content.nbReplacement +' des données existante seront remplacées par '+ content.nbNew + ' données contenu dans le fichier importé aprés la validation par le button.');
                }
            } else if (typeof content.save != 'undefined') {
                if ( content.save ) {
                    if (content.dataType == 'statData') {
                        alert('Le fichier importé sera traité dans quelques instants, vous receverez un mail  pour vous confirmer l\'enregistrement des données.');
                    } else {
                        alert('Les données sont enregistrées avec succès');
                    }
                    $('#datatable_import').html('')
                    var blocImport = $('#tab_import form:visible')[0];
                    $(blocImport).find('.btn-import-data').removeClass('show').addClass('hide');
                } else {
                    alert('Porbleme d\'enregistrement des données');
                }
            }else if (typeof content.response != 'undefined') {
                alert(content.response);
            }
        };

        this._refreshAfterUpdate = function() {
            $('a[href="#tab_table"],a[href="#tab_graph"]').on('click', function(e){
                if ($('a[href="#tab_add"]').parent().hasClass('active') || $('a[href="#tab_edit"]').parent().hasClass('active') || $('a[href="#tab_import"]').parent().hasClass('active')) {

                    var elmt = $('#stat button.filter-submit');
                    if ($('#education button.filter-submit').is(':visible') || $('#education').parent().hasClass('active')){
                        elmt = $('#education button.filter-submit');
                    }else if($('#naming button.filter-submit').is(':visible') || $('#naming').parent().hasClass('active')){
                        elmt = $('#naming button.filter-submit');
                    }else if($('#variety button.filter-submit').is(':visible') || $('#variety').parent().hasClass('active')){
                        elmt = $('#variety button.filter-submit');
                    }
                    $(elmt).trigger('click');
                }
            });
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
                    hearder += '<th>Actions</th>';
                    hearderFilter += '<td><button class="btn btn-sm yellow filter-submit margin-bottom"><i class="fa fa-search"></i></button></td>';
                    $.each(content.labelfields, function (key, val) {
                        if (key != 'id') {
                            hearder += '<th data-sort="' + key + '" class="sorting '+key+'" tabindex="0" aria-controls="datatable_orders" rowspan="1" colspan="1" ><span>' + val + '</span></th>';
                            hearderFilter += '<td rowspan="1" colspan="1" class="'+key+'"><a><input name="'+ key +'" class="form-control form-filter input-sm filter-col" type="text"><i class="fa fa-remove"></i><i class="icon-magnifier"></i></a></td>';
                        }
                    });
                    hearder = '<tr role="row" class="heading">' + hearder + '</tr><tr role="row" class="filter">' + hearderFilter + '</tr>';
                }
                $.each(content.data, function (key, items) {
                    classCSS = key%2 ? 'odd':'even';
                    body += '<tr role="row '+classCSS+'">';
                    body += '<td><button data-dbtype="'+content.dbType+'" class="btn-edit-data btn btn-sm yellow margin-bottom" value="'+items.id+'">'
                        +'<i class="fa fa-edit"></i></button></td>';
                    $.each(items, function (key, val) {
                        if (key != 'id' && key != 'listReferenceName') {
                            var style= '';
                            if (key == 'productCategoryName' || key =='productType' || key=='appellationCode') {
                                val = '<a class="info-naming" data-appellationCode="'+items.appellationCode+'" data-appellationName="'+items.appellationName+'" data-fieldName="'+key+'">' + val ;
                                val += key!='appellationCode' ? ' ' +content.textView +'</a>':'</a>';
                            } else if (key == 'referenceName') {
                                val = items.listReferenceName;
                            } else if (key == 'url') {
                                val = '<a class="info-naming" data-appellationCode="'+items.appellationCode+'" data-appellationName="'+items.appellationName+'" data-fieldName="referenceName">' + items.referenceName +' ' +content.textView +'</a>';
                            } else if (key == 'internetAdress' && val) {
                                val = (val.indexOf('http') == 0||val.indexOf('HTTP') == 0) ? val:'http://'+val;
                                val = '<a target="_blank" href="'+val+'">'+content.textViewMore+'</a>';
                            } else if (key == 'value') {
                            	
                                val = (val || val==0) ? val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " "):'';
                                style = ' style="text-align: right;"';
                            }
                            val  = val!==null && val !=='null' ? val: '';
                            body += '<td'+style+'>' + val + '</td>';
                        }
                    });
                    body +='</tr>';
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
                $('#'+idTable+' tbody').html('<tr><td colspan="'+$('#'+idTable).find(".heading th").length+'">'+$.handleSearch._trans.no_result_search+'</td></tr>');
            }
            $('#'+idTable).parents().eq(1).removeClass('hide').addClass('show');
            $('a[href="#tab_table"]').trigger('click');
        };

        this._editData = function() {
            $('body').on('click','.btn-edit-data', function(e) {
                e.preventDefault();
                $.handleSearch._hideSelectedFilters();
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
                    if ($(this).hasClass('required') && $(this).val() == '') {
                        $(this).parents().eq(2).addClass('has-error');
                        isValid = false;
                    }else{
                        $(this).parents().eq(2).removeClass('has-error');
                    }
                    if ($(this).attr('type') == 'checkbox' && $(this).attr('checked')) {
                        data += '&' + $(this).attr('name') + '=' + $(this).val();
                    }else if ($(this).attr('type') != 'checkbox'){
                        data += '&' + $(this).attr('name') + '=' + $(this).val();
                    }
                });

                if (isValid) {
                    $.handleSearch._sendRequest('backoffice/create-data', 'POST', data, true);
                }else{
                    alert('Veuillez saisir les champs obligatoires');
                }
            });
            return false;
        };

        this._importData = function() {
            $('.btn-upload-data, .btn-import-data,#btn-import-file-product').on('click', function(e) {
                e.preventDefault();
                var isValid = true;
                var blocImport = $(this).parents().eq(3)[0];
                if ($(blocImport).find('input[name=dataFile]').val() == '') {
                    alert('Veuillez sélectionner un fichier');
                    return isValid = false;
                }
                if($(this).attr('id') == 'btn-import-file-product' && ($(blocImport).find('input[name=dataFileProduts]').val() == '' ||  $(blocImport).find('input[name=dataFileReferences]').val() == '')) {
                    alert('Veuillez sélectionner les dex fichiers à importés');
                    return isValid = false;
                }
                if (isValid) {
                    data = new FormData($(blocImport).get(0));
                    data.append('dbType', $(this).attr('data-dbtype'));
                    if ($(this).hasClass('btn-import-data')) {
                        data.append('save', '1');
                    }
                    $.handleSearch._sendRequest('backoffice/import-data', 'POST', data, true);

                }
                return false;
            });

            $('.reset-upload').on('click', function(e) {
                e.preventDefault();
                var blocImport = $(this).parents().eq(3)[0];
                $(blocImport).find('input[type=file]').val('');
                $(blocImport).find('.btn-upload-data').removeClass('hide');
                $(blocImport).find('.btn-import-data').removeClass('show').addClass('hide');
                if ($(blocImport).find('.upload-product')) {
                    $(blocImport).find('.upload-product').removeClass('show').addClass('hide');
                    $(blocImport).find('.upload-naming').removeClass('hide');
                }
                $('#datatable_import').html('');
            });

            $("input[name=dataFileProduts],input[name=dataFileReferences]").change(function() {
                if($('input[name=dataFileProduts]').val() != '' &&  $('input[name=dataFileReferences]').val() != '') {
                    $('#btn-import-file-product').removeClass('hide');
                }
            });
        };

        /**
         * Get linked data naming to the selected country in form add/edit
         * Auto set appellationCode on change appellationName
         * Auto set parentCode on change parentName
         * @private
         */
        this._getlinkedNamingDataBycountry = function()  {
            $('#form-edit-naming [name="countryCode"], #form-naming [name="countryCode"]').on('change', function(){
                var data = 'countryCode='+$(this).val();
                $.handleSearch._sendRequest('backoffice/linked-naming-country', 'POST', data, true);
            });

            $('#form-edit-naming [name="appellationName"], #form-naming [name="appellationName"]').on('change', function(){
                var currentForm = $('#form-edit-naming');
                if ($('#tab_add.active #form-naming').length) {
                    currentForm = $('#form-naming');
                }
                var selectedOption = $(this).find('option[value="'+$(this).val()+'"]');
                /**Dont change AppelationCode when edit form, just change for NEW appelationName */
                if ($(currentForm).attr('id') == 'form-edit-naming' && typeof $(selectedOption).attr('data-code') != 'undefined' && $(selectedOption).attr('data-code') != $(currentForm).find('input[name="appellationCode"]').val()) {
                    console.log($(selectedOption).attr('data-code'));
                    console.log($(currentForm).find('input[name="appellationCode"]').val());
                    alert('Attention vous devez pas choisir ce nom, il est déjà attribué à un code ');
                }else if (!$(selectedOption).attr('data-code')) {
                    var countryCode = $(this).parents().eq(3).find('[name="countryCode"]').val();
                    var code = $(this).find('option').map(function() {
                        return $(this).attr('data-code') && countryCode ? $(this).attr('data-code').slice(countryCode.length):0;
                    }).get();
                    var lastCode = Math.max.apply(Math, code );
                    lastCode++;
                    $(selectedOption).attr('data-code',countryCode+lastCode);
                }
                /**Change code only for create form */
                if ($(currentForm).attr('id') == 'form-naming') {
                    $(currentForm).find('input[name="appellationCode"]').val($(selectedOption).attr('data-code'));
                }
            });
            $('#form-edit-naming [name="parentName"], #form-naming [name="parentName"]').on('change', function(){
                var currentForm = $('#form-edit-naming');
                if ($('#tab_add.active #form-naming').length) {
                    currentForm = $('#form-naming');
                }
                $(currentForm).find('input[name="parentCode"]').val($(this).find('option[value="'+$(this).val()+'"]') .attr('data-code'));
            });
        };

        /**
         *
         * @private
         */
        this._addProductNaming = function() {
            $('#add-product').on('click', function(){
                var enableAdd = true;
                $('#naming-products table tbody select').each(function(){
                	if ($.handleSearch._isEmpty($(this).val())) {
                        $(this).parents().eq(2).addClass('has-error');
                        enableAdd = false;
                    }else{
                        $(this).parents().eq(2).removeClass('has-error');
                    }
                });
                if (enableAdd) {
                    var elm = $('#naming-products table tbody tr').clone();
                    $(elm).find('div.bs-select').remove();
                    $('#naming-products table tbody').append('<tr>'+$(elm).html()+'</tr>');
                    $('#naming-products').find('select[name="categories[]"]').each(function() {
                        $(this).selectpicker('refresh');
                        console.log($(this));
                    });
                } else {
                    alert('Veuillez remplir tous les champs vides avant d\'ajouter un nouveau type de produit');
                }

            });
            $('body').on('click','#naming-products .remove-product', function(){
                if ($('body').find('#naming-products .remove-product').length>1) {
                    $(this).parents().eq(1).remove();
                }
            });
            $('#add-product-edit').on('click', function(){
                var enableAdd = true;
                $('#naming-products-edit table tbody select').each(function(){
                    if ($.handleSearch._isEmpty($(this).val())) {
                        $(this).parents().eq(2).addClass('has-error');
                        enableAdd = false;
                    }else{
                        $(this).parents().eq(2).removeClass('has-error');
                    }
                });
                if (enableAdd) {
                    var elm = $('#naming-products-edit table tbody tr').clone();
                    $(elm).find('div.bs-select').remove();
                    $('#naming-products-edit table tbody').append('<tr>'+$(elm).html()+'</tr>');
                    $('#naming-products-edit').find('select[name="categories[]"]').each(function(){
                        $(this).selectpicker('refresh');
                    });
                } else {
                    alert('Veuillez remplir tous les champs vides avant d\'ajouter un nouveau type de produit');
                }

            });
            $('body').on('click','#naming-products-edit .remove-product', function(){
                if ($('body').find('#naming-products-edit .remove-product').length>1) {
                    $(this).parents().eq(1).remove();
                }
            });
        };

        this._addReferenceNaming = function() {
            $('#add-reference').on('click', function(){
                var elm = $('#naming-references table tbody tr').html();
                var enableAdd = true;
                $('#naming-references table tbody input[name="references[]"]').each(function(){
                    if ($.handleSearch._isEmpty($(this).val())) {
                        $(this).parents().eq(2).addClass('has-error');
                        enableAdd = false;
                    }else{
                        $(this).parents().eq(2).removeClass('has-error');
                    }
                });
                if (enableAdd) {
                    $('#naming-references table tbody').append('<tr>' + elm + '</tr>');
                } else {
                    alert('Veuillez remplir tous les champs vides avant d\'ajouter une nouvelle base légale');
                }
            });
            $('body').on('click','#naming-references .remove-reference', function(){
                if ($('body').find('#naming-references .remove-reference').length>1) {
                    $(this).parents().eq(1).remove();
                }
            });
            $('#add-reference-edit').on('click', function(){
                var elm = $('#naming-references-edit table tbody tr').html();
                var enableAdd = true;
                $('#naming-references-edit table tbody input[name="references[]"]').each(function(){
                    if ($.handleSearch._isEmpty($(this).val())) {
                        $(this).parents().eq(2).addClass('has-error');
                        enableAdd = false;
                    }else{
                        $(this).parents().eq(2).removeClass('has-error');
                    }
                });
                if (enableAdd) {
                    $('#naming-references-edit table tbody').append('<tr>' + elm + '</tr>');
                } else {
                    alert('Veuillez remplir tous les champs vides avant d\'ajouter une nouvelle base légale');
                }
            });
            $('body').on('click','#naming-references-edit .remove-reference', function(){
                if ($('body').find('#naming-references-edit .remove-reference').length>1) {
                    $(this).parents().eq(1).remove();
                }
            });
        };

        /**
         *
         * @private
         */
        this._editParameters = function () {
            $(document).on('submit','#form-parameters', function(e) {
                e.preventDefault();
                var valid = true;
                var data = '';
                $.each( $('#form-parameters').find('input.db-field, select.db-field') ,function(){
                	if ($(this).val() == '') {
                		valid = false;
                	}
                	data += $(this).attr('name')+'='+$(this).val()+'&';
                });

                if (valid  && data !='') {
                    $.handleSearch._sendRequest('backoffice/edit-parameters', 'POST', data, true);
                } else {
                    alert('Tous les champs sont obligatoires');
                }
            });
        }

        this._calculateStat = function () {
            $(document).on('submit','#form-claculatetd-stat', function(e) {
                e.preventDefault();
                var valYear = $(this).find('[name="calculatedStatYear"]').val();
                var statType = $(this).find('[name="calculatedStat"]').val();
                if (valYear != undefined && statType != undefined ) {
                    $.handleSearch._sendRequest('backoffice/calculate-stat', 'POST', 'statType='+statType+'&year='+valYear, true);
                } else {
                    alert('Veuillez choisir une année valide');
                }
            });
        }

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
                    $('a[href=#tab_caluclatedstat]').removeClass('show').addClass('hide');
                    $('a[href=#tab_parameter]').removeClass('show').addClass('hide');
                    if ($(this).attr('data-dbtype') == 'stat') {
                        $('#selected-filters').removeClass('show').addClass('hide');
                        $('#selected-statType').removeClass('hide').addClass('show');
                        $('a[href=#tab_graph]').removeClass('hide').addClass('show');
                        $('a[href=#tab_caluclatedstat]').removeClass('hide').addClass('show');
                        $('a[href=#tab_parameter]').removeClass('hide').addClass('show');
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
                $('#tab_edit form').removeClass('show').addClass('hide');
                $('#tab_import form').removeClass('show').addClass('hide');
                $('#form-'+$(this).attr('data-dbtype')).removeClass('hide').addClass('show');
                $('#form-edit-'+$(this).attr('data-dbtype')).removeClass('hide').addClass('show');
                $('#import-'+$(this).attr('data-dbtype')).removeClass('hide').addClass('show');
                $('#datatable_import').html('');
                $('.btn-import-data').removeClass('show').addClass('hide');
                $.handleSearch._showSelectedFilters();
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
                    if ($(this).val()) {
                        data += '&'+$(this).attr('name') + '=' + $(this).val();
                    }
                });
            }
            data += '&view=tab3&bo=1';
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
                    $(this).parents().eq(1).find('select#yearMax').val($('select#yearMax option:nth-child(1)').val());
                }
                $(this).parents().eq(1).find('select').trigger('change');
                $(this).parents().eq(1).find('select.bs-select').selectpicker('refresh');
                $('#datatable_orders tbody').html('');
                $('#container-graphic').html('');
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
                var data = 'appellationName=' + $(this).attr('data-appellationName')+'&appellationCode='+$(this).attr('data-appellationCode');
                data += $(this).attr('data-fieldname') == 'referenceName' ? '&isCtg=0':'&isCtg=1';
                $.handleSearch._sendRequest('info-naming', 'POST', data, false);
            });
        };

        this._handleClickModeleImportFile = function() {
            $('body').on('click','a#link-import-file-naming' ,function () {
                var url = /[^?]+/.exec($(this).attr('href'))[0]+'?dbType=naming&';
                $.each($('#naming .filter select'), function()  {
                    if ($(this).val() != '') {
                        url += $(this).attr('name') + '=' + $(this).val()+'&';
                    }
                });
                if (url != '') {
                    url += 'countryCode='+$('#country').val();
                }

                $(this).attr('href',url);
            });
            $('body').on('click','a#link-import-file-education' ,function () {
                var url = /[^?]+/.exec($(this).attr('href'))[0]+'?dbType=education&';
                if ($('#country').val() != '') {
                    url += 'countryCode='+$('#country').val();
                }

                $(this).attr('href',url);
            });
            $('body').on('click','a#link-import-file-variety' ,function () {
                var url = /[^?]+/.exec($(this).attr('href'))[0]+'?dbType=variety&';
                if ($('#country').val() != '') {
                    url += 'countryCode='+$('#country').val();
                }

                $(this).attr('href',url);
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
        };

        this._isEmpty = function(val) {
        	if (!val || val =='' || val == 'null' || val == 'NULL') {
        		return true;
        	}
        	return false;
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
        $.handleSearch._hideSelectedFiltersOnClick();
        $.handleSearch._showSelectedFiltersOnClick();
        $.handleSearch._refreshAfterUpdate();
        $.handleSearch._addProductNaming();
        $.handleSearch._addReferenceNaming();
        $.handleSearch._editParameters();
        $.handleSearch._calculateStat();
        $.handleSearch._handleClickModeleImportFile();
        $.handleSearch._getlinkedNamingDataBycountry();
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
            $('#stat button.filter-submit').trigger('click');

            $('#container_scroll').kinetic();
            $('#attach').on('click', function () {
                if ($(this).hasClass('active')) {
                    $('#container_scroll').kinetic('detach');
                    $(this).removeClass('active');
                } else {
                    $('#container_scroll').kinetic('attach');
                    $(this).addClass('active');
                }
            });
        },500);
        $('.select2me').select2({
            placeholder: "Select",
            allowClear: true
        });
    });
});