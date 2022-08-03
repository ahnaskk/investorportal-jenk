@extends('layouts.admin.admin_lte')
<?php use App\Settings; ?>
<?php use App\Models\Message; ?>
<?php use App\ParticipentPayment; ?>
<?php use App\ReassignHistory; ?>
<?php use App\Models\Views\MerchantUserView; ?>
<?php use App\MerchantUser; ?>
<?php use App\CompanyAmount; ?>
<?php use App\User; ?>
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Merchant Details</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Merchant Details</div>
    </a>
</div>
{{ Breadcrumbs::render('merchantView',$merchant) }}
@php
$company_id=isset($_GET['company_id'])?$_GET['company_id']:0;
$m_investor_id=isset($_GET['investor_id'])?$_GET['investor_id']:0;
@endphp
@if($total_invested>$total_invested_rtr && $complete_per<=0)
<div class="alert alert-danger" role="alert">
    Profit will be -ve if Invested amount is greater than RTR for this merchant
</div>
@endif
@if($company_amount_update==1 && 0)
<!-- <div class="col-md-12"> -->
<!-- <div class="box-body alert-box-body"> -->
<!-- <div class="alert alert-success alert-dismissable"> -->
<!-- <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button> -->
<!-- The Actual Invested Amount does not match with Company Funded Amount. Do you want to update the Funded Amount? -->
<!-- <a href="{{url('admin/merchants/update-max-participant-fund',['mid'=>$merchant->id])}}" class="btn  btn-success">Yes</a> -->
<!-- <a class="btn btn-default" data-bs-dismiss="alert" aria-hidden="true">No</a> -->
<!-- <a class="btn btn-info" id="update_max_participant_fund_button"><i class="glyphicon glyphicon-info-sign"></i> Info</a> -->
<!-- <div class="row" id="update_max_participant_fund_area" style="display:none"> -->
<!-- <p>If updated, the following values will change: </p> -->
<!-- <p>* Merchant Funded Amount </p> -->
<!-- <p>* Maximum Participant Amount</p> -->
<!-- <p>* Merchant RTR</p> -->
<!-- <p>* Payment Amount</p> -->
<!-- </div> -->
<!-- </div> -->
<!-- </div> -->
<!-- </div> -->
@endif
@if($CompanyFundDiffrenceFlag)
<div class="col-md-12">
    <div class="box-body alert-box-body">
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>
            Company Share does not match with Investor Funded Amount. If updated, the Investor Funded Amount will Become Company Share , the following values will change:
            <p></p>
            <p>* Company Share Amount </p>
            <p>* Merchant Maximum Participant Amount</p>
            Do you want to update the Company Share?
            <button onclick="ConfirmToAdjustCompanyFundedAmount()" class="btn btn-primary">Yes</button>
            <a class="btn btn-default" data-bs-dismiss="alert" aria-hidden="true">No</a>
            <a class="btn btn-info" id="company_diffrence_area_button" href="#"  title="View details of the changed values"><i class="glyphicon glyphicon-info-sign"></i> Info</a>
            <p></p>
            <div id="company_diffrence_area" style="display:none">
                <div class="col-md-6">
                    <p class="text-left">
                        <span style="color:#4cae4c" title="New values are displayed with this color in the table below"> New</span>
                        <span style="color:#8b6cff" title="Current values are displayed with this color in the table below"> Current</span>
                        <span style="color:#dc3545" title="Difference between the New and the Current values are displayed with this color in the table below"> Difference</span>
                    </p>
                    <table class="table table-list-search table-bordered">
                        <thead>
                            <tr>
                                <td>Company</td>
                                <td class="text-left">Maximum Share</td>
                                <td class="text-left">Funded</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php DB::beginTransaction(); ?>
                            <?php CompanyAmount::FinalizeCompanyShare($merchant_id); ?>
                            <?php $CompanyFundDiffrenceAfter=CompanyAmount::CompanyFundDiffrence($merchant_id); ?>
                            <?php DB::rollback(); ?>
                            <?php foreach ($CompanyFundDiffrence as $key => $Company): ?>
                            <?php $company_amount_diff=round($CompanyFundDiffrenceAfter[$key]->max_participant-$Company->max_participant,2); ?>
                            @if($company_amount_diff)
                            <tr>
                                <th>{{$Company->name}}</th>
                                <th class="text-left">
                                    <div class="row">
                                        <span style="color:#8b6cff" title="Current"> {{FFM::dollar($Company->max_participant)}}</span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#4cae4c" title="New">{{ FFM::dollar($CompanyFundDiffrenceAfter[$key]->max_participant) }} </span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#dc3545"> @if($company_amount_diff>0) <i style="color:black">Increase</i> @endif {{FFM::dollar($company_amount_diff)}}</span>
                                    </div>
                                </th>
                                <th class="text-left">{{FFM::dollar($Company->company_funded)}}</th>
                            </tr>
                            @endif
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="1">Merchant Maximum Participant Amount</td>
                                <?php $after_max_participant_fund=$CompanyFundDiffrenceAfter->sum('max_participant'); ?>
                                <?php $change_max_participant_fund=$after_max_participant_fund-$merchant->max_participant_fund; ?>
                                <td colspan="2" class="text-left">
                                    <div class="row">
                                        <span style="color:#8b6cff" title="Current"> {{FFM::dollar($merchant->max_participant_fund)}}</span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#4cae4c" title="New">{{ FFM::dollar($after_max_participant_fund) }} </span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#dc3545"> @if($change_max_participant_fund>0) <i style="color:black">Increase</i> @endif {{FFM::dollar($change_max_participant_fund)}}</span>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@if($fund_amount_change_flag==1 && false)
