@extends('layouts.marketplace.admin_lte')

@section('content')
    <?PHP
      $fee=[

                    '0'=>'0'
                    ,'0.25'=>'0.25'
                    ,'0.5'=>'0.5'
                    ,'0.75'=>'0.75'
                    ,'1'=>'1'

                    ,'1.25'=>'1.25'

                    ,'1.5'=>'1.5'

                    ,'1.75'=>'1.75'

                    ,'2'=>'2'

                    ,'2.25'=>'2.25'

                    ,'2.5'=>'2.5'

                    ,'2.75'=>'2.75'

                    ,'3'=>'3'

                    ,'3.25'=>'3.25'

                    ,'3.5'=>'3.5'

                    ,'3.75'=>'3.75'

                    ,'4'=>'4'

                    ,'4.25'=>'4.25'

                    ,'4.5'=>'4.5'

                    ,'4.75'=>'4.75'

                    ,'5'=>'5'];

    $percentages =['100','95','90','85','80','75','70','65','60','55','50','45','40','35','30','25','20','15','10','5'];

    // $commissions=[0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15];

    ?>


@include('layouts.investor.partials.lte_alerts')


    <div class="wrap"></div>
    <div class="grid" style="margin-top: 20px;">
        <div class="col-md-12 col-sm-12">
            <div class="margtCardFiltr">
                    <label>Filter</label>
                    <select onchange="javascript:location.href = this.value;" style="padding: 1px;" class="form-control">
                         <option selected="selected" value="">Select</option>
                          <option value="?filter=0">All</option>
                          <option {{$filter_id==1?'Selected':''}} value="?filter=1" >Partially Funded</option>
                          <option {{$filter_id==2?'Selected':''}} value="?filter=2" >Not Funded</option>
                        </select>
            </div>
        </div>
    <!--     <ul style="wid"> -->
            @if(count($funds)==0)
                Sorry No Records Found. 
            @endif
           @foreach($funds as $fund)
           @if($fund->invest_status!=1)
           @if($fund->max_participant_fund > $fund->marketplaceInvestors()->sum('amount'))
           <?PHP
           $maximum_amount=$fund->max_participant_fund - $fund->marketplaceInvestors()->sum('amount');
           $max_per=$maximum_amount/$fund->funded*100;
           $maximum_amount=$fund->max_participant_fund - $fund->marketplaceInvestors()->sum('amount'); 
           $commission_per= $fund->commission;
           $under_writing_fee_per= $fund->under_writing_fee;
           $factor_rate = $fund->factor_rate;

           ?>



           {!! Form::open(['route'=>'investor::marketplace::funds_request', 'method'=>'POST','class'=>'fundRequest','id'=>'fund_'.$fund->id]) !!}
           <input type="hidden"  name="id" value="{{$fund->id}}" >



            <div class="marktPlceCard" style="width: 48%;float: left;margin: 10px">
                <div class="marktPlceCardTitle">
                    <div class="col-md-12 col-sm-12">{{$fund->business_en_name}}</div>
                </div>
                <div class="wrap"></div>
                <div class="firstSec grid">
                    <div class="markt-diagram">
                        <div id="demo-pie-1" class="pie-title-center demo-pie-1 diagrm" data-percent="{{100-$max_per}}">
                                            <!--     <canvas height="150" width="150"></canvas>
                                            -->

                                            <span class="pie-value"></span> <span class="pie-title">Funding Completed</span>
                                            <span class="pie-value-pending"></span> <span class="pie-title-pending">Available</span>



                                        </div>
                                    </div>
                                    <div class="markt-details-one">
                                        <table class="table">
                                            <tbody>
                                                 <tr>
                                                <td>Maximum Participation Available</td>
                                                <td>${{Form::text('maximum_amount',$fund->max_participant_fund,['readonly'=>'readonly'])}}</td>
                                            </tr>
                                                <tr>
                                                <td>Total Funded Amount</td>
                                                <td>${{Form::text('funded',$fund->funded,['id'=>'funded_'.$fund->id,'readonly'=>'readonly'])}}</td>
                                            </tr>
                                            <tr>
                                                <td>RTR</td>
                                                <td>

                                                    ${{Form::text('rtr',$fund->rtr,['id'=>'rtr_'.$fund->id,'readonly'=>'readonly'])}}

                                                </td>
                                            </tr>


                                            <tr>
                                                <td>Prepaid</td>
                                                <td>

          {{Form::hidden('prepaid_status',$fund->m_s_prepaid_status,['id'=>'m_s_prepaid_status_'.$fund->id])}} 


          {{Form::hidden('maximum_amount',$maximum_amount,['id'=>'maximum_amount_'.$fund->id])}}  








                  {{Form::text('syndication_fee',$fund->m_syndication_fee,['id'=>'m_syndication_fee_'.$fund->id,'readonly'=>'readonly'])}}



                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Daily Payment</td>
                                                <td>${{Form::text('daily_payment',$fund->payment_amount,['readonly'=>'readonly','id'=>'daily_payment_'.$fund->id])}}</td>
                                            </tr>

                                            </tbody></table>

                                    </div>
                                </div>
                                <div class="SecondSec grid">
                                    <div class="SecondSec-left">
                                        <table class="table">
                                            <tbody>

                                            <tr>
                                                <td>Number of Payments</td>
                                                <td>

                                                   {{Form::text("pmnts",$fund->pmnts,["id"=>"pmnts_".$fund->id,'readonly'=>'readonly'])}}

                                                    </td>
                                            </tr>
                                            <tr>
                                                <td>Factor Rate</td>
                                                <td>




                          {{Form::text('factor_rate',$fund->factor_rate,['id'=>'factor_rate_'.$fund->id,'readonly'=>'readonly'])}}



                                                </td>

                                            </tr>
                                                <tr>

                                              <td><b>Net Value</b></td>

                                                  <td>


   {{ Form::checkbox('gross_value',1,false, ['class'=>'gross_value1','onchange'=>"gross_value_amount($fund->id);"] ) }}



                                                  </td>


                                          <td><b>Your amount</b></td>
                                              <td>

                                                       <span class="input-group-addon">$</span>
                        {!! Form::text('amount',$maximum_amount,['id'=>'amo_'.$fund->id,'onkeyup'=>"this.value = calculatePercentage(this.value,$maximum_amount,this.id);"]) !!}


                   {{Form::hidden('amount_1',0,['id'=>'amo_1_'.$fund->id])}}

                   {{Form::hidden('fund_id',$fund->id,['id'=>'fund_id','class'=>'fund_id'])}}



                                                </td>

                                            </tr>
                                            <td width="100"></td>
                                              <td width="100">

            <span class="input-group-addon">%</span>




           {!! Form::text('amount_per','100',['id'=>'per_'.$fund->id,'onkeyup'=>"this.value = calculateAmount(this.value,$maximum_amount,this.id);"]) !!}   


                                                </td>







                                            </tr>



                                        </tbody></table>
                                    </div>
                                    <div class="SecondSec-right">
                                        <table class="table">
                                            <tbody>






                                            <tr>
                                                <td>Commission Payable (%)</td>
                                                <td>

                                              %{{Form::text('commission',isset($fund->commission)? $fund->commission : old('commission'),['id'=>'commission_'.$fund->id,'readonly'=>'readonly'])}}



                                                    </td>
                                            </tr>
                                            <tr>
                                                <td>Management Fee (%)</td>
                                                <td>

                                            %{{Form::text('mgmnt_fee',$fund->m_mgmnt_fee,['id'=>'mgmnt_fee_'.$fund->id,'readonly'=>'readonly'])}} 



                                            </tr>

                                            <tr>
                                                <td>Underwriting Fee (%)</td>
                                                <td>



                                 %{{Form::text('underwriting_fee',$fund->underwriting_fee,['id'=>'underwriting_fee_'.$fund->id,'readonly'=>'readonly'])}} 


                                            </tr>

                                        </tbody></table>
                                    </div>
                                </div>
                                <div class="thirdSec grid">

                                 <!--    <div class="thirdSec-bid grid">
                                        <label class="control-label">Your Amount</label> -->
                              <!--           <select style="padding: 1px;" class="form-control marktAmount" name="amount" id="funded_sel_{{$fund->id}}" @if($fund->funded == $maximum_amount ) onchange="change_amount(this,{{$fund->id}}) @endif">
                                           <option selected="selected" value="{{$maximum_amount}}">{{$maximum_amount}} | {{FFM::percent($max_per)}}  </option>
                                           @foreach($percentages as $percentage)
                                           @if($maximum_amount > $fund->funded*$percentage/100)
                                           <option value="{{$fund->funded*$percentage/100}}">{{$fund->funded*$percentage/100}} | {{FFM::percent($percentage)}}</option>
                                           @endif
                                           @endforeach

                                       </select> -->

                                   </div>
                                   <div class="thirdSec-btns">


                  <a onclick="confirmation({{$fund->id }})"  class="form-control btn btn-success fund"> Fund</a>     

                   <a class="form-control btn btn-success" href="{{route("investor::marketplace::document",["mid" => $fund->id])}}">View Docs</a>


                                </div>
                            </div>


                     {!! Form::close() !!}


                     @endif
                      @endif
                    @endforeach
            <!--     </ul> -->
            </div>

        </div>
    </div>




    @stop



@section('scripts')
<script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}" type="text/javascript"></script>

<script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}" type="text/javascript"></script>

<script src='{{ asset("js/pie-chart.js")}}' type="text/javascript"></script>

<script type="text/javascript">


  function confirmation(fund_id)
  {
      //alert(fund_id);

       if(confirm("Are you sure that you want to funding this deal for TRI STATE ROOFING?  If you select yes, then you are committing to syndicating in this deal"))
       {    

           amount=$('#amo_'+fund_id).val();
      if(($('.gross_value1').is(":checked")))
        {

            percentage_prepaids2 = percentage_prepaids(fund_id);
            amount = amount *100/(100+percentage_prepaids2)

            $('#amo_1_'+fund_id).val( parseFloat(amount).toFixed(2) );

        }else{
            $('#amo_1_'+fund_id).val(parseFloat(amount).toFixed(2));

        }

          $('#fund_'+fund_id).submit();


       }
        else
        {

           return 0;
        } 



  }

  function percentage_prepaids(fund_id=0){
    var percentage2=0;

   // fund_id = $(".fund_id").val();

    var commission_per= $('#commission_'+fund_id).val();

    if(commission_per)
      percentage2=parseInt(percentage2)+parseInt(commission_per);


    var underwriting_fee_per=$('#underwriting_fee_'+fund_id).val(); 

    if(underwriting_fee_per)
    percentage2=parseInt(percentage2)+parseInt(underwriting_fee_per);

    var m_syndication_fee_per=$('#m_syndication_fee_'+fund_id).val();


    var m_s_prepaid_status=$('#m_s_prepaid_status_'+fund_id).val();


    var factor_rate=$('#factor_rate_'+fund_id).val();
    var prepaid_per=0;


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


  function investment_calculator(amount,reverse=0,fund_id) {

    var total=0;

    percentage_prepaid = percentage_prepaids(fund_id);


    if(reverse)
    {   //1100 *100/110
        total =  ((parseFloat(amount)*100 /(100+parseInt(percentage_prepaid))));

    }
    else
    {
        total = parseFloat(amount)+ ((parseFloat(amount)*parseInt(percentage_prepaid)/100));
    }


        var investment_arr = [];
        investment_arr['total'] =total;
        investment_arr['percentage'] =percentage_prepaid;
       return investment_arr;



  }

  function gross_value_amount(value)
   {


        var total=0;
        var amount=$('#amo_'+value).val();

        if(!amount)
        {
            return null; 
        }

        var merchant_amount = $('#maximum_amount_'+value).val(); 


      if($('.gross_value1').is(":checked") && (amount!=0) ){

           investment_arr = investment_calculator(amount,0,value); 
           total=investment_arr['total'];
           percentage=investment_arr['percentage'];
           var percentage_i = (total/ ( parseFloat(merchant_amount)+merchant_amount*percentage/100) )*100;

            $("#per_"+value).val(percentage_i.toFixed(2)); 

          $('#amo_1_'+value).val( parseFloat(total).toFixed(2) );


     }
     else
     {

           investment_arr = investment_calculator(amount,1,value);
           total=investment_arr['total'];
            percentage=investment_arr['percentage'];
            var percentage_i = (total/merchant_amount)*100;
           $("#per_"+value).val(percentage_i.toFixed(2));

           $('#amo_1_'+value).val( parseFloat(total).toFixed(2) );


     }

          $('#amo_'+value).val(total.toFixed(2));

   }



function calculatePercentage(amount,maxamount,amoid){ 

 var perid = amoid.replace("amo_", "per_");

 var fund_id=amoid.replace("amo_","");

 percentage_prepaids2 = percentage_prepaids(fund_id);


  if(parseInt(amount) < 1 || isNaN(amount)) 
  { var percentage = (1*100)/maxamount;
  $('#'+perid).val(percentage.toFixed(2));
            return 1; 
          }
        else if(parseInt(amount) > maxamount) 
     { 
     $('#'+perid).val(100);
            return maxamount; }
        else {


if(($('.gross_value1').is(":checked")))
{
    var percentage = (amount /  ( parseFloat(maxamount)+(maxamount*percentage_prepaids2/100) ) )*100;

}else{

    var percentage = (amount /  ( parseFloat(maxamount) ) )*100;
}     

        $('#'+perid).val(percentage.toFixed(2));
         return amount;
        }
}

function calculateAmount(percentage,maxamount,perid,commission_per,under_writing_fee_per,factor_rate){ 

   var amoid = perid.replace("per_", "amo_");
   var fund_id = perid.replace("per_","");

  if(parseInt(percentage) < 1 || isNaN(percentage)) 
  { var amount = 1*maxamount/100;
  $('#'+amoid).val(amount.toFixed(2));
            return 1; }
    else if(parseInt(percentage) > 100) 
     { 
    $('#'+amoid).val(maxamount.toFixed(2));
            return 100; }
        else {
       var amount = percentage*maxamount/100;
       var investment_arr = investment_calculator(amount,0,fund_id); 
       total=investment_arr['total'];
      $('#'+amoid).val(total.toFixed(2));
         return percentage;
        }


}







function change_amount(this_f,id) {

    set_factor_rate(this_f,id);
    //set_pmnts(this_f,id);
    // body...
}
        function set_factor_rate(this_f,id)
        {
            /* alert('he'); */
            (this_id)=this_f.id;
                rtr_id = 'rtr_'+id;//this_id.replace("factor_rate", "rtr")
                fund_id ='funded_sel_'+id;// this_id.replace("factor_rate", "funded")
                funded_amount =  $('#'+fund_id).val()?$('#'+fund_id).val():0;


                factor_rate =  $('#factor_rate_'+id).val()?$('#factor_rate_'+id).val():0;

                rtr =  parseFloat(funded_amount)*parseFloat(factor_rate);

                $('#'+rtr_id).val(parseFloat(rtr).toFixed(2));//=33;
                this_f.rtr=rtr;
                set_pmnts(this_f,id);
            } 

            function set_pmnts(this_f2,id)
            {
                   // alert('he');
                   (this_id)=this_f2.id;
                   daily_payment_id = 'daily_payment_'+id; /*this_id.replace("pmnts", "daily_payment")*/
                rtr_id ='rtr_'+id;// this_id.replace("pmnts", "rtr")
                pmnts_id ='pmnts_'+id;// this_id.replace("pmnts", "rtr")
                rtr_amount =  $('#'+rtr_id).val()?$('#'+rtr_id).val():0;
                pmnts =  $('#'+pmnts_id).val()?$('#'+pmnts_id).val():0;
                //alert(funded_amount);
                daily_payment =  (parseFloat(rtr_amount)/parseFloat(pmnts)).toFixed(2);
                daily_payment = isFinite(daily_payment)?daily_payment:0;
                /*alert(daily_payment_id);*/
                $('#'+daily_payment_id).val(daily_payment);//=33;
            }



            $(document).ready(function () 
            {

                $('.demo-pie-1').pieChart({
                    barColor: '#68b828',
                    trackColor: '#eee',
                    lineCap: 'butt',
                    lineWidth: 8,
                    onStep: function (from, to, percent) {
                        $(this.element).find('.pie-value').text(Math.round(percent)+' %');
                        $(this.element).find('.pie-value-pending').text(Math.round(100-percent)+' %');
                    }
                });


            });






        </script>




        @stop
        @section('styles')
        <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">

<style type="text/css">
input[readonly="readonly"] {
      border:0px;
      background:none;

    outline:none;
}


/*Progress bar*/
.pie-title-center {
  display: inline-block;
  position: relative;
  text-align: center;
}

.pie-value {
  display: block;
  position: absolute;
  font-size: 29px;
  height: 40px;
  top: 50%;
  left: 0;
  right: 0;
  margin-top: -50px;
  line-height: 40px;
  }.pie-value {
     display: block;
     position: absolute;
     font-size: 29px;
     height: 40px;
     top: 50%;
     left: 0;
     right: 0;
     margin-top: -50px;
     line-height: 40px;
     }.pie-value-pending {

        display: block;
        position: absolute;
        font-size: 22px;
        height: 40px;
        top: 82%;
        left: 0;
        right: 0;
        margin-top: -50px;
        line-height: 40px;
    }
    .pie-title {
        display: block;
        position: absolute;
        font-size: 14px;
        height: 40px;
        top: 42%;
        left: 0;
        right: 0;
        margin-top: -15px;
        line-height: 40px;
        }.pie-title-pending {
            display: block;
            position: absolute;
            font-size: 14px;
            height: 40px;
            top: 70%;
            left: 0;
            right: 0;
            margin-top: -15px;
            line-height: 40px;
        }
    </style>

    @stop
