@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">
      <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_title}}</div>     
      </a>
      
</div>
<?PHP
$i2=0;
while($i2<=6) 
{
    // $i+=0.25;
    $fee["$i2"]=$i2;
    $i2=$i2+0.25;
}
$merchant_amount=$merchant->funded;
$factor_rate=$merchant->funded;
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


?>
<input type="hidden" name="merchant_amount" id="merchant_amount" value="{{$merchant_amount}}">
<input type="hidden" name="factor_rate" id="factor_rate" value="{{$factor_rate}}">
<div class="col-md-12">

 

    <!-- general form elements -->
    <div class="box box-primary">

                <!-- form start -->

                {!! Form::open(['method'=>'POST','id'=>'investorCreateForm']) !!}
                <input type="hidden" name="mer_id" value="{{ $merchant_id }}">
                <!-- <input type="hidden" name=""> -->
                <div class="box-body-div col-md-12">
                   <div class="box-head">
                    @include('layouts.admin.partials.lte_alerts')
                    
                   </div>
                    <div class="merchant-head assign-inv-head">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="exampleInputEmail1"><span>Merchant Name:</span>{{$merchant->name}}</label>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                    
                          <!-- <div class="col-md-3">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Maximum Participant Fund Available : {{ FFM::dollar($max_participant_fund) }}</label>
                              
                            </div>
                        </div> -->
        <!--     <div class="btn-wrap ">
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="button" value="Assign Investor Based On Liquidity" class="btn btn-success mr-3 btn-lg" id="assign_investor_based_on_liquidity" name="assign_investor_based_on_liquidity" onclick="listInvetsorBasedOnLiquidity();">
                    </div>
                </div>
            </div>
         -->
                        </div>
                    <div class="row">
                        <div class="col-md-3 shrink">
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
                        <div class="col-md-3 shrink">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Investor Name <font color="#FF0000"> * </font></label>
                                <select id="user_id" name="user_id" class="form-control js-placeholder-user_id">
                                    <option value ="">Select Investor</option>
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
                          <div class="col-md-3 shrink">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Amount <font color="#FF0000"> * </font></label> 
                                <div class="input-group">                                       
                                    <span class="input-group-text">$</span>
                                    <div class="grow max-ma pr">
                                        {!! Form::text("input_amount_field",old('amount_field'),['class'=>'form-control accept_digit_only','id'=>'input_amount_field','required'=>'required','onchange'=>'calculateParticipantPercentage(this.value);']) !!}                    
                                    </div>
                                </div>
                                <!-- <span id="error_message_for_amount" class="text-danger" style="color:red;"></span>  -->
                            </div>
                        </div> 
                        <div class="col-md-3 shrink">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Share <font color="#FF0000"> * </font></label> 
                                <div class="input-group">
                                    @if($merchant->funded!=0)
                                    <span class="input-group-text">%</span>
                                    <div class="grow max-ma pr"> 
                                        {!! Form::text("input_amount_per",old('amount_per'),['readonly'=>'readonly' ,'class'=>'form-control','id'=>'input_amount_per']) !!}
                                    </div>
                                    @endif                                      
                                </div>
                                <span id="error_message_for_amount" class="text-danger" style="color:red;"></span> 
                            </div>
                        </div> 
                      
                        <div class="col-md-3 shrink">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Management Fee <font color="#FF0000"> * </font></label> 
                                <div class="input-group">
                                    @if($merchant->funded!=0)
                                    <span class="input-group-text">%</span>
                                    <div class="grow max-ma pr"> 
                                        {!! Form::select('input_mgmnt_fee_per',$fee_values,isset($merchant)? number_format($merchant->m_mgmnt_fee ,2): number_format(old('input_mgmnt_fee_per'),2),['class'=>'form-control','id'=>'input_mgmnt_fee_per']) !!}
                                        <!-- {!! Form::text("input_mgmnt_fee_per",isset($merchant)? $merchant->m_mgmnt_fee : old('mgmnt_fee_per'),['class'=>'form-control','id'=>'input_mgmnt_fee_per','placeholder'=>'']) !!} -->
                                    </div>
                                    @endif                                      
                                </div>
                                <!-- <span id="error_message_for_amount" class="text-danger" style="color:red;"></span>  -->
                            </div>
                        </div> 
                        <div class="col-md-3 shrink">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Underwriting Fee <font color="#FF0000"> * </font></label> 
                                <div class="input-group">
                                    @if($merchant->funded!=0)
                                    <span class="input-group-text">%</span>
                                    <div class="grow max-ma pr"> 
                                        {!! Form::select('input_underwriting_fee_per',$fee_values,isset($merchant)? number_format($merchant->underwriting_fee,2) : number_format(old('input_underwriting_fee_per'),2),['class'=>'form-control','id'=>'input_underwriting_fee_per']) !!}
                                        <!-- {!! Form::text("input_underwriting_fee_per",isset($merchant)? $merchant->underwriting_fee : old('input_underwriting_fee_per'),['class'=>'form-control','id'=>'input_underwriting_fee_per','placeholder'=>'']) !!} -->
                                    </div>
                                    @endif                                      
                                </div>
                                <!-- <span id="error_message_for_amount" class="text-danger" style="color:red;"></span>  -->
                            </div>
                        </div> 
                       <!--   <div class="col-md-3 shrink">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Commission <font color="#FF0000"> * </font></label> 
                                <div class="input-group">
                                    @if($merchant->funded!=0)
                                    <span class="input-group-text">%</span>
                                    <div class="grow max-ma pr"> 
                                        {!! Form::text("commission_per",isset($merchant)? $merchant->commission : old('commission_per'),['class'=>'form-control','id'=>'commission_per','placeholder'=>'']) !!}
                                    </div>
                                    @endif                                      
                                </div>
                               
                            </div>
                        </div>  -->
                       <!--  <div class="col-md-3 shrink">
                            <div class="form-group synd-march">
                                <label for="exampleInputEmail1">Syndication ON </label>
                                <?php $fee = array('1'=>'RTR','2'=>'Amount') ?>
                                <div class="">
                                    <div class="input-group">
                                        {!! Form::select('input_synd_on',$fee,isset($merchant)? $merchant->m_s_prepaid_status :old('input_synd_on'),['class'=>'form-control','id'=>'input_synd_on']) !!}
                                    
                                    </div>
                                </div>
                            </div> 
                        </div> -->
                    
                        
                         <div class="col-md-3 shrink">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Upsell Commission <font color="#FF0000"> * </font></label> 
                                <div class="input-group">
                                    @if($merchant->funded!=0)
                                    <span class="input-group-text">%</span>
                                    <div class="grow max-ma pr"> 
                                        {!! Form::select('input_upsell_commission_per',$upsell_comm_values,isset($merchant)? number_format($merchant->up_sell_commission,2) : number_format(old('input_upsell_commission_per'),2),['class'=>'form-control','id'=>'input_upsell_commission_per']) !!}
                                        <!-- {!! Form::text("input_upsell_commission_per",isset($merchant)? $merchant->up_sell_commission : old('input_upsell_commission_per'),['class'=>'form-control','id'=>'input_upsell_commission_per','placeholder'=>'']) !!} -->
                                    </div>
                                    @endif                                      
                                </div>
                                <!-- <span id="error_message_for_amount" class="text-danger" style="color:red;"></span>  -->
                            </div>
                        </div> 
                        <div class="col-md-6 shrink">
                        <div class="form-group synd-march inv-syndication-fee">
                            <label for="exampleInputEmail1">Syndication Fee </label>
                            <div class="">
                                <div class="input-group">
                                    {!! Form::select('input_synd_fee_per',$fee_values,isset($merchant)? number_format($merchant->m_syndication_fee,2) : number_format(old('input_synd_fee_per'),2),['class'=>'form-control','id'=>'input_synd_fee_per']) !!}
                                   
                                        <span class="input-group-text" >%</span>
                                        <span class="input-group-text">
                                            <label>
                                                <input {{old('input_synd_on')==2?'checked':(isset($merchant)?($merchant->m_s_prepaid_status==2?'checked':''):'')}}
                                                value="2" type="radio" name="input_synd_on" id="s_prepaid_amount" class="m_prepaid"> On Funding Amount?
                                            </label>
                                        </span>
                                        <span class="input-group-text">
                                            <label>
                                                <input {{old('input_synd_on')==1?'checked':(isset($merchant)?($merchant->m_s_prepaid_status==1?'checked':''):'')}}
                                                value="1" type="radio" name="input_synd_on" id="s_prepaid_rtr" class="m_prepaid"> On RTR?
                                            </label>
                                        </span>
                                   
                                </div>
                            </div>
                        </div> 
                    </div> 
                    </div>

                    <div class="btn-wrap btn-right">
                        <div class="btn-box">      
                        <input type="submit" class="btn btn-primary" id="add_btn" name="add_btn" value="ADD">
                        </div>                   
                    </div>
                </div>
                {!! Form::close() !!}
                {!! Form::open(['route'=>'admin::merchant_investor::create', 'method'=>'POST','id'=>'investorCreateForm1','class' => 'box-body']) !!}


                 <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class=" grid table-responsive">
                    {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true) !!}
                    <div class="blockCust pull-right" style="padding-bottom: 15px">                        
                    </div>
                </div>
            </div>


            <div class="btn-wrap btn-right">
                <div class="btn-box">      
                    <input type="button" value="Save" class="btn btn-success mr-3 btn-lg" id="save_btn" name="save_btn">
                    <input type="button" value="Cancel" class="cancel btn btn-light btn-lg" id="cancel_btn" name="cancel_btn" onclick = "cancelParticipant();">
                    <a href="{{URL::to('admin/merchants/view',$merchant_id)}}" class="btn btn-primary">View merchant</a>                
                </div>                   
            </div>

     

 {!! Form::close() !!}
         
    </div>
    
  
