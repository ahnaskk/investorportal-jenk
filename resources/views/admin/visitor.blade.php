@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
	<h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($title) ? $title : '' }} </h3>
	<a href="#" class="help-link">
		<i class="fa fa-question-circle" aria-hidden="true"></i>
		<div class="tool-tip">{{ isset($title) ? $title : '' }}</div>
	</a>
</div>
{{ Breadcrumbs::render('admin::visitor::index') }}
<div class="col-md-12">
	<div class="box">
		<div class="box-head ">
			@include('layouts.admin.partials.lte_alerts')
		</div>
		<div class="box-body">
			<div class="dataTables_wrapper form-inline dt-bootstrap">
				<div class="row">
					<div class="col-sm-12 table-responsive table-container">
						<table id="table-visitor" class="table table-striped" width=100%>
							<thead>
								<tr>
									<th>Users</th>
									<th>Device</th>
									<th>Platform</th>
									<th>Browser</th>
									<th>IP</th>
									<th>Time</th>
								</tr>
							</thead>
							<tbody>
								@foreach($Visitors as $key => $Visitor)
								<tr     style="@if(isset($onlineUsers[$Visitor->visitor_id])) background-color:#04a65a; @endif">
									<td style="@if(isset($onlineUsers[$Visitor->visitor_id])) color:white !important @endif">{{ $Visitor->Visitor->name }}</td>
									<td style="@if(isset($onlineUsers[$Visitor->visitor_id])) color:white !important @endif">{{ $Visitor->device }}</td>
									<td style="@if(isset($onlineUsers[$Visitor->visitor_id])) color:white !important @endif">{{ $Visitor->platform }}</td>
									<td style="@if(isset($onlineUsers[$Visitor->visitor_id])) color:white !important @endif">{{ $Visitor->browser }}</td>
									<td style="@if(isset($onlineUsers[$Visitor->visitor_id])) color:white !important @endif">{{ $Visitor->ip }}</td>
									<td style="@if(isset($onlineUsers[$Visitor->visitor_id])) color:white !important @endif">{{ FFM::datetime($Visitor->created_at) }}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop
@section('scripts')
<script>
$(document).ready(function () {
	$('.table-container tr').on('click', function () {
		$('#' + $(this).data('display')).toggle();
	});
	$('#table-visitor').DataTable({
		"order"    : [2, 'desc'],
		"stateSave": true,
		"stateSaveCallback": function (settings, data) {
			window.localStorage.setItem("datatable", JSON.stringify(data));
		},
		"stateLoadCallback": function (settings) {
			var data = JSON.parse(window.localStorage.getItem("datatable"));
			if (data) data.start = 0;
			return data;
		}
	});
});
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
