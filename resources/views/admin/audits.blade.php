<?php use App\Settings; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
	<h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($title) ? $title : 'Audit Report' }} </h3>
	<a href="#" class="help-link">
		<i class="fa fa-question-circle" aria-hidden="true"></i>
		<div class="tool-tip">{{ isset($title) ? $title : 'Audit Report' }}</div>
	</a>
</div>
<div class="col-md-12">
	<div class="box">
		<div class="box-body">
			<div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
				<div class="row">
					<div class="col-sm-10"></div>
					<div class="row">
						<div class="col-sm-12 table-responsive">
							<table class="table table-list-search table-bordered text-capitalize" >
								<?php foreach ($ActualModel as $key => $value): ?>
									<tr>
										<th>{{$key}}</th>
										<th>{{$value}}</th>
									</tr>
								<?php endforeach; ?>
							</table>
						</div>							
					</div>
					<div class="row">
						<div class="col-sm-12 table-responsive">
							<table class="table table-list-search table-bordered text-capitalize" >
								<thead class="thead-dark">
									<tr>
										<th scope="col">Model</th>
										<th scope="col">Action</th>
										<th scope="col">User</th>
										<th scope="col">Time</th>
										<th scope="col">url</th>
										<th scope="col">Old Values</th>
										<th scope="col">New Values</th>
									</tr>
								</thead>
								<tbody id="audits">
									@foreach($audits as $audit)
									<tr>
										<td>{{ $audit->auditable_type }} (id: {{ $audit->auditable_id }})</td>
										<td>{{ $audit->event }}</td>
										<td>{{ $audit->user->name }}</td>
										<td>{{ $audit->created_at }}</td>
										<td>{{ $audit->url }}</td>
										<td>
											<table class="table table-list-search table-bordered text-capitalize">
												@foreach($audit->old_values as $attribute => $value)
												<tr>
													<td><b>{{ $attribute }}</b></td>
													<td>{{ $value }}</td>
												</tr>
												@endforeach
											</table>
										</td>
										<td>
											<table class="table table-list-search table-bordered text-capitalize">
												@foreach($audit->new_values as $attribute => $value)
												<tr>
													<td><b>{{ $attribute }}</b></td>
													<td>{{ $value }}</td>
												</tr>
												@endforeach
											</table>
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
</div>
@endsection
@section('scripts')
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="text/javascript">
Swal.fire('info!', 'This is Only For Debugging Purposes', 'info');
</script>
@stop
@section('styles')
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
@stop
