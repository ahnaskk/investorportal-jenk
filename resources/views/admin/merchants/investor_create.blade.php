@extends('layouts.admin.admin_lte')
@section('content')
<?PHP
$i2=0;
while($i2<=6) 
{
    // $i+=0.25;
    $fee["$i2"]=$i2;
    $i2=$i2+0.25;
}
$merchant_amount=$merchant->funded;
$commission_per=$merchant->commission;
$underwriting_fee_per=$merchant->underwriting_fee;
$merchant_id=$merchant->id;
//$m_syndication_fee_per=$merchant->m_syndication_fee;
//$m_s_prepaid_status=$merchant->m_s_prepaid_status; 
$factor_rate = $merchant->factor_rate;
//$select_val=[5,10,15,20,25,30,35,40,45,50];
for($i=5;$i<=50;$i++) 
{
    $select_val[$i]=$i;
}
$final_val=[];
$percent=($merchant_amount>0)?($max_participant_fund/$merchant_amount*100):0;
// $final_val[$max_participant_fund]=FFM::dollar($max_participant_fund)." - ". FFM::percent($percent);
foreach ($select_val as $key => $value) 
{
    $per_value = $value*$merchant_amount/100;
    if($per_value<$max_participant_fund)
    {
        $final_val[$per_value]=FFM::dollar($per_value)." - ". FFM::percent($value);
    }else
    {
        $final_val[$per_value]=FFM::dollar($per_value)." - ". FFM::percent($value);
    }
}
?>
<input type="hidden" name="merchant_amount" id="merchant_amount" value="{{$merchant_amount}}">
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Merchant Investor</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Merchant Investor</div>     
    </a>
