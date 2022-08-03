<?php use App\InvestorTransaction; ?>
<?php use App\Library\Helpers\InvestorTransaction as InvestorTransactionHelper; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<input type="hidden" name="liquidity" id="liquidity" value="{{$liquidity}}">
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ ($page_title)?$page_title:'' }}</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">
            @if($action=="create") Create Transactions @else Edit Transactions @endif 
        </div>
    </a>
</div>
@if($action=="create")
{{ Breadcrumbs::render('create_transaction',$Investor) }}
@else
{{ Breadcrumbs::render('edit_transaction',$Investor) }}
@endif
<div class="col-md-12">
    <div class="box box-primary">
        @include('layouts.admin.partials.lte_alerts')
        @if($action=="create")
        {!! Form::open(['route'=>['admin::investors::transaction::store','id'=>$investorId ,  'method'=>'POST'],'id'=>'transaction_form','name'=>'transaction_form']) !!}
        @else
        {!! Form::open(['route'=>['admin::investors::transaction::update','id'=>$investorId ,'tid'=>$transaction->id,  'method'=>'POST'],'id'=>'transaction_form']) !!}
        @endif
        <div class="box-body" id="valid-req">
            <div class="row">
                <div class="alert alert-warning alert-dismissible fade show warning-msg" id ="liquidity_warning" style="display:none;">
                    <strong>Warning!</strong> Your liquidity has become negative.
                    <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
                </div>
                <input type="hidden" name="investor_id" value="{{$investorId}}">
                @if($action=="create")
                <input type="hidden" name="investor_type" id="investor_type" value="{{$investor_type}}">
                @endif
                <div class="col-md-4 invest-trans">
                    <label for="exampleInputEmail1">Amount <span class="validate_star">*</span></label>
                    {!! Form::text('amount',isset($transaction->amount) ? abs($transaction->amount) : old('amount'),['class'=>'form-control accept_digit_only','required','id'=> 'inputAmount','data-parsley-required-message' => 'Amount is required','placeholder'=>'Enter the amount']) !!}
                    <span id="invalid-inputAmount" />
                </div>
                <div class="col-md-4 invest-trans">
                    <label for="exampleInputEmail1">Investment Date <span class="validate_star">*</span></label>
                    {!! Form::text('date1',isset($transaction) ? $transaction->date : old('date', Carbon\Carbon::today()->format('Y-m-d')),['class'=>'form-control datepicker','id'=>'investment_date1', 'autocomplete' => 'off', 'placeholder' => \FFM::defaultDateFormat("format")]) !!}
                    <input type="hidden" name="date" class="date_parse" id="investment_date" value="{{ isset($transaction) ? $transaction->date : old('date', Carbon\Carbon::today()->format('Y-m-d')) }}">
                    <span id="invalid-investment_date1" />
                </div>
                <div class="col-md-4 invest-trans tras-cate">
                    <label for="exampleInputEmail1" style="display:block;">Transaction Category <span class="validate_star">*</span></label>
                    <select id="transaction_category" name="transaction_category" class="form-control">
                        @foreach($transaction_categories as $key => $transaction_category)
                        <option {{ isset($transaction) ? ($transaction->transaction_category==$key?'selected':'' ) : ''}} value="{{$key==0?'':$key}}">{{$transaction_category}}</option>
                        @endforeach
                    </select>
                    <span id="invalid-transaction_category" class="clearfix" />
                </div>
                <?php $userId=Auth::user()->id;?>
                {!! Form::hidden('creator_id',$userId) !!}
                @if($action=="create")
                <div class="col-md-4 invest-trans">
                    <label for="exampleInputEmail1">Investor <span class="validate_star">*</span></label>
                    <select id="investor_id" name="investor_id" class="form-control">
                        @foreach($investors as $investor)
                        <option  {{ !empty(old('investor_id'))?old('investor_id')==$investor->id?'selected':'':($investorId==$investor->id?'selected':'') }} value="{{$investor->id}}">{{$investor->name}}
                        </option>
                        @endforeach
                    </select>
                    <span id="invalid-investor_id" />
                </div>
                @else
                <div class="col-md-4 invest-trans">
                    <label for="exampleInputEmail1">Investor <span class="validate_star">*</span></label>
                    <input type="text" class="form-control" disabled value="{{$transaction->investor->name}}">
                </div>
                @endif
                <div class="col-md-4 invest-trans">
                    <label for="exampleInputEmail1">Transaction Type <span class="validate_star">*</span></label>
                    <?PHP
                    $transaction_types=[1=>'Debit',2=>'Credit'];
                    ?>
                    {{Form::hidden('tran_type','',['class'=>'form-control','id'=>'tran_type'] )}}
                    {{Form::select('transaction_type',$transaction_types,isset($transaction) ?  $transaction->transaction_type :old('transaction_type') ,['class'=>'form-control','placeholder'=>'Select Transaction Type','id'=>'transaction_type','disabled'=>'disable'] )}}
                    <span id="invalid-transaction_type" />
                </div>
                {{-- <div class="col-md-4 invest-trans">
                    <label for="exampleInputEmail1">Transaction Method <span class="validate_star">*</span></label>
                    {!! Form::select('transaction_method',[''=>'Please Select']+InvestorTransaction::transactionMethodOptions(),isset($transaction) ? $transaction->transaction_method : old('transaction_method'),['class'=>'form-control','id'=>'transaction_method','required']) !!}
                    <span id="invalid-transaction_method" />
                </div> --}}
                <div class="col-md-4 invest-trans">
                    <label for="exampleInputEmail1">Notes</label>
                    {!! Form::text('category_notes',isset($transaction) ? $transaction->category_notes : old('category_notes'),['class'=>'form-control','id'=>'category_notes']) !!}
                    <span id="invalid-notes" />
                </div>
                <!-- <div class="col-md-4 invest-trans" style="display: none;" id="maturity_date_dev"> -->
                <!-- <label for="exampleInputEmail1">Maturity Date</label> -->
                <!-- {!! Form::text('maturity_date1',isset($transaction) ? $transaction->maturity_date : old('maturity_date'),['class'=>'form-control datepicker','id'=>'maturity_date1','autocomplete'=>'off', 'placeholder' => \FFM::defaultDateFormat("format")]) !!} -->
                <!-- <input type="hidden" class="date_parse" name="maturity_date" id="maturity_date" value="{{ isset($transaction) ? $transaction->maturity_date : old('maturity_date') }}"> -->
                <!-- <span id="invalid-maturity_date1" /> -->
                <!-- </div> -->
                <div class="col-md-12">
                    <div class="btn-wrap btn-right">
                        <div class="btn-box">
                            <a href="{{URL::to('admin/investors/transactions',$investorId)}}" class="btn btn-success">View Lists</a>
                            @if($action=="create")
                            {!! Form::submit('Create',['class'=>'btn btn-primary','id'=>'tran_create']) !!}
                            @else
                            {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    @stop
    @section('scripts')
    <script type="text/javascript"> 
    $('#tran_create').on('click',function(e) { 
        e.preventDefault;
        let text = "Are you sure?";
        if (confirm(text) == true) {
            $('form#transaction_form').submit();
            
        }else{
            return false;
        }
    });
    
    $("#transaction_method").change(function(){
        $('#transaction_method-error').hide();
    });
</script>
<script type="text/javascript">
$('#inputAmount').keypress(function(event) {
    if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
    ((event.which < 48 || event.which > 57) &&
    (event.which != 0 && event.which != 8))) {
        event.preventDefault();
    }
    var text = $(this).val();
    if ((text.indexOf('.') != -1) &&
    (text.substring(text.indexOf('.')).length > 2) &&
    (event.which != 0 && event.which != 8) &&
    ($(this)[0].selectionStart >= text.length - 2)) {
        event.preventDefault();
    }
});
</script>
<script>
$(document).ready(function () {
    TransactionTypeEditEnabledList=["{!! InvestorTransactionHelper::getTransactionTypeEditEnabledList() !!}"];
    var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
    var category = $('#transaction_category').val();
    $.validator.addMethod('minStrict', function (value, el, param) {
        return value > param;
    });
    
    $.validator.addMethod("dateRange", function(value, element, params) {
        var date = moment(value, default_date_format).format('YYYY-MM-DD');
        var from = moment(params.from, 'YYYY-MM-DD').format('YYYY-MM-DD');
        var to = moment(params.to, 'YYYY-MM-DD').format('YYYY-MM-DD');
        if (date >= from && date <= to) {
            return true;
        }
        return false;
    }, 'Enter Date between 01/01/2016 to 12/31/2026');
    $('#transaction_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            amount: {
                minStrict: 0,
                max: 99999999999,
                required: {
                    depends:function(){
                        $(this).val($.trim($(this).val()));
                        return true;
                    }
                },
            },
            // maturity_date :{ required: function(element) {
            //                   if($('#investor_type').val()==1 && $('#tran_type').val()==2)
            //                       return true;
            //                   else
            //                   return false;
            //             }
            //         },
            // maturity_date :{ required: function(element) {
            //                    if($('#transaction_category').val()==1)
            //                        return true;
            //                    else
            //                    return false;
            //              }
            //          },
            
            transaction_method:
            {
                required: true,
                
            },
            
            transaction_category: {
                required: true,
            },
            date1:{
                dateRange: {
                    from: '2016-01-01',
                    to: '2026-12-31'
                }
            }
        },
        messages: {
            amount: {
                required : "Enter Amount",
                minStrict:"Please enter a number greater than zero",
                max:"Please enter a number less than or equal to 99999999999",
            },
            transaction_category: { required :"Select Category"},
            transaction_method: { required :"Select Transaction method"},
            
            maturity_date1 : {required : "Enter Maturity Date"},
        },
        
        errorPlacement: function(error, element) {
            error.appendTo('#invalid-' + element.attr('id'));
        }
    });
    $('#transaction_category').change(function() {
        if($('#transaction_category').val()) {
            $('#transaction_category-error').hide();
        }
        if($('#transaction_category').val()==1) {
            $('#maturity_date_dev').css('display','block');
        } else {
            $('#maturity_date_dev').css('display','none');
        }
    });
    
    var trans_type = $('#transaction_type').val();
    
    var select = document.getElementById("transaction_method");
    
    // pass $debit_transaction_method_options from InvestorTransactionRepository. 
    var credit_transaction_method_options = "{!!InvestorTransaction::transactionMethodOptionsCredit()!!}"
    
    // pass $debit_transaction_method_options from InvestorTransactionRepository. 
    var debit_transaction_method_options = "{!!InvestorTransaction::transactionMethodOptionsDebit()!!}"
    
    $('#transaction_category').change(function() {
        var category = $(this).val();
        if(category==1 || category==13 || category==18){
            $('#transaction_type').val(2).change();
            var trans_method=$('#transaction_method').html(credit_transaction_method_options);
            document.getElementById('liquidity_warning').style.display="none";
        } else {
            var amount = $('#inputAmount').val();
            var liquidity = $('#liquidity').val();
            if(parseFloat(amount) > parseFloat(liquidity) && ($('#transaction_type').val()==1) && category!=''){
                document.getElementById('liquidity_warning').style.display="block";
                document.getElementById('liquidity_warning').innerHTML = "<strong>Warning!</strong> Your liquidity may become -ve. You have only $"+liquidity+' as liquidity. <button type="button" class="close" data-bs-dismiss="alert">&times;</button>';
            } else {
                document.getElementById('liquidity_warning').style.display="none";
            }
            if($.inArray(category, TransactionTypeEditEnabledList) == -1){
                $('#transaction_type').val(1).change();
            }
            var trans_method=$('#transaction_method').html(debit_transaction_method_options);
        }
    });
    $('#inputAmount').change(function(event) {
        category = $('#transaction_category').val();
        var amount = $('#inputAmount').val();
        var liquidity = $('#liquidity').val();
        if((parseFloat(amount) > parseFloat(liquidity)) && category!=''){
            if(category==1 || category==13 || category==18){
                document.getElementById('liquidity_warning').style.display="none";
            }else{
                document.getElementById('liquidity_warning').style.display="block";
                document.getElementById('liquidity_warning').innerHTML = "<strong>Warning!</strong> Your liquidity may become -ve.You have only $"+liquidity+' as liquidity <button type="button" class="close" data-bs-dismiss="alert">&times;</button>';
            }
        }else{
            document.getElementById('liquidity_warning').style.display="none";
        }
    });
    $('#inputAmount').keypress(function(event) {
        if(event.which == 46 && $.trim($(this).val()).indexOf('.') != -1) {
            event.preventDefault();
        } // prevent if already dot
        if(event.which == 44 && $(this).val().indexOf(',') != -1) {
            event.preventDefault();
        } // prevent if already comma
    });
    $('#inputAmount').keyup(function(event) {
        // skip for arrow keys
        if(event.which >= 37 && event.which <= 40){
            event.preventDefault();
        }
        $(this).val(function(index, value) {
            value = value.replace(/,/g,'');
            return value;
            // return numberWithCommas(value);
        });
    });
    function numberWithCommas(x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }
    $(".accept_digit_only").keypress(function (evt) {
        var theEvent = evt || window.event;
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
        if (key.length == 0) return;
        var regex = /^[0-9.,\b]+$/;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }
    });
    $('#transaction_category').change(function(){
        var transaction_category=$(this).val();
        if($.inArray(transaction_category, TransactionTypeEditEnabledList) !== -1){
            $('#transaction_type').attr('disabled',false);
        } else {
            $('#transaction_type').attr('disabled',true);
        }
    });
    $('#transaction_category').change();
    $('#transaction_type').change(function(){
        var transaction_type=$(this).val();
        $('#tran_type').val(transaction_type);
        var amount = $('#inputAmount').val();
        var liquidity = $('#liquidity').val();
        if(transaction_type==1){
        if(parseFloat(amount) > parseFloat(liquidity) && ($('#transaction_type').val()==1) && $('#transaction_category').val()!=''){
            document.getElementById('liquidity_warning').style.display="block";
            document.getElementById('liquidity_warning').innerHTML = "<strong>Warning!</strong> Your liquidity may become -ve. You have only $"+liquidity+' as liquidity. <button type="button" class="close" data-bs-dismiss="alert">&times;</button>';
        } else {
            document.getElementById('liquidity_warning').style.display="none";
        }
       }else{
            document.getElementById('liquidity_warning').style.display="none";  
       }
    });
    $('#transaction_type').change();
});
</script>
@stop
@section('styles')
<style>
.warning-msg {
    color: #9F6000;
    background-color: #FEEFB3;
    margin: 10px 0;
    padding: 10px;
    border-radius: 3px 3px 3px 3px;
}
</style>
<link href="{{ asset('/css/optimized/transactions_create.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
