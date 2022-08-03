$(".js-investor-placeholder-multiple").select2({
    placeholder: "Select Investor(s)",
    //Function for investor select ajax with pagination
    ajax: {
        url: URL_getInvestors,
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