</div>

@stop
@section('scripts')
<script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}"
            type="text/javascript"></script>

<script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}"
            type="text/javascript"></script>
            {!! $tableBuilder->scripts() !!}



<script src='{{ asset('js/jquery_validate_min.js') }}' type="text/javascript"></script>
    <script>
        $(document).ready(function() { 
            $('#company').change(function(){
                var _this = $(this);
                var company = _this.val();
                $.ajax({
                    type: "POST",
                    url: "{{route("admin::merchant_investor::filtered-investor")}}",
                    data:{'_token': _token,'company':company,'creator_id':{{$creator_id}},'funded_investors':{{ json_encode($funded_investors) }} },
                    success: function(data){
                        if(data){
                            $('#user_id').empty();
                            $('#user_id').append('<option value ="">Select Investor</option>');
                            investor_data = data;
                            for(var i = 0; i < investor_data.length; i++){
                                liquidity = (investor_data[i]['user_details'].liquidity - investor_data[i]['user_details'].reserved_liquidity_amount).toFixed(2);
                                $('#user_id').append("<option data-liquidity="+liquidity+" data-management-fee="+investor_data[i].management_fee+" data-synd-fee="+investor_data[i].global_syndication+" data-name="+investor_data[i].name+" value="+investor_data[i].id+">"+investor_data[i].name+" - "+liquidity+"</option>");
                            }
                        }
                    }
                });
            });
        var table = window.LaravelDataTables["dataTableBuilder"];  
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/cancel-all-participant-row',
            type: 'POST',            

            data: {},
            success: function (data) {   
             
               window.scrollTo(0,0);        
            }
        }).done(function () { 
                        
        table.draw('page');

         });  
         jQuery.validator.addMethod("greaterThanZero",
    function (value, element, param) {          
          return parseFloat(value) > 0;
    });


            $('#investorCreateForm').validate({ // initialize the plugin
                errorClass: 'errors',
                rules: {
                    input_amount_field: {
                        required: true,
                        greaterThanZero: true
                    },
                    input_amount_per: {
                        range: [0,100],
                        greaterThanZero: true
                    },
                    input_underwriting_fee_per: {
                        required: true,
                        range   :  [0,5]
                    },
                    input_mgmnt_fee_per: {
                        required: true,
                        range   :  [0,5]
                    },
                    input_synd_fee_per: {
                        required: true,
                        range   :  [0,5]
                    },
                    commission_per: {
                        required: true,
                        range   :  [0,5]
                    },
                    input_upsell_commission_per: {
                        required: true,
                        range   :  [0,10]
                    },
                    user_id: {
                        required: true
                    },
                    input_synd_on:{
                        required: function(element) {
                        if($('#input_synd_fee_per').val()!=0)
                        return true;
                        else
                        return false;
                    },
                    }
                    
                },
                messages: {
                    input_amount_field: {
                        required: "Please Enter Amount",
                        greaterThanZero:"Amount should be greater than zero"
                    },
                    input_amount_per: {
                        range: "Share should be greater than 0 and less than or equal to 100",
                        greaterThanZero:"Share should be greater than zero"
                    },
                    input_underwriting_fee_per: {
                        required: "Please Enter Underwriting Fee",
                    },
                    input_mgmnt_fee_per: {
                        required: "Please Enter Management Fee",
                    },
                    input_synd_fee_per: {
                        required: "Please Enter Syndication Fee",
                    },
                    user_id: {
                        required: "Please Select An Investor",
                    },
                    input_synd_on: {
                        required: "Please Select syndication Fee On",
                    },
                },
                submitHandler: function(form) {
                   var table = window.LaravelDataTables["dataTableBuilder"];  
                   document.getElementById('example2_wrapper').style.display='block'; 
                   $investor_id= $('#user_id').val();
                   var m_s_prepaid_status=$("input[type='radio'][name='input_synd_on']:checked").val();
    
                    if($investor_id)
                    {      
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: '/admin/merchants/list-investor-for-assign',
                            type: 'POST',            
                            data: {user_id:$("#user_id").val(),share:$('#input_amount_per').val(),amount:$('#input_amount_field').val(),'mgmnt_fee_per':$('#input_mgmnt_fee_per').val(),'underwriting_fee_per':$('#input_underwriting_fee_per').val(),'syndication_fee_per':$('#input_synd_fee_per').val(),'syndication_on':m_s_prepaid_status,'commission':$('#commission_per').val(),'upsell_commission_per':$('#input_upsell_commission_per').val()},
                            success: function (data) {  
                               

                            }
                        }).done(function () { 
                                 $('#input_amount_field').val(null);   
                                 $('#input_amount_per').val(null); 
                                 $("#user_id").val(null).change(); 
                                 $("#company").val(0).change(); 

                                 $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                url: '/admin/investors/merchantFee',
                                type: 'POST',            
                                data: {merchant_id:$merchant_id},
                                success: function (data) {   
                                    if(data.status==1){
                                    $('#input_mgmnt_fee_per').val(data.management_fee).change();                      
                                    $('#input_synd_fee_per').val(data.syndication_fee).change(); 
                                    if(data.s_prepaid_status==2)
                                    $( "#s_prepaid_amount" ).prop( "checked", true );
                                    if(data.s_prepaid_status==1)
                                    $( "#s_prepaid_rtr" ).prop( "checked", true );    
                                    if(data.s_prepaid_status==0)
                                    $( "#s_prepaid_none" ).prop( "checked", true );  
                                    $('#input_underwriting_fee_per').val(data.underwriting_fee).change(); 

                                    $('#input_upsell_commission_per').val(data.upsell_comm).change(); 
                                    }else{
                                        $('.box-head').html('<div class="alert alert-danger" ><strong> </strong>' + data.message + '<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>');
                                    }
                                }
                            });  
                                 
                        table.draw('page');

                         });
                    }
                }

            });
        });

