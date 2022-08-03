function format(obj) {
    var investmentData = $('<div/>')
    .addClass( 'loading' )
    .text( 'Loading...' );
    $('#row_merchant').val(obj.id);

    var form_data = computedFormData($('#investor-form').serializeArray())
    // var form_data = $('#investor-form').serialize()
    $.ajax( {
        url: '/admin/get-investor-data',
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


function computedFormData(serialData){
    var computed = []
    var exceptions = ['investors[]','owner[]']
    for(var key in serialData){
        if(exceptions.includes(serialData[key].name)){
            continue
        }else{
            computed.push(serialData[key])
        }
    }
    if(window.state && window.state.investorReport){
        for ( var key in window.state.investorReport){
           if(Array.isArray(window.state.investorReport[key]) && window.state.investorReport[key].length > 0){
               for( var i=0 ; i < window.state.investorReport[key].length; i++){
                   var pusher = {
                       name:key,
                       value:window.state.investorReport[key][i]
                   }
                   computed.push(pusher)
               }
           }
        }
    }
    return  $.param(computed)
}