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
			{!! Form::open(['route'=>'admin::merchants::Investment::LiquidityBased::Assign', 'method'=>'POST']) !!}
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
					<label>Investors <span class="validate_star">*</span></label>
					<select id="investors" name="all_investors[]" class="form-control" multiple="multiple">
						@if($all_investors)
						@foreach($all_investors as $investor)
						<?php
						$status          = ($investor->s_prepaid_status == 1)?'(RTR)':'(Amount)';
						$status2         = ($merchant->s_prepaid_status == 1)?'(RTR)':'(Amount)';
						$pre_paid        = ($investor->global_syndication !=0)? '(P)'. $status: '(P)' . $status2;
						$syndication_fee = (!is_null($investor->global_syndication))? '(Investor) -'. $investor->global_syndication . '- '. $pre_paid : ' (Merchant)- '. $merchant->m_syndication_fee ;
						$management_fee  = (!is_null($investor->management_fee))? '(Investor) -'. $investor->management_fee: ' (Merchant)-' . $merchant->m_mgmnt_fee;
						?>
						<option value="<?= $investor->id; ?>" <?php if(in_array($investor->id,$selected_investors)) { echo "selected";} ?>>
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
								<th>Funded Amount</th>
								<td class="text-right">{{FFM::dollar($merchant->funded)}}</td>
							</tr>
							<tr>
								<th>Maximum Participant %</th>
								<td class="text-right">{{FFM::percent($merchant->max_participant_fund_per)}}</td>
							</tr>
							<tr>
								<th>Remaining Amount</th>
								<td class="text-right">{{FFM::dollar($balance)}}</td>
							</tr>
							<tr>
								<th class="text-center" colspan="2"><h4>Rules</h4> </th>
							</tr>
							<tr>
								<th>1 ) Maximum % of Liquidity </th>
								<?php $max_assign_per = Settings::value('max_assign_per'); ?>
								<td class="text-right">{{FFM::percent($max_assign_per)}}</td>
							</tr>
							<tr>
								<?php $max_investment_per = (Settings::where('keys', 'max_investment_per')->value('values')) ?? 0; ?>
								<th>2 ) Maximum Funded Amount ( {{FFM::percent($max_investment_per)}} ) </th>
								<td class="text-right">{{FFM::dollar($merchant->funded*$max_investment_per/100)}}</td>
							</tr>
							<tr>
								<?php $minimum_investment_value = (Settings::where('keys', 'minimum_investment_value')->value('values')) ?? 0; ?>
								<th>3 ) Minimum Funded Amount </th>
								<td class="text-right">{{FFM::dollar($minimum_investment_value)}}</td>
							</tr>
						</table>
					</div>
					<div class="col-md-6">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>Title</th>
									<th>Company Share</th>
									<th>Maximum Participant</th>
									<th>Remaining</th>
									<th>Invested</th>
									<th>Currently Invested</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($selected_companies as $Company => $value): ?>
									<tr>
										<th>{{$Company}}</th>
										<td class="text-right">{{FFM::percent($value['company_share'])}}</td>
										<td class="text-right">{{FFM::dollar($value['max_participant'])}}</td>
										<td class="text-right">{{FFM::dollar($value['remaining'])}}</td>
										<td class="text-right">{{FFM::dollar($value['CompanyInvested'])}}</td>
										<td class="text-right companyAmount" id="Company_{{$value['company_id']}}">{{FFM::dollar(0)}}</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="2">Total</th>
									<td class="text-right">{{FFM::dollar(array_sum(array_column($selected_companies,'max_participant')))}}</td>
									<td class="text-right">{{FFM::dollar(array_sum(array_column($selected_companies,'remaining')))}}</td>
									<td class="text-right">{{FFM::dollar(array_sum(array_column($selected_companies,'CompanyInvested')))}}</td>
									<td class="text-right" id="Company_Total">{{FFM::dollar(0)}}</td>
								</tr>	
							</tfoot>
						</table>
					</div>
				</div>
				<div class="row">
					<div class="col-md-10">
					</div>
					<div class="col-md-2">
						{!! Form::submit('Assign ',['class'=>'btn btn-primary','style'=>"width:100%"]) !!}
						<label for="">Assign investment based on liquidity</label>
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
			<div class="card with-nav-tabs card-default">
				<div class="card-header">
					<ul class="nav nav-tabs">
						<li class="nav-item"><a class="nav-link active" href="#SelectedTab" data-bs-toggle="tab">Selected</a></li>
						<li class="nav-item"><a class="nav-link" href="#RejectedTab" data-bs-toggle="tab">Rejected</a></li>
					</ul>
				</div>
				<div class="card-body">
					<div class="tab-content">
						<div class="tab-pane fade in active show" id="SelectedTab">
							<div class="row">
								<div class="col-md-12">
									{!! $tableBuilder->table(['class' => 'table table-bordered'],true) !!}
								</div>
							</div>
						</div>
						<div class="tab-pane fade" id="RejectedTab">
							<div class="row">
								<div class="col-md-12">
									<table class="table table-bordered" id="RejectedList" width="100%">
										<thead>
											<tr>
												<th>#</th>
												<th>Company</th>
												<th>Investor</th>
												<th>Liquidity</th>
												<th>Available Liquidity</th>
												<th>Eligible</th>
											</tr>
										</thead>
										<tr>
											<th colspan="3" class="text-right">Total</th>
											<th class="text-right">0</th>
											<th class="text-right">0</th>
											<th></th>
										</tr>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop
@section('scripts')
{!! $tableBuilder->scripts() !!}
@component('admin.merchants.investment.liquidity_script') @endcomponent
@stop
@section('styles')
@stop
