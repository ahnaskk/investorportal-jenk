@extends('emails.layouts.header')
@section('content')

<tr>
	<td style=" padding: 10px 50px 10px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size: 22px; font-weight: bold; color: #3d3d3d;">
		Hello 

		@if($status=='payment_mail' || $status=='pending_payment' || $status=='merchant_change_status' || $status=='merchant_api' || $status=='company' || $status=='investor_api') 

		Velocity,

		@elseif($status=='pdf_mail' || $status=='investor')

		{{ $investor_name }}


		@elseif($status=='merchant')

		{{ $merchant_name }}

		@endif

	</td>
</tr>

@if($status=='merchant_change_status' || $status=='pdf_mail' || $status=='pending_payment' || $status=='new_deal' || $status=='merchant' || $status=='merchant_api' || $status=='company' || $status=='investor' || $status=='investor_api')

<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)


		{!! $content !!}


		@endif
	</td>
</tr>


@endif

@if($status=='payment_mail')

<!-- content start here  -->

<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($merchant_name)

		<a href="{{ route('admin::merchants::view', ['id' => $merchant_id]) }}" style="text-decoration:none">
			{{ $merchant_name }}
		</a>

		@endif

	</td>
</tr>



<tr>
	<td style="padding:0 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
		Completed
	</td>
</tr>



<tr>
	<td style="padding:15px 50px 15px; background: #fff; font-size:44px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #e26455; font-weight: bold;">
		@if($complete_per)

		{{ $complete_per }} %

		@endif
	</td>
</tr>

@endif

<tr>
	<td style="padding:0 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
		@if($status=='merchant_change_status' || $status=='pending_payment' || $status=='new_deal')													 

		<a href="{{ route('admin::merchants::view', ['id' => $merchant_id]) }}" style="text-decoration:none">
			<b>View Merchant</b>
		</a>
		@endif
		@if($status=='merchant_api')
		
		<a href="{{URL::to('admin/merchants/view')}}/{{$merchant_id}}" style="text-decoration:none">
			<b>View Merchant</b>
		</a>
		@endif													




	</td>
</tr>

<!-- content end here  -->
@endsection


