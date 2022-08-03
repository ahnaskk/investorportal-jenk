@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:15px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)

		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Api Name:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['api_name'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Method:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['method'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Request :</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['request'] }}</td>
				</tr>
				
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Response:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['response'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Created At:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $content['created_at'] }}</td>
				</tr>
				
			</thead>
		</table>

		@endif
	</td>
</tr>
@endsection