@extends('emails.layouts.header')
@section('content')
<tr>
	<td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
		@if($content)

		<table width="100%" border="1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Transactions:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $params['count_total'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Payment Transactions:</th>
					<td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">{{ $params['count_payment'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Fee Transactions:</th>
					<td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">{{ $params['count_fee'] }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time:</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ $checked_time }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Settled :</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ FFM::dollar($params['total_settled']) }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Settled Payment:</th>
					<td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">{{ FFM::dollar($params['total_settled_payment']) }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Settled Fees:</th>
					<td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">{{ FFM::dollar($params['total_settled_fee']) }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Rcode :</th>
					<td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">{{ FFM::dollar($params['total_rcode']) }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Rcode Payment :</th>
					<td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">{{ FFM::dollar($params['total_rcode_amount']) }}</td>
				</tr>
				<tr>
					<th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Rcode Fees :</th>
					<td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">{{ FFM::dollar($params['total_rcode_fee']) }}</td>
				</tr>
			</thead>
		</table>

		@endif
	</td>
</tr>
@endsection