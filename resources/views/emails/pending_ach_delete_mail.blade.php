@extends('emails.layouts.header')
@section('content')
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


		There are {{$totalCount}} Investor ACH processing transactions for more than 9 days as of {{$date}}. Click the following link to delete it. 


		
	</td>													
</tr>



<tr>
	<td class="content" style="padding: 25px 0 60px 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;" align='center'>

		<a href="{{$confirm_url}}" id='submitMerchant' id="recon_yes" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #56d46f; font-size: 16px; font-weight: bold;  display: inline-block; margin: 0 5px; ">Delete</a>
	</td>
</tr>
@endsection


