$(".js-merchant-placeholder-multiple").select2({
    placeholder: "Select Merchant(s)",
    //Function for merchant select ajax with pagination
    ajax: {
        url: URL_getMerchants,
        dataType: 'json',
        type: 'POST',
        delay: 250,
        data: function (params) {
            var query = {
                '_token': _token,
                search: params.term,
                // type: params._type,
                page: params.page || 1
            }
            return query;
        },
    }
});

var table = window.LaravelDataTables["dataTableBuilder"];
if(performance.navigation.type == 2){
    $(document).ready(function(e){
        setTimeout(() => {table.draw();}, 100);
    })
}