<?php use App\User; ?>
<?php use App\UserDetails; ?>
<?php use App\Label; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
	<h3><i class="fa fa-angle-right" aria-hidden="true"></i><i class="fa fa-user" aria-hidden="true"></i> {{ $Investor->name }}  </h3>
	<a href="#" class="help-link">
		<i class="fa fa-question-circle" aria-hidden="true"></i>
		<div class="tool-tip">{{ $Investor->name }} </div>
	</a>
</div>
{{ Breadcrumbs::render('portfolio',$Investor) }}
@include('layouts.admin.partials.lte_alerts')
<div class="col-md-12 col-sm-12 value-box-wrap">
	<div class="wrapper d-flex align-items-stretch">
		<nav id="sidebar" class="order-last active" class="img" style="background-image: url(images/bg_1.jpg);">
			<div class="custom-menu">
				<button type="button" id="sidebarCollapse" class="btn btn-primary"> </button>
			</div>
			<div class="">
				<ul class="list-unstyled components mb-5">
					@if($Investor->Roles[0]['id']==User::INVESTOR_ROLE)
					@if(Permissions::isAllow('Investor Ach Debit','View'))
					<li> <a href="{{url('admin/investors/achRequest/'.$userId)}}"></i>Transfer To Velocity</a> </li>
					@endif
					@endif
					@if($Investor->Roles[0]['id']==User::INVESTOR_ROLE)
					@if(Permissions::isAllow('Investor Ach Credit','View'))
					<li> <a href="{{url('admin/investors/achRequest/Credit/'.$userId)}}"></i> Transfer To Bank</a> </li>
					@endif
					@endif
					@if($Investor->Roles[0]['id']==User::INVESTOR_ROLE)
					@if(Permissions::isAllow('Investors','View'))
					<li> <a href="{{route('admin::investors::transaction::index', ['id' => $userId])}}"><i class="glyphicon glyphicon-view"></i> Transactions </a> </li>
					@endif
					@endif
					@if(Permissions::isAllow('Investors','View'))
					<li> <a href="{{url('admin/merchant_investor/documents_upload/'.$userId)}}"><i class="glyphicon glyphicon-view"></i> Documents </a> </li>
					@endif
					@if(config('app.env')=='local')
					@if(Permissions::isAllow('Investors','View'))
					<?php $UserDetails=UserDetails::where('user_id',$userId)->first(); ?>
					@if($UserDetails)
					<li> <a href="{{url('admin/audit/UserDetails/'.$UserDetails->id)}}"><i class="glyphicon glyphicon-view"></i> AuditLog </a> </li>
					@endif
					@endif
					@endif
					@if(Permissions::isAllow('Investors','Edit'))
					<li> <a href="{{route('admin::investors::bank_details', ['id' => $userId])}}"><i class="glyphicon glyphicon-view"></i> Bank</a> </li>
					@endif
					@if(@Permissions::isAllow('Generate PDF','Create'))
					<li> <a href="{{route('admin::pdf_for_investors', ['id' => $userId])}}"> Generate PDF</a> </li>
					@endif
					@if(Permissions::isAllow('Investors','Edit'))
					<li> <a href="{{url('admin/investors/edit/'.$userId)}}"></i> Edit</a> </li>
					@endif
				</ul>
			</div>
		</nav>
		<div id="content" class="p-4 p-md-5 pt-5">
			<div class="col-md-12">
				<div class="form-group">
					<div class="row">
						<div class="col-md-3">
							<table class="table table-bordered text-uppercase">
								<thead>
									<tr>
										<th colspan="3" class='head_name'>LIQUIDITY</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['liquidity'])}}</th>
									</tr>
									<tr>
										<th colspan="3" class='head_name'>BLENDED ROI</th>
										<th class="text-right">{{FFM::percent($Investor['data']['blended_rate'])}}</th>
									</tr>
									<tr id="rtr-Area">
										<th colspan="3" class='head_name'>TOTAL RTR</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_rtr'])}}</th>
									</tr>
									<tr class='rtr-sub_area' style="display:none">
										<td></td>
										<th>RTR</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['grand_total_rtr'])}}</th>
										<td></td>
									</tr>
									<tr class='rtr-sub_area' style="display:none">
										<td></td>
										<th>Default RTR</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_rtr_default'])}}</th>
										<td></td>
									</tr>
									<tr class='rtr-sub_area' style="display:none">
										<td></td>
										<th>Settled RTR</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_rtr_settled'])}}</th>
										<td></td>
									</tr>
									<tr class='rtr-sub_area' style="display:none">
										<td colspan="4"> <small>RTR - Default RTR - Settled RTR</small> </td>
									</tr>
									<tr class='rtr-sub_area' style="display:none">
										<td colspan="4"> <small>{{round($Investor['data']['grand_total_rtr'],2)}} - {{round($Investor['data']['total_rtr_default'],2)}} - {{round($Investor['data']['total_rtr_settled'],2)}}</small> </td>
									</tr>
									<tr class="AverageDailyBalance-Area">
										<th colspan="3" class='head_name'>Average Daily Balance</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['average'])}}</th>
									</tr>
									<tr class="PENDINGTOVELOCITY-Area">
										<th colspan="3" class='head_name'>PENDING TO VELOCITY</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['pending_debit_ach_request'])}}</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="col-md-3">
							<table class="table table-bordered text-uppercase">
								<thead>
									<tr id="OVERPAYMENT-Area">
										<th colspan="3" class='head_name'>OVERPAYMENT</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['overpayment'])}}</th>
									</tr>
									<tr class='OVERPAYMENT-sub_area' style="display:none">
										<td></td>
										<th>ACTUAL</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['overpayment_actual'])}}</th>
										<td></td>
									</tr>
									<tr class='OVERPAYMENT-sub_area' style="display:none">
										<td></td>
										<th>CARRY</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['overpayment_carry'])}}</th>
										<td></td>
									</tr>
									<tr class='OVERPAYMENT-sub_area' style="display:none">
										<th colspan="4"><small>ACTUAL + CARRY</small> </th>
									</tr>
									<tr class='OVERPAYMENT-sub_area' style="display:none">
										<th colspan="4">
											<small>
												{{round($Investor['data']['overpayment_actual'],2)}} + 
												{{round($Investor['data']['overpayment_carry'],2)}}
											</small>
										</th>
									</tr>
									<tr id="ROI-Area">
										<th colspan="3" class='head_name'>ROI</th>
										<th class="text-right">{{FFM::percent($Investor['data']['roi'])}}</th>
									</tr>
									<tr class="ROI-sub_area" style="display:none">
										<td></td>
										<th>average principal investment</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['average_principal_investment'])}}</th>
										<td></td>
									</tr>
									<tr class="ROI-sub_area" style="display:none">
										<td></td>
										<th>profit</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['profit'])}}</th>
										<td></td>
									</tr>
									<tr class='ROI-sub_area' style="display:none">
										<th colspan="4"><small>profit / average principal investment * 100</small> </th>
									</tr>
									<tr class='ROI-sub_area' style="display:none">
										<th colspan="4">
											<small>
												{{round($Investor['data']['profit'],2)}} / 
												{{round($Investor['data']['average_principal_investment'],2)}} * 100
											</small>
										</th>
									</tr>
									<tr id="PROJECTEDPORTFOLIOVALUE-Area">
										<th colspan="3" class='head_name'>PROJECTED PORTFOLIO VALUE</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['portfolio_value'])}}</th>
									</tr>
									<tr class="PROJECTEDPORTFOLIOVALUE-sub_area" style="display:none">
										<td></td>
										<th>TOTAL RTR</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_rtr'])}}</th>
										<td></td>
									</tr>
									<tr class="PROJECTEDPORTFOLIOVALUE-sub_area" style="display:none">
										<td></td>
										<th>LIQUIDITY</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['liquidity'])}}</th>
										<td></td>
									</tr>
									<tr class="PROJECTEDPORTFOLIOVALUE-sub_area" style="display:none">
										<td></td>
										<th>CTD</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['ctd'])}}</th>
										<td></td>
									</tr>
									<tr class="PROJECTEDPORTFOLIOVALUE-sub_area" style="display:none">
										<td colspan="4"> <small> TOTAL RTR + LIQUIDITY - CTD</small> </td>
									</tr>
									<tr class="PROJECTEDPORTFOLIOVALUE-sub_area" style="display:none">
										<td colspan="4">
											<small> 
												{{ round($Investor['data']['total_rtr'],2) }} +
												{{ round($Investor['data']['liquidity'],2) }} -
												{{ round($Investor['data']['ctd'],2) }}
											</small>
										</td>
									</tr>
									<tr id="Profit-Area">
										<th colspan="3" class='head_name'>Profit</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['profit'])}}</th>
									</tr>
									<tr class="Profit-sub_area" style="display:none">
										<td></td>
										<th>total profit</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['profit_total_profit'])}}</th>
										<td></td>
									</tr>
									<tr class="Profit-sub_area" style="display:none">
										<td></td>
										<th>bill transaction</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['profit_bill_transaction'])}}</th>
										<td></td>
									</tr>
									<tr class="Profit-sub_area" style="display:none">
										<td></td>
										<th>default total investment</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['default_total_investment'])}}</th>
										<td></td>
									</tr>
									<tr class="Profit-sub_area" style="display:none">
										<td></td>
										<th>default ctd</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['default_ctd'])}}</th>
										<td></td>
									</tr>
									<tr class="Profit-sub_area" style="display:none">
										<td></td>
										<th>carry profit</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['carry_profit'])}}</th>
										<td></td>
									</tr>
									<tr class="Profit-sub_area" style="display:none">
										<td></td>
										<th>overpayment</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['overpayment'])}}</th>
										<td></td>
									</tr>
									<tr class="Profit-sub_area" style="display:none">
										<td colspan="4"> <small> total profit + bill transaction - ( default total investment - default ctd ) + overpayment + carry profit</small> </td>
									</tr>
									<tr class="Profit-sub_area" style="display:none">
										<td colspan="4">
											<small> 
												{{ round($Investor['data']['profit_total_profit'],2) }} -
												{{ round($Investor['data']['profit_bill_transaction'],2) }} -
												(
												{{ round($Investor['data']['default_total_investment'],2) }} - {{ round($Investor['data']['default_ctd'],2) }}
												) +
												{{ round($Investor['data']['overpayment'],2) }} +
												{{ round($Investor['data']['carry_profit'],2) }}
											</small>
										</td>
									</tr>
									<tr class="PENDINGTOUSERBANK-Area">
										<th colspan="3" class='head_name'>PENDING TO USER BANK</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['pending_credit_ach_request'])}}</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="col-md-3">
							<table class="table table-bordered text-uppercase">
								<thead>
									<tr id="INVESTED-Area">
										<th colspan="3" class='head_name'>TOTAL INVESTED</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_investment'])}}</th>
									</tr>
									<tr class="INVESTED-sub_area" style="display:none">
										<td></td>
										<th>invested</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_investment_invested'])}}</th>
										<td></td>
									</tr>
									<tr class="INVESTED-sub_area" style="display:none">
										<td></td>
										<th>prepaid</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_investment_prepaid'])}}</th>
										<td></td>
									</tr>
									<tr class="INVESTED-sub_area" style="display:none">
										<td></td>
										<th>commission</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_investment_commission'])}}</th>
										<td></td>
									</tr>
									<tr class="INVESTED-sub_area" style="display:none">
										<td></td>
										<th>underwriting</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_investment_underwriting'])}}</th>
										<td></td>
									</tr>
									<tr class="INVESTED-sub_area" style="display:none">
										<td></td>
										<th>up sell</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_investment_up_sell'])}}</th>
										<td></td>
									</tr>
									<tr class="INVESTED-sub_area" style="display:none">
										<td colspan="4"> <small> invested + commission + underwriting + prepaid  + up sell</small> </td>
									</tr>
									<tr class="INVESTED-sub_area" style="display:none">
										<td colspan="4">
											<small> 
												{{ round($Investor['data']['total_investment_invested'],2) }} +
												{{ round($Investor['data']['total_investment_prepaid'],2) }} +
												{{ round($Investor['data']['total_investment_commission'],2) }} +
												{{ round($Investor['data']['total_investment_underwriting'],2) }} +
												{{ round($Investor['data']['total_investment_up_sell'],2) }}
											</small>
										</td>
									</tr>
									<tr id="DefaultRate-Area">
										<th colspan="3" class='head_name'>Default Rate</th>
										<th class="text-right">{{FFM::percent($Investor['data']['default_percentage'])}}</th>
									</tr>
									<tr class="DefaultRate-sub_area" style="display:none">
										<td></td>
										<th>Default INVESTED</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['default_total_investment'])}}</th>
										<td></td>
									</tr>
									<tr class="DefaultRate-sub_area" style="display:none">
										<td></td>
										<th>Default CTD</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['default_ctd'])}}</th>
										<td></td>
									</tr>
									<tr class="DefaultRate-sub_area" style="display:none">
										<td></td>
										<th>Overpayment</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['overpayment'])}}</th>
										<td></td>
									</tr>
									<tr class="DefaultRate-sub_area" style="display:none">
										<td colspan="4"><small>(DEFAULT INVESTED-DEFAULT CTD-Overpayment)/TOTAL INVESTED*100</small> </td>
									</tr>
									<tr class="DefaultRate-sub_area" style="display:none">
										<td colspan="4">
											<small>
												( 
												{{round($Investor['data']['default_total_investment'],2)}} - 
												{{round($Investor['data']['default_ctd'],2)}} -
												{{round($Investor['data']['overpayment'],2)}}
												) /
												{{round($Investor['data']['total_investment'],2)}} * 100
											</small>
										</td>
									</tr>
									<tr id="PRINCIPALINVESTMENT-Area">
										<th colspan="3" class='head_name'>PRINCIPAL INVESTMENT </th>
										<th class="text-right">{{FFM::dollar($Investor['data']['principal_investment'])}}</th>
									</tr>
									<tr class="PRINCIPALINVESTMENT-sub_area" style="display:none">
										<td colspan="4"><small>Sum Of InvestorTransaction with transaction_category (1,12)</small> </td>
									</tr>
									<tr id="PaidToDate-Area">
										<th colspan="3" class='head_name'>Paid To Date</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['paid_to_date'])}}</th>
									</tr>
									<tr class="PaidToDate-sub_area" style="display:none">
										<td colspan="4"><small>SUM OF All Debit INVESTORTRANSACTION</small> </td>
									</tr>
								</thead>
							</table>
						</div>
						<div class="col-md-3">
							<table class="table table-bordered text-uppercase">
								<thead>
									<tr>
										<th colspan="3" class='head_name'>NUMBER OF MERCHANTS</th>
										<th class="text-right">{{$Investor['data']['merchant_count']}}</th>
									</tr>
									<tr id="CTD-Area">
										<th colspan="3" class='head_name'>Cash to Date (CTD)</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['ctd'])}}</th>
									</tr>
									<tr class="CTD-sub_area" style="display:none">
										<td></td>
										<th>participant share</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['paid_participant_share'])}}</th>
										<td></td>
									</tr>
									<tr class="CTD-sub_area" style="display:none">
										<td></td>
										<th>Management fee</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['paid_mgmnt_fee'])}}</th>
										<td></td>
									</tr>
									<tr class="CTD-sub_area" style="display:none">
										<td colspan="4"><small>(participant share - Management fee</small> </td>
									</tr>
									<tr class="CTD-sub_area" style="display:none">
										<td colspan="4">
											<small>
												{{round($Investor['data']['paid_participant_share'],2)}} - 
												{{round($Investor['data']['paid_mgmnt_fee'],2)}}
											</small>
										</td>
									</tr>
									<tr id="CURRENTINVESTED-Area">
										<th colspan="3" class='head_name'>CURRENT INVESTED </th>
										<th class="text-right">{{FFM::dollar($Investor['data']['current_invested_amount'])}}</th>
									</tr>
									<tr class="CURRENTINVESTED-sub_area" style="display:none">
										<td></td>
										<th>invested amount</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['invested_amount'])}}</th>
										<td></td>
									</tr>
									<tr class="CURRENTINVESTED-sub_area" style="display:none">
										<td></td>
										<th>cost for ctd(paid principal)</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['cost_for_ctd'])}}</th>
										<td></td>
									</tr>
									<tr class="CURRENTINVESTED-sub_area" style="display:none">
										<td colspan="4"><small>(invested amount - cost for ctd(paid principal)</small> </td>
									</tr>
									<tr class="CURRENTINVESTED-sub_area" style="display:none">
										<td colspan="4">
											<small>
												{{round($Investor['data']['invested_amount'],2)}} - 
												{{round($Investor['data']['cost_for_ctd'],2)}}
											</small>
										</td>
									</tr>
									<tr id="ANTICIPATEDRTR-Area">
										<th colspan="3" class='head_name'>ANTICIPATED RTR </th>
										<th class="text-right">{{FFM::dollar($Investor['data']['anticipated_rtr'])}}</th>
									</tr>
									<tr class="ANTICIPATEDRTR-sub_area" style="display:none">
										<td></td>
										<th>TOTAL RTR</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_rtr'])}}</th>
										<td></td>
									</tr>
									<tr class="ANTICIPATEDRTR-sub_area" style="display:none">
										<td></td>
										<th>CTD</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['ctd'])}}</th>
										<td></td>
									</tr>
									<tr class="ANTICIPATEDRTR-sub_area" style="display:none">
										<td></td>
										<th>Default RTR</th>
										<th class="text-right">{{FFM::dollar($Investor['data']['total_rtr_default_rate'])}}</th>
										<td></td>
									</tr>
									<tr class="ANTICIPATEDRTR-sub_area" style="display:none">
										<td colspan="4">
											<small>
												{{round($Investor['data']['total_rtr'],2)}} - 
												{{round($Investor['data']['ctd'],2)}} -
												{{round($Investor['data']['total_rtr_default_rate'],2)}}
											</small>
										</td>
									</tr>
									<tr class="ANTICIPATEDRTR-sub_area" style="display:none">
										<td colspan="4"><small>TOTAL RTR - CTD - Default Rate RTR)</small> </td>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="card merchant-table-nav with-nav-tabs card-default">
				<div class="card-header">
					<ul class="nav nav-tabs">
						<li class="nav-item"><a class="nav-link active" href="#MerchantTab" data-bs-toggle="tab">Merchants</a></li>
						<li class="nav-item"><a class="nav-link" href="#PaymentTableTab" data-bs-toggle="tab">Payments</a></li>
						<li class="nav-item"><a class="nav-link" href="#ReAssignmentTableTab" data-bs-toggle="tab">Re Assignment</a></li>
					</ul>
				</div>
				<div class="card-body">
					<div class="tab-content">
						<div class="tab-pane fade in active show" id="MerchantTab">
							<div class="row">
								<div class="col-md-3">
									<div class="input-group">
										{{Form::select('status[]',$substatus,"",['class'=>'form-control','id'=>'sub_status_id','multiple'=>'multiple'])}}
									</div>
									<span class="help-block">
										<div class="col-md-8">
											Status
										</div>
										<div class="col-md-4">
											Exclude {{ Form::checkbox('exlcude_sub_status_id',1,false,['id'=>'exlcude_sub_status_id','class'=>'']) }}
										</div>
									</span>
								</div>
								<div class="col-md-2">
									<div class="input-group">
										{{Form::select('overpayment_status',[''=>'All','only_balance'=>"Only Balance",'overpayment_only'=>'Overpayment Only','exclude_overpayment'=>"Exclude Overpayment",'completed_payment'=>'Completed Payment'],"",['class'=>'form-control','id'=>'overpayment_status'])}}
									</div>
									<span class="help-block">
										<div class="col-md-8">
											Balance Status
										</div>
									</span>
								</div>
								<div class="col-md-2">
									<div class="input-group">
										{{Form::select('label',Label::getLabels(),"",['multiple','class'=>'form-control','id'=>'label'])}}
									</div>
									<span class="help-block">
										<div class="col-md-8">
											Label
										</div>
									</span>
								</div>
								<div class="col-md-3">
									<div class="input-group">
										<div class="col-md-6">
											{{Form::number('completed_percentage_value','',['class'=>'form-control','id'=>'completed_percentage_value'])}}
										</div>
										<div class="col-md-6">
											{{Form::select('completed_percentage_option',[ '='=>'Equal To(=)', '<'=>'Less Than(<)', '<='=>'Less Than Equal To(<=)', '>='=>'Greater Than Equal To(>=)', '>'=>'Greater Than(>)'],"==",['class'=>'form-control','id'=>'completed_percentage_option'])}}
										</div>
									</div>
									<span class="help-block">
										<div class="col-md-6">
											Completed Percentage
										</div>
										<div class="col-md-6">
											Options
										</div>
									</span>
								</div>
								<div class="col-md-1">
									<div class="input-group">
										<button type="button" class="btn btn-success" id="Merchant_apply_button" name="button">Apply</button>
									</div>
								</div>
							</div>
							<div class="grid table-responsive">
								<table class="table table-list-search table-bordered" id="MerchantDataTable">
									<thead>
										<tr role="row">
											<th>#</th>
											<th>Merchant</th>
											<th>Date Funded</th>
											<th>Merchant Balance</th>
											<th>Paid Count</th>
											<th>Funded</th>
											<th>Commission</th>
											<th>Upsell Commission</th>
											<th>Rate</th>
											<th>RTR</th>
											<th>Received Amount</th>
											<th>Balance</th>
											<th>Annualized rate</th>
											<th>Completed %</th>
											<th>Status</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
									<tfoot>
										<tr>
											<th colspan="5">Total</th>
											<th class="text-right">0</th>
											<th class="text-right">0</th>
											<th class="text-right">0</th>
											<th class="text-right"></th>
											<th class="text-right">0</th>
											<th class="text-right">0</th>
											<th class="text-right">0</th>
											<th class="text-right"></th>
											<th class="text-right"></th>
											<th class="text-right"></th>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
						<div class="tab-pane fade in" id="PaymentTableTab">
							@component('admin.investors.portfolioComponents.payment',['investor_id'=>$investor_id]) @endcomponent
						</div>
						<div class="tab-pane fade in" id="ReAssignmentTableTab">
							@component('admin.investors.portfolioComponents.reassignment',['investor_id'=>$investor_id]) @endcomponent
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop
@section('scripts')
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script charset="utf-8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script charset="utf-8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script charset="utf-8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script charset="utf-8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script charset="utf-8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script charset="utf-8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script charset="utf-8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
<script type="text/javascript">
Swal.fire('info!', 'This is Only For Debugging Purposes', 'info');
</script>
@include('admin.investors.portfolioComponents.script');
@stop
@section('styles')
<style media="screen">
.card-1 {
	box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
	transition: all 0.3s cubic-bezier(.25,.8,.25,1);
}
.card-1:hover {
	box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
}
.nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
	color: #495057 !important;
}
</style>
<!-- <link href="{{ asset('/css/optimized/portfolio.css?ver=5') }}" rel="stylesheet" type="text/css" /> -->
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="{{ asset('/css/jquery.dataTables1.11.0.min.css') }}">
<link rel="stylesheet" href="{{ asset('/css/buttons.dataTables.min.css') }}">
@component('admin.investors.portfolioComponents.style') @endcomponent
@component('admin.investors.portfolioComponents.secondnavbar') @endcomponent
@stop
