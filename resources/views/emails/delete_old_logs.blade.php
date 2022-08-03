@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)

		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<tr>
			<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">{{ $title }} before {{ $content['date'] }}</th>
				<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">{{ $content['delete_count'] }}</th>
			</tr>
			<tr>
				<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">{{ $title }} deleted</th>
				<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">{{ $content['deleted_count'] }}</th>
			</tr>
		</table>

		@endif
	</td>
</tr>
@endsection