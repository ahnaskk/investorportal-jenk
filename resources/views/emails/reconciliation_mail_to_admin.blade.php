@extends('emails.layouts.header')
@section('content')
<tr>
	<td class="logo" style="    
	font-size: 22px;
	/*border-top: 3px solid #f2f1fb;*/
	color: #45485d;
	line-height: 30px;
	padding: 30px 0 0 20px;
	background: #fff;
	font-family: Arial, sans-serif, Helvetica, Verdana;
	font-weight: 700;">
	Hi,
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
	font-family: Arial, sans-serif, Helvetica, Verdana;">
		Merchant  <a href="{{URL::to('admin/merchants/view')}}/{{$merchant_id}}"> {{$merchant_name}}  </a> has requested a reconciliation.


		
	</td>													
</tr>
<tr>
	<td class="content" style="    
	font-size: 16px;
	line-height: 32px;
	color: #000;
	text-align: justify;
	letter-spacing: 0em;
	padding:20px;
	background: #fff;
	font-family: Arial, sans-serif, Helvetica, Verdana;">
			
			<a href="{{URL::to('admin/merchants/view')}}/{{$merchant_id}}" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold; ">View Merchant
			</a>

		
		
	</td>													
</tr>
@endsection



