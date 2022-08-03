<?php 
use Yajra\DataTables\Html\Column;
$Columns=[];
$Columns[]=Column::make('Investor')
->title('Investor')
->searchable(true)
->width("10%");
$Columns[]=Column::make('Expected')
->title('Expected')
->width("10%")
->className('text-right')
->searchable(true);
$Columns[]=Column::make('Given')
->title('Given')
->searchable(true)
->className('text-right')
->width("30%");
$Columns[]=Column::make('Diffrence')
->title('Diffrence')
->searchable(true)
->className('text-right')
->width("10%");
$html = $tableBuilder->columns($Columns);
$html->ajax([
	'url' => route('Merchant::Payment::ExpectationVsGivenData::SingleTableData',[$ParticipentPayment["id"]]),
	'type' => 'post',
	'data' => 'function(d){
		d._token      = "'.csrf_token().'";
		d.merchant_id = "'.$ParticipentPayment["id"].'";
	}'
]);
$html->parameters([
	'searching' => false,
	'lengthMenu'=> [[10,50,100,'-1'],[10,50,100,'All']],
	"pageLength"=> 50,
	'order'     => [[1, 'desc']],
	'dom'       => 'Bfrtip',
	'buttons'   => ['colvis', 'pageLength'],
	'footerCallback' => "function(t,o,a,l,m){
		var n=this.api(),o=window.LaravelDataTables['paymentInvestorDataTables-".$ParticipentPayment["id"]."'].ajax.json();
		$(n.column(0).footer()).html('Total');
		$(n.column(1).footer()).html(o.Total_expected_participant_share);
		$(n.column(2).footer()).html(o.Total_participant_share);
		$(n.column(3).footer()).html(o.Total_Diffrence);
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
					{!! $html->table(['class' => 'table table-striped table-bordered', 'id' => 'paymentInvestorDataTables-'.$ParticipentPayment["id"]],$footer=true) !!}
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
	var rahees=window.LaravelDataTables['paymentInvestorDataTables-{{$ParticipentPayment["id"]}}'];
	$('.single_table_change').change(function(){
		rahees.draw();
	});
});
</script>
@endsection
