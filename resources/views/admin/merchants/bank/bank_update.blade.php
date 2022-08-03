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
  @if( $action == 'create')
{{ Breadcrumbs::render('merchantCreateBankAccounts',$merchant) }}
@else
{{ Breadcrumbs::render('merchantEditBankAccounts',$merchant) }}
@endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">

            
            <!-- form start -->
                   
        @php  $validation=isset($bank_details->id)?'':'<span class="validate_star">*</span>'; @endphp
        {!! Form::open(['route'=> ['admin::merchants::bank.update','merchant_id' => $merchant_id], 'method'=>'POST','id'=>'bank_details_form']) !!}
      


                <div class="box-body box-body-sm ">
                    @include('layouts.admin.partials.lte_alerts')

                   


                    <input type="hidden" name="merchant_id" value="{{$merchant_id}}">
                    
                  
                    @isset($id)
                <input type="hidden" name="id" value="{{ $id }}">
                    @endisset
                
                   

                    <div class="form-group">
                        <label for="accountHolderName">Account Holders Name <span class="validate_star">*</span></label>
    {!! Form::text('account_holder_name',isset($bank_details)? $bank_details->account_holder_name : old('account_holder_name'),['class'=>'form-control','placeholder'=>'Account holder name', 'id'=>'accountHolderName']) !!}
                    </div>

                    <div class="form-group">
                        <label for="routingNumber">Routing <span class="validate_star">*</span></label>
                        {!! Form::text('routing_number',isset($bank_details)? $bank_details->routing_number: old('routing_number'),['class'=>'form-control','placeholder'=>'Enter Bank Routing Number', 'id'=>'routingNumber']) !!}
                    </div>

                    <div class="form-group">
                        <label for="bankName">Bank Name <span class="validate_star">*</span></label>
                        {!! Form::text('bank_name',isset($bank_details)? $bank_details->bank_name : old('bank_name'),['class'=>'form-control','placeholder'=>'Bank name will be fetched from routing number', 'id'=>'bankName', 'readonly']) !!}
                    </div>
            
                     <div class="form-group accnt_number">
                        @php $place_holder=isset($bank_details)?$masked_accountNo:'Enter Account Number' @endphp
                        <label for="accountNumber">Account Number {!! $validation !!}</label>
                        <input placeholder="@php echo $place_holder @endphp" id="accountNumber" class="form-control ac_no" type="password" name="account_number" />
                        <input type="checkbox" id="check" class="hidden" onchange="myFunction()"/>
                        <label class="icon eye-wrapper" for="check">
                            <i class="fa fa-eye open"></i> 
                            <i class="fa fa-eye-slash close"></i> 
                        </label>
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
                            <a class="btn btn-success" href="{{ route('admin::merchants::bank.index', ['merchant_id' => $merchant_id]) }}">Back to list</a>
                          
                            @if($action=='create')

                            {!! Form::submit('Create',['class'=>'btn btn-primary']) !!}

                            @else

                            {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}



                            @endif
                             
                         </div>
                    </div>


                </div>
                <!-- /.box-body -->
            {!! Form::close() !!}
        </div>
        <!-- /.box -->


    </div>


@stop

@section('scripts')

<script>
var action=@json($action);
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
if(action == 'create'){
$(document).ready(function () {
    $('#bank_details_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            account_holder_name: {
                required: true,
                maxlength: 255,
            },
            routing_number: {
                required: true,
                maxlength: 255,
            },
            bank_name: {
                required: true,
                maxlength: 255,
            },
            account_number: {
                required: true,
                maxlength: 255,
                minlength: 4,
                digits:true,
            }, 
            
        },
        messages: {
            account_holder_name: "Enter A/C Holder Name",
            routing_number: "Enter Routing Number",
            bank_name: { required :"Enter Bank Name",                 
                    },
            account_number: { required :"Enter A/C No", 
            minlength:"Account number must contain atleast 4 digit"                
                    }, 
        },
  
    }); 

});
}
else{
$(document).ready(function () {
    $('#bank_details_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            account_holder_name: {
                required: true,
                maxlength: 255,
            },
            routing_number: {
                required: true,
                maxlength: 255,
            },
            bank_name: {
                required: true,
                maxlength: 255,
            },
            account_number: {               
                maxlength: 255,
                minlength: 4,
                digits:true,
            }, 
        },
        messages: {
            account_holder_name: "Enter A/C Holder Name",
            routing_number: "Enter Routing Number",
            bank_name: { required :"Enter Bank Name"},
            account_number: { 
                minlength:"Account number must contain atleast 4 digit"                
            }, 
        },
  
    }); 

});
}
$(document).ready(function(){
    $("#bank_details_form").submit(function(e){
        if($(this).valid()){
            return true
        }
        else{
            e.preventDefault()
            return false
        }
    })
})
$(document).on('submit', 'form', function() {
    $(this).find('button:submit, input:submit').attr('disabled', 'disabled');
});
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
                $('#bankName').valid()
            } else {
                $('#routingNumber').val('')
                $('#bankName').val('')
                $('#routingNumber').valid();
                $('#routingNumber-error').text('Invalid Routing Number');
            }
        }
    })



   
  
})
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
