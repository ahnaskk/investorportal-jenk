@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)

		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">{{ $content['date_time'] }}</th>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">{{ $content['type'] }}</th>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">{{ $content['count'] }}</th>
				</tr>
			</thead>
			</tbody>
		</table>

		@endif
	</td>
</tr>
@endsection