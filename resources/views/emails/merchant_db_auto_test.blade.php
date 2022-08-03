@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)

		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Name</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">IDs</th>
				</tr>
			</thead>
			<tbody>
				@if($content['last_payment'] != null)
				<tr>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">Last Payment Date is null</td>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['last_payment'] }}</td>
				</tr>
				@endif
				@if($content['last_status'])
				<tr>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">Last Status Updated Date is null</td>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['last_status'] }}</td>
				</tr>
				@endif
			</tbody>
		</table>

		@endif
	</td>
</tr>
@endsection