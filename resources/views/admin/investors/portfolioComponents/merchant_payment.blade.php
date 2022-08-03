<?php 
use Yajra\DataTables\Html\Column;
$Columns=[];
$Columns[]=Column::make('payment_date')
->title('Date')
->searchable(true);
$Columns[]=Column::make('payment')
->title('Payment')
->className('text-right')
->searchable(true);
$Columns[]=Column::make('participant_share')
->title('Participant Share')
->className('text-right')
->searchable(true);
$Columns[]=Column::make('mgmnt_fee')
->title('Management Fee')
->searchable(true)
->className('text-right');
$Columns[]=Column::make('net_amount')
->title('Net Amount')
->searchable(true)
->className('text-right');
$Columns[]=Column::make('overpayment')
->title('Overpayment')
->searchable(true)
->className('text-right');
$Columns[]=Column::make('principal')
->title('Principal')
->searchable(true)
->className('text-right');
$Columns[]=Column::make('profit')
->title('Profit')
->searchable(true)
->className('text-right');
$Columns[]=Column::make('balance')
->title('Balance')
->searchable(true)
->className('text-right');
$html = $tableBuilder->columns($Columns);
$html->ajax([
	'url' => route('admin::investors::portfolio::merchant_payment',[$single->id]),
	'type' => 'post',
	'data' => 'function(d){
		d._token      = "'.csrf_token().'";
		d.merchant_id = "'.$single->id.'";
		d.investor_id = "'.$single->user_id.'";
	}'
]);
$html->parameters([
	'searching' => true,
	'lengthMenu'=> [[10,50,100,'-1'],[10,50,100,'All']],
	"pageLength"=> 50,
	'order'     => [[8, 'asc']],
	// 'dom'       => 'Bfrtip',
	// 'buttons'   => ['pageLength'],
	'footerCallback' => "function(t,o,a,l,m){
		var n=this.api(),o=window.LaravelDataTables['paymentInvestorDataTables-".$single->id."'].ajax.json();
		$(n.column(0).footer()).html('Total');
		// $(n.column(1).footer()).html(o.Total_expected_participant_share);
		// $(n.column(2).footer()).html(o.Total_participant_share);
		// $(n.column(3).footer()).html(o.Total_Diffrence);
	}",
]);
?>
@extends('layouts.plane_layout.app')
@section('content')
<div class="ibox">
	<div class="ibox-content">
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					{!! $html->table(['class' => 'table table-striped table-bordered', 'id' => 'paymentInvestorDataTables-'.$single->id]) !!}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('style')
@endsection
@section('script')
{!! $html->scripts() !!}
<script type="text/javascript">
$(document).ready(function(){
	var rahees=window.LaravelDataTables['paymentInvestorDataTables-{{$single->id}}'];
	$('.single_table_change').change(function(){
		rahees.draw();
	});
});
</script>
@endsection