</div>
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
    @include('layouts.admin.partials.lte_alerts')
        <div class="row">
            <div class="col-md-12">
                <!-- form start -->
                {!! Form::open(['route'=>'admin::merchant_investor::create', 'method'=>'POST','id'=>'investorCreateForm']) !!}
                <input type="hidden" name="mer_id" value="{{ $merchant_id }}">
                <!-- <input type="hidden" name=""> -->
                <div class="box-body col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                        
                            <div class="form-group">
                                <label for="exampleInputEmail1">Filter Investor Name By Companies </label>
                                <select id="company" name="company" class="form-control js-placeholder-company">
                                    <option value="0">All Companies </option>
                                    @foreach($allCompanies as $key => $company)
                                    <option value="{{$key}}">{{$company}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    <div class="col-md-4">
                        
                        <div class="form-group">
                            <label for="exampleInputEmail1">Investor Name <font color="#FF0000"> * </font></label>
                            <select id="user_id" name="user_id" class="form-control js-placeholder-user_id">
                                <option value=""></option>
                                @foreach($investors_data as $investor)
                                @php
                                $liquidity = $investor['user_details']["liquidity"] - $investor['user_details']["reserved_liquidity_amount"];
                                @endphp
                                <option 
                                data-liquidity='{{$liquidity}}' 
                                data-management-fee='{{$investor['management_fee']}}' 
                                data-synd-fee='{{$investor['global_syndication']}}' 
                                data-name='{{$investor['name']}}' 
                                {{ old('user_id')==$investor['id']?'selected': ($merchant->user_id==$investor['id']?'selected':'') }}
                                value="{{$investor['id']}}"> {{$investor['name']}} - {{$liquidity}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Merchant Name </label>
                            <select id="merchant_id" name="merchant_id" class="form-control" required="required" disabled>
                                @foreach($merchants as $single_merchant)
                                <option  data-funded-date='{{$single_merchant->date_funded}}' data-funded-amount='{{ $single_merchant->funded }}' {{ (old('merchant_id')==$single_merchant->id)?"selected": (($single_merchant->id==$merchant_id)?"selected":'')}} value="{{$single_merchant->id}}">{{$single_merchant->name}} </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="merchant_id" value="{{$merchant_id}}">
                        </div>
                    </div>
                    
</div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Transaction Type</label>
                                <div class="input-group">
                                    {!! Form::select('transaction_type',['ACH Works'],isset($merchant)? $merchant->transaction_type : old('transaction_type'),['class'=>'form-control','id'=>'transaction_type']) !!}
                                </div>
                            </div>
                        </div>             
                            {{Form::hidden('amount',0,['id'=>'amount'])}}
                            {{ Form::hidden('max_participant_fund',$max_participant_fund ,['id'=>'max_participant_fund']) }}
                            <div class="col-md-4 participant-amnt">
                                <div class="form-group">
                                    <label for="exampleInputEmail1" title="( Maximum available amount : {{ FFM::dollar($max_participant_fund) }})">Participant Amount <font color="#FF0000"> * </font></label>
                                    <div class="input-group">
                                        <div class="col-md max-ma pr">
                                            {!! Form::number("amount_field",old('amount_field'),['class'=>'form-control accept_digit_only','min'=>0,'step'=>"0.01",'id'=>'amount_field','placeholder'=>'Amount','onchange'=>'calculateParticipantPercentage(this.value);','required'=>'required']) !!}                    
                                        </div>
                                        @if($merchant->funded!=0)
                                        <span class="input-group-text">%</span>
                                        <div class="col-md-4 max-ma pr pr-0"> 
                                            {!! Form::text("amount_per",old('amount_per'),['readonly'=>'readonly' ,'class'=>'form-control','id'=>'amount_per','placeholder'=>'Percentage','onchange'=>'calculateParticipantAmount(this.value);']) !!}
                                            <label id="error_message_for_amount" class="errors" style="color:red;"></label> 
                                        </div>
                                        @endif
                                        
                                    </div>
                                </div>
                            </div> 

                            @if(in_array($merchant->label,$labels))

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Force investment and update maximum funded amount.</label>
                                    <div class="input-group check-box-wrap">
                                        <div class="input-group-text">
                                            <label class="chc">
                                                {{Form::checkbox('force_investment',1,0,['id'=>'force_investment'])}}
                                                <span class="checkmark chek-m"></span>
                                                <span class="chc-value">Check This</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @endif

                            @if($merchant->funded!=0)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Net Value</label>
                                    <div class="input-group check-box-wrap">
                                        <div class="input-group-text">
                                            <label class="chc">
                                            {{Form::checkbox('gross_value',1,1,['id'=>'gross_value'])}}
                                                <span class="checkmark chek-m"></span>
                                                <span class="chc-value">Check This</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                  
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1"> Date Funded </label>
                            {!! Form::text('payment_date1',isset($merchant)? $merchant->date_funded : old('date_funded'),['class'=>'form-control datepicker','id'=>'date_funded1','required'=>'required','placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete' => 'off']) !!}
                            <input type="hidden" name="payment_date" value="{{ isset($merchant)? $merchant->date_funded : old('date_funded') }}" class="date_parse" id="date_funded">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group synd-march">
                            <label for="exampleInputEmail1">Syndication Fee </label>
                            <div class="">
                                <div class="input-group">
                                    {!! Form::select('syndication_fee',$syndication_fee_values,isset($merchant)? number_format($merchant->m_syndication_fee,2) : old('syndication_fee'),['class'=>'form-control','id'=>'syndication_fee']) !!}
                                    <div class="mrch">
                                        <span class="input-group-text" >%</span>
                                        <span class="input-group-text">
                                            <label>
                                                <input {{old('s_prepaid_status')==2?'checked':(isset($merchant)?($merchant->m_s_prepaid_status==2?'checked':''):'')}}
                                                value="2" type="radio" name="s_prepaid_status" id="s_prepaid_amount" class="m_prepaid"> On Funding Amount?
                                            </label>
                                        </span>
                                        <span class="input-group-text">
                                            <label>
                                                <input {{old('s_prepaid_status')==1?'checked':(isset($merchant)?($merchant->m_s_prepaid_status==1?'checked':''):'')}}
                                                value="1" type="radio" name="s_prepaid_status" id="s_prepaid_rtr" class="m_prepaid"> On RTR?
                                            </label>
                                        </span>
                                    </div> 
                                </div>
                            </div>
                        </div> 
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Management Fee</label>
                            <div class="input-group">
                                {!! Form::select('mgmnt_fee',$syndication_fee_values,isset($merchant)? number_format($merchant->m_mgmnt_fee,2) : old('mgmnt_fee'),['class'=>'form-control','id'=>'mgmnt_fee']) !!}
                                <span class="input-group-text">% </span>
                            </div>
                        </div>
                </div>

                <div class="form-group col-md-4">
                    <label for="exampleInputEmail1">Upsell Commission</label>

                    <div class="input-group">
                        {!! Form::select('up_sell_commission_per',$upsell_commission_values,null,['class'=>'form-control' ,'pattern'=>"^-?[0-9]\d*(\.\d+)?$",'id'=>'up_sell_commission_per']) !!}
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                 

                @php
                 $underwriting_status=isset($merchant->underwriting_status)?$merchant->underwriting_status:'';
                 $underwriting_status=json_decode($underwriting_status);

                @endphp

                 <div class="col-md-4">
                <div class="form-group synd-march">
                    <label for="exampleInputEmail1">Underwriting Fee (%)<font color="#FF0000"> * </font></label>

                    <div class="input-group">
                      
                        {!! Form::select('underwriting_fee',$syndication_fee_values,isset($merchant)? number_format($merchant->underwriting_fee,2) : old('underwriting_fee'),['class'=>'form-control' ,'pattern'=>"^-?[0-9]\d*(\.\d+)?$", 'min'=>'0','max'=>'5','id'=>'underwriting_fee']) !!}
                        <span class="input-group-text">%</span>
                     </div>
                                      
                                   </div>
                 </div>
                 
                     <?php $userId=Auth::user()->id;?>
                    {!! Form::hidden('creator_id',$userId) !!}
                   
                    
                    <div class="col-md-12 btn-wrap btn-right">
                        <div class="btn-box">
                            {!! Form::submit('Update',['class'=>'btn btn-primary','id'=>'update_btn']) !!}
                            <a class="btn btn-success" href="{{URL::to('admin/merchants/edit',$merchant->id)}}">Edit Merchant</a>
                            <a class="btn btn-danger" href="{{URL::to('admin/merchants/view',$merchant->id)}}">View Merchant</a>
                        </div>
                    </div>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <!-- /.box -->
    <div class="modal fade" id="confirmPayment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-left:10px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <span id="paymentbox"></span>
                    <b>Do you really want to add payments again ?</b>
                </div>
                <div class="modal-footer">
                    <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                    <a href="javascript:void(0)" class="btn btn-primary" id="submit" data-bs-dismiss="modal">Yes</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmInvestment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-left:10px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <span id="paymentbox"></span>
                    <b>You selected to fund syndicates, Do you want to continue?</b>
                </div>
                <div class="modal-footer">
                    <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                    <a href="javascript:void(0)" class="btn btn-primary" id="submit_confirm_investment" data-bs-dismiss="modal">Yes</a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript">

    $('#up_sell_commission_per').focus(()=>{
        $('#up_sell_commission_per').mask("0.00");
    });

$("#up_sell_commission_per").keypress(function (evt) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    if (key.length == 0) return;
    var regex = /^[0-9.,\b]+$/;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault) theEvent.preventDefault();
    }
    if(evt.which == 46 && $(this).val().indexOf('.') != -1) {
      evt.preventDefault();

   } // prevent if already dot
});