</script>

<script type="text/javascript"> 
     $('.accept_digit_only').keypress(function (event) {//ONLY DOTS AND NUMBERS,DECIMAL UP TO 2 POINTS
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
    var table = window.LaravelDataTables["dataTableBuilder"];  
    $('#dataTableBuilder tbody').on('keypress', '.decimal', function (e) {
         
    //ONLY DOTS AND NUMBERS,DECIMAL UP TO 2 POINTS
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
    $('#dataTableBuilder tbody').on('keypress', '.fee_percentage_class', function (e) {
         
    var character = String.fromCharCode(e.keyCode)
    var newValue = this.value + character;
    if (isNaN(newValue) || parseFloat(newValue) * 100 % 1 > 0) {        
        e.preventDefault();
        return false;
    }

});
$('#dataTableBuilder tbody').on('blur', '.share-details-controlbbbbb', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    var share = $(this).val();

    var merchant_amount = $('#merchant_amount').val();
    var amount = merchant_amount*share/100;
    var total = investment_calculator(amount);
   
    tr.children('td').children('.amount-details-control  ').val(total['total']);
    var amount = tr.children('td').children('.amount-details-control  ').val();
    var row = table.row(tr);console.log(row.data());
  
}); 
$('#dataTableBuilder tbody').on('blur', '.commission_amount_details_class', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    var merchant_amount = $('#merchant_amount').val();
    var factor_rate = $('#factor_rate').val();
    var commission_amount = $(this).val();
    var amount = tr.children('td').find("#amount").val();
    var commission_per = (commission_amount/amount)*100;
    if(commission_per>5){     
        tr.children('td').find("#error_span"+this.id).html("Please enter amount less than 5% of participant amount");
        $('#save_btn').attr('disabled','disabled');
    }else{
        tr.children('td').find("#error_span"+this.id).html("");
        tr.children('td').find("#commission").val(commission_per);
        $('#save_btn').removeAttr('disabled');
    }

});
$('#dataTableBuilder tbody').on('change', '.mgmnt_fee_per', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
   if($(this).val()>5){
       $('#save_btn').attr('disabled','disabled');
       tr.children('td').find("#error_span"+this.id).html("Please enter percentage less than 5");
    }else{
        tr.children('td').find("#error_span"+this.id).html("");
        $('#save_btn').removeAttr('disabled');
    }
    var amount = tr.children('td').find("#amount").val();
    var factor_rate = $('#factor_rate').val(); 
    var mgmnt_fee_amount = ((amount*factor_rate)*$(this).val())/100;
    mgmnt_fee_amount = mgmnt_fee_amount.toFixed(2);
    tr.children('td').find("#mgmnt_fee_amount").val(mgmnt_fee_amount);
    var participant_id=tr.children('td').find("#participant_id").val();
          var syndiaction_fee_percent=tr.children('td').find("#syndication_fee").val();
          var syndiaction_on=tr.children('td').find("#syndication_on").val();
          var upsell_commission_percent=tr.children('td').find("#upsell_commission").val();
          var mgmnt_fee_percent=tr.children('td').find("#mgmnt_fee").val();
          var underwritng_fee_percentage=tr.children('td').find("#underwriting_fee").val();
          var amount = tr.children('td').find("#amount").val();
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/update-assign-investor-session',
            type: 'POST',            

            data: {'participant_id':participant_id,'underwriting_fee_percent':underwritng_fee_percentage,'syndiaction_fee_percent':syndiaction_fee_percent,'syndiaction_on':syndiaction_on,'upsell_commission_percent':upsell_commission_percent,'mgmnt_fee_percent':mgmnt_fee_percent,'amount':amount},
            success: function (data) {   
              

            }
        }).done(function () { 
                        
        table.draw('page');

         });

});
$('#dataTableBuilder tbody').on('blur', '.commission_percent_details_class', function (e) {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    if($(this).val()>5){
    $('#save_btn').attr('disabled','disabled');
    tr.children('td').find("#error_span"+this.id).html("Please enter percentage less than 5");
    }else{
    $('#save_btn').removeAttr('disabled');
    tr.children('td').find("#error_span"+this.id).html("");
    
    var merchant_amount = $('#merchant_amount').val();
    var factor_rate = $('#factor_rate').val();
    var commission_percentage = $(this).val();
    var amount = tr.children('td').find("#amount").val();
    var commission_amount = (amount*commission_percentage)/100;
    commission_amount = commission_amount.toFixed(2);
    tr.children('td').find("#commission_amount").val(commission_amount);
    }

}); 



