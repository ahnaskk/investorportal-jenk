<?php use App\Bank; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
    </a>
</div>
@if($action == 'create')
{{ Breadcrumbs::render('create_investor_bank',$investor) }}
@else
{{ Breadcrumbs::render('edit_investor_bank',$investor) }}
@endif
<div class="col-md-12">
    <div class="box box-primary box-sm-wrap">
        @php  $validation=isset($bank_details->id)?'':'<span class="validate_star">*</span>'; @endphp
        @php
        $bid=isset($id)?$id:'';
        if(!isset($investor_id)) $investor_id=isset($bank_details)? $bank_details->investor_id:0;
        @endphp
        {!! Form::open(['route'=>'admin::investors::updateBank', 'method'=>'POST','id'=>'bank_details_form']) !!}
        <div class="box-body box-body-sm ">
            @include('layouts.admin.partials.lte_alerts')
            <input type="hidden" name="investor_id" value="{{$investor_id}}">
            <input type="hidden" name="bid" value="{{ $bid }}">
            <div class="form-group">
                <label for="accountHolderName">The Account Holders Name <span class="validate_star">*</span></label>
                {!! Form::text('account_holder_name',isset($bank_details)? $bank_details->account_holder_name : old('account_holder_name'),['class'=>'form-control','placeholder'=>'Account holder name', 'id'=>'accountHolderName']) !!}
            </div>
            <div class="form-group accnt_number">
                @php $place_holder=isset($bank_details)?$masked_accountNo:'Enter Account Number' @endphp
                <label for="accountNumber">Account Number {!! $validation !!}</label>          
                <input placeholder="@php echo $place_holder @endphp" id="accountNumber" class="form-control ac_no" type="password" name="acc_number">
                <input type="checkbox" id="check" class="hidden" onchange="myFunction()"/>
                <label class="icon eye-wrapper" for="check">
                    <i class="fa fa-eye open"></i> 
                    <i class="fa fa-eye-slash close"></i> 
                </label>
            </div>
            <div class="form-group">
                <label for="routingNumber">
                    Routing <span class="validate_star">*</span>
                    @if(config('app.env')=="development")
                    <a href="#" class="help-link">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>
                        <div class="tool-tip">Sample : 322271724</div>
                    </a>
                    @endif
                </label>
                {!! Form::text('routing',isset($bank_details)? $bank_details->routing: old('routing'),['class'=>'form-control','placeholder'=>'Enter Bank Routing Number', 'id'=>'routingNumber']) !!}
            </div>
            <div class="form-group">
                <label for="bankName">Bank Name <span class="validate_star">*</span></label>
                {!! Form::text('name',isset($bank_details)? $bank_details->name : old('name'),['class'=>'form-control','placeholder'=>'Bank name will be fetched from routing number', 'id'=>'bankName', 'readonly']) !!}
            </div>
            <div class="form-group">
                <label for="exampleInputEmail1">Bank Address <span class="validate_star">*</span></label>
                {!! Form::textarea('bank_address',isset($bank_details)? $bank_details->bank_address: old('bank_address'),['class'=>'form-control','placeholder'=>'Enter Bank Address']) !!}
            </div>
            <div class="form-group">
                <div class="input-group check-box-wrap"> 
                    <label>Bank Type <span class="validate_star">*</span></label>
                    
                        <?php
                        $CREDITCHECK=false; $DEBITCHECK=false;
                        if(isset($bank_details)){
                            $type=explode(',',$bank_details->type);
                            if(in_array(Bank::CREDIT,$type)) $CREDITCHECK=true;
                            if(in_array(Bank::DEBIT,$type)) $DEBITCHECK=true;
                        }
                        ?>
                        <div class="row">
                            
                                <div class="col-md-6">
                                    <div class="input-group-text nested">
                                        <div class="main">
                                            {{ Form::checkbox('type[]',Bank::DEBIT,$DEBITCHECK,['id'=>'debit','class'=>'bank_type checkType']) }}
                                            {{ Form::label('debit', ucfirst('Debit'),['for'=>'debit']) }}
                                        </div>
                                        <div class="sub">
                                            {{ Form::checkbox('default_debit',1,isset($bank_details)? $bank_details->default_debit: old('default_debit'),['id'=>'default_debit','class'=>'checkType']) }}
                                            {{ Form::label('default_debit', ucfirst('Set As Default Debit')) }} &emsp;&emsp;&emsp;&emsp;
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group-text nested">
                                        <div class="main">
                                            {{ Form::checkbox('type[]',Bank::CREDIT,$CREDITCHECK,['id'=>'credit','class'=>'bank_type checkType']) }}
                                            {{ Form::label('credit', ucfirst('Credit'),['for'=>'credit']) }}
                                        </div>
                                        <div class="sub">
                                            {{ Form::checkbox('default_credit',1,isset($bank_details)? $bank_details->default_credit: old('default_credit'),['id'=>'default_credit','class'=>'checkType']) }}
                                            {{ Form::label('default_credit', ucfirst('Set As Default Credit')) }}
                                        </div>
                                    </div>
                                </div>
                            
                        </div>
                       
                      
                        
                </div>
            </div>
            <div class="btn-wrap btn-right">
                <div class="btn-box" >
                    <a class="btn btn-success" href="{{URL::to('admin/investors/bank_details/'.$investor_id)}}">Back to list</a>
                    @if($action=='create')
                    {!! Form::submit('Create',['class'=>'btn btn-primary','id'=>'submitButton']) !!}
                    @else
                    {!! Form::submit('Update',['class'=>'btn btn-primary','id'=>'submitButton']) !!}
                    @endif
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
@section('scripts')
<script>
var action=@json($action);
$(document).ready(function () {
    // initialize the plugin
    $("input[type='checkbox'][name='type[]']").change(function() {
        if ($("input[type='checkbox'][name='type[]']:checked").length){
            $(this).valid()
        }
    });
    $("input.ac_no").keypress(function(event) {
        return /\d/.test(String.fromCharCode(event.keyCode));
    });
    $('input.ac_no').on('paste', function (event) {
      if (event.originalEvent.clipboardData.getData('Text').match(/[^\d]/)) {
      event.preventDefault();
      }
    });
   $("input.ac_no").on("keypress",function(event){
        if(event.which < 48 || event.which >58){
            return false;
        }
    });
    if(action == 'create'){
    $('#bank_details_form').validate({
        errorClass: 'errors',
        rules: {
            account_holder_name: { required: true, maxlength: 255, },
            name:                { required: true, maxlength: 255, },
            acc_number:          { required: true, maxlength: 255, minlength: 4,},
            routing:             { required: true, maxlength: 255, ABARoutingNumberFormat: true },
            bank_address: { required:true },
            'type[]':            { required: true },
        },
        messages: {
            account_holder_name: "Enter A/C Holder Name",
            name:             { required :"Enter Bank Name", },
            acc_number:       { required :"Enter A/C No", minlength:"Account number must contain atleast 4 digit" },
            'type[]':         { required :"Please Select Any Type", },
            bank_address: {required:"Enter bank address"}
        },
    });
    }
    else{
    $('#bank_details_form').validate({
        errorClass: 'errors',
        rules: {
            account_holder_name: { required: true, maxlength: 255, },
            acc_number:          { minlength: 4, },
            name:                { required: true, maxlength: 255, },
            routing:             { required: true, maxlength: 255, ABARoutingNumberFormat: true },
            bank_address: { required:true },
            'type[]':            { required: true },
        },
        messages: {
            account_holder_name: "Enter A/C Holder Name",
            name:             { required :"Enter Bank Name", },
            'type[]':         { required :"Please Select Any Type", },
            bank_address: {required:"Enter bank address"},
            acc_number:             { minlength:"Account number must contain atleast 4 digit", },
        },
    });
    }
    jQuery.validator.addMethod("ABARoutingNumberFormat", function(value, element) {
        //all 0's is technically a valid routing number, but it's inactive
        if (!value) { return false; }
        var routing = value.toString();
        while (routing.length < 9) {
            routing = '0' + routing; //I refuse to import left-pad for this
        }
        //gotta be 9  digits
        var match = routing.match("^\\d{9}$");
        if (!match) { return false; }
        //The first two digits of the nine digit RTN must be in the ranges 00 through 12, 21 through 32, 61 through 72, or 80.
        //https://en.wikipedia.org/wiki/Routing_transit_number
        const firstTwo = parseInt(routing.substring(0, 2));
        const firstTwoValid =  (0 <= firstTwo && firstTwo <= 12)
        || (21 <= firstTwo && firstTwo <= 32)
        || (61 <= firstTwo && firstTwo <= 72)
        || firstTwo === 80;
        if (!firstTwoValid) { return false; }
        //this is the checksum
        //http://www.siccolo.com/Articles/SQLScripts/how-to-create-sql-to-calculate-routing-check-digit.html
        const weights = [3, 7 ,1];
        var sum = 0;
        for (var i=0 ; i<8; i++) {
            sum += parseInt(routing[i]) * weights[i % 3];
        }
        return (10 - (sum % 10)) % 10 === parseInt(routing[8]);
    }, "Please Enter valid Routing Number");
});
$(document).on('submit', 'form', function() {
    $(this).find('button:submit, input:submit').attr('disabled', 'disabled');
});
URL_checkRoutingNumber = 'https://www.routingnumbers.info/api/data.json?rn='
$('#routingNumber').on('change', function() {
    routingNumber =  $(this).val()
    $.ajax({
        type:'GET',
        url:URL_checkRoutingNumber+routingNumber,
        success:function(data)
        {
            if (data.code == 200) {
                $('#bankName').val(data.customer_name)
                $('#bankName').valid()
            } else {
                $('#routingNumber').val('')
                $('#bankName').val('')
                $('#routingNumber').valid()
                $('#routingNumber-error').text('Invalid Routing Number')
            }
        }
    })
})
</script>
<script type="text/javascript">
$(document).ready(function(){
    disable_default_bank_type();
    $(".bank_type").click(function(){
        disable_default_bank_type();
    });
    function disable_default_bank_type() {
        $('#default_debit').attr('disabled',true);
        $('#default_credit').attr('disabled',true);
        $(".bank_type:checked").each(function(){
            if($(this).val()=='debit'){
                $('#default_debit').attr('disabled',false);
            }
            if($(this).val()=='credit'){
                $('#default_credit').attr('disabled',false);
            }
        });
    }
    // if ($('.bank_type').is(":checked")){
    // 
    // } 
});
</script>
<script>
function myFunction() {
  var x = document.getElementById("accountNumber");
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}
</script>
<link href="{{ asset('/css/optimized/bank_details.css?ver=5') }}" rel="stylesheet" type="text/css" />
 <link rel="stylesheet" href="{{ asset('/css/font-awesome.min.css') }}">
 <style type="text/css">
     .form-control.ac_no{
        position: relative;
     }
     .form-group.accnt_number i.close{
        opacity: 1;
     }
     .icon.eye-wrapper{
        position: absolute;
        display: flex;
        width: auto;
        top: 55%;
        padding: 0!important;
        right: 17px;
     }
      .form-group.accnt_number #check i {
        padding: 0
      }

     .form-group.accnt_number #check~.eye-wrapper i.close,.form-group.accnt_number #check:checked~.eye-wrapper i.open{
        display: none;
     }
     .form-group.accnt_number #check:checked~.eye-wrapper i.close,.form-group.accnt_number #check~.eye-wrapper i.open{
        display: block;
     }

 </style>
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css') }}" rel="stylesheet" type="text/css" />
@stop
