$(function(){
    $('#typeSearch').on('click', function() {
        $('#naming').removeClass('show').addClass('hide');
        $('#education').removeClass('show').addClass('hide');
        $('#variety').removeClass('show').addClass('hide');
        $('#stat').removeClass('show').addClass('hide');
        $('#protection').removeClass('show').addClass('hide');
        $('#'+$(this).val()).removeClass('hide').addClass('show');
        if ($(this).val() == 'stat') {
            $('#yearMax').parent().removeClass('hide').addClass('show');
            $('#yearMin').parent().removeClass('hide').addClass('show');
            $('#year').parent().removeClass('show').addClass('hide');
        }else{
            $('#yearMax').parent().removeClass('show').addClass('hide');
            $('#yearMin').parent().removeClass('show').addClass('hide');
            $('#year').parent().removeClass('hide').addClass('show');
        }
    });
});