<div class="col-md-12">
    <div class="box-body alert-box-body">
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>
            Company Share does not match with Investor Funded Amount. If updated, the Investor Funded Amount will be adjusted, by increasing/decreasing the penny value and rounding them. Also, the following values will change:
            <p></p>
            <p>* Investor Funded Amount </p>
            <p>* Investor RTR Amount</p>
            <p>* Invested Amount</p>
            <p>* Liquidity</p>
            Do you want to update the Invested Amount?
            <button onclick="ConfirmToAdjustInvestorFundedAmount()" class="btn btn-primary">Yes</button>
            <a class="btn btn-default" data-bs-dismiss="alert" aria-hidden="true">No</a>
            <a class="btn btn-info" id="investor_diffrence_area_button" href="#"  title="View details of the changed values"><i class="glyphicon glyphicon-info-sign"></i> Info</a>
            <p></p>
            <div class="col-md-8" id="investor_diffrence_area" style="display:none">
                <p class="text-left">
                    <span style="color:#4cae4c" title="New values are displayed with this color in the table below"> New</span>
                    <span style="color:#8b6cff" title="Current values are displayed with this color in the table below"> Current</span>
                    <span style="color:#dc3545" title="Difference between the New and the Current values are displayed with this color in the table below"> Difference</span>
                </p>
                <table class="table table-list-search table-bordered">
                    <thead>
                        <th>Company</th>
                        <th>Investor</th>
                        <th class="text-left">Funded</th>
                        <th class="text-left">RTR</th>
                        <th class="text-left">Invested</th>
                        <th class="text-left">Liquidity</th>
                        <th class="text-left">Custom Amount</th>
                    </thead>
                    <?php $MerchantUserAdjustedBefor=DB::table('merchant_user_views')->where('merchant_id',$merchant_id)->where('investor_id','!=',504)->orderBy('company')->get(); ?>
                    <?php $investor_ids=DB::table('merchant_user_views')->where('merchant_id',$merchant_id)->where('investor_id','!=',504)->pluck('investor_id','investor_id') ?>
                    <?php $Investors=DB::table('user_details')->whereIn('user_id',$investor_ids)->pluck('liquidity','user_id')->toArray(); ?>
                    <?php DB::beginTransaction(); ?>
                    <?php MerchantUser::InvestmentAmountAdjuster($merchant_id); ?>
                    <?php $MerchantUserAdjustedAfter=MerchantUserView::where('merchant_id',$merchant_id)->where('investor_id','!=',504)->orderBy('company')->get(); ?>
                    <?php DB::rollback(); ?>
                    <tbody>
                        <?php foreach ($MerchantUserAdjustedAfter as $key => $value): ?>
                            <?php $amount_diff=$value->amount-$MerchantUserAdjustedBefor[$key]->amount; ?>
                            <?php $invest_rtr_diff=$value->invest_rtr-$MerchantUserAdjustedBefor[$key]->invest_rtr; ?>
                            <?php $total_investment_diff=$value->total_investment-$MerchantUserAdjustedBefor[$key]->total_investment; ?>
                            <?php $new_liquidity=$Investors[$value->investor_id]-$total_investment_diff; ?>
                            @if($amount_diff || $invest_rtr_diff || $total_investment_diff)
                            <tr>
                                <td>{{$value->CompanyModal->name}}</td>
                                <td>{{$value->Investor}}</td>
                                <td class="text-left">
                                    <div class="row">
                                        <span style="color:#4cae4c" title="New"> {{ FFM::dollar($value->amount) }}</span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#8b6cff" title="Current">{{ FFM::dollar($MerchantUserAdjustedBefor[$key]->amount) }} </span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#dc3545"> @if($amount_diff>0) <i style="color:black">Increase</i> @endif {{ FFM::dollar($amount_diff) }}</span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <div class="row">
                                        <span style="color:#4cae4c" title="New">{{ FFM::dollar($value->invest_rtr) }}</span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#8b6cff" title="Current">{{ FFM::dollar($MerchantUserAdjustedBefor[$key]->invest_rtr) }} </span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#dc3545">{{ FFM::dollar($invest_rtr_diff) }}</span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <div class="row">
                                        <span style="color:#4cae4c" title="New">{{ FFM::dollar($value->total_investment) }}</span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#8b6cff" title="Current">{{ FFM::dollar($MerchantUserAdjustedBefor[$key]->total_investment) }} </span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#dc3545">{{ FFM::dollar($total_investment_diff) }}</span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <div class="row">
                                        <span style="color:#4cae4c" title="New Value">{{ FFM::dollar($new_liquidity) }}</span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#8b6cff" title="Current Value">{{ FFM::dollar($Investors[$value->investor_id]) }} </span>
                                    </div>
                                    <div class="row">
                                        <span style="color:#dc3545">{{ FFM::dollar($total_investment_diff) }}</span>
                                    </div>
                                </td>
                                <td class="text-left">
                                    <button type="button" class="btn btn-success CustomerInvestmentChange"  title="Enter Custom Amount" investor="{{$value->Investor}}" actual_funded="{{$MerchantUserAdjustedBefor[$key]->amount}}" funded="{{FFM::dollar($MerchantUserAdjustedBefor[$key]->amount)}}" table_id="{{$value['id']}}"  name="button">Edit</button>
                                </td>
                            </tr>
                            @endif
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
<div class="col-md-12">
    <div class="box">
        <div class="loadering-statement" style="display:none;">
            <div class="loader"></div><br>
            <h5 class="alert alert-warning"><b>Please wait for a while!!</b></h5>
        </div>
        <div class="box-head">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <section class="bg-box">
            <div class="box-primary">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="paymntGnrte marc-btn">
                            @if(@Permissions::isAllow('Merchants','View'))
                            <div class="row">
                                <div class="merchant-view">
                                    <div class="merchant-top-drop">
                                        @if(@Permissions::isAllow('Merchants','Edit'))
                                        {!! Form::select('sub_status_id',$statuses, isset($merchant)? $merchant->sub_status_id : old('sub_status_id'),['class'=>'form-control','id'=>'sub_status_id']) !!}
                                        @endif
                                    </div>
                                    <div class="merchant-top-drop">
                                        @if(@Permissions::isAllow('Merchants','Edit'))
                                        {!! Form::select('m_investors',$merchant_investors,($m_investor_id)?$m_investor_id:old('sub_status_id'),['class'=>'form-control','id'=>'m_investor_id','placeholder'=>'Select Investors']) !!}
                                        @endif
                                    </div>
                                </div>
                                <div class="merchant-btn-wrap">
                                    <ul class="menu flex">
                                        @if($payment_started<=0 && $merchant->sub_status_id!=17)
                                        @if(in_array($merchant->label,$labels))
                                        <li><a title="Assign Based On Payment" class="btn btn-secondary assi-lq" id="assign_payment_button" value=""><img src='{{asset("/images/icon02.png")}}'> Roll Ins Payments </a></li>
                                        <li><a title="New Assign Based On Payment" class="btn btn-secondary assi-lq" href="{{ route('admin::merchants::Investment::PaymentBased::Page',[$merchant->id]) }}"><img src='{{asset("/images/icon02.png")}}'>New Roll Ins Payments </a></li>
                                        @endif

                                        @endif

                                        @if($payment_started<=0 && $merchant->sub_status_id!=17)
                                        <!-- <li><a title="Assign Investor Based On Liquidity" class="btn btn-primary assi-lq" id="assign_button" value=""><img src='{{asset("/images/icon02.png")}}'>Assign Investors</a></li> -->
                                        <li><a title="New Assign Investor Based On Liquidity" class="btn btn-success assi-lq" href="{{ route('admin::merchants::Investment::LiquidityBased::Page',[$merchant->id]) }}"><img src='{{asset("/images/icon02.png")}}' data-cy=cy_assi_based_liq>Assign Based On Liquidity</a></li>
                                        <li><a href="{{route('admin::merchant_investor::create')}}/{{$merchant->id}}" class="btn btn-primary inv-new"><i class="fa fa-plus" aria-hidden="true"></i> New Investor</a></li>
                                        <li><a href="{{route('admin::merchants::assign-investor',$merchant->id)}}" class="btn  btn-primary" data-cy="cy_assign_new_inv"> Assign New Investor</a></li>
                                        @endif
                                        @if(@Permissions::isAllow('Notes','View'))
                                        <li><a href="{{route('admin::notes::update_s',$merchant->id)}}" class="btn btn-primary not-invs"><i class="fa fa-sticky-note" aria-hidden="true"></i> Notes ({{$mnotes_count}})</a></li>
                                        @endif
                                        @if(@Permissions::isAllow('Generate statement','View') && $merchant->sub_status_id!=17)
                                        <!-- <li><a id="last_statement" class="btn btn-primary up-load"> Open Last Statement</a></li> -->
                                        @endif
                                        @if(@Permissions::isAllow('Credit Card Payment','Edit') && $merchant->sub_status_id!=17)
                                        @if($merchant_user_count != 0)
                                        <li><a href="{{url('admin/merchants/creditcard-payment/'.$merchant->id)}}" class="btn  btn-default"><i class="glyphicon glyphicon-credit-card"></i> Credit Card</a></li>
                                        @endif
                                        @endif
                                        @if(@Permissions::isAllow('Bank','View'))
                                        <li><a href="{{route('admin::merchants::bank.index',['merchant_id'=>$merchant->id])}}" class="btn btn-primary up-load"> Bank</a></li>
                                        @endif
                                        @if(@Permissions::isAllow('Payment Term','Edit') && $merchant->sub_status_id!=17)
                                        <li><a href="{{route('admin::merchants::payment-terms',['mid'=>$merchant->id])}}" id="edit_term" class="btn btn-primary up-load">ACH Terms</a></li>
                                        @if($ach_active)
                                        @if($merchant->paymentTerms->count())
                                        @if(!$merchant->payment_pause_id)
                                        <li><button id="pause-payment" class="btn btn-warning up-load" click> Pause ACH</button></li>
                                        @endif
                                        @if($merchant->payment_pause_id)
                                        <li><button id="resume-payment" class="btn btn-success up-load"> Resume ACH</button></li>
                                        @endif
                                        @endif
                                        @endif
                                        @endif
                                        @if(@Permissions::isAllow('Merchants','Edit'))
                                        <li><a href="{{URL::to('admin/merchants/edit',$merchant->id)}}" class="btn btn-danger"><i class="glyphicon glyphicon-edit"></i>Edit</a></li>
                                        @endif
                                        @if(@Permissions::isAllow('Add Payment','Create') && $merchant->sub_status_id!=17)
                                        <li><a href="{{URL::to('admin/payment/create',$merchant->id)}}" class="btn  btn-success"><i class="glyphicon glyphicon-piggy-bank"></i> Add Payment</a></li>
                                        @endif
                                        @if($merchant->max_participant_fund_per>=100)
                                        <!-- for temparaly the hide the agent fee -->
                                        <li>
                                             @if(!empty($agent_fee_on_substatus) && in_array($merchant->sub_status_id,$agent_fee_on_substatus))

                                            @if($merchant->agent_fee_applied==1)
                                            <input type="checkbox" data-cy="cy_agent_fee_btn"  checked data-toggle="toggle" data-on="Agent Fee On" data-off="Agent Fee Off" name="apply_agent_fee" id="apply_agent_fee" data-title="">  
                                            @else
                                            <input type="checkbox" data-cy="cy_agent_fee_btn" data-toggle="toggle" data-on="Agent Fee On" data-off="Agent Fee Off" name="apply_agent_fee" id="apply_agent_fee" data-title=""> 
                                            @endif

                                            @endif
                                        </li>
                                        @if(config('app.env')=='local')
                                        @if(@Permissions::isAllow('Merchants','Edit'))
                                        <li><a href="{{URL::to('admin/audit/Merchant',$merchant->id)}}" class="btn  btn-info"><i class="glyphicon"></i> Audit Log</a></li>
                                        @endif
                                        @endif
                                        @endif
                                         
                                        @if(@Permissions::isAllow('Merchants','View'))
                                        <li><a href="{{URL::to('admin/merchants/activity-logs',$merchant->id)}}" class="btn btn-danger">Log</a></li>
                                        @endif
                                        @if(@Permissions::isAllow('Merchants','View'))
                                        <li><a href="{{URL::to('admin/merchants/payoffLetterForMerchants',$merchant->id)}}" class="btn btn-primary up-load">PayOff Letter</a></li>
                                        @endif
                                        @if(@Permissions::isAllow('Generate statement','Create') && $merchant->sub_status_id!=17)
                                        <li><a id="regenerate_statement" class="btn btn-primary up-load">Balance Report</a></li>
                                        <li><a href="{{route('admin::merchants::document',['mid'=>$merchant->id])}}" class="btn btn-primary up-load"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Upload Docs</a></li>
                                        @if(config('app.env')=='local')
                                        <li><a href="{{route('admin::merchants::date_wise_investor_payment',['mid'=>$merchant->id])}}" class="btn btn-primary up-load"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Date Wise Investor Payment</a></li>
                                        @endif
                                        @endif
                                        @if($merchant->marketplace_status == 1)
                                        <li><a href="{{URL::to('admin/merchants/story',$merchant->id)}}" class="btn  btn-primary"> Story</a></li>
                                        <li><a href="{{URL::to("admin/merchants/$merchant->id/faq")}}" class="btn  btn-primary"> FAQ</a></li>
                                        @endif
                                        <!-- @php $user_d=isset($user->id)?$user->id:''; @endphp
                                        @if($user_d)
                                        <a class="btn btn-info" id="reset_confirmation">Reset Password</a>
                                        @endif -->
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mrchntVwDetails">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="merchant-details">
                                    <div class="box-row">
                                        <div class="title">Merchant</div>
                                        <?php
                                        $website = config('settings.communication_portal_website');
                                        $sendor_id = config('settings.communication_portal_sendor_id');
                                        $cell_phone=Message::TrimMobileNo($merchant->cell_phone);
                                        $cell_phone=Message::InternationalizeNo($cell_phone);
                                        ?>
                                        <?php $message_panel_link=$website."/user/contact?";
                                        $message_panel_link.="to=".$cell_phone;
                                        $message_panel_link.="&from=".$sendor_id;
                                        $message_panel_link.="&email=".$merchant->notification_email;
                                        $message_panel_link.="&first_name=".$merchant->name;
                                        $message_panel_link.="&sms=hi ".$merchant->name;
                                        ?>
                                        <div class="value">
                                            {{$merchant->name}}
                                            <!-- @if($merchant->cell_phone) <a href="{{$message_panel_link}}" target="_blank" class="pull-right"><i class="glyphicon glyphicon-send"></i></a> @endif -->
                                        </div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Date Funded</div>
                                        <div class="value" title="Created On {{FFM::datetime($merchant->created_at)}} by {{$merchant->creator ?? '--'}}">{{FFM::date($merchant->date_funded)}}</div>
                                    </div>
                                    @if(isset($merchant->MerchantDetails) && ($merchant->MerchantDetails->agent_name != ''))
                                    <div class="box-row">
                                        <div class="title">Name of ISO</div>
                                        <div class="value">{{$merchant->MerchantDetails->agent_name}}</div> 
                                    </div>
                                    @endif
                                    <div class="box-row">
                                        <div class="title"><span title="Net Investment Amount">Funded</span></div>
                                        <div class="value">{{FFM::dollar($merchant->funded)}}</div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Factor Rate</div>
                                        <div class="value">
                                            {{$merchant->factor_rate?round($merchant->factor_rate,4):'--'}}
                                            @if($merchant->old_factor_rate > 0 || $merchant->old_factor_rate < 0)
                                            (was {{ round($merchant->old_factor_rate,4) }})
                                            @endif
                                            @if(in_array($merchant->sub_status_id,[4,22,18,19,20]))
                                            @if($merchant->factor_rate!=$merchant->new_factor_rate)
                                            <span title="{{ round($merchant->new_factor_rate,4) }}">(new {{ number_format($merchant->new_factor_rate,4) }})</span>
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">RTR</div>
                                        <div class="value"><span class="value" title="{{FFM::dollar($merchant->rtr-$m_fee)}} (Without fee of {{FFM::dollar($m_fee)}})">{{FFM::dollar($merchant->rtr)}} </span></div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Payment Amount</div>
                                        <div class="value">{{FFM::dollar($merchant->payment_amount)}}</div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Advance Type </div>
                                        <div class="value">
                                            {{ isset($advance_types[$merchant->advance_type])?$advance_types[$merchant->advance_type]:'' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="merchant-details">
                                    <div class="box-row">
                                        <div class="title">Lender</div>
                                        <div class  ="value">
                                            <?php $lender=isset($merchant->lendor->name)?$merchant->lendor->name:'NILL'; ?>
                                            <a href="{{ url('admin/lender/view/'.$merchant->lender_id) }}">{{$lender}}</a>
                                        </div>
                                    </div>
                                    @if(config('app.env')=='local')
                                    <div class="box-row">
                                        <div class="title">Label</div>
                                        <div class="value">{{ isset($merchant->LabelModel->name)?$merchant->LabelModel->name:'' }}</div>
                                    </div>
                                    @endif
                                    <div class="box-row">
                                        <div class="title">No Of Payments</div>
                                        <div class="value">{{$merchant->pmnts?$merchant->pmnts:'--'}}</div>
                                    </div>
                                    <?PHP
                                    if($merchant->complete_percentage > 99) {
                                        $payment_left = "None";
                                    }
                                    if($payment_left < 0){
                                        $payment_left = 0;
                                    }                                    
                                    ?>
                                    <div class="box-row">
                                        <div class="title">Payments Left</div>
                                        @if($amount_difference>=1)
                                        <div class="value">
                                            <font color="blue">{{$payment_left}}</font>
                                        </div>
                                        @else
                                        <div class="value">{{$payment_left}}</div>
                                        @endif
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Actual Payments Left</div>
                                        <div class="value">
                                            @if($actual_payment_left <= 0) <font color="blue">None</font>
                                            @else
                                            {{$actual_payment_left}}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">First payment date</div>
                                        <div class="value" title="Created On {{  ($merchant_first_payment_data) ? FFM::datetime($merchant_first_payment_data['created_at']) : null }} by {{($merchant_first_payment_data) ? $merchant_first_payment_data['creator'] : '--'}}">{{FFM::date($merchant->first_payment)}}</div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Last payment date</div>
                                        <div class="value" title="Created On {{ ($merchant_last_payment_data) ? FFM::datetime($merchant_last_payment_data['created_at']) : null }} by {{ ($merchant_last_payment_data) ?  $merchant_last_payment_data['creator'] : '--'}}">{{FFM::date($merchant->last_payment_date)}}</div>
                                    </div>
                                     
                                    @if(in_array($merchant->sub_status_id,[1,5]))
                                    <div class="box-row">
                                        <div class="title">Pace payment</div>
                                        <div class="value">
                                            {{ round($num_pace_payment,2)}} {{$merchant->advance_type=="weekly_ach"?Str::plural('week',$num_pace_payment): Str::plural('day',$num_pace_payment)}}
                                        </div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Pace Balance</div>
                                        <div class="value">
                                            {{FFM::dollar($pace_amount)}} ({{FFM::percent($num_pace_percentage)}})
                                        </div>
                                    </div>
                                    @endif

                                    <div class="box-row">
                                        @if($merchant_balance>=0)
                                        <div class="title">Merchant Balance</div>
                                        <div class="value">
                                            {{FFM::dollar($merchant_balance)}}
                                            <!--  {{FFM::dollar($full_balance)}} -->
                                        </div>
                                        @else
                                        <div class="title">Merchant Overpayment</div>
                                        <div class="value">
                                            {{FFM::dollar($merchant_balance*-1)}}
                                        </div>
                                        @endif
                                    </div>


                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="merchant-details">
                                    <div class="box-row">
                                        <div class="title">Commission(%)</div>
                                        <div class="value">{{$merchant->commission?$merchant->commission:'0'}}%</div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">CTD</div>
                                        <div class="value"><span title="{{FFM::dollar($ctd_sum-$t_mang_fee)}} (Without fee of {{ FFM::dollar($t_mang_fee) }} )">{{FFM::dollar($ctd_sum)}} </span></div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Status</div>
                                        <div class="value" title="Updated On {{FFM::datetime($last_status_updated_date)}}">{{$merchant->payStatus}}</div>
                                    </div>
                                    <!-- sub status flag start -->
                                    @if(in_array($merchant->sub_status_id,[18,19,20,4,22,15,16,2]))
                                    <div class="box-row">
                                        <div class="title">Sub-Status Flag</div>
                                        <div class="value">
                                            @if($merchant->sub_status_flag)
                                            {{isset($substatus_flags[$merchant->sub_status_flag])?$substatus_flags[$merchant->sub_status_flag]:'' }}
                                            <a id="sub_status_flag_id" class="btn btn-primary"><span><i class="glyphicon glyphicon-edit"></i></span></a>
                                            @else
                                            <button id="sub_status_flag_id" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i></button>
                                            @endisset
                                        </div>
                                    </div>
                                    @endif
                                    <!-- sub status flag end -->
                                    <div class="box-row">
                                        <div class="title">Syndicate Percent</div>
                                        <div class="value">{{ FFM::percent($syndication_percent) }}</div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Our Syndicate Amount</div>
                                        <?php
                                        $syndication_amount_actual=str_replace(['$',','],'',$syndication_amount);
                                        $integer_syndication_amount=floor($syndication_amount_actual);
                                        $decimal_syndication_amount=round($syndication_amount_actual-$integer_syndication_amount,2);
                                        ?>
                                        @if($decimal_syndication_amount=="0.99")
                                        <div class="value" @if(config('app.env')=='local') title="{{$syndication_amount}}" @endif>${{number_format($syndication_amount_actual,1)}}</div>
                                        @else
                                        <div class="value">{{$syndication_amount}}</div>
                                        @endif
                                    </div>
                                    <div class="box-row">
                                        <div class="title"><span title="Gross Investment Amount">Total Invested</span></div>
                                        <div class="value">
                                            {{FFM::dollar($total_invested)}}
                                        </div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Complete %</div>
                                        <div class="value">{{ FFM::percent($complete_per) }}</div>
                                    </div>                                     
                                    <!-- please remove this commended lines after jun-30-2021 -->
                                    <!-- we need to display the actual complted % here -->
                                    <!-- @if(in_array($merchant->sub_status_id,[4,22,18,19,20])) -->
                                    <!-- @if(round($complete_per,2)!=$merchant->actual_complete_percentage) -->
                                    <!-- <div class="box-row"> -->
                                    <!-- <div class="title">Actual Complete %</div> -->
                                    <!-- <div class="value">{{ FFM::percent($merchant->actual_complete_percentage) }}</div> -->
                                    <!-- </div> -->
                                    <!-- @endif -->
                                    <!-- @endif -->
                                    
                                </div>
                            </div>
                            @php
                            $a=($syndication_percent*$merchant->payment_amount/100);
                            $b=($merchant->m_mgmnt_fee*$a)/100;
                            $c=$a-$b;
                            $o_fee=$syndication_payment-$c;
                            @endphp
                            <div class="col-md-3">
                                <div class="merchant-details">
                                    <div class="box-row">
                                        <div class="title">Syndicate Payment</div>
                                        <div class="value"><span class="value"  title="{{ FFM::dollar($c)}} (Without fee of {{ FFM::dollar($o_fee) }} )">{{ FFM::dollar($syndication_payment) }} </span></div>
                                    </div>
                               
                                    <div class="box-row">
                                        <div class="title">Participant Balance</div>
                                        <div class="value">
                                            @if($balance_mgmnt_fee < 0)
                                            @php $balance_mgmnt_fee = 0;@endphp
                                            @endif
                                            @if($balance_our_portion<=0)
                                            @php $balance=0;$bal_mgmnt_fee=0; @endphp
                                            @else
                                            @php $balance=$balance_our_portion;$bal_mgmnt_fee=$balance_mgmnt_fee; @endphp
                                            @endif
                                            
                                            @php $bal=( ($balance-$bal_mgmnt_fee)>0)?$balance-$bal_mgmnt_fee:0; @endphp
                                            <span  title="{{ FFM::dollar($bal)}} (Without fee of {{ FFM::dollar($bal_mgmnt_fee) }} )"> {{FFM::dollar(($balance>0)?$balance:0)}} </span>
                                            
                                            
                                           
                                        </div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">CTD (Our Portion)</div>
                                        <div class="value"><span title="{{FFM::dollar($ctd_our_portion-$disabled_company_participant_share)}} (Without fee of {{ FFM::dollar($t_mang_fee-$disabled_company_mang_fee)}} )" id="ctd">{{FFM::dollar(($t_mang_fee-$disabled_company_mang_fee)+($ctd_our_portion-$disabled_company_participant_share))}}</span></div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Net Zero Balance</div>
                                        <div class="value">
                                            {{FFM::dollar($net_zero_balance)}}
                                            <!-- @php
                                            $net_zero_balance=($net_zero-$ctd_our_portion-$agent_fee)>0?$net_zero-$ctd_our_portion-$agent_fee:0; @endphp -->
                                            
                                        </div>
                                        </div>
                                        <div class="box-row">
                                            <div class="title">Profit</div>
                                            <div class="value">{{FFM::dollar($profit_value_net)}}</div>
                                        </div>
                                        
                                        @if($overpayment>0)
                                        <div class="box-row">
                                            <div class="title">Overpayments</div>
                                            <?php $fee=round($overpayment>0?$overpayment-$overpayment_fee:0,3); ?>
                                            @if($fee)
                                            <?php $fee_p = \FFM::dollar($fee) .' (+ fee '. \FFM::dollar($overpayment_fee) .')'; ?>
                                            @else
                                            <?php $fee_p = \FFM::dollar(0); ?>
                                            @endif
                                            <div class="value"><span title="{{ $fee_p }} " id="overpayment">{{ FFM::dollar(round($overpayment>0?$overpayment:0,3)) }}</span></div>
                                        </div>

                                        @endif
                                       

                                        @php $substatus_1=[1,5]; @endphp
                                        @if(in_array($merchant->sub_status_id,$substatus_1))
                                        <div class="box-row">
                                            <div class="title">Our Pace Balance</div>
                                            <div class="value">
                                                {{FFM::dollar($our_pace_amount)}}
                                            </div>
                                        </div>
                                        @endif
                                        @if($merchant->code)
                                        <div class="box-row">
                                            <div class="title">Last Rcode</div>
                                            <div class="value">{{ $merchant->code  }} ( {{$merchant->description }} ) ( {{ FFM::date($last_rcode_date) }} )</div>
                                        </div>
                                        @endif
                                        @if($missed_payments!=0)
                                        <div class="box-row">
                                            <div class="title">ACH returns</div>
                                            <div class="value">{{ $missed_payments  }}</div>

                                        </div>
                                        @endif
                                        <div class="box-row" id="agent_fee_per_div" @if($merchant->agent_fee_applied==1 && in_array($merchant->sub_status_id,$agent_fee_status)) style="display:block;" @else style="display:none;" @endif >
                                        <div class="title">Agent Fee (%)</div>
                                        <div class="value">{{FFM::percent($agent_fee_per)}}</div>
                                    </div>
                                    @if($agent_fee!=0)
                                    <div class="box-row">
                                        <div class="title">Agent Fee </div>
                                        <div class="value">{{FFM::dollar($agent_fee)}}</div>
                                    </div>
                                    @endif
                                    </div>
                                    <!-- <div class="box-row">
                                        <div class="title">Profit</div>
                                        <div class="value">{{FFM::dollar($profit_value_net)}}</div>
                                    </div>
                                    <div class="box-row">
                                        <div class="title">Overpayments</div>
                                        <?php $fee=round($overpayment>0?$overpayment-$overpayment_fee:0,3); ?>
                                        @if($fee)
                                        <?php $fee_p = \FFM::dollar($fee) .' (+ fee '. \FFM::dollar($overpayment_fee) .')'; ?>
                                        @else
                                        <?php $fee_p = \FFM::dollar(0); ?>
                                        @endif
                                        <div class="value"><span title="{{ $fee_p }} " id="overpayment">{{ FFM::dollar(round($overpayment>0?$overpayment:0,3)) }}</span></div>
                                    </div> -->
                                   <!--  @php $substatus_1=[1,5]; @endphp
                                    @if(in_array($merchant->sub_status_id,$substatus_1))
                                    <div class="box-row">
                                        <div class="title">Our Pace Balance</div>
                                        <div class="value">
                                            {{FFM::dollar($our_pace_amount)}}
                                        </div>
                                    </div>
                                    @endif -->
                                    <!-- @if($merchant->code)
                                    <div class="box-row">
                                        <div class="title">Last Rcode</div>
                                        <div class="value">{{ $merchant->code  }} ( {{$merchant->description }} ) ( {{ FFM::date($last_rcode_date) }} )</div>
                                    </div>
                                    @endif
                                    @if($missed_payments!=0)
                                    <div class="box-row">
                                        <div class="title">ACH returns</div>
                                        <div class="value">{{ $missed_payments  }}</div>
                                    </div>
                                    @endif -->
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- test -->
            @if(!Auth::user()->hasRole(['company']))
            <div class="view-merchant-option">
                <div class="row">
                    @if($company_d)
                    <?php $comcompany_merchant_amount_difference=$merchant->max_participant_fund-array_sum(array_column($company_d, 'amount')); ?>
                    @foreach($company_d as $key=>$company)
                    @if($company['max_participant_percentage'] || $company['funded'])
                   <div @if($company['status'] == 0) style="display:none;" @endif class="col-md-4 row_test" @if(config('app.env')=='local') title="Maximum Participant Amount - Company Funded Difference ${{number_format($comcompany_merchant_amount_difference,4)}}" @endif>
                        <div class="form-group merchant-company-percentage">
                            <label class="value">{{ FFM::percent(($company['max_participant_percentage'])) }}</label>
                            <div class="content-right">
                                <div class="merchant-data">
                                    <div class="title">Company</div>
                                    <label style="cursor: pointer;"> <input type="checkbox" value="{{$key}}" {{(isset($company_id)) ? ($key==$company_id?"checked":"")  : ""}} onclick="filter_change({{$key}})" name="company_check" class="company_check"> {{ $company['name'] }}
                                    </label>
                                </div>
                                @if(config('app.env')=='local')
                                <div class="merchant-data">
                                    <div class="title">Share</div>
                                    {{ FFM::dollar($company['amount']) }}
                                </div>
                                @endif
                                <div class="merchant-data">
                                    <div class="title">Completed</div>
                                    @php
                                    $complete_per1=0;
                                    if(isset($per[$key]['amount']) && isset($per[$key]['rtr'])) {
                                        $complete_per1 = ($per[$key]['rtr'] > 0) ? (($per[$key]['amount'] / $per[$key]['rtr']) * 100) : 0;
                                    }
                                    $complete_per1=round($complete_per1,2);
                                    if($complete_per1 == -0){$complete_per1 = 0;}
                                    @endphp
                                    {{ FFM::percent($complete_per1) }}
                                </div>
                                @if(config('app.env')=='local')
                                <?php $company_amount_funded_diff=round($company['amount']-$company['funded'],4); ?>
                                @if($company_amount_funded_diff)
                                <div class="merchant-data text-danger" title="diff {{ number_format($company_amount_funded_diff,4) }}">
                                    <div class="title">Funded</div>
                                    {{ FFM::dollar($company['funded']) }}
                                </div>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @endif
                </div>
            </div>
            @endif
            <div class="card merchant-table-nav with-nav-tabs card-default">
                <div class="card-header">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link active" href="#InvestorTab" data-bs-toggle="tab">Investors</a></li>
                        <li class="nav-item"><a class="nav-link" href="#PaymentTableTab" data-bs-toggle="tab">Payments</a></li>
                        <li class="nav-item"><a class="nav-link" href="#ACHTableTab" data-bs-toggle="tab">ACH Schedule Of Payments</a></li>
                        @if(config('app.env')=='local')
                        <li class="nav-item"><a class="nav-link" href="#TransactionTab" data-bs-toggle="tab">Transaction</a></li>
                        @endif
                        @if(config('app.env')=='local')
                        <li class="nav-item"><a class="nav-link" href="#Principal_Porfit_LevelTab" data-bs-toggle="tab">Progress Level</a></li>
                        @endif
                        @if(config('app.env')=='local')
                        <li class="nav-item"><a class="nav-link" href="#Expected_Share_And_Given_Tab" data-bs-toggle="tab">Expected Share And Given Share</a></li>
                        @endif
                        @if(config('app.env')=='local')
                        <li class="nav-item"><a class="nav-link" href="#Expected_RTR_And_Given_Tab" data-bs-toggle="tab">Expected RTR And Given RTR</a></li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="InvestorTab">
                            <div class="grid table-responsive" id="investmentTable">
                                @if(@Permissions::isAllow('Merchants','Delete'))
                                
                                <a href="#" class="btn  btn-danger delete_multi" style="margin: 0 0 20px" id="delete_multi_investment"><i class="glyphicon glyphicon-trash"></i> Delete <span style="display: none;" id="i_count"></span>  Selected </a>
                                @endif
                                <div class="alert alert-warning" id="liquidity_warning" style="display:none;">
                                    <strong>Warning!</strong> Your liquidity has become negative.
                                    <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
                                </div>
                                <div class="loadering" style="display:none;">
                                    <div class="loader"></div><br>
                                    <h5 class="alert alert-warning"><b>Selected records are being deleted. Please wait until the page refreshes automatically</b></h5>
                                </div>
                                <table class="table table-list-search table-bordered" id="investorTable">
                                    <thead>
                                        <tr>
                                            <th>
                                            @if(!Auth::user()->hasRole(['company']) && !Auth::user()->hasRole(['viewer']) && !Auth::user()->hasRole(['collection user']))
                                                <label class="chc">
                                                    <input type="checkbox" name="delete_multi_investment" id="delete_investment">
                                                    <span class="checkmark chek-m"></span>
                                                </label>
                                                @endif
                                            </th>
                                            <th>Investor</th>
                                            <th class="text-left"><span title="Net Investment Amount">Amount</span></th>
                                            <th class="text-left">RTR</th>
                                            <th class="text-left"><span title="Gross Investment Amount">Total Invested</span></th>
                                            <th class="text-left">Received Amount</th>
                                            @if(config('app.env')=='local')
                                            <th class="text-left">CTD</th>
                                            @if(isset($merchant->sub_status_id) && in_array($merchant->sub_status_id,[18,19,20]))
                                            <th class="text-left">Net Balance</th>
                                            @endif
                                            @endif
                                            <th class="text-left">Balance</th>
                                            @if(config('app.env')=='local')
                                            <th class="text-left">Principal</th>
                                            <th class="text-left">Profit</th>
                                            @endif
                                            <th class="text-left">Share</th>
                                            <th class="text-left">Paid Management Fee</th>
                                            <th class="text-left">Syndication Fee</th>
                                            <th class="text-left">Underwriting Fee</th>
                                            @if(!Auth::user()->hasRole(['company']) && !Auth::user()->hasRole(['viewer']) && !Auth::user()->hasRole(['collection user']))
                                            <th>Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $total_investors=count($investor_data); $total_amount = $total_rtr= $total_prepaid= $total_underwritting_fee = $total_mgmnt_fee_amount=$total_share=$total_balance=$total_invested_amnt=$total_received=$total_ctd=$total_net_balance=$total_mt_fee_amount=$total_profit=$total_principal= $total_extra_rec_percent = 0; ?>
                                        <?php
                                        $OverpaymentAccount = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id');
                                        $OverpaymentAccount->where('user_has_roles.role_id', User::OVERPAYMENT_ROLE);
                                        $OverpaymentAccount = $OverpaymentAccount->first(['users.id']);
                                        ?>
                                        <?php
                                        $ExcludedInvestorCount=DB::table('merchant_user')->where('merchant_id',$merchant->id);
                                        if($OverpaymentAccount) $ExcludedInvestorCount=$ExcludedInvestorCount->where('user_id','!=',$OverpaymentAccount->id);
                                        $ExcludedInvestorCount=$ExcludedInvestorCount->count();
                                        ?>
                                        <?php $InvestorCount=0; $total_share_per=0?>
                                        @foreach($investor_data as $investor)
                                        @if($investor->amount!=0 || round($investor->actual_paid_participant_ishare,2)!=0  || $ExcludedInvestorCount==0)
                                        <?php $InvestorCount++; ?>
                                        <?php
                                        $total_amount            += $investor->amount;
                                        $total_rtr               += $investor->invest_rtr;
                                        $total_underwritting_fee += $investor->under_writing_fee;
                                        $total_share             += $investor->share;
                                        $total_invested_amnt     += $investor->total_invested;
                                        $total_mgmnt_fee_amount  += $investor->paid_mgmnt_fee;
                                        $total_mt_fee_amount     += $investor->mgmnt_fee_amount;
                                        $total_managmentfee      += $investor->mgmnt_fee;
                                        $total_syndicationfee    += $investor->pre_paid;
                                        $total_prepaid           += $investor->pre_paid;
                                        $total_principal         += $investor->paid_principal;
                                        $total_profit            += $investor->paid_profit;
                                        ?>
                                        <tr>
                                            @if(!Auth::user()->hasRole(['company']) && !Auth::user()->hasRole(['viewer']) && !Auth::user()->hasRole(['collection user']))
                                            <td>
                                                <label class="chc">
                                                    <input type='checkbox' class='delete_bulk_investments' name='delete_bulk_investments[]' data-id="{{$investor->id}}" value='{{$investor->id}}' onclick='uncheckMainInvestment();'>
                                                    <span class="checkmark"></span>
                                                </label>
                                            </td>
                                            @else
                                            <td>
                                            </td>
                                            @endif
                                            <td for="Investor">
                                                <a target="_blank" href="{{URL::to('admin/investors/edit',$investor->user_id)}}">{{$investor->name}}</a>
                                                @if(config('app.env')=='local')
                                                @if(!in_array($investor->role_id,[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]))
                                                @if(isset($merchant->sub_status_id) && !in_array($merchant->sub_status_id,[11,4,22,18,19,20]))
                                                <form action="{{URL::to('admin/merchants/re-assign')}}">
                                                    @if(@Permissions::isAllow('Merchants','Edit'))
                                                    <button type="submit" class="mrc-clik" onclick="return re_assign_show.call(this)">Re-Assign</button>
                                                    @endif
                                                    <div class="test098" style="display:none;">
                                                        <?php $reassign_amount=0; ?>
                                                        <!--  division by zero -->
                                                        @if($merchant->factor_rate)
                                                        <?php $reassign_amount=round(($investor->invest_rtr-$investor->actual_paid_participant_ishare)/$merchant->factor_rate,4); ?>
                                                        @else
                                                        <?php $reassign_amount=0; ?>
                                                        @endif
                                                        <?php if($reassign_amount==0) $reassign_amount=0; ?>
                                                        <input type="number" step="any" class="form-control inve-vie reassign_amount accept_digit_only" value="{{ $reassign_amount }}" min="0.01" max="{{($reassign_amount)}}" name="reassign_amount">
                                                        <!--<input type="hidden" class="maxAmount" value="{{($investor->invest_rtr-$investor->paid_participant_ishare)}}">         -->
                                                        <!--  <label> Collect Amount </label>
                                                        <input type="checkbox" name="collect_amount_flag" value="1"> -->
                                                        <select name="new_investor">
                                                            @foreach($investors as $key=> $investor2)
                                                            @if($investor->user_id!=$investor2->id && $investor->company==$investor2->company)
                                                            <option value="{{$investor2->id}}">
                                                                {{$investor2->id.' '.$investor2->name .' '}}
                                                                @if($investor2->userDetails)
                                                                {{FFM::dollar($investor2->userDetails->liquidity)}}
                                                                @endif
                                                            </option>
                                                            @endif
                                                            @endforeach
                                                        </select>
                                                        <br> <br>
                                                        <select name="type">
                                                            @foreach(ReassignHistory::typeOptions() as  $type_id => $type_name)
                                                            <option value="{{$type_id}}"> {{$type_name}} </option>
                                                            @endforeach
                                                        </select>
                                                        <br> <br>
                                                    </div>
                                                    <input type="hidden" name="investment_id" value="{{$investor->id}}" />
                                                </form>
                                                @endif
                                                @endif
                                                @endif
                                            </td>
                                            @php $fee =$investor->mgmnt_fee_amount; $title='';
                                            if($fee>0) {
                                                $title=FFM::dollar($investor->invest_rtr-$fee).'(Without fee of'. FFM::dollar($fee).' )';
                                            }
                                            @endphp
                                            <td class="text-left" for="Amount" title="${{ number_format($investor->amount,4) }}">{{FFM::dollar($investor->amount)}}</td>
                                            <td class="text-left" for="RTR"><span title="{{ $title }}" id="overpayment">{{FFM::dollar($investor->invest_rtr)}} </span></td>
                                            <td class="text-left" for="Total Invested" title="${{ number_format($investor->total_invested,4) }}">{{FFM::dollar($investor->total_invested)}}</td>
                                            <td class="text-left" for="Received Amount">
                                                <span class="inline">
                                                    @if($investor->actual_paid_participant_ishare!=0)
                                                    <?php $total_received+=$investor->actual_paid_participant_ishare; ?>
                                                    @if($investor->invest_rtr>0)
                                                    <?php $comleted_percentage=$investor->invest_rtr?($investor->actual_paid_participant_ishare/$investor->invest_rtr)*100:0; ?>
                                                    {{FFM::dollar($investor->actual_paid_participant_ishare)}} 
                                                    @if(!in_array($investor->role_id,[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]))
                                                    <i>({{FFM::percent($comleted_percentage)}})</i>
                                                    @endif
                                                    @else
                                                    <?php $comleted_percentage=$investor->invest_rtr; ?>
                                                    {{FFM::dollar($investor->actual_paid_participant_ishare)}} 
                                                    @if(!in_array($investor->role_id,[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]))
                                                    <i>({{FFM::percent($comleted_percentage)}})</i>
                                                    @endif
                                                    @endif
                                                    @else
                                                    <?php $comleted_percentage=$investor->actual_paid_participant_ishare; ?>
                                                    {{FFM::dollar($investor->actual_paid_participant_ishare)}}
                                                    @if(!in_array($investor->role_id,[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]))
                                                    <i>({{FFM::percent($comleted_percentage)}})</i>
                                                    @endif
                                                    @endif
                                                </span>
                                            </td>
                                            @if(config('app.env')=='local')
                                            <td class="text-left" for="CTD">
                                                @if($investor->actual_paid_participant_ishare!=0)
                                                <?php $total_ctd+=$investor->actual_paid_participant_ishare-$investor->paid_mgmnt_fee; ?>
                                                {{FFM::dollar($investor->actual_paid_participant_ishare-$investor->paid_mgmnt_fee)}} 
                                                @endif
                                            </td>
                                            @if(isset($merchant->sub_status_id) && in_array($merchant->sub_status_id,[18,19,20]))
                                            <td class="text-left" for="Net Balance">
                                                @if($investor->actual_paid_participant_ishare!=0)
                                                <?php $single_net_balance=$investor->total_invested-($investor->actual_paid_participant_ishare-$investor->paid_mgmnt_fee); ?>
                                                @if($single_net_balance>0)
                                                <?php $total_net_balance+=$single_net_balance; ?>
                                                {{FFM::dollar($single_net_balance)}} 
                                                @endif
                                                @endif
                                            </td>
                                            @endif
                                            @endif
                                            <td class="text-left" for="Balance">
                                                <?php $balance = round($investor->invest_rtr-$investor->actual_paid_participant_ishare,2); ?>
                                                <span title="{{$balance}}">
                                                    @if(round($balance,2)>=0) {{FFM::dollar($balance)}} @else {{FFM::dollar($balance*-1)}} <br> 
                                                    @if($investor->role_id==User::AGENT_FEE_ROLE)
                                                    Agent Fee
                                                    @else
                                                    Overpayment 
                                                    @endif
                                                    @endif
                                                </span>
                                                <?php 
                                                if($investor->role_id!=User::AGENT_FEE_ROLE){
                                                    $total_balance+=$balance; 
                                                }
                                                ?>
                                            </td>
                                            @if(config('app.env')=='local')
                                            <td class="text-left" for="Principal">
                                                {{FFM::dollar($investor->paid_principal)}}
                                            </td>
                                            <td class="text-left" for="Profit">
                                                {{FFM::dollar($investor->paid_profit)}}
                                            </td>
                                            @endif
                                            <td class="text-left" for="Share">
                                                <?php $per=0; ?>
                                                @if($merchant->funded>0)
                                                <?php $per=($merchant->funded)? $investor->amount/$merchant->funded*100:0; ?>
                                                @endif
                                                {{FFM::percent($per)}}
                                                <?php $total_share_per = $total_share_per+round($per,2);?>
                                                @if($total_invest_rtr>0)
                                                 @if(in_array($investor->role_id,[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]))
                                                 @if(round($balance,2)<0) 
                                                 <?php $balance = $balance*-1; ?>
                                                 @endif 
                                                 <?php $extra_percent = ($balance/$total_invest_rtr)*100;
                                                 $total_extra_rec_percent = $total_extra_rec_percent+round($extra_percent,2);?><br>
                                                 <i>+{{FFM::percent($extra_percent)}}</i>
                                                 
                                                @endif
                                                 @endif
                                            </td>
                                            <td class="text-left" for="Paid Management Fee">{{FFM::dollar($investor->paid_mgmnt_fee)}}
                                                <!-- {{FFM::dollar($investor->mgmnt_fee_amount)}} --> ({{FFM::percent($investor->mgmnt_fee)}})
                                            </td>
                                            <td class="text-left">
                                                <!--  {{FFM::dollar($investor->syndication_fee_amount)}} -->
                                                @if($investor->pre_paid)
                                                {{FFM::dollar($investor->pre_paid)}}
                                                @else
                                                {{FFM::dollar(0)}}
                                                @endif
                                                ({{FFM::percent($investor->syndication_fee_percentage)}})
                                            </td>
                                            <td class="text-left">
                                                {{FFM::dollar($investor->under_writing_fee)}}
                                                ({{FFM::percent($investor->under_writing_fee_per)}})
                                            </td>
                                            <td>
                                                @if(@Permissions::isAllow('Merchants','View'))
                                                <a title="Document" href="{{route('admin::merchant_investor::document',['mid' => $merchant->id , 'iid'=>$investor->user_id])}}" class="btn btn-xs btn-primary tblActn"><i class="glyphicon glyphicon-file"></i></a>
                                                @endif
                                                @if(@Permissions::isAllow('Merchants','Edit'))
                                                @if(!in_array($investor->role_id,[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]))
                                                <a title="Edit" href="{{route('admin::merchant_investor::edit',['id'=>$investor->id])}}" class="btn btn-xs btn-primary tblActn"><i class="glyphicon glyphicon-edit"></i></a>
                                                @endif
                                                <a title="View" href="{{route('admin::merchant_investor::view_investor',['id'=>$investor->id])}}" target="_blank" class="btn btn-xs btn-primary" @if(config('app.env')!='local') style="display:none" @endif ><i class="glyphicon glyphicon-eye-open InvsetorView"></i></a>
                                                @if(!$complete_per)
                                                <div class="block">
                                                    {{ Form::open(['route'=>'admin::merchants::investorMerchantStatus', 'method'=>'POST'])}}
                                                    <div class="tdActnPending">
                                                        {{Form::select('status', ['0' => 'Pending', '1' => 'Approved','2'=>'Hide','3'=>"Re-assigned", '4' => 'Rejected'], $investor->status, array('onchange' => "checkLiquidity(this,'$investor->liquidity','$investor->tot_amount','$investor->name','$investor->status')"),['class' => 'form-control','id'=>'request_status']) }}
                                                    </div>
                                                    <div class="tdActnUpdate up-date">
                                                        {{Form::hidden('id',$investor->id)}}
                                                        {{Form::hidden('investor_id',$investor->user_id)}}
                                                        {{Form::hidden('investor_name',$investor->name)}}
                                                        {{Form::hidden('merchant_id',$merchant->id)}}
                                                        {{ Form::submit('Update',['class'=>'btn btn-primary'])}}
                                                    </div>
                                                    {{ Form::close()}}
                                                </div>
                                                @endif
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                        <tr>
                                            <td></td>
                                            <td>{{$InvestorCount}} Total:</td>
                                            <td class="text-left">{{FFM::dollar($total_amount)}}</td>
                                            <td class="text-left"><span title="{{FFM::dollar($total_rtr-$total_mt_fee_amount)}} (Without fee of {{ FFM::dollar($total_mt_fee_amount) }} )" id="overpayment">{{FFM::dollar($total_rtr)}} </span></td>
                                            <td class="text-left">{{FFM::dollar($total_invested_amnt)}}</td>
                                            <td class="text-left">{{FFM::dollar($total_received)}}</td>
                                            @if(config('app.env')=='local')
                                            <td class="text-left">{{FFM::dollar($total_ctd)}}</td>
                                            @if(isset($merchant->sub_status_id) && in_array($merchant->sub_status_id,[18,19,20]))
                                            <td class="text-left">{{FFM::dollar($total_net_balance)}}</td>
                                            @endif
                                            @endif
                                            <td class="text-left">
                                                @if($merchant->agent_fee_applied!=1)
                                                @if(round($total_balance,2)>=0) {{FFM::dollar($total_balance)}} @else {{FFM::dollar($total_balance*-1)}} <br> Overpayment @endif
                                                @endif
                                            </td>
                                            @if(config('app.env')=='local')
                                            <td class="text-left">{{FFM::dollar($total_principal)}}</td>
                                            <td class="text-left">{{FFM::dollar($total_profit)}}</td>
                                            @endif
                                            <td class="text-left">
                                                <?php 
                                                $total_per=0;
                                                if(isset($merchant->funded)){
                                                    if(!empty($merchant->funded) && $merchant->funded!=0){
                                                        $total_per=$total_amount/$merchant->funded*100;
                                                    }
                                                }
                                                ?>
                                                <!-- {{FFM::percent($total_per)}} <br> -->
                                                {{FFM::percent($total_share_per)}} <br>
                                                @if($total_extra_rec_percent>0)
                                               <i>+{{FFM::percent($total_extra_rec_percent)}}</i>
                                                @endif
                                                
                                            </td>
                                            <td class="text-left">{{ FFM::dollar($total_mgmnt_fee_amount) }} </td>
                                            <td class="text-left">{{FFM::dollar($total_syndicationfee)}} </td>
                                            <td class="text-left">{{FFM::dollar($total_underwritting_fee)}}</td>
                                            @if(!Auth::user()->hasRole(['company']) && !Auth::user()->hasRole(['collection user']))
                                            <td></td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade " id="PaymentTableTab">
                            <div class="box box-padTB merch-tab" style="padding-top: 35px;">
                                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                                    <div class="col-sm-12">
                                        @if(!in_array($merchant->sub_status_id,[4,22,18,19,20]))
                                        @if(@Permissions::isAllow('Add Payment','Delete'))
                                        <input type="text" autocomplete="off" class="multi-datepicker form-control" name="delete_dates_p1" id="delete_dates_p1">
                                        <input type="hidden" name="delete_dates_p" class="date_parse" id="delete_dates_p">
                                        <a href="#" class="btn  btn-danger delete_multi delete-mul2" id="delete_multi_submit"><i class="glyphicon glyphicon-trash"></i> Delete <span style="display: none;" id="p_count"></span> Selected</a>
                                        @endif
                                        @endif
                                        <div class="grid table-responsive" style="padding-top:2%;">
                                            <div class="paymentloadering" style="display:none;">
                                                <div class="loader"></div><br>
                                                <h5 class="alert alert-warning"><b>Selected records are being deleted. Please wait until the page refreshes automatically.</b></h5>
                                            </div>
                                            {!! $tableBuilder->table(['class' => 'table table-bordered '],true) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade " id="TransactionTab">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="title" style="color:black">{{ Form::label('status', ucfirst('status'),['for'=>'status']) }}</div>
                                        {{ Form::select('transaction_status',ParticipentPayment::statusOptions(),[ParticipentPayment::StatusPending,ParticipentPayment::StatusCompleted],['id'=>'transaction_status','class'=>'TransactionDataTableChange','multiple']) }}
                                    </div>
                                </div>
                                <table class="table table-list-search table-bordered" id="TransactionDataTable">
                                    <thead>
                                        <tr>
                                            <th>Transaction Id</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th class="text-left">Debit</th>
                                            <th class="text-left">Credit</th>
                                            <th class="text-left">Profit</th>
                                        </tr>
                                        @if(!$TransactionFundedCheck)
                                        <tr>
                                            <th colspan="8"> <a href="{{url('admin/merchant_investor/add_merchant_investment_transaction/'.$merchant_id)}}" class="btn btn-primary btn-sm" style="width:100%">Generate Investment Amount Transaction</a> </th>
                                        </tr>
                                        @endif
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-left">Total</th>
                                            <th class="text-left">0</th>
                                            <th class="text-left">0</th>
                                            <th class="text-left">0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade " id="ACHTableTab">
                            <div class="col-md-12">
                                <table class="table table-list-search table-bordered" id="">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Payment Received</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ach_payments as $key => $ach_payment)
                                        <tr style="{{$ach_payment->ach_style}}">
                                            <td>{{ ++$key }}</td>
                                            <td>{{ $ach_payment->payment_date }}</td>
                                            <td>
                                                {{ $ach_payment->payment_amount }}
                                            </td>
                                            <td>{{ $ach_payment->status_type }}</td>
                                            <td>{{ $ach_payment->total_payments }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade " id="Principal_Porfit_LevelTab">
                            <div class="row">
                                <div id="investmentProgressbar"></div>
                            </div>
                            <div class="row">
                                <div id="principal_circle" class="pie_progress" role="progressbar" data-barcolor="#2c97c4" data-barsize="10" aria-valuemin="0" aria-valuemax="{{$total_invested_amnt}}">
                                    <div class="pie_progress__number">0%</div>
                                    <div class="pie_progress__label">Principal</div>
                                </div>
                                <div id="profit_circle" class="pie_progress" role="progressbar" data-barcolor="#3daf2c" data-barsize="10" aria-valuemin="0" aria-valuemax="{{$total_rtr-$total_invested_amnt-$expected_management_fee}}">
                                    <div class="pie_progress__number">0%</div>
                                    <div class="pie_progress__label">Profit</div>
                                </div>
                                <div id="total_circle" class="pie_progress" role="progressbar" data-barcolor="#0dcaf0" data-barsize="10" aria-valuemax="{{round($total_rtr,2)}}">
                                    <div class="pie_progress__number">0%</div>
                                    <div class="pie_progress__label">Total</div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade " id="Expected_Share_And_Given_Tab">
                            @component('admin.merchants.merchantViewPageComponents.expected_share_and_given_share_table',['merchant'=>$merchant,'tableBuilder'=>$tableBuilder]) @endcomponent
                        </div>
                        <div class="tab-pane fade " id="Expected_RTR_And_Given_Tab">
                            @component('admin.merchants.merchantViewPageComponents.expected_rtr_and_given_rtr_table',['merchant'=>$merchant,'syndication_percent'=>$syndication_percent,'investor_data'=>$investor_data]) @endcomponent
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </section>
    </div>
</div>
<!-- assin based on payment model start -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Assign Based On Payments</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(count($all_auto_investors)>0)
                <!-- form start -->
                {!! Form::open(['route'=>'admin::merchant_investor::assign-payment', 'method'=>'POST']) !!}
                <div class="form-group">
                    <div class="date-star" id="date-star">
                        <div class="col-md-6 report-input">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control from_date1 datepicker" id="date_start1"  value="{{$merchant->date_funded}}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"  required="required"/>
                                <input type="hidden" class="date_parse" name="date_start" id="date_start" value="{{$merchant->date_funded}}">
                            </div>
                            <span class="help-block">From Date <font color="#FF0000"> * </font></span>
                        </div>
                        <div class="col-md-6 report-input">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input class="form-control to_date1 datepicker" id="date_end1" value="{{$merchant->date_funded}}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off" required="required"/>
                                <input type="hidden" class="date_parse" name="date_end" id="date_end" value="{{$merchant->date_funded}}">
                            </div>
                            <span class="help-block">To Date <font color="#FF0000"> * </font></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {{Form::select('auto_company',$companies,'',['class'=>'form-control','id'=>'auto_company','placeholder'=>'Select Company'])}}
                </div>
                <div class="form-group">
                    <div class="form-group">
                        <label>Select Investors</label>
                        <select id="auto_investors" name="auto_investors[]" class="form-control" multiple="multiple" required="required">
                            @if($all_auto_investors)
                            @foreach($all_auto_investors as $investor)
                            @php $netamount=isset($total_payments[$investor->id])?$total_payments[$investor->id]:0;
                            $liquidity_1= isset($avil_liquidity[$investor->id])?$avil_liquidity[$investor->id]:0;
                            $invested_amount= isset($collected_amount[$investor->id])?$collected_amount[$investor->id]:0;
                            $low_liquidity='';
                            @endphp
                            @if($invested_amount>$liquidity_1 && $invested_amount!=0)
                            @php $low_liquidity= '( available liquidity is '.FFM::dollar($liquidity_1) .')'; @endphp
                            @endif
                            <option value="<?php echo $investor->id ?>" <?php if(in_array($investor->id,$auto_investors)) { echo "selected";} ?>> {{ $investor->name }} ( {{  isset($total_payments[$investor->id])?FFM::dollar($total_payments[$investor->id]):'' }} ) <span style="background-color:red;font-weight:bold"> {{ $low_liquidity }} </span>
                            </option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <input type='hidden' name='merchant_id' id='merchant_id' value='{{$merchant_id}}'>
                </div>
                @if($all_auto_investors)
                @php $html=$html1=$low_liquidity='';
                $html.='After assign based on payments ,the liquidity of investors ';
                @endphp
                @foreach($all_auto_investors as $investor)
                <!-- '<a href="{{ url('admin/investors/portfolio/'.$investor->id) }}"></a>'; ?> -->
                @php $netamount=isset($total_payments[$investor->id])?$total_payments[$investor->id]:0;
                $liquidity_1= isset($avil_liquidity[$investor->id])?$avil_liquidity[$investor->id]:0;
                $invested_amount= isset($collected_amount[$investor->id])?$collected_amount[$investor->id]:0;
                @endphp
                @if($invested_amount>$liquidity_1 && $invested_amount!=0)
                @php
                $low_liquidity.= $investor->name .',';
                @endphp
                @endif
                @endforeach
                @php
                $html1.=' will be zero';
                @endphp
                <div class="row">
                    <div class="col-md-12" style="word-break:break-all">
                        @if($low_liquidity)
                        {{ $html}} <span style="color:red;font-weight:bold"> {{ $low_liquidity }} </span> {{$html1 }}
                        @endif
                    </div>
                </div>
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <!-- /.box-body -->
                        <div class="box-footer">
                            {!! Form::submit('Assign',['class'=>'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
                <!-- /.box -->
                @else
                <h3 align="center">No Investors Available</h3>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end model -->
<div class="modal fade" id="yourModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Assign Investors</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label><b> M - Management Fee , S - Syndication Fee , P - Pre-paid Status </b></label>
                @if(count($all_investors)>0)
                {!! Form::open(['route'=>'admin::merchant_investor::assign-investor', 'method'=>'POST']) !!}
                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        <input type="button" id="unselect" name="unselect" value="Unselect" class="btn btn-success">
                        <input type="button" id="select_all" name="select_all" value="Select All Investors" class="btn btn-success">
                    </div>
                </div>
                <div class="form-group">
                    <?PHP
                    //$company=[''=>'All',1=>'Velocity',58=>'VP Funding'];
                    ?>
                    {{Form::select('company',$companies,'',['class'=>'form-control','id'=>'company','placeholder'=>'Select Company'])}}
                </div>
                <div class="form-group">
                    <div class="form-group">
                        <label>Select Investors</label>
                        <select id="investors" name="all_investors[]" class="form-control" multiple="multiple">
                            @if($all_investors)
                            @foreach($all_investors as $investor)
                            <?php
                            $status=($investor->s_prepaid_status==1)?'(RTR)':'(Amount)';
                            $status2=  ($merchant->s_prepaid_status==1)?'(RTR)':'(Amount)';
                            $pre_paid=($investor->global_syndication !=0)?  '(P)'. $status : '(P)' .  $status2;
                            $syndication_fee = (!is_null($investor->global_syndication))? '(Investor) -'. $investor->global_syndication . '- '. $pre_paid  : ' (Merchant)- '. $merchant->m_syndication_fee ;
                            // .'(P)' . ( ($investor->s_prepaid_status==1)?'(RTR)':'(Amount)')
                            // .'(P)' . ( ($investor->s_prepaid_status==1)?'(RTR)':'(Amount)')
                            $management_fee=(!is_null($investor->management_fee))? '(Investor)-' . $investor->management_fee : ' (Merchant)-' . $merchant->m_mgmnt_fee;
                            ?>
                            <option value="<?php echo $investor->id; ?>" <?php if(in_array($investor->id,$selected_investors)) { echo "selected";} ?>>
                                {{ $investor->name }} - {{ $syndication_fee }} (S) - {{ $management_fee }} (M)
                            </option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <input type='hidden' name='merchant_id' value='{{$merchant_id}}'>
                </div>
                <div class="row">
                    <div class="box-body col-md-6">
                        <div class="box-footer">
                            {!! Form::submit('Assign',['class'=>'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
                @else
                <h3 align="center">No Investors Available</h3>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="confirmChangeStatus" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Change Merchant Status</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['method'=>'POST','id'=>'change_substatus']) !!}
            <div class="modal-body">
                <p>Do you want to change status now ?</p>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal" id="cancel">Cancel</a>
                <a href="javascript:void(0)" class="btn btn-success" id="submitChangeStatus" data-bs-dismiss="modal">Yes</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
{{-- Sub status Flag modal --}}
@if($merchant->sub_status_id!=11)
<div class="modal fade" id="subStatusFlagModal" tabindex="-1" role="dialog" aria-labelledby="subStatusFlagLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Update Merchant Sub-Status Flag</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['method'=>'POST','id'=>'subStatusFlagForm'],['class' => 'form']) !!}
            <div class="modal-body">
                {{ Form::label('sub-status-flag', 'Sub-Status Flag') }}
                {{Form::select('sub-status-flag', $substatus_flags, isset($merchant->sub_status_flag) ? $merchant->sub_status_flag : 0 ,['class' => 'form-control']) }}
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal" id="cancel">Cancel</a>
                <a href="javascript:void(0)" class="btn btn-success" id="submitSubStatusFlag" data-bs-dismiss="modal">Yes</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endif
<div class="modal fade" id="confirmMail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Reset Password Mail Confirmation</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['method'=>'POST','id'=>'confirm_mail']) !!}
            <div class="modal-body">
                <p>Do you want to Reset Password Now ?</p>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                <a href="javascript:void(0)" class="btn btn-success" id="submitMail" data-bs-dismiss="modal">Yes</a>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="RevertPaymentModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Debit Payment </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['method'=>'POST','id'=>'PaymentRevertForm']) !!}
            <div class="modal-body">
                <div class="row">
                    <table class="table table-bordered">
                        <tr>
                            <th>Paid Date</th>
                            <th id="RevertPaidDate">Paid Date</th>
                        </tr>
                        <tr>
                            <th>Payment</th>
                            <th id="RevertPayment">100</th>
                        </tr>
                    </table>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        {{ Form::label('date', 'Revert Date *') }}
                        <?php 
                        $revert_date_mode = (Settings::where('keys', 'revert_date_mode')->value('values'))??Settings::Revert_CurrentDate;
                        if($revert_date_mode==Settings::Revert_CurrentDate){
                            $revert_date_mode = true; 
                        } else {
                            $revert_date_mode = false;
                        }
                        ?>
                        {{ Form::text('date', date('Y-m-d'),['class' => 'form-control datepicker','id'=>'revert_date']) }}
                        {{ Form::hidden('participent_payment_id','',['id'=>"revert_participent_payment_id"]) }}
                        {{ Form::hidden('date','',['class'=>'date_parse']) }}
                    </div>
                    <div class="col-md-8">
                        {{ Form::label('reason', 'Reason') }}
                        {{ Form::text('reason','',['class' => 'form-control']) }}
                        
                    </div>
                </div>
                @if($debit_ach_active)
                <div class="row">
                    <div class="col-md-4">
                        <label for="InitiateACH">Initiate ACH</label>
                        <input data-toggle="toggle" data-onstyle="success" type="checkbox" class="form-control" name="initiate_ach" value="1" id="InitiateACH">
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal" id="cancel">Cancel</button>
                <button type="button" class="btn btn-success" id="PaymentRevertSubmit">Yes</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
<div class="modal fade" id="CustomFundedAmountModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h4 class="modal-title">Funded Amount Change </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['method'=>'POST','id'=>'CustomFundedAmount']) !!}
            <div class="modal-body">
                <div class="row">
                    <table class="table table-bordered">
                        <tr>
                            <th>Investor</th>
                            <th id="CustomerFundInvestor"></th>
                        </tr>
                        <tr>
                            <th>Funded Amount</th>
                            <th id="CustomerFundAmount"></th>
                        </tr>
                    </table>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        {{ Form::label('amount', 'New Funded') }}
                        {{ Form::number('amount','',['class' => 'form-control','id'=>'CustomFundedAmount_New_Funded','autofocus']) }}
                        {{ Form::hidden('merchant_user_id','',['id' => 'CustomFundedAmount_merchant_user_id']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal" id="cancel">Cancel</button>
                <button type="button" class="btn btn-success" id="CustomFundedAmountSubmit">Yes</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
</div>
{{-- Sub status Flag modal end --}}
@stop
@section('scripts')
<script src="{{ asset('js/bootstrap-toggle.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/notify.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/jquery.stepProgressBar.js') }}"></script>
<script src="{{ asset('js/jquery-asPieProgress.js') }}"></script>
{!! $tableBuilder->scripts() !!}
<script src="{{ asset('select2/js/select2.full.min.js') }}"></script>
<script type="text/javascript">
$(document).on('submit', 'form', function() {
    var restriction_disable = $(this).find('button:submit, input:submit').attr('restriction_disable');
    if(restriction_disable != "true"){
        $(this).find('button:submit, input:submit').attr('disabled', 'disabled');
    }
});
</script>
<script type="text/javascript">
$('#add_agent_fee_btn').on('click',function(event) {
    event.preventDefault();
    var agent_fee_per = $('#agent_fee_per').val();
    $.ajax({
        type:'POST',
        data: {'_token': _token,'agent_fee_per':agent_fee_per,'merchant_id':merchant_id},
        url:URL_agent_fee,
        success:function(data)
        {
            if (data.status == 1) {
                $('.box-head-agent-fee').html('<div class="alert alert-success" ><strong>Success! </strong>' + data.msg + '</div>');
            }else{
                $('.box-head-agent-fee').html('<div class="alert alert-danger" ><strong>Failed! </strong>' + data.msg + '</div>');
            }
            window.scrollTo(0,0);
        }
    });
});
$('#assign_button').click(function(e) {
    $("#yourModal").modal('show');
    //$('#investors').trigger('change.select2');
    // $('#investors').trigger('change');
    //  $('#investors').trigger('change.select2');
});
$('#assign_payment_button').click(function(e) {
    $("#paymentModal").modal('show');
});
var table = window.LaravelDataTables["dataTableBuilder"];
var URL_investmentDelete               = "{{ URL::to('admin/merchant_investor/delete_investments') }}";
var URL_RevertPayment                  = "{{ URL::to('admin/payment/revert-payment') }}";
var URL_SingleInvestorFundAmountChange = "{{ URL::to('admin/merchant_investor/updateInvetment') }}";
var URL_paymentDelete                  = "{{ URL::to('admin/merchant_investor/delete') }}";
var redirectUrl                        = "{{ URL::to('admin/merchants/view/') }}";
var URL_undoReassign                   = "{{ URL::to('admin/merchants/undo-reassign/') }}";
var URL_CompanyFilter                  = "{{ URL::to('admin/merchants/company_filter') }}";
var URL_AutoCompanyFilter              = "{{ URL::to('admin/merchants/auto_company_filter') }}";
var URL_confirmMail                    = "{{ URL::to('admin/merchants/reset-password') }}";
var URL_agent_fee                      = "{{ URL::to('admin/merchants/update-agent-fee') }}";
var merchant_id                        ="{{ $merchant_id }}";
var company_id                         ="{{ $company_id }}";
var m_investor_id                      ="{{ $m_investor_id }}";
/*Delete multiple payments in a merchant*/
var delay = 10;
$(document).on('mouseover','.checkbox11',function(){
    $(this).prop('title', '');
});
$(document).ready(function() {
    $("#apply_agent_fee").change(function(e){if(
        this.checked==true){
            var agent_fee_status =1;
            document.getElementById('agent_fee_per_div').style.display = "block";
        }else{
            document.getElementById('agent_fee_per_div').style.display = "none";
            var agent_fee_status =0;
        }
        // var agent_fee_per = $('#apply_agent_fee').val();alert(agent_fee_per);
        $.ajax({
            type:'POST',
            data: {'_token': _token,'agent_fee_status':agent_fee_status,'merchant_id':merchant_id},
            url:URL_agent_fee,
            success:function(data)
            {   
                if (data.status == 1) {
                    $('.box-head').html('<div class="alert alert-success" ><strong>Success! </strong>' + data.msg + '</div>');
                }else{
                    $('.box-head').html('<div class="alert alert-danger" ><strong>Failed! </strong>' + data.msg + '</div>'); 
                }
                window.scrollTo(0,0);
            }
        });
    });
    $('.flexMenu-viewMore>a').click(function(){
        if ($(this).text() == "More")
        $(this).text("Hide");
        else if($(this).text() == "Hide")
        $(this).text("More");
    });
    $('#reset_confirmation').on('click',function()
    {
        $('#confirmMail').modal('show');
    });
    $('#submitMail').on('click',function() {
        $.ajax({
            type:'POST',
            data: {'_token': _token,'merchant_id':merchant_id},
            url:URL_confirmMail,
            success:function(data)
            {
                if (data.status == 1) {
                    $('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                }
                window.scrollTo(0,0);
            }
        });
    });
    $("#unselect").click(function(e){
        $('#investors').val('').trigger("change.select2");
        $('#company').val('').trigger("change.select2");
    });
    $('#select_all').click(function() {
        if($('#company').val())
        {
            var investors = @json($selected_investors);
            var company = $('#company').val();
            var array = [];
            $.ajax({
                type: 'GET',
                data: {
                    'investors': investors,
                    'company': company,
                    '_token': _token
                },
                url: URL_CompanyFilter,
                success: function(data) {
                    var items = data.result;
                    for (var i in items) {
                        // alert(items[i].id);
                        array.push(items[i].id);
                    }
                    $('#investors').attr('selected', 'selected').val(array).trigger('change.select2');
                }
            });
        }
        else
        {
            $('#investors option').prop('selected',true).trigger("change.select2");
        }
        document.getElementById("error_message_for_investor").innerHTML = '';
    });
    $('#auto_company').change(function(e) {
        var investors = @json($auto_investors);
        var company = $('#auto_company').val();
        var array = [];
        $.ajax({
            type: 'GET',
            data: {
                'investors': investors,
                'company': company,
                '_token': _token
            },
            url: URL_AutoCompanyFilter,
            success: function(data) {
                var items = data.result;
                for (var i in items) {
                    array.push(items[i].id);
                }
                $('#auto_investors').attr('selected', 'selected').val(array).trigger('change.select2');
            }
        });
    });
    $('#company').change(function(e) {
        var investors = @json($selected_investors);
        var company = $('#company').val();
        var array = [];
        $.ajax({
            type: 'GET',
            data: {
                'investors': investors,
                'company': company,
                '_token': _token
            },
            url: URL_CompanyFilter,
            success: function(data) {
                var items = data.result;
                for (var i in items) {
                    // alert(items[i].id);
                    array.push(items[i].id);
                }
                $('#investors').attr('selected', 'selected').val(array).trigger('change.select2');
            }
        });
    });
    $(".js-company-placeholder").select2({
        placeholder: "Select A Company"
    });
    var merchantId = '<?php echo $merchant->id; ?>';
    $('#delete_multi_submit').on('click', function() {
        count=0;
        id = [];
        $('.delete_bulk:checked').each(function() {
            count=count+1;
            id.push($(this).val());
        });
        if (confirm('Do you really want to delete selected ('+ count +') items?')) {
            days_var = ($('#delete_dates_p').val());
            if (!id.length && days_var) {
                if(count == 0){
                    alert('Please select at least one valid date to delete.');
                    return
                }
                // $(".paymentloadering").css("display", "block");
                // $.ajax({
                //     type: 'POST',
                //     data: {
                //         'days': days_var,
                //         'merchant_id':{{$merchant_id}},
                //         '_token': _token
                //     },
                //     url: URL_paymentDelete,
                //     success: function(data) {
                //         if (data.status == 1) {
                //             window.location = redirectUrl + '/' + merchantId;
                //             //$.notify("Payment delete successfully", "success");
                //             // setTimeout(function () {
                //             //   window.location = redirectUrl+merchantId;
                //             //  // location.reload();
                //             //  }, delay);
                //         } else {
                //             alert('Something Went Wrong.');
                //         }
                //     }
                // });
                return;
            }
            //console.log(id);
            if (id.length > 0) {
                $(".paymentloadering").css("display", "block");
                //window.location.href = "{{URL::to('admin/merchant_investor/delete/')}}?multi_id="+id;
                $.ajax({
                    type: 'POST',
                    data: {
                        'multi_id': id,
                        'merchant_id':{{$merchant_id}},
                        '_token': _token
                    },
                    url: URL_paymentDelete,
                    dataType: 'json',
                    success: function(data) {
                        if (data.status == 1) {
                            window.location = redirectUrl + '/' + merchantId;
                            //$.notify("Payment delete successfully", "success");
                            // setTimeout(function () {
                            //   window.location = redirectUrl+merchantId;
                            //  // location.reload();
                            //  }, delay);
                        } else {
                            $(".paymentloadering").css("display", "none");
                            $('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                        }
                    }
                });
            } else {
                alert('Please select at least one record to delete.');
            }
        }
    });
    //  delete mutiple assigned investors for merchant
    $('#delete_multi_investment').on('click', function() {
        // alert('clicked');
        var el = this;
        var id_arr = [];
        var count=0;
        $('.delete_bulk_investments:checked').each(function() {
            id_arr.push($(this).val());
            count=count+1;
        });
        if (confirm('Do you really want to delete the selected ('+count +') items?')) {
            if (id_arr.length > 0) {
                $(".loadering").css("display", "block");
                $.ajax({
                    type: 'POST',
                    data: {
                        'multi_id': id_arr,
                        'merchant_id':{{$merchant_id}},
                        '_token': _token
                    },
                    url: URL_investmentDelete,
                    success: function(data) {
                        if (data.status == 1) {
                            window.location = redirectUrl + '/' + merchantId;
                            // setTimeout(function () {
                            //  // window.location = redirectUrl;
                            //   location.reload();
                            //  }, delay);
                        } else {
                            window.location = redirectUrl + '/' + merchantId;
                        }
                    }
                });
            } else {
                alert('Please select atleast one record to delete.');
            }
        }
    });
});
$('#delete_investment').on('click', function() {
    if ($(this).is(':checked', true)) {
        var count = 0;
        $(".delete_bulk_investments").prop('checked', true);
    } else {
        $(".delete_bulk_investments").prop('checked', false);
    }
});
$('#delete_payment').on('click', function() {
    if ($(this).is(':checked', true)) {
        var count = 0;
        $(".delete_bulk").prop('checked', true);
        $('.delete_bulk:checked').each(function() {
            count=count+1;
        });
    } else {
        $('.delete_bulk').prop('checked', false);
    }
});
// $("#reAssignForm").validate({
//
//        rules: {
//            reassign_amount: {
//                required: true,
//                numbersWithComma:true,
//                max: function () {
//                    return $('.maxAmount').val();
//                }
//            }
//        },
//         messages: {
//                reassign_amount: "Please enter less than or equal to max value" ,
//         }
//    });
function uncheckMain() {
    var uncheck = 0;
    $('input:checkbox.delete_bulk').each(function() {
        if (!this.checked) {
            uncheck = 1;
            $('#delete_payment').prop('checked', false);
        }
    });
    if (uncheck == 0) {
        $('#delete_payment').prop('checked', true);
    }
}
function uncheckMainInvestment() {
    var uncheck = 0;
    $('input:checkbox.delete_bulk_investments').each(function() {
        if (!this.checked) {
            uncheck = 1;
            $('#delete_investment').prop('checked', false);
        }
    });
    if (uncheck == 0) {
        $('#delete_investment').prop('checked', true);
    }
}
$('.reassign_amount').keypress(function(event) {
    if (event.which == 46 && $(this).val().indexOf('.') != -1) {
        event.preventDefault();
    } // prevent if already dot
    if (event.which == 44 &&
        $(this).val().indexOf(',') != -1) {
            event.preventDefault();
        } // prevent if already comma
    });
    $('.reassign_amount').keyup(function(event) {
        // skip for arrow keys
        if (event.which >= 37 && event.which <= 40) {
            event.preventDefault();
        }
        $(this).val(function(index, value) {
            value = value.replace(/,/g, '');
            return (value);
        });
    });
    /*function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}*/
$("input.accept_digit_only").keypress(function(event) {
    return /\d/.test(String.fromCharCode(event.keyCode));
});
$('#m_investor_id').on('change',function()
{
    $investor_id=$('#m_investor_id').val();
    if(company_id!=0 && $investor_id!=0)
    {
        window.location = '?company_id=' + company_id+'&investor_id='+$investor_id;
    }
    else if($investor_id!=0)
    {
        window.location = '?investor_id=' + $investor_id;
    }
    else if($investor_id==0)
    {
        var url = window.location.href;
        var a = url.indexOf("?");
        var b = url.substring(a);
        var c = url.replace(b, "");
        url = c;
        window.location = url;
    }
});
$(".accept_digit_only").keypress(function(evt) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    if (key.length == 0) return;
    var regex = /^[0-9.,\b]+$/;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault) theEvent.preventDefault();
    }
});
var re_assign_show = function() {
    if ($(this).text() == 'Update') {
        return true;
    } else {
        $(this).text('Update').addClass('upd-viw');
        $(this).next(".test098").show();
        return false;
    }
    //return 0;
}
function filter_change(company_id = '') {
    // $investor_id=$('#m_investor_id').val();
    if ($(".company_check").is(':checked') && m_investor_id==0) {
        (window.location = '?company_id=' + company_id);
    }
    else if($(".company_check").is(':checked') && m_investor_id!=0)
    {
        if(m_investor_id!=0)
        {
            (window.location = '?company_id=' + company_id+'&investor_id='+m_investor_id);
        }
    }
    else {
        if(m_investor_id==0)
        {
            var url = window.location.href;
            var a = url.indexOf("?");
            var b = url.substring(a);
            var c = url.replace(b, "");
            url = c;
            window.location = url;
        }
        else
        {
            window.location = '?investor_id='+m_investor_id;
        }
    }
    //alert($(".company_check").is(':checked'));
    // body...
}
function checkLiquidity(data, liquidity, amount, name, status) {
    if (status != 1) {
        if (data.value == 1) {
            if (parseFloat(amount) > parseFloat(liquidity)) {
                document.getElementById('liquidity_warning').style.display = "block";
                if (parseFloat(liquidity) < 0) {
                    document.getElementById('liquidity_warning').innerHTML = "<strong>Warning!</strong> Liquidity  of investor " + name + " is $" + liquidity + '<button type="button" class="close" data-bs-dismiss="alert">&times;</button>';
                } else {
                    document.getElementById('liquidity_warning').innerHTML = "<strong>Warning!</strong> Liquidity  of investor " + name + " may become -ve as they a liquidity of $" + liquidity + ' only! <button type="button" class="close" data-bs-dismiss="alert">&times;</button>';
                }
            } else {
                document.getElementById('liquidity_warning').style.display = "none";
            }
        } else {
            document.getElementById('liquidity_warning').style.display = "none";
        }
    } else {
        document.getElementById('liquidity_warning').style.display = "none";
    }
}
function undo_function(investor_id, merchant_id) {
    if (merchant_id) {
        $.ajax({
            type: 'POST',
            data: {
                'investor_id': investor_id,
                'merchant_id': merchant_id,
                '_token': _token
            },
            url: URL_undoReassign,
            success: function(data) {
                if (data.result != 'success') { alert(data.result); return false; }
                window.location.reload();
            }
        });
    }
}
</script>
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript">
function openForm() {
    $('.box-head-agent-fee').html(' ');
    document.getElementById("popupForm").style.display = "block";
}
function closeForm() {
    document.getElementById("popupForm").style.display = "none";
}
dateDisabled = @json($dates2);
var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
//alert(dateDisabled);
function addZ(n) {
    return n < 10 ? '0' + n : '' + n;
}
//Date picker
$('#delete_dates_p1').datepicker({
    autoclose: false,
    format: default_date_format.toLowerCase(),
    multidate: true,
    clearBtn: true,
    todayBtn: "linked",
    beforeShowDay: function(date) {
        //console.log(("0" + (date.getDay())).slice(-2));
        this_day = date.getFullYear() + '-' + addZ(date.getMonth() + 1) + '-' + addZ(date.getDate());
        return;
        if ($.inArray(this_day, dateDisabled) !== -1) {
            return;
        }
        return false;
    }
});
$('#delete_dates_p1').on('change changeDate', function(){
    var val = $(this).val();
    if(val && moment(val, default_date_format).isValid())
    {
        val = val.split(',');
        var new_arr = val.map(item => {
            let year = moment(item, default_date_format).year();
            if(year.toString().length == 1 || year.toString().length == 2) {
                year = moment(year, 'YY').format('YYYY');
            }
            return moment(item, default_date_format).set('year', year).format(default_date_format);
        });
        var new_arr1 = val.map(item => {
            let year = moment(item, default_date_format).year();
            if(year.toString().length == 1 || year.toString().length == 2) {
                year = moment(year, 'YY').format('YYYY');
            }
            return moment(item, default_date_format).set('year', year).format('YYYY-MM-DD');
        });
        if(new_arr) {
            $('.delete_bulk').prop('checked', false);
            $(new_arr).each(function(i, selected){
                var el = $('#PaymentTableTab #dataTableBuilder').find('td:contains('+selected+')');
                el.siblings('.checkbox11').find('.delete_bulk').prop('checked', true);
            });
            new_arr = new_arr.join(',');
            new_arr1 = new_arr1.join(',');
            $(this).val(new_arr);
            $(this).datepicker('update');
            $(this).siblings('.date_parse').val(new_arr1);
            // if($(this).valid() == false) {
            //     $(this).val('');
            //     $(this).datepicker('update');
            //     $(this).siblings('.date_parse').val('');
            // }
        }
    }else {
        $(this).siblings('.date_parse').val('');
        $('.delete_bulk').prop('checked', false);
    }
});
$(document).ready(function() {
    var URL_confirmSubstatus = "{{ URL::to('admin/merchants/change-substatus') }}";
    var URL_company_investor = "{{ URL::to('admin/merchants/company_investors') }}";
    var merchant_id = "{{ $merchant->id }}";
    $('#sub_status_id').on('change', function() {
        $('#confirmChangeStatus').modal('show');
    });
    $('#cancel').on('click', function() {
        //alert('hii');
        // $('#sub_status_id').val(['']);
        $('#sub_status_id').attr('selected', 'selected').val(['']).trigger('change.select2');
    });
    $('#submitChangeStatus').on('click', function() {
        var substatus_id = $('#sub_status_id').val();
        $.ajax({
            type: 'POST',
            data: {
                '_token'       : _token,
                'merchant_id'  : merchant_id,
                'sub_status_id': substatus_id
            },
            url: URL_confirmSubstatus,
            success: function(data) {
                if (data.status != 2) {
                    $('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');                    
                    location.reload(true); 
                } else {
                    $('.box-head').html('<div class="alert alert-warning alert-dismissable col-ssm-12" >' + data.msg + '</div>');                    
                }
            }
        });
    });
    $('#dataTableBuilder tbody').on('click', 'td.details-control ', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });
    //Created by SHAH33R
    $('#regenerate_statement').on('click', function() {
        var URL_pdfGenerationAction = "{{ URL::to('admin/generate_pdf_for_merchants') }}";
        var merchants = [merchant_id];
        if (merchants) {
            $(".loadering-statement").css("display", "block");
            $('.box-head').html('')
        }
        $.ajax({
            type: 'POST',
            data: {
                'merchants': merchants,
                '_token': _token
            },
            url: URL_pdfGenerationAction,
            success: function(data) {
                if (data.status == 1) {
                    $(".loadering-statement").css("display", "none");
                    $('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                } else {
                    $('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                }
            }
        });
    });
    $('#last_statement').on('click', function() {
        var url = "{{ URL::to('admin/view_statements_merchants/'.$merchant->id) }}";
        $(".loadering-statement").css("display", "block");
        $('.box-head').html('')
        $.ajax({
            type: 'POST',
            data: {
                '_token': _token
            },
            url: url,
            success: function(data) {
                if (data.status == 1) {
                    $(".loadering-statement").css("display", "none");
                    $('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                } else {
                    $('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                }
            }
        });
    });
    //Sub status flag change
    $('#sub_status_flag_id').on('click', function() {
        $('#subStatusFlagModal').modal('show');
    });
    $('#submitSubStatusFlag').on('click', function() {
        var URL_subStatusFlag = "{{ URL::to('admin/merchants/change-substatus-flag') }}";
        var substatusflag_id = $('#sub-status-flag').val();
        $.ajax({
            type: 'POST',
            data: {
                '_token': _token,
                'merchant_id': merchant_id,
                'sub_status_flag': substatusflag_id
            },
            url: URL_subStatusFlag,
            success: function(data) {
                if (data.status == 1) {
                    $('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                    setTimeout(function() {
                        location.reload();
                    }, 2500);
                } else {
                    $('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                }
            }
        });
    });
    var paused = "{{ $merchant->payment_pause_id }}";
    $('#pause-payment').on('click', function() {
        if (confirm('Do you really want to Pause payments?')) {
            var URL_pausePayment = "{{ route('admin::merchants::payments.pause') }}";
            if (!paused) {
                console.log('hi')
                $.ajax({
                    type: 'POST',
                    data: {
                        '_token': _token,
                        'merchant_id': merchant_id
                    },
                    url: URL_pausePayment,
                    success: function(data) {
                        if (data.status == 1) {
                            $('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                            setTimeout(function() {
                                location.reload();
                            }, 2500);
                        } else {
                            $('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                        }
                    }
                })
            }
        }
    })
    $('#resume-payment').on('click', function() {
        if (confirm('Do you really want to Resume payments?')) {
            var URL_resumePayment = "{{ route('admin::merchants::payments.resume') }}";
            if (paused) {
                $.ajax({
                    type: 'POST',
                    data: {
                        '_token': _token,
                        'merchant_id': merchant_id
                    },
                    url: URL_resumePayment,
                    success: function(data) {
                        if (data.status == 1) {
                            $('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                            setTimeout(function() {
                                location.reload();
                            }, 2500);
                        } else {
                            $('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
                        }
                    }
                })
            }
        }
    })
    //Documnt ready close
});
function addCommas(nStr) {
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}
function format(obj) {
    var partpayTableHtml ='<table class="table dataTable no-footer" cellpadding="0" cellspacing="0" border="0" style=""> <tr class="text-danger">';
    partpayTableHtml+='<tr class="text-danger">';
    partpayTableHtml+='<td class="partic">#</td>';
    partpayTableHtml+='<td class="partic">Participant</td>';
    partpayTableHtml+='<td class="text-left">Participant Share</td>';
    partpayTableHtml+='<td class="text-left">Management Fee</td>';
    partpayTableHtml+='<td class="text-left">To Participant</td>';
    @if(config('app.env')=='local')
    partpayTableHtml+='<td class="text-left">Principal</td>';
    partpayTableHtml+='<td class="text-left">Profit</td>';
    @endif
    partpayTableHtml+='<td class="text-left">Overpayment</td>';
    partpayTableHtml+='</tr>';
    partpayTableHtml+='</table>';
    var partpayTable=$(partpayTableHtml);
    var partPay =((obj.participant_payment));
    var total_syndication_amount =0;
    var total_mgmnt_fee          =0;
    var total_to_participant     =0;
    var total_principal          =0;
    var total_profit             =0;
    var total_overpayment        =0;
    $.each(partPay, function(key, val) {
        syndication_amount =(val.syndication_amount).replace(/[^\d.-]/g, '');
        mgmnt_fee          =(val.mgmnt_fee).replace(/[^\d.-]/g, '');
        to_participant     =(val.to_participant).replace(/[^\d.-]/g, '');
        principal          =(val.principal).replace(/[^\d.-]/g, '');
        profit             =(val.profit).replace(/[^\d.-]/g, '');
        overpayment        =(val.overpayment).replace(/[^\d.-]/g, '');
        total_syndication_amount =parseFloat(total_syndication_amount)+parseFloat(syndication_amount);
        total_mgmnt_fee          =parseFloat(total_mgmnt_fee)+parseFloat(mgmnt_fee);
        total_to_participant     =parseFloat(total_to_participant)+parseFloat(to_participant);
        total_principal          =parseFloat(total_principal)+parseFloat(principal);
        total_profit             =parseFloat(total_profit)+parseFloat(profit);
        total_overpayment        =parseFloat(total_overpayment)+parseFloat(overpayment);
        var partpaymentRow = $('<tr>' +
        '<td>' + ++key + '</td>' +
        '<td title="' + val.participant_full_name + '">' + val.participant + '</td>' +
        '<td class="text-left" title='+val.real_syndication_amount+'>' + val.syndication_amount + '</td>' +
        '<td class="text-left">' + val.mgmnt_fee + '</td>' +
        '<td class="text-left">' + val.to_participant + '</td>' +
        @if(config('app.env')=='local')
        '<td class="text-left">' + val.principal + '</td>' +
        '<td class="text-left">' + val.profit + '</td>' +
        @endif
        '<td class="text-left">' + val.overpayment + '</td>' +
        '</tr>');
        partpayTable.append(partpaymentRow);
    });
    var partpaymentRowHtml ='<tr style="background-color:#f6f8fb">';
    partpaymentRowHtml+='<td class="text-left" colspan="2"></td>';
    partpaymentRowHtml+='<td class="text-left">$' + addCommas(parseFloat(total_syndication_amount).toFixed(2)) + '</td>';
    partpaymentRowHtml+='<td class="text-left">$' + addCommas(parseFloat(total_mgmnt_fee).toFixed(2)) + '</td>';
    partpaymentRowHtml+='<td class="text-left">$' + addCommas(parseFloat(total_to_participant).toFixed(2)) + '</td>';
    @if(config('app.env')=='local')
    partpaymentRowHtml+='<td class="text-left">$' + addCommas(parseFloat(total_principal).toFixed(2)) + '</td>';
    partpaymentRowHtml+='<td class="text-left">$' + addCommas(parseFloat(total_profit).toFixed(2)) + '</td>';
    @endif
    partpaymentRowHtml+='<td class="text-left">$' + addCommas(parseFloat(total_overpayment).toFixed(2)) + '</td>';
    partpaymentRowHtml+='</tr>';
    var partpaymentRow = $(partpaymentRowHtml);
    partpayTable.append(partpaymentRow);
    return partpayTable;
}
// $('*[title]').tooltip('disable');
// // $('#ctd').tooltip('show');
// // $('#ctd_our_portion').tooltip('show');
// // $('#net_zero').tooltip('show');
// // $('#overpayment').tooltip('show');
// // $('#our_pace_amount').tooltip('show');
// $('[data-toggle="tooltip"]').tooltip('show');
</script>
<script type="text/javascript" defer>
$(document).ready(function() {
    $("#auto_investors").children().find("li.select2-selection__choice").prevObject.each(function(index, option) {
        let brackets = option.innerHTML.split("(").join('').split(")")
        if (brackets.length == 3) {
            option.classList.add("change_color")
        }
    })
    // create our custom selection adapter by extending the select2 default one
    $.fn.select2.amd.define('select2-ClassPreservingMultipleSelection', [
        'jquery',
        'select2/selection/multiple',
        'select2/utils'
    ], function($, MultipleSelection, Utils) {
        function ClassPreservingMultipleSelection($element, options) {
            ClassPreservingMultipleSelection.__super__.constructor.apply(this, arguments);
        }
        Utils.Extend(ClassPreservingMultipleSelection, MultipleSelection);
        // this function was changed to propagate the `selection` argument
        ClassPreservingMultipleSelection.prototype.selectionContainer = function(selection) {
            var $baseContainer = ClassPreservingMultipleSelection.__super__.selectionContainer.apply(this, arguments);
            return $baseContainer.addClass(selection.element.className);
        };
        // this is a copy-paste of the base method with only one line changed
        ClassPreservingMultipleSelection.prototype.update = function(data) {
            this.clear();
            if (data.length === 0) {
                return;
            }
            var $selections = [];
            for (var d = 0; d < data.length; d++) {
                var selection = data[d];
                // This is the only changed line in this method - we added the 'selection' propagation
                var $selection = this.selectionContainer(selection);
                var formatted = this.display(selection, $selection);
                $selection.append(formatted);
                $selection.prop('title', selection.title || selection.text);
                $selection.data('data', selection);
                $selections.push($selection);
            }
            var $rendered = this.$selection.find('.select2-selection__rendered');
            Utils.appendMany($rendered, $selections);
        };
        return ClassPreservingMultipleSelection;
    });
    $("#auto_investors").select2({
        selectionAdapter: $.fn.select2.amd.require('select2-ClassPreservingMultipleSelection')
    });
});
$('#adjust_investor_funded_amount_area_button').click(function(){
    $('#adjust_investor_funded_amount_area').toggle();
});
$('#investor_diffrence_area_button').click(function(){
    $('#investor_diffrence_area').toggle();
});
$('#company_diffrence_area_button').click(function(){
    $('#company_diffrence_area').toggle();
});
$('#update_max_participant_fund_button').click(function(){
    $('#update_max_participant_fund_area').toggle();
});
function ConfirmToAdjustInvestorFundedAmount() {
    if (!confirm("Are you sure to proceed ? Please Confirm!")) { return false; }
    window.location = "{{url('admin/merchants/adjust-investor-funded-amount',['mid'=>$merchant->id])}}";
}
function ConfirmToAdjustCompanyFundedAmount() {
    if (!confirm("Are you sure to proceed ? Please Confirm!")) { return false; }
    window.location = "{{url('admin/merchants/adjust-company-funded-amount',['mid'=>$merchant->id])}}";
}
</script>
<script type="text/javascript">
var TransactionDataTable = $('#TransactionDataTable').DataTable({
    "processing" : true,
    "serverSide" : true,
    "fixedHeader": true,
    "searching"  : false,
    "lengthMenu" : [[50, 100, 200, 1000], [50, 100, 200, 1000, ] ],
    "ajax": {
        "url": "<?= route('admin::merchants::TransactionData') ?>",
        "dataType": "json",
        "type": "POST",
        data: function(d) {
            d._token        = "<?= csrf_token() ?>";
            d.merchant_id   = "{{$merchant_id}}";
            d.status        = $('#transaction_status').val();
        },
    },
    "columns": [
        {"data": "id",'className':"text-left", 'visible': true },
        {"data": "date", 'visible': true },
        {"data": "type", 'visible': true },
        {"data": "mode_of_payment", 'visible': true },
        {"data": "status", 'visible': true },
        {"data": "debit",'className':"text-left", 'visible': true },
        {"data": "credit",'className':"text-left", 'visible': true },
        {"data": "balance",'className':"text-left", 'visible': true },
    ],
    "footerCallback":function(t,o,a,l,m){
        var n=this.api(),o=TransactionDataTable.ajax.json();
        $(n.column(5).footer()).html(o.debit);
        $(n.column(6).footer()).html(o.credit);
        $(n.column(7).footer()).html(o.balance);
    },
});
$('.TransactionDataTableChange').change(function() {
    TransactionDataTable.draw();
});
</script>
<script src="{{asset('js/flexmenu.min.js')}}" type="text/javascript"></script>
<script type="text/javascript" >
$('ul.menu.flex').flexMenu({
    showOnHover: true,
    linkText: 'More'
});
$('ul.menu.flex-multi-off').flexMenu({
    showOnHover: true,
    linkText: 'More'
});
</script>
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function(){
    $(document).on('click','.revert_button',function(){
        var table_id = $(this).attr('table_id');
        var date     = $(this).attr('date');
        var payment  = $(this).attr('payment');
        $('#revert_participent_payment_id').val(table_id);
        $('#RevertPaidDate').html(date);
        @if(!$revert_date_mode)
        $('#revert_date').val(date).change();
        @else
        $('#revert_date').val("{{ date('m-d-Y') }}").change();
        @endif
        $('#RevertPayment').html(payment);
        $('#PaymentRevertSubmit').attr('disabled',false);
        $('#RevertPaymentModal').modal('show');
    });
});
$('#PaymentRevertSubmit').on('click', function() {
    if (!confirm('Do you really want to Revert the payment')) { return false; }
    $('#PaymentRevertSubmit').attr('disabled',true);
    var data = $('#PaymentRevertForm').serialize();
    $.ajax({
        type: 'POST',
        data: data,
        url: URL_RevertPayment,
        success: function(data) {
            if(data.status==0){ Swal.fire('warning!', data.message, 'error');  $('#PaymentRevertSubmit').attr('disabled',false); return false; }
            Swal.fire({
                icon: 'info',
                title: 'Result',
                text: data.message,
                confirmButtonText: `OK`,
            }).then((result) => {
                location.reload();
            });
        }
    });
});
$('.CustomerInvestmentChange').click(function(){
    var table_id=$(this).attr('table_id');
    var investor=$(this).attr('investor');
    var funded=$(this).attr('funded');
    var actual_funded=$(this).attr('actual_funded');
    $('#CustomFundedAmount_New_Funded').val(actual_funded);
    $('#CustomFundedAmount_merchant_user_id').val(table_id);
    $('#CustomerFundInvestor').text(investor);
    $('#CustomerFundAmount').text(funded);
    $('#CustomFundedAmountModal').modal('show');
});
$('#CustomFundedAmountSubmit').on('click', function() {
    $('#CustomFundedAmountSubmit').attr('disabled',false);
    if (!confirm('Do you really want to Update Funded Amount')) { return false; }
    $('#CustomFundedAmountSubmit').attr('disabled',true);
    var data = $('#CustomFundedAmount').serialize();
    $.ajax({
        type: 'POST',
        data: data,
        url: URL_SingleInvestorFundAmountChange,
        success: function(data) {
            if(data.status==0){ Swal.fire('warning!', data.message, 'error');  $('#CustomFundedAmountSubmit').attr('disabled',false); return false; }
            Swal.fire({
                icon: 'info',
                title: 'Result',
                text: data.message,
                confirmButtonText: `OK`,
            }).then((result) => {
                location.reload();
            });
        }
    });
});
</script>
<script>
jQuery(function($) {
    $('#investmentProgressbar').stepProgressBar({
        currentValue: '{{$total_received}}',
        steps: [
            { topLabel: 'Start', value: 0},
            { topLabel: 'Principal', value: {{$total_invested_amnt+$total_mgmnt_fee_amount}}},
            { topLabel: 'RTR', value: {{round($total_rtr,2)}}},
            @if($total_balance<0)
            { topLabel: 'Overpayment', value: {{$total_rtr+abs($total_balance)}}},
            @endif
        ],
        unit: '$'
    });
    $('#investmentProgressbar').stepProgressBar('setCurrentValue', {{$total_received}});
});
</script>
<script type="text/javascript">
jQuery(function($) {
    $('.pie_progress').asPieProgress({
        namespace: 'pie_progress'
    });
    $('.pie_progress').asPieProgress('start');
    $('#principal_circle').asPieProgress('go', {{round($paid_principal,2)}});
    $('#profit_circle').asPieProgress('go', {{round($paid_profit,2)}});
    $('#total_circle').asPieProgress('go', {{round($total_received,2)}});
});
</script>

@stop
@section('styles')
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
<style type="text/css">
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
.nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
    color: #495057 !important;
}
.form-control.multi-datepicker[readonly] {
    background-color: inherit;
}
</style>
<style type="text/css">
.alert-danger {
    background-color: #f18376!important;
}
* {
    box-sizing: border-box;
}
.openBtn {
    display: flex;
    justify-content: left;
}
.openButton {
    border: none;
    border-radius: 5px;
    background-color: #1c87c9;
    color: white;
    padding: 14px 20px;
    cursor: pointer;
    position: fixed;
}
.loginPopup {
    position: relative;
    text-align: center;
    width: 100%;
}
.formPopup {
    display: none;
    position: fixed;
    left: 45%;
    top: 5%;
    transform: translate(-50%, 5%);
    border: 3px solid #999999;
    z-index: 9;
}
.formContainer {
    max-width: 300px;
    padding: 20px;
    background-color: #fff;
}
.formContainer input[type=text].custom-type,
.formContainer input[type=password].custom-type {
    width: 100%;
    padding: 15px;
    margin: 5px 0 20px 0;
    border: none;
    background: #eee;
}
.formContainer input[type=text].custom-type:focus,
.formContainer input[type=password].custom-type:focus {
    background-color: #ddd;
    outline: none;
}
.formContainer .btn.custom-type {
    padding: 12px 20px;
    border: none;
    background-color: #8ebf42;
    color: #fff;
    cursor: pointer;
    width: 100%;
    margin-bottom: 15px;
    opacity: 0.8;
}
.formContainer .cancel.custom-type {
    background-color: #cc0000;
}
.formContainer .btn.custom-type:hover,
.openButton:hover {
    opacity: 1;
}
.toggle.btn{
    min-width: 150px;
}
.help-tip{
    position: relative;
    top: 18px;
    right: 18px;
    text-align: center;
    background-color: #BCDBEA;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 14px;
    line-height: 26px;
    cursor: default;
}
.help-tip:before{
    content:'i';
    font-weight: bold;
    color:#fff;
}
span.inline{
    word-break: keep-all;
    display: inline;
    white-space: nowrap;
}
.help-tip:hover  ~ p{
    display:block;
    transform-origin: 100% 0%;
    z-index: 10;
    -webkit-animation: fadeIn 0.3s ease-in-out;
    animation: fadeIn 0.3s ease-in-out;
}
.help-tip ~ p{    /* The tooltip */
    display: none;
    text-align: left;
    background-color: #1E2021;
    padding: 20px;
    width: 300px;
    position: absolute;
    border-radius: 3px;
    box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
    right: -4px;
    color: #FFF;
    font-size: 13px;
    right: -27px;
    top: 34px;
    line-height: 1.4;
}
.help-tip ~ p:before{ /* The pointer of the tooltip */
    position: absolute;
    content: '';
    width:0;
    height: 0;
    border:6px solid transparent;
    border-bottom-color:#1E2021;
    right:10px;
    top:-12px;
}
.help-tip p:after{ /* Prevents the tooltip from being hidden */
    width:100%;
    height:40px;
    content:'';
    position: absolute;
    top:-40px;
    left:0;
}
/* CSS animation */
@-webkit-keyframes fadeIn {
    0% { 
        opacity:0; 
        transform: scale(0.6);
    }
    100% {
        opacity:100%;
        transform: scale(1);
    }
}
@keyframes fadeIn {
    0% { opacity:0; }
    100% { opacity:100%; }
}
</style>
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel='stylesheet'/>
<link href="{{ asset('/css/optimized/merchant_view.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/jquery.stepProgressBar.css') }}" rel="stylesheet">
<link href="{{ asset('/css/asPieProgress.css') }}" rel="stylesheet">
<style media="screen">
.pie_progress {
    color: black !important;
    width: 200px;
    margin: 10px auto;
}
@media all and (max-width: 768px) {
    .pie_progress {
        color: black !important;
        width: 80%;
        max-width: 300px;
    }
}
</style>
@stop