$('#company').change(function(){

    var _this = $(this);
    var company = _this.val();
   
    $.ajax({
        type: "POST",
        url: "{{route("admin::merchant_investor::filtered-investor")}}",
        data:{'_token': _token,'company':company,'creator_id':{{$creator_id}} },
        success: function(data){
            if(data){
                $('#user_id').empty();
                $('#user_id').append('<option value="">Select Investor</option>');
                investor_data = data;
                for(var i = 0; i < investor_data.length; i++){
                    liquidity = (investor_data[i]['user_details'].liquidity - investor_data[i]['user_details'].reserved_liquidity_amount).toFixed(2);
                    $('#user_id').append("<option data-liquidity="+liquidity+" data-management-fee="+investor_data[i].management_fee+" data-synd-fee="+investor_data[i].global_syndication+" data-name="+investor_data[i].name+" value="+investor_data[i].id+">"+investor_data[i].name+" - "+liquidity+"</option>");
                }
            }
        }
    });
});


$('#amount_field').keypress(function (event) {//ONLY DOTS AND NUMBERS,DECIMAL UP TO 2 POINTS
        var txt = this;
        var charCode = (event.which) ? event.which : event.keyCode
        if (charCode == 46) {
            if (txt.value.indexOf(".") < 0)
                return true;
            else
                return false;
        }

        if (txt.value.indexOf(".") > 0) {
            var txtlen = txt.value.length;
            var dotpos = txt.value.indexOf(".");
            //Change the number here to allow more decimal points than 2
            if ((txtlen - dotpos) > 2)
                return false;
        }

        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;

        return true;
    });
