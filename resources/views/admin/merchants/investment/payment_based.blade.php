<?php use App\Settings; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
	<h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
	<a href="#" class="help-link">
		<i class="fa fa-question-circle" aria-hidden="true"></i>
		<div class="tool-tip">{{isset($page_title)?$page_title:''}} </div>     
	</a>
</div>
{{ Breadcrumbs::render('AssignInvestorsBasedOnLiquidity',$merchant) }}
@include('layouts.admin.partials.lte_alerts')
<div class="col-md-12">
	<div class="box box-primary">
		<div class="box-body">
			{!! Form::open(['route'=>'admin::merchants::Investment::PaymentBased::Assign', 'method'=>'POST']) !!}
			<input type='hidden' name='merchant_id' id="merchant_id" value='{{$merchant_id}}'>
			<div class="form-group">
				<div class="col-md-4">
					{{Form::select('company',[0=>'All']+$companies,0,['class'=>'form-control','id'=>'company','placeholder'=>'Select Company'])}}
					<label>Company</label>
				</div>
				<div class="col-md-1">
					<input type="button" id="unselect" name="unselect" value="Unselect" class="btn btn-info">
				</div>
				<div class="col-md-2">
					<input type="button" id="select_all" name="select_all" value="Select All Investors" class="btn btn-success">
				</div>
			</div>
			<div class="form-group">
				<label class="text-right"><b> M - Management Fee , S - Syndication Fee , P - Pre-paid Status </b></label>
				<div class="form-group">
					<label>Investors</label>
					<select id="investors" name="all_investors[]" class="form-control" multiple="multiple">
						@if($all_auto_investors)
						@foreach($all_auto_investors as $investor)
						<?php
						$status          = ($investor->s_prepaid_status == 1)?'(RTR)':'(Amount)';
						$status2         = ($merchant->s_prepaid_status == 1)?'(RTR)':'(Amount)';
						$pre_paid        = ($investor->global_syndication !=0)? '(P)'. $status: '(P)' . $status2;
						$syndication_fee = (!is_null($investor->global_syndication))? '(Investor) -'. $investor->global_syndication . '- '. $pre_paid : ' (Merchant)- '. $merchant->m_syndication_fee ;
						$management_fee  = (!is_null($investor->management_fee))? '(Investor) -'. $investor->management_fee: ' (Merchant)-' . $merchant->m_mgmnt_fee;
						?>
						<option value="{{ $investor->id }}" <?php if(in_array($investor->id,$auto_investors)) { echo "selected";} ?>>
							{{ $investor->name }} - {{ $syndication_fee }} (S) - {{ $management_fee }} (M)
						</option>
						@endforeach
						@endif
					</select>
				</div>
				<div class="form-group">
					<div class="col-md-4">
						<table class="table table-bordered">
							<tr>
								<th>Merchant</th>
								<td class="text-right" id="Merchant_name">{{ $merchant->name }}</td>
							</tr>
							<tr>
								<th>Label</th>
								<td class="text-right" id="Merchant_Label">{{ $merchant->Label->name }}</td>
							</tr>
							<tr>
								<th>Funded</th>
								<td class="text-right" id="Merchant_funded">{{FFM::dollar($merchant->funded)}}</td>
							</tr>
							<tr>
								<th>Factor Rate</th>
								<td class="text-right" id="Merchant_funded">{{round($merchant->factor_rate,4)}}</td>
							</tr>
							<tr>
								<th>RTR</th>
								<td class="text-right" id="Merchant_rtr">{{FFM::dollar($merchant->rtr)}}</td>
							</tr>
							<tr>
								<th>Payment</th>
								<td class="text-right" id="payment_amount">{{FFM::dollar(0)}}</td>
							</tr>
							<tr>
								<th>Net Investment</th>
								<td class="text-right" id="net_investment">{{FFM::dollar(0)}}</td>
							</tr>
							<tr>
								<th>Invested</th>
								<td class="text-right" id="investment">{{FFM::dollar(0)}}</td>
							</tr>
						</table>
					</div>
					<div class="col-md-5">
						<table class="table table-bordered" id="CompanyShareDataTable">
							<thead>
								<tr>
									<th>Title</th>
									<th>Company Share</th>
									<th>Max Participant</th>
								</tr>
							</thead>
							<tbody>
								<tfoot>
									<tr>
										<th colspan="2">Total</th>
										<th>0</th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col-md-10">
							<div class="col-md-2 report-input">
								<div class="input-group">
									<div class="input-group-text">
										<span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
									</div>
									<input class="form-control from_date1 datepicker" id="date_start1"  value="{{$merchant->date_funded}}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"  required="required"/>
									<input type="hidden" class="date_parse" name="date_start" id="date_start" value="{{$merchant->date_funded}}">
								</div>
								<span class="help-block">From Date <font color="#FF0000"> * </font></span>
							</div>
							<div class="col-md-2 report-input">
								<div class="input-group">
									<div class="input-group-text">
										<span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
									</div>
									<input class="form-control to_date1 datepicker" id="date_end1" value="{{date('Y-m-d',strtotime($merchant->date_funded.'+ 1 week'))}}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off" required="required"/>
									<input type="hidden" class="date_parse" name="date_end" id="date_end" value="{{date('Y-m-d',strtotime($merchant->date_funded.'+ 1 week'))}}">
								</div>
								<span class="help-block">To Date <font color="#FF0000"> * </font></span>
							</div>
						</div>
						<div class="col-md-2">
							{!! Form::submit('Assign ',['class'=>'btn btn-primary','style'=>"width:100%"]) !!}
							<label for="">Assign investment based on Payment</label>
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
		<div class="box box-primary">
			<div class="box-body">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-12">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						{!! $tableBuilder->table(['class' => 'table table-bordered'],true) !!}
					</div>
				</div>
				<div class="row" hidden>
					<div class="col-md-12">
						<table class="table table-bordered" id="RejectedList">
							<thead>
								<tr>
									<th>#</th>
									<th>#</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	@stop
	@section('scripts')
	{!! $tableBuilder->scripts() !!}
	@component('admin.merchants.investment.payment_script') @endcomponent
	@stop
	@section('styles')
	@stop
