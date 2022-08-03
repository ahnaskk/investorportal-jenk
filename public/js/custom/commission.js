function format(obj) {
    var investmentData = $('<div/>')
    .addClass( 'loading' )
    .text( 'Loading...' );
    $('#row_merchant').val(obj.id);
    var form_data = computedFormData(obj.id)
    
    $.ajax( {
        url: '/admin/get-commission-data',
        data:form_data,
        dataType: 'json',
        success: function ( json ) {
            investmentData
            .html(json.html)
            .removeClass( 'loading' );
        }
    } );
    return investmentData;
}

$('#dataTableBuilder tbody').on('click', 'td.details-control ', function () {
    var tr = $(this).closest('tr');
    var row = table.row(tr);
    if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
    }
    else {
        row.child(format(row.data())).show();
        tr.addClass('shown');
    }
});

$('.from_date1').change(function () {
    var from_date1 = $('.from_date1').val();
    $(".from_date2").val(from_date1);
});
$('.to_date1').change(function () {
    var to_date1 = $('.to_date1').val();
    $(".to_date2").val(to_date1);
});
$('.from_date2').change(function () {
    var from_date2 = $('.from_date2').val();
    $(".from_date1").val(from_date2);
});
$('.to_date2').change(function () {
    var to_date2 = $('.to_date2').val();
    $(".to_date1").val(to_date2);
});
$('#date_type').click(function(){
    if($(this).is(':checked')){
        $('#time_filter').show();
        $('#test').hide();
        $("#test").css("display", "none");
    } else {
        $('#time_filter').hide();
        $("#test").css("display", "block");
        $('#test').show();
    }
});
// $('.timepicker').datetimepicker({
//           format: 'HH:mm:ss'
//         }); 
$('#date_type1').click(function()
{
    if($(this).is(':checked')){
        $('#date-star2').show();
        $('#date-star').hide();
    } else {
        $('#date-star2').hide();
        $('#date-star').show();
        $('#time_filter').hide();
    }    
});


function computedFormData(row_merchant){
    if(window.state && window.state.commissionReport){
        window.state.commissionReport.forEach(function(params){
            if(params.name == 'row_merchant'){
                params.value = row_merchant
            }
        })
        return  $.param(window.state.commissionReport)
    }else{
        return $('#commission-form').serialize()
    }
}