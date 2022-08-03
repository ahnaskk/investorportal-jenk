@extends('emails.layouts.header')
@section('content')
<tr>
	<td class="logo" style="    
		font-size: 22px;
		/*border-top: 3px solid #f2f1fb;*/
		color: #45485d;
		line-height: 30px;
		padding: 30px 0 0;
		background: #fff;
		font-family: Arial, sans-serif, Helvetica, Verdana;
		font-weight: 700;
		text-align: center;">
		Dear {{$merchant_name}},
	</td>													
</tr>

<tr>
	<td class="content" style="    
		font-size: 16px;
		line-height: 32px;
		color: #000;
		text-align: justify;
		letter-spacing: 0em;
		padding:20px 25px;
		background: #fff;
		font-family: Arial, sans-serif, Helvetica, Verdana;"
	>


		As you are aware, pursuant to paragraph 10 of the Future Receivables Sale and Purchase Agreement, you have the right of reconciliation if you have experienced either a decrease or increase in your daily receipts over the past month. If you request a reconciliation, you will be required to provide a copy of your bank statements, credit card processing statements, and pertinent aging report(s) for the reconciliation month at issue, which will be reviewed by Velocity Group USA, Inc. Would you like to request a reconciliation?


		
	</td>													
</tr>



<tr>
	<td class="content" style="padding: 25px 0 60px 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;" align='center'>
		<?php $arr1['merchant_id'] = $arr2['merchant_id'] = $merchant_id;
		$arr1['day'] = $arr2['day'] = $days;
		$arr1['status'] = 1;														
		$arr2['status'] = 0;
		$ser_arr1 = urlencode(serialize($arr1));
		$ser_arr2 = urlencode(serialize($arr2));


		?>
		
		<a href="{{URL::to('reconciliation-status')}}/{{$ser_arr1}}')}}" id='submitMerchant' id="recon_yes" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #56d46f; font-size: 16px; font-weight: bold;  display: inline-block; margin: 0 5px; ">Yes</a>
		<a href="{{URL::to('reconciliation-status')}}/{{$ser_arr2}}" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #f05559; font-size: 16px; font-weight: bold; display: inline-block; margin: 0 5px; ">No</a>
	</td>
</tr>
@endsection


