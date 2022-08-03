<?php use App\Label; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
	<h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
	<a href="#" class="help-link">
		<i class="fa fa-question-circle" aria-hidden="true"></i>
		<div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
	</a>
</div>
{{ Breadcrumbs::render('AdvancePlusInvestments',$Investor) }}
<div class="col-md-12">
	<div class="box">
		<div class="box-body">
			<div class="form-group">
				<div class="filter-group-wrap" >
					<div class="row">
						<div class="col-md-4 report-input">
							<div class="input-group">
								<div class="input-group-text">
									<span class="glyphicon glyphicon-user" aria-hidden="true"></span>
								</div>
								{{Form::select('investor_id',$investors,$investor_id,['wire:model'=>'investor_id','class'=>'form-control js-investor-placeholder-multiple table_change','id'=>'investor_id'])}}
							</div>
							<span class="help-block">Investors</span>
						</div>
						<div class="col-md-4 report-input">
							<div class="input-group">
								{{Form::select('label[]',Label::getLabels(),$labels,['class'=>'form-control js-investor-placeholder-multiple table_change','id'=>'label','multiple'])}}
							</div>
							<span class="help-block">Labels</span>
						</div>
						<div class="col-md-4 report-input">
							<div class="input-group">
								<button type="button" id="fetch_button" class="btn btn-success" name="button">Filter</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="dataTables_wrapper form-inline dt-bootstrap">
				<div class="grid table-responsive">
					<table class="table stripe row-border order-column text-capitalize" id="Datatable" width="100%">
						<thead>
							<tr>
								<th class="fixed_header"> <b> Merchant </b> </th>
								<th class="fixed_header text-right"> <b> Funded </b> </th>
								<th class="fixed_header"> <b> Date </b> </th>
								<th class="text-right">% </th>
								@foreach ($dates as $date)
								<th class="text-right vertical_text" title="{{ $date }}"> <div class="box_rotate">{{ $date }}</div> </th>
								@endforeach
							</tr>
						</thead>
						<tbody>
							@foreach ($data as $merchant_id => $single)
							<tr>
								<td> <a href="{{ route('admin::merchants::view',$merchant_id) }}" target="_blank">{{ $single['Merchant'] }}</a> </td>
								<td class="text-right"> {{ $single['amount'] }} </td>
								<td> {{ $single['date'] }} </td>
								<td class="text-right"> {{ $single['percentage'] }} </td>
								@foreach ($dates as $date)
								<td class="text-right"> {{ isset($single['list'][$date])?$single['list'][$date]:'-' }} </td>
								@endforeach
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@stop
@section('scripts')
<script type="text/javascript" src="{{ asset('js/dataTables.fixedColumns.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/dataTables.fixedHeader.min.js') }}"></script>
<script type="text/javascript">
var Datatable = $('#Datatable').DataTable({
	searching     : false,
	ordering      : false,
	scrollCollapse: true,
	paging        : false,
	fixedHeader   : true,
	fixedColumns  : {
		left: 3,
	}
});
$('#fetch_button').click(function(){
	waterfall_function();
});
function waterfall_function(){
	var investor_id = $('#investor_id').val();
	var labels      = $('#label').val();
	var label       = labels.toString();
	window.location.href="{{ url('admin/reports/AdvancePlusInvestments') }}/"+investor_id+'/'+label;
}
</script>
@stop
@section('styles')
<style media="screen">
th, td { white-space: nowrap; }
.vertical_text {
	writing-mode : vertical-lr;
}
.box_rotate {
	-ms-transform: rotate(211deg); /* IE 9 */
	transform    : rotate(211deg);
	z-index      : 1;
}
.fixed_header{
	z-index   : 2;
	background: #FFF !important;
}
</style>
<link rel="stylesheet" href="{{ asset('css/jquery.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/fixedColumns.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/fixedHeader.dataTables.min.css') }}">
@stop
