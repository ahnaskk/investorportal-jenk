@extends('layouts.marketplace.admin_lte')
@section('content')
<meta name="_token" content="{{csrf_token()}}" />
<style type="text/css">            
    .wrapper    { width: 100%; margin: 0 auto; margin: 0 auto; height: auto; padding:0px; border: 1px solid #f0f0f0; padding: 30px; }
    .table      { border-right: 1px solid #ddd; border-bottom:  1px solid #ddd; margin: 5px 0 20px }
    .table td   { border-left: 1px solid #ddd; font-size: 14px; border-top: 1px solid #ddd; padding: 8px 15px; border-collapse: collapse; }
    p           { text-align: justify; margin: 0 0 25px }
    h1          { text-align: center; font-size: 22px; margin: 0; }
    h2          { text-align: center; }
    .kbw-signature { width: 350px; height: 150px;}
    #sig canvas{
        width: 100% !important;
        height: auto;
    }
    .kbw-signature {
      border: 1px solid #eeee !important;
      border-radius: 4px;
      overflow: hidden;
    }
    .signature-wrap { background: #f5fbfd; border-radius: 5px; padding: 25px; width: 100%; border: 1px solid #eee; margin: 0 0 10px }
    .signature-box { position: relative; width: 350px; margin: 2px 0 10px }
    .btn-submit { padding: 5px 20px; color: #fff; font-weight: 700; font-size: 16px; background: #dd4b39; float: right; }
    .message.primary {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      padding: 50px;
      text-align: center;
      color: #666;
      letter-spacing: 0.05em;
      font-size: 16px;
    }
    .sign-close {  
      color: transparent;
      cursor: pointer; 
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
    }

</style>     


<div class="wrapper" style="background: #fff;">  

  <a class="" href="{{URL::to('/investor/marketplace/marketplace')}}"> Back to Marketplace </a>  
    @if(Session::has('flash_message'))
        <div class="alert" style="background-color:#dd4b39;color:#fff">   <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>  {{ Session::get('flash_message') }} </div>
        @endif        
  <h1>EXHIBIT B</h1>
  <h2>PARTICIPATION AGREEMENT</h2>
  <p>
    THIS IS THE OFFER AS DESCRIBED IN THE PARTICIPATION AGREEMENT DATED  <B>{{strtoupper($participant_date)}} </B>
    BETWEEN <B>{{strtoupper($participant)}}</B> AND <B>VELOCITY GROUP USA INC.</B>. RELATING TO A MERCHANT AGREEMENT
    DATED <br><B>{{strtoupper($merchant_date)}} </B> BETWEEN LEAD AND <b>{{strtoupper($merchant)}}</b>.
</p>
<p>
    WHEREAS THE LEAD HAS BEEN REQUESTED BY CLIENT TO PURCHASE CERTAIN FUTURE RECEIVABLES AT THE DESCRIBED TOTAL CONTRACT PURCHASE PRICE ("PENDING PURCHASE"), NOW AND THEREFORE THE LEAD HEREBY OFFERS TO SELL TO PARTICIPANT A CERTAIN PERCENTAGE INTEREST IN THE PENDING PURCHASE ("PARTICIPATION PERCENT") AS DESCRIBED BELOW.           
</p>
<p>
    PLEASE RETURN A SIGNED COPY OF THIS OFFER TO US, INDICATING WHETHER YOU ACCEPT THIS OFFER. IF YOU ACCEPT THE OFFER, PLEASE BE PREPARED TO PAY PARTICIPANT'S PURCHASE PRICE WITHIN TWENTY-FOUR (24) HOURS OF YOUR EXECUTION OF THIS FORM OF PARTICIPATION OR YOU WILL LOSE YOUR PARTICIPATION AGREEMENT, AS DESCRIBED IN THE PARTICIPATION AGREEMENT.    
</p>

<table width="100%">
    <tr>
       <td width="50%"><label>By :</label> VELOCITY GROUP USA INC.</td>
        <td><label>Date :</label> {{$participant_date}}</td>
    </tr>
</table>
<table class="table" width="100%" cellspacing="0" cellpadding="0">
      <tr>
     <td width="50%"><b>Funder (Lead) Name</b></td>
                <td>
                    VELOCITY GROUP USA INC.
                </td>
     </tr>
    <tr>
        <td><b>Merchant DBA</b></td>
        <td>
            {{strtoupper($participant)}}
        </td>

    </tr>
    <tr>
        <td><b>Legal Name</b></td>
        <td>{{strtoupper($business_en_name)}}</td>

    </tr>
    <tr>
    <td><b>Merchant Funding Date:</b></td>
                <td>
                {{$date_funded}}
                </td>
    </tr>
        <tr>
    <td><b>Merchant Cash Advance (Yes/No)</b></td>
                <td>
                {{$mca}}
                </td>
    </tr>
        <tr>
    <td><b>Credit Card Sales Split (Yes/No)</b></td>
                <td>
                {{$credit_card}}
                </td>
    </tr>
    <tr>
        <td><b>Total Contract Purchase Price (Funded Amount)</b></td>      
        <td >{{FFM::dollar($funded)}}</td>      
    </tr>
    <tr>
        <td ><b>Total Contract Receivable ("RTR")</b></td>     
           <td>{{FFM::dollar($rtr)}}</td>

    </tr>
    <tr>
        <td ><b>Factor Rate</b></td>
        <td>{{ $factor_rate }}</td>       
    </tr>
    <tr>
<!--         <td width="50%"><b>Daily  Payment:</b></td> -->
        <td><b>Contract Payment Amount</b></td>
        <td>{{FFM::dollar($daily_payment)}}</td>   
    </tr>
    <tr>
           <tr>
        <td><b>Payment Interval (Daily or Weekly):</b></td>
        <td>  
              @if($advance_type=="daily_ach")
                       DAILY ACH
                        @elseif($advance_type=="weekly_ach")
                       WEEKLY ACH
                        @elseif($advance_type=="credit_card_split")
                       CREDIT CARD SPILT
                        @elseif($advance_type=="variable_ach")
                       VARIABLE ACH
                        @elseif($advance_type=="lock_box")
                        LOCK BOX
                         @elseif($advance_type=="hybrid")
                       HYBRID
                        @endif
                    </td>       
    </tr>
               <tr>
        <td><b>Number of Payments</b></td>
        <td>  {{strtoupper($pmnts)}}</td>       
    </tr>
        <tr>
        <td><b>Estimated Term (Months)</b></td>
        <td>{{$estimated_term_months}}</td>
    </tr>
        </tr>
        <tr>
        <td><b>Upfront Broker Commission $</b></td>
       <td>{{FFM::dollar($upfront_commission)}}</td>
    </tr>
       </tr>
        </tr>
        <tr>
        <td><b>Upfront Broker Commission %</b></td>
        <td>{{FFM::percent($upfront_commission_per)}}</td>  
    </tr>
<!--     <tr>
        <td width="50%"><b>Estimated Turn (Days)</b></td>
        <td>{{FFM::dollar($estimated_turns)}}</td>
    </tr> -->
    <!-- <tr>
        <td width="50%"><b>Upfront Sales Commission (% of Funding Amount)</b></td>
        <td>{{FFM::percent($upfront_commission_per)}}</td>    
    </tr>
    <tr>
        <td width="50%"><b>Participant's Portion of Commission(% of RTR)</b></td>
        <td>{{FFM::dollar($participant_commission)}}</td>    
    </tr>  
    <tr> 
        <td width="50%"><b>Management Fee (% of RTR Amount)</b></td>
        <td>{{FFM::percent($management_fee)}}</td>
    </tr> -->
      <tr>
       <td><b>Management Fee $</b></td>
 <td>{{FFM::dollar($management_fee)}}</td>
    </tr>
    <tr>
       <td><b>Management Fee %</b></td>
        <td>{{ FFM::percent($management_fee_per) }}</td>
    </tr>
           <tr>
        <td><b>Syndication Fee $</b></td>
       <td>{{FFM::dollar($m_syndication_fee)}}</td>
    </tr>
    <tr>
        <td><b>Syndication Fee %</b></td>
         <td>{{ FFM::percent($m_syndication_fee_per) }}</td>
    </tr>
       <tr>
        <td ><b>Underwriting Fee $</b></td>
       <td>{{FFM::dollar($underwriting_fee)}}</td>
    </tr>
    <tr>
        <td ><b>Underwriting Fee %</b></td>
        <td>{{ FFM::percent($underwriting_fee_per) }}</td>
    </tr>
        <tr>
        <td ><b>Participant Purchase Price (Funding Amount)</b></td>
        <td>{{FFM::dollar($participant_funded_amount)}}</td>
    </tr>
    <tr>
        <td ><b>Participant's percentage of Contract</b></td>
        <td>{{FFM::percent($participant_percent)}}</td>
    </tr>
<!--     <tr>
        <td width="50%"><b>Participant RTR:</b></td>
        <td>{{FFM::dollar($participant_rtr)}}</td>
    </tr> -->
        <tr>
        <td> <b>Participant RTR (Gross)</b></td>
          <td>{{FFM::dollar($rtr_gross)}}</td>
    </tr>
        <tr>
        <td><b>Participant RTR (Net)</b></td>
      <td>{{FFM::dollar($rtr_net)}}</td>
    </tr>
         <tr>
        <td ><b> Participant Payment Amount</b></td>
           <td>{{FFM::dollar($payment_amount)}}</td>
    </tr>
          <tr>
        <td ><b> Total Amount Due From Participant</b></td>
        <td>{{FFM::dollar($duetotal)}}</td>
    </tr>
</table>
<!--     <table width="100%">
        <tr>
            <td>
             <b> Total Participation Amount Due:  {{FFM::dollar($duetotal)}}</b>
         </td>

     </tr>
 </table> -->

<p style="margin-top: 50px;">
THE PARTICIPATION INTEREST OFFERED HEREBY IS HIGHLY SPECULATIVE AND INVOLVES A HIGH DEGREE OF RISK. THE LEAD DOES NOT MAKE ANY REPRESENTATION REGARDING THE SUITABILITY AND/OR PROSPECTS OF THIS INVESTMENT APART FROM THOSE EXPLICITLY OUTLINED IN THE PARTICIPATION AGREEMENT. THIS PARTICIPATION SHOULD NOT BE PURCHASED BY ANY INVESTOR WHO CANNOT AFFORD THE LOSS OF THEIR ENTIRE INVESTMENT. </p>
<p>   

    THE INFORMATION CONTAINED IN THIS 'EXHIBIT B: PARTICIPATION AGREEMENT' IS HIGHLY CONFIDENTIAL INFORMATION AS DEFINED IN THE CONFIDENTIALITY AGREEMENT EXECUTED ON <B>{{strtoupper($participant_date)}} </B> BETWEEN Velocity Group USA Inc. AND <B>{{strtoupper($participant)}}</B>, WHICH IS INCORPORATED HEREIN BY REFERENCE AND MADE A 'EXHIBIT B'. 
</p>
<p>  ACKNOWLEDGE AND ACCEPTED UNDER THE TERMS AND CONDITIONS OF THE PARTICIPATION AGREEMENT REFERENCED ABOVE.    
</p>


  {!! Form::open(['route'=>'investor::marketplace::participation_agreement', 'method'=>'POST']) !!}    
  <div class="signature-wrap">
    <label>Signature:</label>
  
    <div class="signature-box">
      <div id="sig"></div>
      <div class="message primary">
        Click Here and Draw Your Signature Here
        <div class="sign-close">×</div>
      </div>
    </div>

    <button id="clear" class="btn btn-danger btn-sm">Clear Signature</button>
    <textarea id="signature64" name="signed" style="display: none"></textarea>
  </div>
  
  <br/><br/>
  <table width="100%"> 
    <tr>
        <td width="50%"><label>By :</label> {{strtoupper($participant)}} </td>   
        <td></td>      
    </tr> 
    <tr>
        <td width="50%"> <label> Date : </label> {{$date_en}}</td>
        <td></td> 
    </tr> 
       <tr>
        <td width="50%"> <label> Server : </label> {{$server}}</td>
        <td><input  value="Save" class="btn btn-submit" type="submit"></td> 
    </tr>     

</table> 


{!! Form::close() !!}  

</div>


@stop


@section('scripts')
<script type="text/javascript" src="{{URL::to('sign/jquery.min.js') }} "></script> 
<link type="text/css" href="{{URL::to('sign/jquery-ui.css')}}" rel="stylesheet"> 
<script type="text/javascript" src="{{URL::to('sign/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{URL::to('sign/jquery.signature.js')}}"></script>  
<link rel="stylesheet" type="text/css" href="{{URL::to('sign/jquery.signature.css')}}">

<script type="text/javascript">
    var sig = $('#sig').signature({syncField: '#signature64', syncFormat: 'PNG'});
    $('#clear').click(function(e) {
        e.preventDefault();
        sig.signature('clear');
        $("#signature64").val('');
    });


    $(document).ready(function(c) {
      $('.sign-close').on('click', function(c){
        $(this).parent().fadeOut('slow', function(c){
        });
      }); 
    });
</script>

@stop


