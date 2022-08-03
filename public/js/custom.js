    $('#sub_admin1').select2({
        'placeholder': 'Select Admin',
        ajax: {
            url: URL_getInvestorAdmin,
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.items, function (item) {
                        return {
                            text: item.name,
                            slug: item.name,
                            id: item.id
                        }
                    })
                };
            }
        }
    }).change(function () {
       
    });
    
    