$('#dataTableBuilder tbody').on('blur', '.upsell_commission_details_classnoneed', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    var merchant_amount = $('#merchant_amount').val();    
    var amount = tr.children('td').find("#amount").val();
    var upsell_commission_amount = $(this).val();
    var upsell_commission = (upsell_commission_amount/amount)*100;  
     if(upsell_commission>5){     
        tr.children('td').find("#error_span"+this.id).html("Please enter amount less than 5% of participant amount");
        $('#save_btn').attr('disabled','disabled');
    }else{
        tr.children('td').find("#error_span"+this.id).html("");
        tr.children('td').find("#upsell_commission").val(upsell_commission);
        $('#save_btn').removeAttr('disabled');
    }  
     var total_upsell_amount = 0;
         table.rows().eq(0).each(function(colIdx) {
          total_upsell_amount =  parseFloat(total_upsell_amount)+parseFloat($(".row"+colIdx).children('td').find("#upsell_commission_amount").val());
         });
         $("#total_upsell_commission").val(total_upsell_amount);
    

});
$('#dataTableBuilder tbody').on('change', '.upsell_commission_percent_details_class', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
   
        $('#save_btn').removeAttr('disabled');
        tr.children('td').find("#error_span"+this.id).html("");
    
    var merchant_amount = $('#merchant_amount').val();    
    var commission_percentage = $(this).val();
    var amount = tr.children('td').find("#amount").val();
    var commission_amount = (amount*commission_percentage)/100;
    commission_amount = commission_amount.toFixed(2);
    tr.children('td').find("#upsell_commission_amount").val(commission_amount);
    
    var total_upsell_amount = 0;
         table.rows().eq(0).each(function(colIdx) {
          total_upsell_amount =  parseFloat(total_upsell_amount)+parseFloat($(".row"+colIdx).children('td').find("#upsell_commission_amount").val());
         });
         $("#total_upsell_commission").val(total_upsell_amount);

          var participant_id=tr.children('td').find("#participant_id").val();
          var syndiaction_fee_percent=tr.children('td').find("#syndication_fee").val();
          var syndiaction_on=tr.children('td').find("#syndication_on").val();
          var upsell_commission_percent=tr.children('td').find("#upsell_commission").val();
          var mgmnt_fee_percent=tr.children('td').find("#mgmnt_fee").val();
          var underwritng_fee_percentage=tr.children('td').find("#underwriting_fee").val();
          var amount = tr.children('td').find("#amount").val();
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/update-assign-investor-session',
            type: 'POST',            

            data: {'participant_id':participant_id,'underwriting_fee_percent':underwritng_fee_percentage,'syndiaction_fee_percent':syndiaction_fee_percent,'syndiaction_on':syndiaction_on,'upsell_commission_percent':upsell_commission_percent,'mgmnt_fee_percent':mgmnt_fee_percent,'amount':amount},
            success: function (data) {   
              

            }
        }).done(function () { 
                        
        table.draw('page');

         });

}); 

