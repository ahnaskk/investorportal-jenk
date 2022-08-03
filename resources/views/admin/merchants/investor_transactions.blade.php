<?php use App\Library\Helpers\InvestorTransaction as InvestorTransactionHelper; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Investor Transactions</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Investor Transactions</div>
    </a>
</div>
{{ Breadcrumbs::render('admin::merchants::investor_transactions') }}
<div class="col-md-12">
    <div class="box box-primary">
        @include('layouts.admin.partials.lte_alerts')
        <div class="box-body">
            <div class="form-box-styled">
                {!! Form::open(['route'=>'admin::merchants::investor_transactions','method'=>'POST','id'=>'investor_transactions']) !!}
                <div class="row">
                    <div class="col-md-5">
                        <span class="help-block labels">Merchant</span>
                        <div class="input-group rb">
                            {{Form::select('merchant',$allMerchants,$merchant,['class'=>'form-control js-merchant-placeholder-multiple','placeholder'=>'Select merchant','id'=>'merchant'])}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <span class="help-block labels">Company</span>
                        {{Form::select('company',$companies,$company,['class'=>'form-control js-company-placeholder','placeholder'=>'Please select company','id'=>'companies'])}}
                    </div>
                    <div class='col-md-3'>
                        <span class="help-block"> Date <font color="#FF0000"> * </font></span>
                        <div class="input-group rb">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            {!! Form::text('date_transaction1',$transaction_date,['id'=>'datepicker1', 'class'=>'form-control
                            datepicker','placeholder'=>'Select Date','autocomplete'=>'off']) !!}
                            <input type="hidden" name="date_transaction" id="datepicker" class="date_parse">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class='col-md-3'>
                        <span class="help-block">Transaction Category <font color="#FF0000"> * </font></span>
                        <div class="input-group rb">
                            {!! Form::select('transaction_category',$transaction_categories,$transaction_category,['class'=>'form-control','id'=> 'transaction_category']) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <span class="help-block">Transaction Type <font color="#FF0000"> * </font></span>
                        <?PHP $transaction_types=[1=>'Debit',2=>'Credit']; ?>
                        <div class="input-group rb">
                            {{Form::hidden('tran_type',isset($transaction_type) ? $transaction_type : '',['class'=>'form-control','id'=>'tran_type'] )}}
                            {{Form::select('transaction_type',$transaction_types,isset($transaction_type) ? $transaction_type : old('transaction_type') ,['class'=>'form-control','id'=>'transaction_type','disabled'=>'disable'] )}}
                        </div>
                    </div>
                    <div class='col-md-2'>
                        <span class="help-block">Notes</span>
                        <div class="input-group rb">
                            {!! Form::text('notes',$notes,['class'=>'form-control','id'=> 'notes']) !!}
                        </div>
                    </div>
                    <div class='col-md-2'>
                        <span class="help-block">Transaction Amount <font color="#FF0000"> * </font></span>
                        <div class="input-group rb">
                            {!! Form::text('amount',$amount,['class'=>'form-control','id'=> 'amount','required'=>true ]) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php 
                        ($split==1) ? $checked1 = 'true':$checked1 = ''; 
                        ($split==2) ? $checked2 = 'true':$checked2 = ''; 
                        ($split==3) ? $checked3 = 'true':$checked3 = '';  
                        ?>
                        {{ Form::radio('split_amount','1',$checked1,['class' => 'split_amount','id' => 'split_amount1']) }}
                        <label for="split_amount1">Split Amount Based Investors Share</label>
                        {{ Form::radio('split_amount','2',$checked2,['class' => 'split_amount', 'id' => 'split_amount2']) }}
                        <label for="split_amount2">Assign Equally</label>
                        {{ Form::radio('split_amount','3',$checked3,['class' => 'split_amount', 'id' => 'split_amount3']) }}
                        <label for="split_amount3">Split Amount Based Investors Count</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 btn-wrap btn-right">
                    <div class="btn-box">
                        <a href="{{route('admin::merchants::investor_transactions')}}" class="btn btn-danger"
                        style="margin-right: 10px;">Reset</a>
                        {!! Form::submit('View Investors',['class'=>'btn btn-primary']) !!}
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    @if(count($investors)>0)
    {!! Form::open(['route'=>'admin::merchants::investor_transactions_store', 'method'=>'POST','id'=>'investor_transactions_store']) !!}
    {!! Form::hidden("merchant_id",$merchant,['class'=>'form-control']) !!}
    {!! Form::hidden("date",$transaction_date,['class'=>'form-control']) !!}
    {!! Form::hidden("transaction_category",$transaction_category,['class'=>'form-control']) !!}
    {!! Form::hidden("transaction_type",$transaction_type,['class'=>'form-control']) !!}
    {!! Form::hidden("notes",$notes,['class'=>'form-control']) !!}
    <?php  $total=0; ?>
    <div class="box-body">
        <table class="table dataTable">
            <thead>
                <tr>
                    <th> <div class="form-group"> <label for="name">Investor <i class="fa fa-user-o" aria-hidden="true"></i> </label> </div> </th>
                    <th> <div class="form-group"> <label for="rate">Date<i class="fa fa-re" aria-hidden="true"></i> </label> </div> </th>
                    @if($split == 1)
                    <th> <div class="form-group"> <label for="rate">Share(%)<i class="fa fa-re" aria-hidden="true"></i> </label> </div> </th>
                    @endif
                    <th> <div class="form-group"> <label for="rate">Transaction Type<i class="fa fa-re" aria-hidden="true"></i> </label> </div> </th>
                    <th> <div class="form-group"> <label for="rate">Notes<i class="fa fa-re" aria-hidden="true"></i> </label> </div> </th>
                    <th> <div class="form-group"> <label for="rate">Amount($)<i class="fa fa-re" aria-hidden="true"></i> </label> </div> </th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 0; ?>
                @foreach($investors as $investor)
                <?php 
                if($split == 1){
                    $investorShare = ($investor->amount*100)/$total_funded;
                    $investorShare = round($investorShare,2);
                    if($company_merchant_flag == 1){
                        $investorShare =  round(($investorShare/$company_share)*100,2);
                    }
                    $splitted_amount = ($amount*$investorShare)/100;
                } 
                if($split == 2){
                    $splitted_amount = $amount;
                }
                if($split == 3){
                    $splitted_amount = round($amount/count($investors),2);
                }
                $splitted_amount = round($splitted_amount,2);
                $total = $total + $splitted_amount;
                ?>
                <tr>
                    <td>
                        <div class="form-group">
                            <div class="name-inn"> 
                                <a href="{{URL::to('admin/investors/portfolio',$investor->user_id )}}">{{ ($companyflag == 1) ?$investor->name : $investor->Investor->name}} </a>
                                {!! Form::hidden("investor_id[$i]",$investor->user_id,['class'=>'form-control']) !!}
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <div class="name-inn"> {{\FFM::date($transaction_date)}} </div>
                            <span id="error_message_for_payment_date" class="text-danger error_message_for_payment_date"></span>
                        </div>
                    </td>
                    @if($split == 1)
                    <td>
                        <div class="form-group">
                            <div class="name-inn"> {{$investorShare}} </div>
                        </div>
                    </td>
                    @endif
                    <td>
                        <div class="form-group">
                            <div class="name-inn"> @if($transaction_type == 1) Debit @else Credit @endif </div>
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <div class="name-inn">
                                <input type="text" class="form-control" name="notes[]" id='notes' value="{{$notes}}">
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <div class="name-inn">
                                <input type="text" class="form-control rate name-inv total_amount" name="transaction_amount[{{$i}}]"
                                id='transaction_amount[{{$i}}]' value="{{$splitted_amount}}">
                                
                            </div>
                        </div>
                    </td>
                </tr>
                <?php $i++; ?>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    @if($split == 1)
                    <td></td>
                    @endif
                    <td></td>
                    <td><strong> Total(Investors Count : {{count($investors)}}) </strong></td>
                    <td><strong id="grand_total"> ${{round($total,2);}}</strong></td>
                </tr>
            </tfoot>
        </table>
        <div class=" btn-wrap btn-right">
            <div class="btn-box">
                <input type="button" value="Add Transactions" class="btn btn-primary" id="paymentClick">
            </div>
        </div>
    </div>
    {!! Form::close() !!}
</div>
@endif
<div id="success-modal" class="modal">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <span id="paymentbox"></span>
                <b>Do you really want to add the payment again ?</b>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                <a href="javascript:void(0)" class="btn btn-primary" id="submit" data-bs-dismiss="modal">Yes</a>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{asset('js/custom/helper.js')}}"></script>
<script>
    $('#amount').keypress(function(event) {
        if(event.which == 46 && $(this).val().indexOf('.') != -1) {
            event.preventDefault();
        } // prevent if already dot
        if(event.which == 44 && $(this).val().indexOf(',') != -1) {
            event.preventDefault();
        } // prevent if already comma
    });
    $('#amount').keyup(function(event) {
        // skip for arrow keys
        if(event.which >= 37 && event.which <= 40){
            event.preventDefault();
        }
        $(this).val(function(index, value) {
            value = value.replace(/,/g,'');
            return (value);
        });
    });
$(".total_amount").blur(function () {
    total = 0;
    $(".total_amount").each(function () {
        if ($(this).val() != '' || $(this).val() != 0) {
            total = parseFloat(total) + parseFloat($(this).val());
        }
    });
    $('#grand_total').html(parseFloat(total.toFixed(2)));
});
$(".js-merchant-placeholder-multiple").select2({
    placeholder: "Select Merchant",
    allowClear : true
});
$(document).ready(function () {
    TransactionTypeEditEnabledList=["{!! InvestorTransactionHelper::getTransactionTypeEditEnabledList() !!}"];
    var trans_cat = $("#transaction_category").val();
    if(trans_cat == 0){
        $("#transaction_category").val('');
    }
    $('.dataTable').DataTable({
        paging   : false,
        searching: false,
        order    : [ [0, "asc"] ],
        "aoColumns": [
            { "bSortable": true },
            { "bSortable": false },
            { "bSortable": false },
            @if($split == 1) { "bSortable": false }, @endif
            { "bSortable": false },
            { "bSortable": false },
        ]
    });
    $(".total_amount").each(function () {
        $(this).validate();
    });
    $('#investor_transactions_store').validate();
    $('#transaction_category').change(function () {
        var category = $('#transaction_category').val();
        if($.inArray(category, TransactionTypeEditEnabledList) !== -1){
            $('#transaction_type').attr('disabled',false);
        } else {
            if (category == 1 || category == 13 || category == 18) {
                $('#transaction_type').val(2).change();
                $('#tran_type').val(2).change();
            } else {
                $('#transaction_type').val(1).change();
                $('#tran_type').val(1).change();
            }
            $('#transaction_type').attr('disabled',true);
        }
    });
    $('#transaction_type').change(function(){
        var transaction_type=$(this).val();
        $('#tran_type').val(transaction_type);
    });
    $('#paymentClick').click(function () {
        $(".total_amount").blur();
        var amt_length = $("input[name='transaction_amount[]']").length;
        if (amt_length == 1) {
            amount = $("#_amount1").val();
            if (amount == 0 || amount == '') {
                alert('Amount Should Be Greater Than 0');
                return false;
            }
            if ($('#investor_transactions_store').valid()) {
                $("#investor_transactions_store").submit();
            } else {
                return false;
            }
        } else {
            error = 1;
            $("[name^=transaction_amount]").each(function () {
                $(this).rules("add", {
                    number: true,
                    step: 0.01,
                    messages: {
                        number: "Only accept numbers",
                        step: "Only 2 decimal allowed"
                    }
                });
                if ($(this).val() && $(this).val() != 0) {
                    error = 0;
                }
            });
            $('#investor_transactions_store').validate();
            if (error == 0) {
                if ($('#investor_transactions_store').valid()) {
                    $(this).prop('disabled', true);
                    $("#investor_transactions_store").submit();
                }
            } else {
                alert("Please enter atleast 1 transaction amount greater than 0");
                return false;
            }
        }
    });
    $.validator.addMethod('minStrict', function (value, el, param) {
        return value >= param;
    });
    $(".js-company-placeholder").select2({
        placeholder: "Select Company",
        allowClear: true
    });
    $('#investor_transactions').validate({
        errorClass: 'errors_msg1',
        rules: {
            transaction_category: { required: true, },
            date_transaction1   : { required: true, },
            split_amount        : { required: true },
            amount: {
                required : true,
                number   : true,
                minStrict: 0.01,
                step     : 0.01
            },
        },
        messages: {
            transaction_category: {
                required: "Please select transaction category",
            },
            date_transaction1: {
                required: "Enter Date",
                date    : 'Please enter valid date'
            },
            amount: {
                required : "Please enter amount",
                number   : "Only accept numbers",
                minStrict: "Enter amount greater than 0",
                step     : "Only 2 decimal allowed"
            },
            split_amount: {
                required: "Please select split type",
            },
        },
    });
    if($('#transaction_category').val()) $('#transaction_category').change();
});
</script>
@stop
@section('styles')
<style type="text/css">
.select2-selection__rendered {
    display: inline !important;
}
.select2-search--inline {
    float: none !important;
}
.form-control.multi-datepicker[readonly] {
    background-color: inherit;
}
.row {
    padding-top: 12px !important;
}
</style>
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/genarate_interest.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel="stylesheet" />
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop