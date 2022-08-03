@extends('layouts.admin.admin_lte')

@section('content')
    <?PHP
    $fee=[
    '0'=>'0'
    ,'0.5'=>'0.5'
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

    $percentages =['100','95','90','85','80','75','70','65','60','55','50','45','40','35','30','25','20','15','10'];

    $commissions=[1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15];

    ?>

            @include('layouts.admin.partials.lte_alerts')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Marketplace</h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Marketplace</div>     
      </a>

</div>


    <div class="col-md-12">
        <div class="box">
        <div class="wrap"></div>
        <div class="grid">
           <div class="box-body"> 
            <div class="filter-group-wrap form-box-styled">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <div class="input-group">
                    <div class="margtCardFiltr">

                        <select onchange="javascript:location.href = this.value;" style="padding: 1px;" class="form-control">
                        <option selected="selected" value="">Select</option>
                          <option value="?filter=0">All</option>
                          <option {{$filter_id==1?'Selected':''}} value="?filter=1" >Partially Funded</option>
                          <option {{$filter_id==2?'Selected':''}} value="?filter=2" >Not Funded</option>
                        </select>
                    </div>
                    </div>
                    <span class="help-block">Filter</span>
                </div>
            </div>
        </div>
       <?php $flag=0; ?>
        <ul class="marktPlcUl">

            @if(empty($funds))


                    <h3> No Details Found </h3>


            @endif


            <?php //print_r($funds->toArray()) ?>


           @foreach($funds as $fund) 


           @if($fund->max_participant_fund > $fund->marketplaceInvestors()->sum('amount'))
           <?PHP
           $flag=1;
           $maximum_amount=$fund->max_participant_fund - $fund->marketplaceInvestors()->sum('amount');
           $max_per=($fund->funded > 0) ? ($maximum_amount/$fund->funded)*100 : 0;
           $maximum_amount=$fund->max_participant_fund - $fund->marketplaceInvestors()->sum('amount');  
           ?>
           <li>

           {!! Form::open(['route'=>'investor::marketplace::funds_request', 'method'=>'POST']) !!}
           <input type="hidden" name="id" value="{{$fund->id}}" >
           <div class="marktPlceCard">
                <div class="marktPlceCardTitle">
                    <div class="col-md-12 col-sm-12">{{$fund->name}}</div>
                </div>
                <div class="wrap"></div>
                <div class="firstSec grid">
                    <div class="markt-diagram">
                        <div id="demo-pie-1" class="pie-title-center demo-pie-1 diagrm" data-percent="{{100-$max_per}}">
                        <!--     <canvas height="150" width="150"></canvas>  -->

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
                                                <td>Pre-Paid</td>
                                                <td>



${{Form::text('syndication_fee',$fund->m_syndication_fee,['id'=>'m_syndication_fee_'.$fund->id,'readonly'=>'readonly'])}}

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

                                                    {{($fund->pmnts)}}

                                                    </td>
                                            </tr>
                                            <tr>
                                                <td>Factor rate</td>
                                                <td>


                                                 {{Form::text('factor_rate',$fund->factor_rate,['id'=>'factor_rate_'.$fund->id,'readonly'=>'readonly'])}}


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



                                            {{Form::text('commission',$fund->commission,['id'=>'commission_'.$fund->id,'readonly'=>'readonly'])}} %


                                                    </td>
                                            </tr>
                                            <tr>
                                                <td>Management Fee (%)</td>
                                                <td>


                                           {{Form::text('mgmnt_fee',$fund->m_mgmnt_fee,['id'=>'mgmnt_fee_'.$fund->id,'readonly'=>'readonly'])}}        
                                           %


                                               </td>
                                            </tr>

                                             <tr>

                                                <td>Underwritting Fee (%)</td>
                                                <td>
                                               {{Form::text('underwriting_fee',$fund->underwriting_fee,['id'=>'underwriting_fee_'.$fund->id,'readonly'=>'readonly'])}}           
                                               %
                                                     </td>
                                            </tr>

                                        </tbody></table>
                                    </div>
                                </div>
                                <div class="thirdSec grid">
                                    <div class="thirdSec-bid grid">
                                        <label class="control-label">Your Amount</label>
                                        <select style="padding: 1px;" class="form-control marktAmount" name="amount" id="funded_sel_{{$fund->id}}" @if($fund->funded == $maximum_amount ) onchange="change_amount(this,{{$fund->id}}) @endif">
                                           <option selected="selected" value="{{$maximum_amount}}">{{$maximum_amount}} | {{FFM::percent($max_per)}}  </option>
                                           @foreach($percentages as $percentage)
                                           @if($maximum_amount > $fund->funded*$percentage/100)
                                           <option value="{{$fund->funded*$percentage/100}}">{{$fund->funded*$percentage/100}} | {{FFM::percent($percentage)}}</option>
                                           @endif
                                           @endforeach

                                       </select>

                                   </div>
                                   <div class="thirdSec-btns">


                                    <!-- <input onclick="return confirm(&quot;Are you sure that you want to funding this deal for TRI STATE ROOFING?  If you select yes, then you are committing to syndicating in this deal&quot;)" class="form-control btn btn-success" value="Fund" type="submit"> -->


                                    <!-- <a class="form-control btn btn-success" href="{{route("admin::document",["mid" => $fund->id])}}">View Docs</a> -->
                                </div>
                            </div>
                        </div>

                     {!! Form::close() !!}
                    </li>
                    @endif 
                    @endforeach
                      @if($flag==0 && !empty($funds))
                <div class="col-md-12">
                     <div class="box box-primary">
                    <h3> No Details Found </h3>
                    </div>
                    </div>
            @endif

                </ul>
            </div> 
            </div> 
            </div> 


    @stop



@section('scripts')
<script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}" type="text/javascript"></script>

<script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}" type="text/javascript"></script>

<script src='{{ asset("js/pie-chart.js")}}' type="text/javascript"></script>

<script type="text/javascript">


/*$(document).ready(function () {
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
*/

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
          <link href="{{ asset('/css/optimized/marketplace.css?ver=5') }}" rel="stylesheet" type="text/css" />

    @stop