$('#dataTableBuilder tbody').on('blur', '.underwriting_fee_amount_detail_classnownoneed', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    var merchant_amount = $('#merchant_amount').val();    
    var amount = tr.children('td').find("#amount").val();
    var underwriting_fee = $(this).val();
    var underwriting_fee_per = (underwriting_fee/amount)*100;  
     if(underwriting_fee_per>5){     
        tr.children('td').find("#error_span"+this.id).html("Please enter amount less than 5% of participant amount");
        $('#save_btn').attr('disabled','disabled');
    }else{
        tr.children('td').find("#error_span"+this.id).html("");
        tr.children('td').find("#underwriting_fee").val(underwriting_fee_per);
        $('#save_btn').removeAttr('disabled');
    }  
     var total_underwriting_fee = 0;
         table.rows().eq(0).each(function(colIdx) {
          total_underwriting_fee =  parseFloat(total_underwriting_fee)+parseFloat($(".row"+colIdx).children('td').find("#underwriting_fee_amount").val());
         });
         $("#total_underwriting_fee").val(total_underwriting_fee);
    

});


$('#dataTableBuilder tbody').on('change', '.underwriting_fee_percent_detail_class', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    if($(this).val()>5){
        $('#save_btn').attr('disabled','disabled');
        tr.children('td').find("#error_span"+this.id).html("Please enter percentage less than 5");
    }else{
        $('#save_btn').removeAttr('disabled');
        tr.children('td').find("#error_span"+this.id).html("");
    
    var merchant_amount = $('#merchant_amount').val();    
    var underwritng_fee_percentage = $(this).val();
    var amount = tr.children('td').find("#amount").val();
    var underwriting_fee = (amount*underwritng_fee_percentage)/100;
    underwriting_fee = underwriting_fee.toFixed(2);
    tr.children('td').find("#underwriting_fee_amount").val(underwriting_fee);
    }
    var total_underwriting_fee = 0;
         table.rows().eq(0).each(function(colIdx) {
          total_underwriting_fee =  parseFloat(total_underwriting_fee)+parseFloat($(".row"+colIdx).children('td').find("#underwriting_fee_amount").val());
         });
          $("#total_underwriting_fee").val(total_underwriting_fee);
          var participant_id=tr.children('td').find("#participant_id").val();
          var syndiaction_fee_percent=tr.children('td').find("#syndication_fee").val();
          var syndiaction_on=tr.children('td').find("#syndication_on").val();
          var upsell_commission_percent=tr.children('td').find("#upsell_commission").val();
          var mgmnt_fee_percent=tr.children('td').find("#mgmnt_fee").val();
          var amount = tr.children('td').find("#amount").val();
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/update-assign-investor-session',
            type: 'POST',            

            data: {'participant_id':participant_id,'underwriting_fee_percent':underwritng_fee_percentage,'syndiaction_fee_percent':syndiaction_fee_percent,'syndiaction_on':syndiaction_on,'upsell_commission_percent':upsell_commission_percent,'mgmnt_fee_percent':mgmnt_fee_percent,'amount':amount},
            success: function (data) {   
              

            }
        }).done(function () { 
                        
        table.draw('page');

         });

}); 