$('#update_btn').on('click',function(event)
{
    event.preventDefault();
    $merchant_id='<?php echo $merchant_id ?>';
     $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchant_investor/check-company-share',
            type: 'POST',            
            data: {user_id:$("#user_id").val(),'merchant_id':$merchant_id,'amount':$("#amount_field").val()},
            success: function (data) {
            if(data.success==1){
              $('#investorCreateForm').submit();  
              } else{
                var force_inv = document.getElementById("force_investment"); 
                if(force_inv!=null){
                    if(force_inv.checked==true){
                        $('#investorCreateForm').submit();  
                    }else{
                       if(data.status==0){
                         $("#confirmInvestment").modal('show');
                       }
                       else{
                          $('#investorCreateForm').submit();    
                        } 
                    }
                } else{
                    if(data.status==0){
                         $("#confirmInvestment").modal('show');
                       }
                       else{
                          $('#investorCreateForm').submit();    
                        } 
                }                
                

              } 
               

            }
        })
    //$('#investorCreateForm').submit();
    
});
$('#submit_confirm_investment').on('click',function(event)
{
    $('#investorCreateForm').submit(); 
});
/* 
Calculate the total percentage of all prepaid fees like commission, prepaid, and syndication fee. 
*/
function percentage_prepaids(){
    var percentage2=0;
    var commission_per='{{ $commission_per }}';
    if(commission_per)
    percentage2=parseInt(percentage2)+parseInt(commission_per);
    var underwriting_fee_per='{{ $underwriting_fee_per }}';  
    if(underwriting_fee_per)
    percentage2=parseInt(percentage2)+parseInt(underwriting_fee_per);
    var m_syndication_fee_per=$('#syndication_fee').val();
    var m_s_prepaid_status=$('.m_prepaid').val();
    var factor_rate='{{ $factor_rate }}';
    var prepaid=0;
    if(m_s_prepaid_status==2)
    {
        prepaid_per = m_syndication_fee_per;
    } else if(m_s_prepaid_status==1)
    {
        prepaid_per = m_syndication_fee_per*factor_rate;
    }
    if(prepaid_per)
    percentage2=parseInt(percentage2)+parseInt(prepaid_per);
    return percentage2;
}
/* 
Calculate gross or net invested amount. 
*/
function investment_calculator(amount, reverse=0) { // <-- inner function
    //reverse=1;
    var total=0;
    //if($('#gross_value').is(":checked"))
    {
        percentage_prepaids2 = percentage_prepaids();
        if(reverse)
        {   //1100 *100/110
            total =  ((parseFloat(amount)*100 /(100+parseInt(percentage_prepaids2))));
        }
        else
        {
            total = parseFloat(amount)+ ((parseFloat(amount)*parseInt(percentage_prepaids2)/100));
        }
    }
    var investment_arr = [];
    investment_arr['total'] =total;
    investment_arr['percentage'] =percentage_prepaids2;
    return investment_arr;
}
/* 
Calculate amount based on checkbox.
*/
function calculateParticipantAmount(percentage){
    var merchant_amount = $('#merchant_amount').val();
    var amount = merchant_amount*percentage/100;
    var total = investment_calculator(amount);  // calculate investment amount
    $("#amount_field").val(total.toFixed(2));
    var force_investment= $('#force_investment').is(":checked");
    if( (percentage > 100 && force_investment==false) || (percentage <= 0 && force_investment==false) || isNaN(percentage) || isNaN(merchant_amount)){
        document.getElementById("error_message_for_amount").innerHTML=  'Percentage should be between 0 and 100';
        document.getElementById("update_btn").disabled = true;
    }else{
        document.getElementById("error_message_for_amount").innerHTML=  '';
        document.getElementById("update_btn").disabled = false; 
    }
}
function calculateParticipantPercentage(amount){
    var merchant_amount = $('#merchant_amount').val();
    if(merchant_amount!=0)
    {
        percentage_prepaids2 = percentage_prepaids();
        if(($('#gross_value').is(":checked")))
        {
            var percentage = (amount /  ( parseFloat(merchant_amount)+(merchant_amount*percentage_prepaids2/100) ) )*100;
        }else{
            var percentage = (amount /  ( parseFloat(merchant_amount) ) )*100;
        }
        if(isNaN(percentage)){
            var percentage = 0;
        }
        percentage = percentage.toFixed(2);
        $("#amount_per").val(percentage); 
        var force_investment= $('#force_investment').is(":checked");
        if((percentage > 100 && force_investment==false) || (percentage <= 0 && force_investment==false) || isNaN(percentage) || isNaN(merchant_amount)){
            if(percentage==0){
              document.getElementById("error_message_for_amount").innerHTML=  'Percentage should be a number greater than 0';
            }else{
               document.getElementById("error_message_for_amount").innerHTML=  'Percentage should be between 0 and 100'; 
            }
            
            document.getElementById("update_btn").disabled = true;
        }else{
            document.getElementById("error_message_for_amount").innerHTML=  '';
            document.getElementById("update_btn").disabled = false; 
        }
        if(amount.length==0){
            document.getElementById("error_message_for_amount").innerHTML=  '';
        }
    }
}
//Date picker
$('#datepicker').datepicker({
    autoclose: true,
    format : "yyyy-mm-dd",
    clearBtn: true,
    todayBtn: "linked"
});
// validate payament date for assigned investor
$('#investorCreateForm').submit(function()
{
    //1100 * 100/110 = 1,000
    amount_val = $("#amount_field").val();
    if(($('#gross_value').is(":checked")))
    {
        percentage_prepaids2 = percentage_prepaids();
        amount_val = amount_val *100/(100+percentage_prepaids2)
        $('#amount').val( parseFloat(amount_val).toFixed(2) );
    }else{
        $('#amount').val(parseFloat(amount_val).toFixed(2));
    }
});
$('#investorCreateForm').validate({ // initialize the plugin
    errorClass: 'errors', 
    rules: {
        payment_date1: {
            required:true,
            // dateITA: true,
        },
        amount_field:
        {
            required:true,
        },
        user_id:{
            required:true,
        },
        up_sell_commission_per:
        {
             range:[0,10],
             checkNumeric:true

        },
          'underwriting_status[]':{
                    required: function(element) {
                              if($('#underwriting_fee').val()!=0)
                                  return true;
                              else
                                  return false;
                        },
                },
        merchant_id:{
            required:true,
        },
        s_prepaid_status:
        { required: function(element) {
            if($('#syndication_fee').val()!=0)
            return true;
            else
            return false;
        },
    }
} ,
messages: {
    payment_date1: { required :"Enter Valid Date",
    // dateITA:'Enter Valid Date',                
},
user_id:{ required :" Select Investor " },
amount_field:{ required :"Enter Amount" },
'underwriting_status[]':"Enter Underwriting Status",
s_prepaid_status: { required :"Enter Prepaid Status" },     
}
}); 

    jQuery.validator.addMethod("checkNumeric", function(value, element) {
        var regex = /^\-?([0-9]+(\.[0-9]+)?|Infinity)$/
        return this.optional(element) || jQuery.isNumeric(value);


    }, "Please Enter A Valid Numeric Number");


