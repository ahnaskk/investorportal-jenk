@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)

		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">#</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Merchant</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Amount</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Date</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Status</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Response</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Type</th>
				</tr>
			</thead>
			<tbody>
				@php $i=1; @endphp
				@foreach($content as $key => $req)
				<tr>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $i++ }}</td>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;"><a href="{{URL::to('admin/merchants/view')}}/{{$req['merchant_id']}}">{{ $req['merchant_name'] }}</a></td>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ \FFM::dollar($req['payment_amount']) }}</td>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ \FFM::date($req['payment_date']) }}</td>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $req['status'] }}</td>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $req['message'] }}</td>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $req['type'] }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>

		@endif
	</td>
</tr>
@endsection