
// while select owner or company get all investors

$('#owner,#payout_frequency').change(function(e)
{
    var company=$('#owner').val();
    var payout_frequency=$('#payout_frequency').val();
    var payout_frequency_length=0;
    if (($("#payout_frequency").length > 0)){
        payout_frequency_length=payout_frequency.length;
    }
    var investors = [];
    //if(company.length)
    {
        
        if(company.length!= 0 || payout_frequency_length!=0) {
            
            $.ajax({
                type: 'POST',
                data: {'company':company,'payout_frequency': payout_frequency,'_token': _token},
                url: '/admin/getCompanyWiseInvestors',
                success: function (data) {
                    var result=data.items;
                    for(var i in result) {
                        investors.push(result[i].id);
                    }
                    $('#investors').attr('selected','selected').val(investors).trigger('change.select2');
                },
                error: function (data) {
                }
            });
        } else {
            $('#investors').val('').trigger('change.select2');
        }
    } 
    // else {
    //     $('#investors').val('').trigger('change.select2');
    //     //$('#investors').attr('selected','selected').val('').trigger('change.select2');  
    // }
});  
function isDefined(value){
    if(value)return value
    return false
}
function len(arr){
    arr = isDefined(arr)
    if(arr) return arr.length
}
