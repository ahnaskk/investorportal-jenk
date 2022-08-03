@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($totalCount)
		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $totalCount }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Settled Amount:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">
						<table width="100%" border="1" cellspacing="0" cellpadding="0">
							<tr>
								<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Debit:</th>
								<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $debitAcceptedAmount }}</td>
							</tr>
							<tr>
								<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Credit:</th>
								<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $creditAcceptedAmount }}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Processing Amount:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">
						<table width="100%" border="1" cellspacing="0" cellpadding="0">
							<tr>
								<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Debit:</th>
								<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $debitProcessingAmount }}</td>
							</tr>
							<tr>
								<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Credit:</th>
								<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $creditProcessingAmount }}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Returned Amount:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">
						<table width="100%" border="1" cellspacing="0" cellpadding="0">
							<tr>
								<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Debit:</th>
								<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $debitReturnedAmount }}</td>
							</tr>
							<tr>
								<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Credit:</th>
								<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $creditReturnedAmount }}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Date:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $date }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $checked_time }}</td>
				</tr>
			</thead>
		</table>
		@endif
	</td>
</tr>
@endsection
