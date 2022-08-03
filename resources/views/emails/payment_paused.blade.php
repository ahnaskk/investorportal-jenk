@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)
			<a href="{{ url('admin/merchants/view/'.$content['merchant_id']) }}"> {{ $content['merchant_name'] }}'s </a> ACH payment has been paused @if($content['rcode']) due to Rcode - ({{ $content['rcode']->code }}) @else manually by @endif {{ $content['data']->paused_by }}  on {{ \FFM::datetime($content['data']->paused_at) }}.
		@endif
	</td>
</tr>
@endsection