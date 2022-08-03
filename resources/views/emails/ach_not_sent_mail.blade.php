@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)

		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Next working day:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['ach_date'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked at:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['checked_time'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total ACH sent:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['count'] }}</td>
				</tr>
				@if($content['type'])
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Processing ACH:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['processing'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Declined ACH:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['declined'] }}</td>
				</tr>
				@endif
			</thead>
		</table>

		@endif
	</td>
</tr>
@endsection