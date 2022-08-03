function format(obj) {
  var partpayTable = $('<div/>')
  .addClass( 'loading' )
  .text( 'Loading...' );
  $('#row_merchant').val(obj.id);
  var form_data = computedFormData($('#payment-form').serializeArray()) 

  $.ajax( {
    url: '/admin/get-payment-data',
    data:form_data,
    dataType: 'json',
    success: function ( json ) {
      partpayTable
      .html( json.html )
      .removeClass( 'loading' );
    }
  } );
  return partpayTable;
}


$('#date_type').click(function(){
  if($(this).is(':checked')){
   $('#time_filter').show();
   $('#date-star').hide();
 } else {
  $('#time_filter').hide();
  $('#date-star').show();
}
});


function filter_change() {
 window.location = '?fields='+$('#fields').val();
}  


if ($("#date_type").is(':checked')){
 $('#time_filter').show();
 $('#date-star').hide();
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

$('#date_filter').click(function (e) {
  e.preventDefault();
  window.state = {}
  window.state.paymentReport = {
      'owner[]': $("#owner").val(),
      'investors[]':$("#investors").val()
  }

  table.draw();

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

  $('.timepicker').datetimepicker({
    format: 'HH:mm:ss'
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
    if(window.state && window.state.paymentReport){
        for ( var key in window.state.paymentReport){
           if(Array.isArray(window.state.paymentReport[key]) && window.state.paymentReport[key].length > 0){
               for( var i=0 ; i < window.state.paymentReport[key].length; i++){
                   var pusher = {
                       name:key,
                       value:window.state.paymentReport[key][i]
                   }
                   computed.push(pusher)
               }
           }
        }
    }
    return  $.param(computed)
}