$('#force_investment').on('click',function()
{
    if($('#force_investment').is(":checked"))
    {
        liquiditycheck();
        document.getElementById("error_message_for_amount").innerHTML=  '';
        document.getElementById("update_btn").disabled = false;
    }
});
$(".js-placeholder-user_id").select2({
    placeholder: "Select Investor"
});
$(".js-placeholder-company").select2({
    placeholder: "Select Investor"
});
$('#gross_value').on('click',function()
{
    var total=0; 
    amount=$("#amount_field").val();
    if(!amount)
    {
        return null; 
    }
    var merchant_amount = $('#merchant_amount').val(); 
    if($('#gross_value').is(":checked") && (amount!=0) ){
        investment_arr = investment_calculator(amount);  // calculate full  investment amount
        total=investment_arr['total'];
        percentage=investment_arr['percentage'];
        var percentage_i = (total/ ( parseFloat(merchant_amount)+merchant_amount*percentage/100) )*100;
        $("#amount_per").val(percentage_i.toFixed(2)); 
    }
    else
    {
        investment_arr = investment_calculator(amount,1);  // calculate investment amount
        total=investment_arr['total'];
        percentage=investment_arr['percentage'];
        var percentage_i = (total/merchant_amount)*100;
        $("#amount_per").val(percentage_i.toFixed(2)); 
    }
    $("#amount_field").val(total.toFixed(2));  
});
$('#user_id').change(function()
{
    $investor_id= $('#user_id').val();
    $merchant_id='<?php echo $merchant_id ?>';
    if($investor_id)
    {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/investors/investorFee',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {user_id:$("#user_id").val(),merchant_id:$merchant_id},
            success: function (data) {    
                // alert(data.management_fee);
                // alert(data.syndication_fee);
                $('#mgmnt_fee').val(data.management_fee).change();                      
                $('#syndication_fee').val(data.syndication_fee).change();
                if(data.s_prepaid_status==2)
                $( "#s_prepaid_amount" ).prop( "checked", true );
                if(data.s_prepaid_status==1)
                $( "#s_prepaid_rtr" ).prop( "checked", true );    
                if(data.s_prepaid_status==0)
                $( "#s_prepaid_none" ).prop( "checked", true );
            }
        });
    }
});
$('#user_id').change(function() {
    liquiditycheck();
    mgmnt_fee_percentage=$(this).find(':selected').data('management-fee');
    syndication_fee_percentage=$(this).find(':selected').data('synd-fee');
    participant_name=$(this).find(':selected').data('name');
    // alert(management_fee);
    $('#mgmnt_fee_percentage').val(mgmnt_fee_percentage).trigger('change');
    $('#syndication_fee_percentage').val(syndication_fee_percentage).trigger('change');;
    $('#participant_name').val(participant_name);
});
var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
$('#merchant_id').change(function() {
    funded_date=$(this).find(':selected').data('funded-date');
    funded_amount=$(this).find(':selected').data('funded-amount');
    $('#date_funded').val(funded_date);
    $('#date_funded1').val(moment(funded_date, 'YYYY-MM-DD').format(default_date_format));
    $("#amount_field").empty();
    var option = $('<option></option>').attr("value", 5*funded_amount/100).text(5*funded_amount/100);
    $("#amount_field").append(option);
    var option = $('<option></option>').attr("value", 10*funded_amount/100).text(10*funded_amount/100);
    $("#amount_field").append(option);
    var option = $('<option></option>').attr("value", 15*funded_amount/100).text(15*funded_amount/100);
    $("#amount_field").append(option);
    var option = $('<option></option>').attr("value", 20*funded_amount/100).text(20*funded_amount/100);
    $("#amount_field").append(option);
    var option = $('<option></option>').attr("value", 25*funded_amount/100).text(25*funded_amount/100);
    $("#amount_field").append(option);
    var option = $('<option></option>').attr("value", 30*funded_amount/100).text(30*funded_amount/100);
    $("#amount_field").append(option);
    var option = $('<option></option>').attr("value", 35*funded_amount/100).text(35*funded_amount/100);
    $("#amount_field").append(option);
    var option = $('<option></option>').attr("value", 40*funded_amount/100).text(40*funded_amount/100);
    $("#amount_field").append(option);
    var option = $('<option></option>').attr("value", 45*funded_amount/100).text(45*funded_amount/100);
    var option = $('<option></option>').attr("value", 50*funded_amount/100).text(50*funded_amount/100);
    $("#amount_field").append(option);
    //  $('#amount_field').option('hi');
});
$('#amount_field').change(function() {
    liquiditycheck();
});
function liquiditycheck()
{
    liquidity=$('#user_id').find(':selected').data('liquidity');
    funded_amount=$('#amount_field').val();
    if(parseFloat(funded_amount)>parseFloat(liquidity))
    {
        if(!liquidity)
        {
            liquidity=0;
        }
        if($('#force_investment').is(":checked"))
        {
            alert('Cash in hand is only $'+liquidity+',it will changed to negative liquidity');
        }
        else 
        {
            alert('Cash in hand is only $'+liquidity);
        } 
    }
    //  $('#amount_field').option('hi');
}
// function liquiditycheck_1()
// {
//     liquidity=$('#user_id').find(':selected').data('liquidity');
//     funded_amount=$('#amount_field').val();
//     if(funded_amount>liquidity)
//     {
//         if(!liquidity)
//         {
//             liquidity=0;
//         }
//     }
//   //  $('#amount_field').option('hi');
// }
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/merchant_view_add_investor.css?ver=6') }}" rel="stylesheet" type="text/css" />
@stop
