$(document).ready(function () {
    //initial state
    window.state = {
        commissionReport:$("#commission-form").serializeArray(),
        investorReport:false
    }
    $(".js-status-placeholder-multiple").select2({
        placeholder: "Select Status(es)"
    });

    $(".js-rcode-placeholder-multiple").select2({
        placeholder: "Select Rcode"
    });

    $(".js-lender-placeholder-multiple").select2({
        placeholder: "Select Lender(s)"
    });
    
    $(".js-advtype-placeholder-multiple").select2({
        placeholder: "Select Advance Type"
    });

    $(".js-company-placeholder").select2({
        placeholder: "Select Company"
    });

    $(".js-industry-placeholder-multiple").select2({
        placeholder: "Select Industry(s)"
    });

    $(".js-substatus-flag-placeholder-multiple").select2({
        placeholder: "Select Sub Status Flag"
    });

    $(".js-investor-type-placeholder-multiple").select2({
        placeholder: "Select Investor Type"
    });

    $(".js-category-placeholder-multiple").select2({
        placeholder: "Select Transaction Categories"
    });


    $(".js-rcode-placeholder-multiple").select2({
        placeholder: "Select Rcode(s)"
    });

      $(".js-label-placeholder-multiple").select2({
        placeholder: "Select Label"
    });

    $(".js-company-placeholder-multiple").select2({
        placeholder: "Select Company"
    });
});  // end $(document).ready





