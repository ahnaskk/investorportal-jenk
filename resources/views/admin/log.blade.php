@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
	<h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($title) ? $title : '' }} </h3>
	<a href="#" class="help-link">
		<i class="fa fa-question-circle" aria-hidden="true"></i>
		<div class="tool-tip">{{ isset($title) ? $title : '' }}</div>
	</a>
</div>
{{ Breadcrumbs::render('admin::logs::index') }}
<div class="col-md-12">
	<div class="box">
		<div class="box-head ">
			@include('layouts.admin.partials.lte_alerts')
		</div>
		<div class="box-body">
			<div class="dataTables_wrapper form-inline dt-bootstrap">
				<div class="row">
					<div class="col-sm-9">
					</div>
					<div class="col-sm-3">
						@if($current_file)
						<a href="{{ route('Log::download').'/?download='.base64_encode($current_file) }}"> <span class="glyphicon glyphicon-download-alt"></span> Download file </a> -
						<a id="delete-log" href="{{ route('Log::delete').'?del='.base64_encode($current_file) }}"> <span class="glyphicon glyphicon-trash"></span> Delete file </a> -
						@if(count($files) > 1)
						- <a id="delete-all-log" href="{{ route('Log::deleteAll').'?delall=true' }}"> <span class="glyphicon glyphicon-trash"></span> Delete all file </a>
						@endif
						@endif
					</div>
					<br>
					<br>
				</div>
				<div class="row">
					<div class="col-md-4">
						<div class="list-group">
							@foreach($files as $file)
							<a href="?log={{ base64_encode($file) }}" class="list-group-item @if ($current_file == $file) llv-active @endif">
								<i class="voyager-file-text"></i> {{$file}}
							</a>
							@endforeach
						</div>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12 table-responsive table-container">
						<table id="table-log" class="table table-striped" width=100%>
							<thead>
								<tr>
									<th>Type</th>
									<th>Environment</th>
									<th width="15%">Date</th>
									<th>Content</th>
								</tr>
							</thead>
							<tbody>
								@foreach($logs as $key => $log)
								<tr data-display="stack{{{$key}}}">
									<td class="text-{{{$log['level_class']}}} level">
										<span class="glyphicon glyphicon-{{{$log['level_img']}}}-sign" aria-hidden="true"></span>
										{{$log['level']}}
									</td>
									<td class="text">{{$log['environment']}}</td>
									<td class="date">{{ FFM::datetime($log['date']) }}</td>
									<td class="text">
										@if($log['stack'])
										<a class="pull-right expand btn btn-default btn-xs" data-display="stack{{{$key}}}">
											<span class="glyphicon glyphicon-search"></span>
										</a>
										@endif
										{{{$log['text']}}}
										@if(isset($log['in_file'])) <br/>{{{$log['in_file']}}}@endif
										@if($log['stack'])
										<div class="stack" id="stack{{{$key}}}" style="display: none; white-space: pre-wrap;">
											{{{ trim($log['stack']) }}}
										</div>
										@endif
									</td>
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
	$('#table-log').DataTable({
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
	$('#delete-log, #delete-all-log').click(function () {
		return confirm('Are you sure');
	});
});
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
