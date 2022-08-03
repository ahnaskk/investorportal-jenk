<?php 
use Yajra\DataTables\Html\Column;
$Columns=[];
$Columns[]=Column::make('date')
->title('Date')
->searchable(true)
->className('text-right');
$Columns[]=Column::make('investor_to')
->title('Investor To')
->searchable(true);
$Columns[]=Column::make('amount')
->title('Amount')
->className('text-right')
->searchable(true);
$Columns[]=Column::make('liquidity_change')
->title('Liquidity Change')
->searchable(true)
->className('text-right');
$html = $tableBuilder->columns($Columns);
$html->ajax([
	'url' => route('admin::investors::portfolio::merchant_reassignment',[$single->merchant_id]),
	'type' => 'post',
	'data' => 'function(d){
		d._token      = "'.csrf_token().'";
		d.merchant_id = "'.$single->merchant_id.'";
		d.investor_id = "'.$investor_id.'";
	}'
]);
$html->parameters([
	'searching' => true,
	'lengthMenu'=> [[10,50,100,'-1'],[10,50,100,'All']],
	"pageLength"=> 50,
	'order'     => [[1, 'asc']],
	// 'dom'       => 'Bfrtip',
	// 'buttons'   => ['pageLength'],
]);
?>
@extends('layouts.plane_layout.app')
@section('content')
<div class="ibox">
	<div class="ibox-content">
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					{!! $html->table(['class' => 'table table-striped table-bordered', 'id' => 'paymentInvestorDataTables-'.$single->merchant_id]) !!}
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
	var rahees=window.LaravelDataTables['paymentInvestorDataTables-{{$single->merchant_id}}'];
	$('.single_table_change').change(function(){
		rahees.draw();
	});
});
</script>
@endsection
