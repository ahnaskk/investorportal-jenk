@extends('funding.includes.app')
@section('content')
    <section class="content-area page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 login-outer">
                    <h1>Update Bank Details</h1>


                    <form method="POST" action="{{url('fundings/updatebank')}}" accept-charset="UTF-8"
                          id="bank_details_form" novalidate="novalidate">
                        <div class="box-body box-body-sm bg-grey">
                            @include('layouts.admin.partials.lte_alerts')
                            @csrf
                            <input type="hidden" name="investor_id" value="26">
                            <input type="hidden" name="bid" value="">
                            <div class="form-group">
                                <label for="exampleInputEmail1">The Account Holders Name <span
                                            class="validate_star">*</span></label>
                                <input value="{{old('account_holder_name')}}" class="field"
                                       placeholder="Account holder name" name="account_holder_name" type="text">
                                <label for="account_holder_name" class="error" generated="true"></label>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Account Number <span
                                            class="validate_star">*</span></label>
                                <input value="{{old('acc_number')}}" class="field ac_no"
                                       placeholder="Enter Account Number" name="acc_number" type="number">
                                <label for="acc_number" class="error" generated="true"></label>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">
                                    Routing <span
                                            class="validate_star">*</span>
                                </label>
                                <input value="{{old('routing')}}" class="field" placeholder="Enter Bank Routing Number"  name="routing" type="number" id="routingNumber">
                                <label for="routing" class="error" generated="true"></label>
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">
                                    Bank Name <span
                                            class="validate_star">*</span>
                                </label>
                                <input value="{{old('name')}}" class="field" placeholder="Bank name will be fetched from routing number" name="name" type="text" id="bankName" readonly>
                                <label for="bankName" class="error" generated="true"></label>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Bank Address<span class="validate_star">*</span></label>
                                <textarea class="field" placeholder="Enter Bank Address" name="bank_address" cols="50"
                                          rows="10">{{old('bank_address')}}</textarea>
                                <label for="bank_address" class="error" generated="true"></label>
                            </div>
                            <div class="form-group">
                                <label class="input-group"> Bank Type <span class="validate_star">*</span></label>
                                <div class="input-group-addon check-box-wrap">
                                    <div class="check-box-outer">
                                        <div class="">
                                            <input id="debit" class="checkType" name="type[]" type="checkbox"
                                                   value="debit">
                                            <label for="debit">Debit</label>
                                        </div>
                                        <div class="">
                                            <input id="credit" class="checkType" name="type[]" type="checkbox"
                                                   value="credit">
                                            <label for="credit">Credit</label>
                                        </div>
                                        <div class="">
                                            <input id="default_debit" class="checkType" name="default_debit"
                                                   type="checkbox" value="1">
                                            <label for="default_debit">Default Debit</label>
                                        </div>
                                        <div class="">
                                            <input id="default_credit" class="checkType" name="default_credit"
                                                   type="checkbox" value="1">
                                            <label for="default_credit">Default Credit</label>
                                        </div>
                                    </div>
                                </div>
                                <label for="type[]" class="error" generated="true"></label>
                            </div>
                            <div class="btn-wrap btn-right">
                                <input class="submit-btn" id="submitButton" type="submit" value="Create">
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
<script src="{{url('payment/js/parsley.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>
<script>

    URL_checkRoutingNumber = 'https://www.routingnumbers.info/api/data.json?rn='
    $('#routingNumber').on('change', function() {
        routingNumber =  $(this).val();
        $.ajax({
                   type:'GET',
                   url:URL_checkRoutingNumber+routingNumber,
                   success:function(data)
                   {
                       if (data.code == 200) {
                           $('#bankName').val(data.customer_name)
                           $('#routingNumberError').text('')
                           $('#bankName').valid()

                       } else {
                           $('#routingNumber').val('')
                           $('#bankName').val('')
                           $('#routingNumberError').text('Invalid Routing Number')
                           // $('#routingNumberError').text(data.message)
                       }
                   }
               })
    })


    $('#bank_details_form').validate(
            {
                rules   : {
                    account_holder_name: {required: true, maxlength: 255,},
                    name               : {required: true, maxlength: 255,},
                    acc_number         : {required: true, maxlength: 255,digits: true, minlength: 4,},
                    routing            : {required: true, maxlength: 255, ABARoutingNumberFormat: true},
                    bank_address       : {required:true},
                    'type[]'           : {required: true},
                },
                messages: {
                    account_holder_name: "Enter A/C Holder Name",
                    name               : {required: "Enter Bank Name",},
                    acc_number         : {required: "Enter A/C No",digits: "Enter Valid Acccount No", minlength: 'Account number cannot be less than 4 digits',},
                    'type[]'           : {required: "Please Select Any Type",},
                    bank_address       : {required: "Please enter bank address"}
                }
            });
    jQuery.validator.addMethod("ABARoutingNumberFormat", function (value, element) {
        //all 0's is technically a valid routing number, but it's inactive
        if (!value) {
            return false;
        }
        var routing = value.toString();
        while (routing.length < 9) {
            routing = '0' + routing; //I refuse to import left-pad for this
        }
        //gotta be 9  digits
        var match = routing.match("^\\d{9}$");
        if (!match) {
            return false;
        }
        //The first two digits of the nine digit RTN must be in the ranges 00 through 12, 21 through 32, 61 through 72, or 80.
        //https://en.wikipedia.org/wiki/Routing_transit_number
        const firstTwo = parseInt(routing.substring(0, 2));
        const firstTwoValid = (0 <= firstTwo && firstTwo <= 12)
                              || (21 <= firstTwo && firstTwo <= 32)
                              || (61 <= firstTwo && firstTwo <= 72)
                              || firstTwo === 80;
        if (!firstTwoValid) {
            return false;
        }
        //this is the checksum
        //http://www.siccolo.com/Articles/SQLScripts/how-to-create-sql-to-calculate-routing-check-digit.html
        const weights = [3, 7, 1];
        var sum = 0;
        for (var i = 0; i < 8; i++) {
            sum += parseInt(routing[i]) * weights[i % 3];
        }
        return (10 - (sum % 10)) % 10 === parseInt(routing[8]);
    }, "Please Enter valid Routing Number");
    $('#bank_details_form').submit(function (e) {
        if ($(this).valid()) {
            return true
        }
        else return false
    })