$('#dataTableBuilder tbody').on('blur', '.syndication_fee_details_classnownoneed', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    var merchant_amount = $('#merchant_amount').val();
    var factor_rate = $('#factor_rate').val();
    var amount = tr.children('td').find("#amount").val();
    var syndication_on = tr.children('td').find("#syndication_on").val();
    var syndication_fee_amount = $(this).val();
    if(syndication_on==1){
          var synd_amount = (syndication_fee_amount/(amount*factor_rate))*100;
        }
        if(syndication_on==2){
          var synd_amount = (syndication_fee_amount/amount)*100;  
        } 

        if(synd_amount>5){     
        tr.children('td').find("#error_span"+this.id).html("Please enter amount less than 5% of participant amount/rtr");
        $('#save_btn').attr('disabled','disabled');
        }else{
            tr.children('td').find("#error_span"+this.id).html("");
            tr.children('td').find("#syndication_fee").val(synd_amount);
            $('#save_btn').removeAttr('disabled');
        } 
        var total_syndiaction_fee = 0;
         table.rows().eq(0).each(function(colIdx) {
          total_syndiaction_fee =  parseFloat(total_syndiaction_fee)+parseFloat($(".row"+colIdx).children('td').find("#syndication_fee_amount").val());
         });
         $("#total_syndication_fee").val(total_syndiaction_fee);
  
   

});
$('#dataTableBuilder tbody').on('change', '.syndication_fee_percent_details_class', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    if($(this).val()>5){
        $('#save_btn').attr('disabled','disabled');
        tr.children('td').find("#error_span"+this.id).html("Please enter percentage less than 5");       
    }else{
        $('#save_btn').removeAttr('disabled');
        tr.children('td').find("#error_span"+this.id).html("");    
        var merchant_amount = $('#merchant_amount').val();  
        var factor_rate = $('#factor_rate').val();  
        var syndication_fee_percentage = $(this).val();
        var amount = tr.children('td').find("#amount").val();   
        var syndication_on = tr.children('td').find("#syndication_on").val();
        if(syndication_on==1){
              var synd_amount = (syndication_fee_percentage/100)*(amount*factor_rate);
            }
            if(syndication_on==2){
              var synd_amount = (syndication_fee_percentage/100)*amount;  
            } 
            synd_amount = synd_amount.toFixed(2); 
        tr.children('td').find("#syndication_fee_amount").val(synd_amount);
}
         var total_syndiaction_fee = 0;
         table.rows().eq(0).each(function(colIdx) {
          total_syndiaction_fee =  parseFloat(total_syndiaction_fee)+parseFloat($(".row"+colIdx).children('td').find("#syndication_fee_amount").val());
         });
         $("#total_syndication_fee").val(total_syndiaction_fee);

          var participant_id=tr.children('td').find("#participant_id").val();
          var syndiaction_fee_percent=$(this).val();
          var syndiaction_on=tr.children('td').find("#syndication_on").val();
          var upsell_commission_percent=tr.children('td').find("#upsell_commission").val();
          var mgmnt_fee_percent=tr.children('td').find("#mgmnt_fee").val();
          var underwritng_fee_percentage=tr.children('td').find("#underwriting_fee").val();
          var amount = tr.children('td').find("#amount").val();
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/update-assign-investor-session',
            type: 'POST',            

            data: {'participant_id':participant_id,'underwriting_fee_percent':underwritng_fee_percentage,'syndiaction_fee_percent':syndiaction_fee_percent,'syndiaction_on':syndiaction_on,'upsell_commission_percent':upsell_commission_percent,'mgmnt_fee_percent':mgmnt_fee_percent,'amount':amount},
            success: function (data) {   
              

            }
        }).done(function () { 
                        
        table.draw('page');

         });

}); 
$('#dataTableBuilder tbody').on('change', '.syndication_on_details_class', function () {
    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    var merchant_amount = $('#merchant_amount').val();    
    var factor_rate = $('#factor_rate').val(); 
    var syndication_on = $(this).val();
    var amount = tr.children('td').find("#amount").val();
    var syndication_fee_percentage = tr.children('td').find("#syndication_fee").val(); 
     if(syndication_on==1){
          var synd_amount = (syndication_fee_percentage/100)*(amount*factor_rate);
        }
        if(syndication_on==2){
          var synd_amount = (syndication_fee_percentage/100)*amount;  
        } 
    
    tr.children('td').find("#syndication_fee_amount").val(synd_amount);
          var participant_id=tr.children('td').find("#participant_id").val();
          var syndiaction_fee_percent=tr.children('td').find("#syndication_fee").val();
          var syndiaction_on=tr.children('td').find("#syndication_on").val();
          var upsell_commission_percent=tr.children('td').find("#upsell_commission").val();
          var mgmnt_fee_percent=tr.children('td').find("#mgmnt_fee").val();
          var underwritng_fee_percentage=tr.children('td').find("#underwriting_fee").val();
          var amount = tr.children('td').find("#amount").val();
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/update-assign-investor-session',
            type: 'POST',            

            data: {'participant_id':participant_id,'underwriting_fee_percent':underwritng_fee_percentage,'syndiaction_fee_percent':syndiaction_fee_percent,'syndiaction_on':syndiaction_on,'upsell_commission_percent':upsell_commission_percent,'mgmnt_fee_percent':mgmnt_fee_percent,'amount':amount},
            success: function (data) {   
              

            }
        }).done(function () { 
                        
        table.draw('page');

         });

}); 
function deleteParticipant(participant_id) {
    var table = window.LaravelDataTables["dataTableBuilder"];
       $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/delete-participant-row',
            type: 'POST',            

            data: {'participant_id':participant_id},
            success: function (data) {   
               $('.box-head').html('<div class="alert alert-success" ><strong>Success! </strong>' + data.message + '<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>'); 
               window.scrollTo(0,0);        
            }
        }).done(function () { 
                        
        table.draw('page');

         });

}
function cancelParticipant() {
 var table = window.LaravelDataTables["dataTableBuilder"];
       $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/cancel-all-participant-row',
            type: 'POST',            

            data: {},
            success: function (data) {
               if(data.status == 1){   
               $('.box-head').html('<div class="alert alert-success" ><strong>Success! </strong>' + data.message + '<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>'); 
               window.scrollTo(0,0);
               }        
            }
        }).done(function () { 
                        
        table.draw('page');

         });

}

