@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)
			 <a href="{{ url('admin/merchants/view/'.$content['merchant_id']) }}"> {{ $content['merchant_name'] }} 's </a> ACH payment resumed manually by {{ $content['data']->resumed_by }} on {{ \FFM::datetime($content['data']->resumed_at) }}.
		@endif
	</td>
</tr>
@endsection