</script>
@endpush

@push('style')
<link href="{{ asset('/css/global/main.css')}}" rel="stylesheet" type="text/css" />
<style>
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance : none;
        -moz-appearance    : none;
        appearance         : none;
        margin             : 0;
    }

    .bg-grey {
        background    : #EFF4F9;
        padding       : 47px 53px;
        border-radius : 30px;
        max-width     : 100%;
        margin-bottom : 30px;
    }

    label.error {
        margin-top  : 14px !important;
        color       : red !important;
        font-weight : normal !important;
    }

    .bg-grey .field, .check-box-wrap {
        background    : white;
        padding       : 0 20px;
        width         : 100%;
        border        : none;
        border-radius : 10px;
        font-size     : 16px;
        color         : #9C9FB4;
        box-shadow    : 0 5px 20px rgb(1 5 129 / 5%);
    }

    input.field, .check-box-wrap {
        height : 52px;
    }

    .check-box-wrap {
        display        : flex;
        flex-direction : column;
    }

    .bg-grey textarea.field {
        padding : 20px;
    }

    form {
        display        : flex;
        align-items    : center;
        flex-direction : column;
    }

    .bg-grey label {
        margin      : 0 0 14px 0;
        font-size   : 16px;
        font-weight : bold;
        color       : #48486E;
        text-align  : left;
    }

    .check-box-outer {
        display     : flex;
        height      : 100%;
        align-items : center;
        flex-wrap   : wrap;
    }

    .check-box-outer > div {
        padding     : 0 10px;
        display     : flex;
        align-items : center;
    }

    .check-box-outer > div label {
        margin     : 0 0 0 5px;
        display    : inline-block;
        word-break : keep-all;
    }

    .btn-wrap {
        margin-top      : 20px;
        display         : flex;
        justify-content : flex-end;
    }

    .btn-wrap .submit-btn {
        border          : none;
        outline         : none;
        background      : #3246D3;
        border-radius   : 10px;
        height          : 49px;
        line-height     : 49px;
        text-decoration : none;
        text-align      : center;
        width           : 180px;
        font-size       : 20px;
        color           : white;
        font-weight     : bold;
        box-shadow      : 0 11px 23px -8px #3b4ed5;
        cursor          : pointer;
    }
</style>
@endpush