$('#dataTableBuilder tbody').on('blur', '.amount_details_class', function () {  

    var table = window.LaravelDataTables["dataTableBuilder"]
    var tr = $(this).closest('tr');
    var merchant_amount = $('#merchant_amount').val();
    var factor_rate = $('#factor_rate').val();
    var amount = $(this).val();
    var liquidity = tr.children('td').find("#liquidity").val();
    if(parseFloat(amount)>parseFloat(liquidity))
    {
       alert('Cash in hand is only $'+liquidity+", Your liquidity may become negative after this investment.");        
    }
    var percentage = (amount /  ( parseFloat(merchant_amount) ) )*100; 
    var row = table.row(tr); 
        tr.children('td').find("#share").val(percentage.toFixed(2));
        var share =  tr.children('td').find("#share").val();
        var commission_amount =  tr.children('td').find("#commission_amount").val();
        var commission =  tr.children('td').find("#commission").val();
        var underwriting_fee = tr.children('td').find("#underwriting_fee").val();
        var underwriting_fee_amount = tr.children('td').find("#underwriting_fee_amount").val();
        var syndication_fee = tr.children('td').find("#syndication_fee").val(); 
        var syndication_fee_amount = tr.children('td').find("#syndication_fee_amount").val();
        var upsell_commission_per = tr.children('td').find("#upsell_commission").val();
        var upsell_commission_amount = tr.children('td').find("#upsell_commission_amount").val();
        var syndication_on = tr.children('td').find("#syndication_on").val();  
        var mgmnt_fee_per = tr.children('td').find("#mgmnt_fee").val();       
          
        var comm_amount = (commission/100)*amount;
        tr.children('td').find("#commission_amount").val(comm_amount.toFixed(2));   
          
        var undwrt_amount = (underwriting_fee/100)*amount;
        tr.children('td').find("#underwriting_fee_amount").val(undwrt_amount.toFixed(2));
         
        var upsell_amount = (upsell_commission_per/100)*amount;
        tr.children('td').find("#upsell_commission_amount").val(upsell_amount.toFixed(2));
          
         
        if(syndication_on==1){
          var synd_amount = (syndication_fee/100)*(amount*factor_rate);
        }
        if(syndication_on==2){
          var synd_amount = (syndication_fee/100)*amount;  
        } 
        var mgmnt_fee_amount =  (mgmnt_fee_per/100)*(amount*factor_rate);        
        tr.children('td').find("#syndication_fee_amount").val(synd_amount.toFixed(2));
        tr.children('td').find("#mgmnt_fee_amount").val(mgmnt_fee_amount.toFixed(2));


         var total_amount = total_syndiaction_fee = total_upsell_amount = total_underwriting_fee = total_mgmnt_fee = 0;
         table.rows().eq(0).each(function(colIdx) {
          total_amount =  parseFloat(total_amount)+parseFloat($(".row"+colIdx).children('td').find("#amount").val());
          total_syndiaction_fee =  parseFloat(total_syndiaction_fee)+parseFloat($(".row"+colIdx).children('td').find("#syndication_fee_amount").val());
          total_upsell_amount =  parseFloat(total_upsell_amount)+parseFloat($(".row"+colIdx).children('td').find("#upsell_commission_amount").val());
          total_underwriting_fee =  parseFloat(total_underwriting_fee)+parseFloat($(".row"+colIdx).children('td').find("#underwriting_fee_amount").val());
          total_mgmnt_fee =  parseFloat(total_mgmnt_fee)+parseFloat($(".row"+colIdx).children('td').find("#mgmnt_fee_amount").val());
         });
         $("#total_participant_amount").val(total_amount.toFixed(2));
         $("#total_syndication_fee").val(total_syndiaction_fee);
         $("#total_upsell_commission").val(total_upsell_amount);
         $("#total_underwriting_fee").val(total_underwriting_fee);
         $("#total_mgmnt_fee").val(total_mgmnt_fee);
          var participant_id=tr.children('td').find("#participant_id").val();
          var syndiaction_fee_percent=tr.children('td').find("#syndication_fee").val();
          var syndiaction_on=tr.children('td').find("#syndication_on").val();
          var upsell_commission_percent=tr.children('td').find("#upsell_commission").val();
          var mgmnt_fee_percent=tr.children('td').find("#mgmnt_fee").val();
          var underwritng_fee_percentage=tr.children('td').find("#underwriting_fee").val();
          var amount = tr.children('td').find("#amount").val();
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/update-assign-investor-session',
            type: 'POST',            

            data: {'participant_id':participant_id,'underwriting_fee_percent':underwritng_fee_percentage,'syndiaction_fee_percent':syndiaction_fee_percent,'syndiaction_on':syndiaction_on,'upsell_commission_percent':upsell_commission_percent,'mgmnt_fee_percent':mgmnt_fee_percent,'amount':amount},
            success: function (data) {   
              

            }
            });
         
}); 
function percentage_prepaids(){
    var percentage2=0;
    var commission_per='{{ $commission_per }}';
    if(commission_per)
    percentage2=parseInt(percentage2)+parseInt(commission_per);
    var underwriting_fee_per='{{ $underwriting_fee_per }}';  
    if(underwriting_fee_per)
    percentage2=parseInt(percentage2)+parseInt(underwriting_fee_per);
    var m_syndication_fee_per=$('#input_synd_fee_per').val();
    var m_s_prepaid_status=$('#input_synd_on').val();
    var m_s_prepaid_status=$("input[type='radio'][name='input_synd_on']:checked").val();
    var factor_rate='{{ $factor_rate }}';
    var prepaid=0;
    var prepaid_per = 0;

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
function calculateParticipantAmount(percentage){
    var merchant_amount = $('#merchant_amount').val();
    var amount = merchant_amount*percentage/100;
    var total = investment_calculator(amount);  // calculate investment amount
    $("#input_amount_field").val(total);
    var force_investment= $('#force_investment').is(":checked");
    if( (percentage > 100 && force_investment==false) || (percentage <= 0 && force_investment==false) || isNaN(percentage) || isNaN(merchant_amount)){
        document.getElementById("error_message_for_amount").innerHTML=  'Percentage should be between 0 and 100';
        
    }else{
        document.getElementById("error_message_for_amount").innerHTML=  '';
       
    }
}
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
function calculateParticipantPercentage(amount){
    var merchant_amount = $('#merchant_amount').val();
    var inv_id = $('#user_id').val();
    if(merchant_amount!=0)
    {
        
    liquidity=$('#user_id').find(':selected').data('liquidity');
    if(!liquidity)
        {
            liquidity=0;
        }
    funded_amount=$('#input_amount_field').val();
    if(parseFloat(funded_amount)>parseFloat(liquidity) && inv_id)
    {
            alert('Cash in hand is only $'+liquidity+", Your liquidity may become negative after this investment.");
       
    }
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
        $("#input_amount_per").val(percentage); 
        var force_investment= $('#force_investment').is(":checked");
        if((percentage > 100 && force_investment==false) || (percentage <= 0 && force_investment==false) || isNaN(percentage) || isNaN(merchant_amount)){
            // if(percentage==0){
            //   document.getElementById("error_message_for_amount").innerHTML=  'Percentage should be a number greater than 0';
            // }else{
            //    document.getElementById("error_message_for_amount").innerHTML=  'Percentage should be between 0 and 100'; 
            // }
            
            
        }else{
            document.getElementById("error_message_for_amount").innerHTML=  '';
            
        }
        if(amount.length==0){
            document.getElementById("error_message_for_amount").innerHTML=  '';
        }
    }
}

$('#save_btn').on('click',function(e)
{   $merchant_id='<?php echo $merchant_id ?>';
var proceed =0;
    var assignDatas = [];
         var table = window.LaravelDataTables["dataTableBuilder"];              
         table.rows().eq(0).each(function(colIdx) {
            valueArray = {};
          var syndication_on = $(".row"+colIdx).children('td').find("#syndication_on").val(); 
          var share =  $(".row"+colIdx).children('td').find("#share").val();
          var amount =  $(".row"+colIdx).children('td').find("#amount").val();
          var participant_id =  $(".row"+colIdx).children('td').find("#participant_id").val();
          var mgmnt_fee = $(".row"+colIdx).children('td').find("#mgmnt_fee").val();
          var underwriting_fee = $(".row"+colIdx).children('td').find("#underwriting_fee").val();
          var syndication_fee = $(".row"+colIdx).children('td').find("#syndication_fee").val();
          var commission = $(".row"+colIdx).children('td').find("#commission").val();
          var upsell_commission_per = $(".row"+colIdx).children('td').find("#upsell_commission").val();

          valueArray['share'] = share;          
          valueArray['amount'] = amount;          
          valueArray['participant_id'] = participant_id;          
          valueArray['mgmnt_fee'] = mgmnt_fee;          
          valueArray['underwriting_fee'] = underwriting_fee;          
          valueArray['syndication_fee'] = syndication_fee;          
          valueArray['commission'] = commission;          
          valueArray['upsell_commission_per'] = upsell_commission_per;  
          valueArray['syndication_on'] = syndication_on;       
          assignDatas.push(valueArray); 
          if(mgmnt_fee < 0 || mgmnt_fee > 5 || underwriting_fee < 0 || underwriting_fee > 5){
             $('#save_btn').attr('disabled','disabled');
             proceed = proceed+1;
          } else{
             $('#save_btn').removeAttr('disabled');
          }       
   
         }); 
         if(proceed==1){
           $('#save_btn').attr('disabled','disabled'); 
         }
if(proceed==0){
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/assign-investor-to-merchant',
            type: 'POST',            

            data: {'data':assignDatas,'merchant_id':$merchant_id},
            success: function (data) {  
                if(data.success_message!='' && data.error_message!=''){
                            
                $('.box-head').html('<div class="alert alert-success" ><strong> </strong>' + data.success_message + '</div><div class="alert alert-danger" ><strong> </strong>' + data.error_message + '<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>'); 
                }
                if(data.success_message!='' && data.error_message==''){
                $('.box-head').html('<div class="alert alert-success" ><strong> </strong>' + data.success_message + '<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>'); 
                }
                if(data.error_message!='' && data.success_message==''){
                $('.box-head').html('<div class="alert alert-danger" ><strong> </strong>' + data.error_message + '<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>'); 
                }
              
              
               window.scrollTo(0,0);        
            }
        }).done(function () { 
                        
        table.draw('page');

         });
    }


       
});
$('#user_id').change(function()
{
    $investor_id= $('#user_id').val();
    $merchant_id='<?php echo $merchant_id ?>';
    if($investor_id)
    { 
    liquidity=$('#user_id').find(':selected').data('liquidity');
    if(!liquidity)
        {
            liquidity=0;
        }
    funded_amount=$('#input_amount_field').val();
    if(parseFloat(funded_amount) > parseFloat(liquidity))
    {
            alert('Cash in hand is only $'+liquidity+", Your liquidity may become negative after this investment.");
       
    }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/investors/investorFee',
            type: 'POST',            
            data: {user_id:$("#user_id").val(),merchant_id:$merchant_id},
            success: function (data) {   
                 
                $('#input_mgmnt_fee_per').val(data.management_fee).change();                      
                $('#input_synd_fee_per').val(data.syndication_fee).change(); 
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
  function OnlyPercentage(evt) { 
            var val1;
            if (!(evt.keyCode == 46 || (evt.keyCode >= 48 && evt.keyCode <= 57))) {
                return false;
            } else {
                return true;
            }
            var parts = evt.srcElement.value.split('.');
            if (parts.length > 2)
                return false;
            if (evt.keyCode == 46)
                return (parts.length == 1);
            if (evt.keyCode != 46) {
                var currVal = String.fromCharCode(evt.keyCode);
                val1 = parseFloat(String(parts[0]) + String(currVal));
                if(parts.length==2)
                    val1 = parseFloat(String(parts[0])+ "." + String(currVal));
            }

            if (val1 > 100)
                return false;
            if (parts.length == 2 && parts[1].length >= 2) return false;
        }

function listInvetsorBasedOnLiquidity(){
    $merchant_id='<?php echo $merchant_id ?>';
   var table = window.LaravelDataTables["dataTableBuilder"];
       $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/list-investors-based-on-liquidity',
            type: 'POST',            

            data: {merchant_id:$merchant_id},
            success: function (data) {   
               
               window.scrollTo(0,0);        
            }
        }).done(function () { 
                        
        table.draw('page');

         });
}
function listInvetsorBasedOnPayment(){
    $merchant_id='<?php echo $merchant_id ?>';
   var table = window.LaravelDataTables["dataTableBuilder"];
       $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/merchants/list-investors-based-on-payment',
            type: 'POST',            

            data: {merchant_id:$merchant_id},
            success: function (data) {   
               
               window.scrollTo(0,0);        
            }
        }).done(function () { 
                        
        table.draw('page');

         });
}
</script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/document.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">

        #save_btn{
            margin-right: 10px;
        }
        .input-group.no-wrap{
            flex-wrap: nowrap;
        }
        .no-wrap .form-control{
            width: 80px;
        }
        .grow{
            flex-grow: 1;
            padding-right: 0!important;
        }
        @media (max-width:1200px){
            .shrink{
                width: 50%;
            }
        }
        .m_prepaid.errors{
            width: auto;
        }
        .dataTable .no-wrap .form-control.fee_percentage_class{
            width: fit-content;
            block-size: fit-content;
            width: intrinsic;           
            width: -moz-max-content;    
            width: -webkit-max-content; 
        }

</style>

@stop