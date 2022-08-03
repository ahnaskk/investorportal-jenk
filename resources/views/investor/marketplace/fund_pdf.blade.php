<html>
        <head>
            <title>::: PARTICIPATION AGREEMENT :::</title>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
            <style type="text/css">
                body { 
                    margin: 0;
                    padding: 0;
                    font-family: Arial;
                    font-size: 14px;
                    line-height: 22px
                }
                .wrapper    { 
                    width: 100%;
                    /* margin: 0 auto; */
                    height: auto;
                    padding:0px;
                    border: 1px solid #f0f0f0;
                    padding: 30px;
                }
                .table      { border-right: 1px solid #ddd; border-bottom:  1px solid #ddd; margin: 5px 0 20px }
                .table td   { border-left: 1px solid #ddd; font-size: 14px; border-top: 1px solid #ddd; padding: 2px 15px; border-collapse: collapse; }
                p           { text-align: justify; margin: 0 0 25px }
                h1          { text-align: center; font-size: 22px; margin: 0; }
                h2          { text-align: center; }
            </style>     
        </head>
        <div class="wrapper">            
            <h1>EXHIBIT B</h1>
            <h2>PARTICIPATION AGREEMENT</h2>
         <p>
                THIS IS THE OFFER AS DESCRIBED IN THE PARTICIPATION AGREEMENT DATED  <B>{{strtoupper($participant_date)}} </B>
        BETWEEN <B>{{strtoupper($participant)}}</B> AND <B>VELOCITY GROUP USA INC.</B> RELATING TO A MERCHANT AGREEMENT
        DATED <B>{{strtoupper($merchant_date)}} </B> BETWEEN LEAD AND <b>{{strtoupper($merchant)}}</b>.
        </p>
         <p>
            WHEREAS THE LEAD HAS BEEN REQUESTED BY CLIENT TO PURCHASE CERTAIN FUTURE RECEIVABLES AT THE DESCRIBED TOTAL CONTRACT PURCHASE PRICE ("PENDING PURCHASE"), NOW AND THEREFORE THE LEAD HEREBY OFFERS TO SELL TO PARTICIPANT A CERTAIN PERCENTAGE INTEREST IN THE PENDING PURCHASE ("PARTICIPATION PERCENT") AS DESCRIBED BELOW.           
        </p>
        <p>
            PLEASE RETURN A SIGNED COPY OF THIS OFFER TO US, INDICATING WHETHER YOU ACCEPT THIS OFFER. IF YOU ACCEPT THE OFFER, PLEASE BE PREPARED TO PAY PARTICIPANT'S PURCHASE PRICE WITHIN TWENTY-FOUR (24) HOURS OF YOUR EXECUTION OF THIS FORM OF PARTICIPATION OR YOU WILL LOSE YOUR PARTICIPATION AGREEMENT, AS DESCRIBED IN THE PARTICIPATION AGREEMENT.  
        </p>
         <table width="100%">
        <tr>
      <td width="60%"><label>By :</label> VELOCITY GROUP USA INC.</td>
        <td><label>Date :</label> {{$participant_date}}</td>
    </tr> 
      </table>
    
        <table class="table" width="100%" cellspacing="0" cellpadding="0">
                <tr>
      <td width="60%"><b>Funder (Lead) Name</b></td>
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
        <td>{{FFM::dollar($funded)}}</td>      
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
        {{-- <td width="50%"><b>Daily  Payment:</b></td> --}}
        <td><b>Contract Payment Amount</b></td>
        <td>{{FFM::dollar($daily_payment)}}</td>   
    </tr>
   
        <tr>
        <td><b>Payment Interval (Daily or Weekly):</b></td>
        <td>         @if($advance_type=="daily_ach")
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
                        @endif</td>       
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
          <tr>
       <td><b>Management Fee $</b></td>
       <td>{{FFM::dollar($management_fee)}}</td>
    </tr>
    </table>
   <table class="table" width="100%" cellspacing="0" cellpadding="0">
    {{-- <tr>
        <td width="50%"><b>Estimated Turn (Days)</b></td>
        <td>{{FFM::dollar($estimated_turns)}}</td>
    </tr> 
    <tr>
        <td width="50%"><b>Upfront Sales Commission (% of Funding Amount)</b></td>
        <td>{{FFM::percent($upfront_commission)}}</td>    
    </tr>
    <tr>
        <td width="50%"><b>Participant's Portion of Commission(% of RTR)</b></td>
        <td>{{FFM::dollar($participant_commission)}}</td>    
    </tr>  
    <tr> 
        <td width="50%"><b>Management Fee (% of RTR Amount)</b></td>
        <td>{{FFM::percent($management_fee)}}</td>
    </tr> --}}

    <tr>
       <td width="60%"><b>Management Fee %</b></td>
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
       <td>{{ FFM::dollar($underwriting_fee) }}</td>
    </tr>
    <tr>
        <td ><b>Underwriting Fee %</b></td>
        <td>{{ FFM::percent($underwriting_fee_per) }}</td>
    </tr>
        <tr>
        <td ><b>Participant Purchase Price (Funding Amount)</b></td>
        <td>{{FFM::dollar($participant_funded_amount)}}</td>
    </tr>
    <tr>00:00:00
        <td ><b>Participant's percentage of Contract</b></td>
        <td>{{FFM::percent($participant_percent)}}</td>
    </tr>
    {{-- <tr>
        <td width="50%"><b>Participant RTR:</b></td>
        <td>{{FFM::dollar($participant_rtr)}}</td>
    </tr> --}}
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
    

     
       <p style="margin-top: 50px;">
        THE PARTICIPATION INTEREST OFFERED HEREBY IS HIGHLY SPECULATIVE AND INVOLVES A HIGH DEGREE OF RISK. THE LEAD DOES NOT MAKE ANY REPRESENTATION REGARDING THE SUITABILITY AND/OR PROSPECTS OF THIS INVESTMENT APART FROM THOSE EXPLICITLY OUTLINED IN THE PARTICIPATION AGREEMENT. THIS PARTICIPATION SHOULD NOT BE PURCHASED BY ANY INVESTOR WHO CANNOT AFFORD THE LOSS OF THEIR ENTIRE INVESTMENT. </p>
        <p>   

        THE INFORMATION CONTAINED IN THIS 'EXHIBIT B: PARTICIPATION AGREEMENT' IS HIGHLY CONFIDENTIAL INFORMATION AS DEFINED IN THE CONFIDENTIALITY AGREEMENT EXECUTED ON <B>{{strtoupper($participant_date)}} </B> BETWEEN <b> VELOCITY GROUP USA INC. </b> AND <B>{{strtoupper($participant)}}</B>, WHICH IS INCORPORATED HEREIN BY REFERENCE AND MADE A 'EXHIBIT B'. 
    </p>
    <p>  ACKNOWLEDGE AND ACCEPTED UNDER THE TERMS AND CONDITIONS OF THE PARTICIPATION AGREEMENT REFERENCED ABOVE.    
    </p>
    


        <table width="100%">
            <tr>
                <td width="50%"> <img src="{{$signature}}" /></td>
            </tr>
            </table>
             <table width="100%"> 
                <tr>
            <td width="50%"><label>By :</label> {{strtoupper($participant)}} </td>         
        </tr> 
            <tr>
            <td width="50%"> <label>Date :</label> {{$date_en}}</td>
        </tr>      
          <tr>
            <td width="50%"> <label>Server :</label> {{$server}}</td>
        </tr>      

    </table> 
</div>
</body>
</html>