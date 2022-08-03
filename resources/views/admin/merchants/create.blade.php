
<?php
use App\Models\Views\MerchantUserView;
use App\Bank;
?>
@extends('layouts.admin.admin_lte')
@section('style')
<style>
input[readonly] {
    cursor: text;
    background-color: #fff;
}
</style>
@endsection
@section('content')
<input type="hidden" name="form_action" id="form_action" value="{{$action}}">
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
    </a>
</div>
@if($action=="create")
{{ Breadcrumbs::render('merchantCreate') }}
@else
{{ Breadcrumbs::render('merchantEdit') }}
@endif
<?php 
$funded_amount_investors=0;
if(isset($merchant)){
    $funded_amount_investors = $merchant->investors->sum('amount');
}
$readonly = $funded_amount_investors?true:false;
?>
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
        <div class="box-head">
        </div>
        <!-- form start -->
        @if($action=="create")
        {!! Form::open(['route'=>'admin::merchants::storeCreate', 'method'=>'POST','id'=>'merchant_create_form']) !!}
        @else
        {!! Form::open(['route'=>'admin::merchants::update', 'method'=>'POST','id'=>'merchant_create_form']) !!}
        @endif
        @include('layouts.admin.partials.lte_alerts')
        @php
        $merchant_id=isset($merchant)? $merchant->id : '';
        $pay_status=isset($payment_status)?$payment_status:0;
        $investor_assign_status=isset($investor_assign_status)?$investor_assign_status:'';
        $p_status=($pay_status>0)?'readonly':'';
        $p1_status=($pay_status>0)?'disabled':'';
        @endphp
        <div class="box-body">
            <!-- Styled Form -->
            <div class="form-box-styled">
                <div class="row">
                    <div class="col-md-12"><h3>Business Informations</h3></div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Business Name <span class="validate_star">*</span></label>
                            {!! Form::text('name',isset($merchant)? $merchant->name : old('name'),['class'=>'form-control','id'=>'merchantNameId','required'=>'required','placeholder' => 'Name',$p_status,'autocomplete'=>"off"]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">First Name <span class="validate_star">*</span></label>
                            {!! Form::text('first_name',isset($merchant)? $merchant->first_name : old('name'),['class'=>'form-control','id'=>'first_name','required'=>'required','placeholder' => 'First Name','maxlength'=>"50",'autocomplete'=>"off"]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Last Name </label>
                            {!! Form::text('last_name',isset($merchant)? $merchant->last_name : old('name'),['class'=>'form-control','id'=>'last_name','placeholder' => 'Last Name','maxlength'=>"50",$p_status,'autocomplete'=>"off"]) !!}
                        </div>
                    </div>
                    {!! Form::hidden('merchant_id',isset($merchant)? $merchant->id : '', ['id'=>'merchant_id']) !!}
                    @php
                    $user_id=isset($user->id)?$user->id:'';
                    @endphp
                    {!! Form::hidden('user_id',$user_id,['id'=>'user_id']) !!}
                    <?php $userId=Auth::user()->id;?>
                    @if (!isset($merchant))
                    {!! Form::hidden('creator_id',$userId) !!}
                    @endif
                    <!-- <div class="col-md-4"> -->
                    <!-- <div class="form-group"> -->
                    <!-- <label for="exampleInputEmail1">Business Entity Name <span class="validate_star">*</span> -->
                    <!-- </label> -->
                    <!-- {!! Form::text('business_en_name',isset($merchant)? $merchant->business_en_name : old('business_en_name'),['class'=>'form-control','id'=>'businessEntityNameId','required'=>'required','placeholder' => 'Business Entity Name']) !!} -->
                    <!-- </div> -->
                    <!-- </div> -->
                    <?php  $merchant_state_id=isset($merchant)?$merchant->state_id:old('state_id');
                    $lender_id= isset($merchant)?$merchant->lender_id:(isset($default_lender->id)?$default_lender->id:old('lender_id'));
                    $industry_id=isset($merchant)?$merchant->industry_id:old('industry_id');
                    ?> 
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Business Address </label>
                            {!! Form::text('business_address',isset($merchant)? $merchant->business_address : old('business_address'),['class'=>'form-control','id'=>'merchantBusinessAdress','placeholder' => 'Business Address',$p_status,'autocomplete'=>"off"]) !!}
                        </div>
                    </div>           
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">City </label>
                            {!! Form::text('city',isset($merchant)? $merchant->city : old('city'),['class'=>'form-control','id'=>'merchantCity','placeholder' => 'City',$p_status,'autocomplete'=>"off"]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>State <font color="#FF0000"> * </font> </label>
                            <select id="state_id" name="state_id" class="form-control" >
                                <option selected="selected" disabled="disabled" hidden="hidden" value="">Select State</option>
                                @foreach($states as $state)
                                <option   {{ (($state->id==$merchant_state_id)?'selected':'') }}  value="{{$state->id}}">{{$state->state}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Zip Code </label>
                            {!! Form::text('zip_code',isset($merchant)? $merchant->zip_code : old('zip_code'),['class'=>'form-control','id'=>'merchantZipCode','placeholder' => 'Zip Code',$p_status,'autocomplete'=>"off"]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Industry<span class="validate_star">*</span></label>
                            <select id="industry_id" name="industry_id" class="form-control" >
                                <option selected="selected" disabled="disabled" hidden="hidden" value="">Select Industry</option>
                                @foreach($industries as $industry)
                                <option {{ (old('industry_id')==$industry->id?'selected':($industry_id==$industry->id? 'selected':'')) }} value="{{$industry->id}}">{{$industry->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> 
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Cell Phone </label>
                            {!! Form::text('cell_phone',isset($merchant)? $merchant->cell_phone : old('cell_phone'),['class'=>'form-control','id'=>'merchantCellPhone','placeholder' => 'Cell Phone','autocomplete'=>"off"]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Login Email
                                <span class="pull-right">
                                    <a href=" {{ (isset($user->id)) ? route('admin::merchants::merchantUser', ['id' => $user->id]) : 'javascript:void(0);' }}" style="display: {{ isset($user->id) ? 'block' : 'none' }}" class="badge bg-secondary edit-user-btn"> <i class="glyphicon glyphicon-edit"></i> Edit</a>
                                </span>
                                <div class="form-group synd-march reset_password">
                                    <div class="input-group check-box-wrap">
                                        <div class="input-group-text">
                                            {{--{!! Form::text('email',isset($user) ? $user->email : old('email'),['class'=>'form-control','placeholder' => 'Email','id'=>'email']) !!}--}}
                                            <input value="{{isset($user) ? $user->email : old('email')}}" class="form-control" placeholder="Email" id="email" autocomplete="new-password" name="email" type="text" onfocus="this.removeAttribute('readonly');" readonly >
                                            <label class="chc">
                                                <input type="checkbox" name="email_notification" value="1" id="email_notification" {{$p1_status}} autocomplete = "off"/>
                                                <span class="checkmark chek-m"></span>
                                                <span class="chc-value">Reset password and send notifications</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-4 password-container" style="display: {{ !isset($user->id) ? 'block' : 'none' }}">
                        <label for="exampleInputPassword1">Password  <span style="display: none;" class="validate_star" id="password_1">*</span></label>
                        {!! Form::password('password',['class'=>'form-control','placeholder'=>'Enter password ','id'=> 'inputPassword','minlength'=>6,'autocomplete'=>"off"]) !!}
                        <span id="invalid-inputPassword" />
                    </div>
                    <div class="form-group col-md-4 password-container" style="display: {{ !isset($user->id) ? 'block' : 'none' }}">
                        <label for="exampleInputPassword1">Confirm Password  <span style="display: none;" class="validate_star" id="password_2">*</span><div class="clearfix"></div></label>
                        {!! Form::password('password_confirmation',['class'=>'form-control','placeholder'=>'Enter password ','id'=> 'inputConfirmPassword','minlength'=>6,'autocomplete'=>"off"]) !!}
                        <span id="invalid-inputConfirmPassword" />
                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Notification Emails</label>
                            {!! Form::text('merchant_email', isset($merchant)? $merchant->notification_email : old('merchant_email'),['class'=>'form-control','placeholder' => 'Email','id'=>'notification_email',$p_status,'autocomplete'=>"off"]) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Centrex Advance ID</label>
                            {!! Form::text('centrex_advance_id', isset($merchant)? $merchant->centrex_advance_id : old('centrex_advance_id'),['class'=>'form-control','placeholder' => 'Centrex Advance Id','id'=>'centrex_advance_id','autocomplete'=>'off','onkeypress'=>"return IsAlphaNumeric(event);"]) !!}
                        </div>
                    </div>
                    
                </div>
            </div>
            <!-- Styled Form -->
            <!-- Styled Form -->
            <div class="form-box-styled">
                <div class="row">
                    <div class="col-md-12"><h3>Payment Informations</h3></div>
                    <!-- Styled Form -->
                    <div class="col-md-4">
                        <div class="form-group marh-cre">
                            <label for="exampleInputEmail1">Funded <span class="validate_star">*</span> 
                            </label>
                            <div class="input-group">
                                <span class="input-group-text march-fund">$</span>
                                @if($action!="create")
                                {!! Form::number('funded',isset($merchant->funded)?$merchant->funded : 0,['class'=>'form-control accept_digit_only','id'=>'funded','placeholder' => 'Funded',$p_status,'autocomplete'=>"off"]) !!}
                                @else
                                {!! Form::number('funded',isset($merchant->funded)?$merchant->funded :0,['class'=>'form-control accept_digit_only','id'=>'funded','placeholder' => 'Funded','autocomplete'=>"off"]) !!}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Factor Rate<span class="validate_star">*</span></label>
                            <div class="input-group">
                                {!! Form::text('factor_rate',isset($merchant)? $merchant->factor_rate : old('factor_rate'),['id'=>'factorRate','class'=>'form-control','pattern'=>"^-?[0-9]\d*(\.\d+)?$",$p_status,'autocomplete'=>"off"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">RTR</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                {!! Form::text('rtr',isset($merchant)? $merchant->rtr : old('rtr'),['id'=>'rtr','class'=>'form-control','disabled','autocomplete'=>"off"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Date Funded <span class="validate_star">*</span></label>
                            {!! Form::text('date_funded1',isset($merchant)? $merchant->date_funded : old('date_funded'),['class'=>'form-control datepicker','placeholder'=>\FFM::defaultDateFormat('format'),'id'=>'datepicker1','autocomplete'=>"off",$p1_status]) !!}
                            <input type="hidden" class="date_parse" name="date_funded" value="{{isset($merchant)? $merchant->date_funded : old('date_funded')}}" id="datepicker">
                        </div>
                    </div>
                    @php
                    $count=count($company);
                    $i=1;
                    @endphp
                    @if(!Auth::user()->hasRole(['company']))
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Maximum Participant <font color="#FF0000"> * </font></label>
                            <div class="input-group">
                                <span class="input-group-text">%</span>
                                <div class="col-sm-4 max-marc">
                                    @php
                                    $fund=0;
                                    if(isset($merchant->funded)){
                                        if(!empty($merchant->funded) && $merchant->funded!=0){
                                            $fund=$merchant->funded;
                                        }
                                    }
                                    $max_fund=0;
                                    if(isset($merchant->max_participant_fund)){
                                        if(!empty($merchant->max_participant_fund) && $merchant->max_participant_fund!=0){
                                            $max_fund=($fund)?round($merchant->max_participant_fund/$fund*100,2):0;
                                        }
                                    }
                                    @endphp
                                    <input type="text" placeholder="00.00" value="{{ ($max_fund)?$max_fund:old('max_participant_fund_per') }}" class="form-control " name="max_participant_fund_per" id="max_participant_fund_per" class="max_part  perntages_class_main" {{ $p_status }} autocomplete="off">
                                </div>
                                @php  $x_fund=old('max_participant_fund')?old('max_participant_fund'):0 @endphp
                                <div class="col-sm max-ma">
                                    <input type="text"  style="" class="form-control " value="{{isset($merchant->funded)?round($merchant->max_participant_fund,2):$x_fund}}"  id="max_participant_fund" name="max_participant_fund" class="max_part" {{ $p_status }}>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- company section taken -->
                    <input type="hidden" name="company_count" id="company_count" value={{$count}}>
                    <div class="form-box-styled btwn">
                        <div class="row" >
                            @if(isset($company))
                            @foreach($company as $key =>$value)
                            <div class="col-md-4 col-sm-12 row_test">
                                <div class="form-group">
                                    <span class="clip">
                                        <label for="exampleInputEmail1">{{ $value }}</label>
                                        <font color="#FF0000"> * </font>
                                    </span>
                                    <div class="input-group d-flex">
                                        <span class="input-group-text">%</span>
                                        <div class="col-sm-4 max-marc">
                                            <!--  {!! Form::text("company_per[$key]",isset($compamy_d[$key])? $compamy_d[$key]['max_participant_percentage']:0,['class'=>'form-control perntages_class company_per','id'=>"company_per[$key]",'placeholder'=>'VP Advance Percentage','onchange'=>"calculatePercentage(this.id,$key);"]) !!} -->
                                            {!! Form::text("company_per[$key]",isset($company_d[$key])? round($company_d[$key]['max_participant_percentage'],2):'0',['class'=>'form-control perntages_class company_per','id'=>'company_per_'.$i,'onchange'=>"calculatePercentage(this.id,$key);",'onkeypress'=>"return OnlyPercentage(event)", $p_status,'min'=>0,'readonly']) !!}
                                        </div>
                                        {!! Form::hidden("company_id[$key]",isset($value)?$key : old('company_id'),['class'=>'form-control company_id','id'=>'company_id','placeholder'=>'']) !!}
                                        <div class="col-sm max-ma">
                                            <?php $company_funded_amount=MerchantUserView::where('merchant_id',$merchant_id)->where('company',$key)->sum('amount'); ?>
                                            {!! Form::text("company_max[$key]",isset($company_d[$key])? round($company_d[$key]['max_participant'],2) :
                                            ((old('company_max'))?(old('company_max')):0)
                                            ,['class'=>'form-control company_max','id'=>'company_max_'.$i,'placeholder'=>'','onchange'=>"calculateMaxFund(this.id);",$p_status,'autocomplete'=>'off']) !!}
                                            <label id="error_message_for_velocity1_max" class="errors_msg1"></label>
                                        </div>
                                    </div>
                                    <label id="error_message_for_company_per_{{$i}}" class="errors_msg1"></label>
                                </div>
                            </div>
                            @php $i++ @endphp
                            @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Credit Score</label>
                            <div class="input-group">
                                {!! Form::number('credit_score',isset($merchant)? $merchant->credit_score : old('credit_score'),['class'=>'form-control',$p_status,'autocomplete'=>"off"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Commission<span class="validate_star">*</span></label>
                            <div class="input-group">
                                {!! Form::number('commission',isset($merchant)? $merchant->commission : old('commission'),['class'=>'form-control','pattern'=>"^-?[0-9]\d*(\.\d+)?$",$p_status,'readonly'=>$readonly,'autocomplete'=>"off"]) !!}
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Number Of Payments<span class="validate_star">*</span></label>
                            {!! Form::number('pmnts',isset($merchant)? $merchant->pmnts : old('pmnts'),['class'=>'form-control',$p_status,'autocomplete'=>"off"]) !!}
                        </div>
                    </div>
                    <!-- <div class="col-md-4"> -->
                    <!-- <div class="form-group"> -->
                    <!-- <label for="exampleInputEmail1">Open Status</label> -->
                    <!-- {!! Form::select('open_item',[0=>"No",1=>"Yes"] ,isset($merchant)? $merchant->open_item : old('open_item'),['class'=>'form-control']) !!} -->
                    <!-- </div> -->
                    <!-- </div> -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Status<span class="validate_star">*</span></label>
                            {!! Form::select('sub_status_id',$statuses,isset($merchant)? $merchant->sub_status_id : old('sub_status_id'),['class'=>'form-control',$p1_status]) !!}
                            @if($p1_status)
                            <input type="hidden" name="sub_status_id" value="{{$merchant->sub_status_id}}">
                            @endif
                            @if(isset($merchant->pay_off) && $merchant->pay_off == 1 )
                                @php
                                $payoff=isset($merchant->pay_off)? $merchant->pay_off :0;
                                @endphp
                                <label><input type="checkbox" value="0" name="pay_off" > Reset Payoff &nbsp</label>
                                @endif
                                @if(isset($merchant->money_request_status) && $merchant->money_request_status == 1)
                                @php
                                $money_request=isset($merchant->money_request_status)?($merchant->money_request_status == 1):0;
                            @endphp
                            <label><input type="checkbox" value="1" {{ ($money_request==1)?"checked":0 }} name="money_request_status" {{$p1_status}}> Reset Money Request</label>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Advance Type<span class="validate_star">*</span></label>
                            {!! Form::select('advance_type',\App\Merchant::getAdvanceTypes(),isset($merchant)? $merchant->advance_type : old('advance_type'),['class'=>'form-control',$p1_status]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Marketplace?<span class="validate_star">*</span></label>
                            {!! Form::select('marketplace_status',[0=>'No',1=>'Yes'],isset($merchant)? $merchant->marketplace_status : old('marketplace_status'),['class'=>'form-control','id'=>'marketplace',$p1_status]) !!}
                            <span id="error_message_for_marketplace" class="text-danger"></span>
                        </div>
                    </div>
                    @php
                    $notify_investor=isset($merchant->notify_investors)?$merchant->notify_investors:'';
                    @endphp
                    <div class="col-md-4" id="notify_investor_div">
                        <div class="form-group">
                            <label for="notify_investors">Notify investor through email
                            </label>
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                        <input type="checkbox" name="notify_investors" value="1" id="notify_investors" {{ ($notify_investor)?'checked':'' }} {{$p1_status}} autocomplete="off"/>
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check This</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Source<font color="#FF0000"> * </font></label>
                            <select id="source_id" name="source_id" class="form-control" {{$p1_status}}>
                                @foreach($merchant_source as $source)
                                <option {{isset($merchant)?$source->id==$merchant->source_id?'selected':'':''}}  value="{{$source->id}}">
                                    {{$source->name}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if(empty($lender_login))

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Lender <span class="validate_star">*</span></label>
                            <select id="lender_id" name="lender_id"  class="form-control" required="required" {{ $p1_status}}>
                                <option selected="selected" disabled="disabled" hidden="hidden" value="">Select Lender</option>
                                @foreach($admins as $admin)
                                <option  {{ ($lender_id==$admin->id)?'selected':'' }} value="{{$admin->id}}" >
                                    {{$admin->name}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @else
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Lender <span class="validate_star">*</span></label>
                            <select id="lender_id" name="lender_id"  class="form-control" required="required" {{$p1_status}}>
                                <option value="{{$lender_data['id']}}">{{$lender_data['name']}}</option>
                            </select>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Management Fee (%)<span class="validate_star">*</span></label>
                            {!! Form::select('m_mgmnt_fee',$syndication_fee_values,isset($merchant)? number_format($merchant->m_mgmnt_fee,2) :(isset($default_lender->management_fee)?number_format($default_lender->management_fee,2):old('m_mgmnt_fee')),['class'=>'form-control','placeholder'=>'Enter Management Fee','id'=> 'inputManagementFee','data-parsley-required-message' => 'Management Fee Field is required','required'=>'required',$p1_status]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Label</label>
                            {!! Form::select('label',$label,isset($merchant)? $merchant->label : old('label'),[$p1_status]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Origination Fee (%)</label>
                            <div class="input-group">
                                {!! Form::text('origination_fee',isset($merchant)? $merchant->origination_fee : old('origination_fee'),['class'=>'form-control','id'=>'origination_fee',$p_status,'autocomplete'=>"off"]) !!}
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    @php
                    $underwriting_status=isset($merchant->underwriting_status)?$merchant->underwriting_status:(isset($default_lender->underwriting_status)?$default_lender->underwriting_status:old('underwriting_status'));
                    $underwriting_status=json_decode($underwriting_status);
                    $s_prepaid_status=isset($default_lender)?$default_lender->s_prepaid_status:0;

                    @endphp
                   
                    <div class="col-md-6">
                        <div class="form-group synd-march">
                            <label for="exampleInputEmail1">Syndication Fee (%)<font color="#FF0000"> * </font></label>
                            <div class="input-group">
                                {!! Form::select('m_syndication_fee',$syndication_fee_values,isset($merchant)? number_format($merchant->m_syndication_fee,2) :(isset($default_lender->global_syndication)?number_format($default_lender->global_syndication,2): old('m_syndication_fee')),['class'=>'form-control' ,'pattern'=>"^-?[0-9]\d*(\.\d+)?$", 'min'=>'0','max'=>'5','id'=>'m_syndication_fee',$p1_status]) !!}
                                <div class="mrch">
                                    <span class="input-group-text">%</span>
                                    <span class="input-group-text">
                                        <label>
                                <input {{ ( (old('m_s_prepaid_status')==2 || $s_prepaid_status==2))?'checked':(isset($merchant)?($merchant->m_s_prepaid_status==2?'checked':''):'')}}
                                            value="2" type="radio" name="m_s_prepaid_status" id="s_prepaid_amount" {{$p1_status}} >  On Funding Amount?
                                        </label>
                                    </span>
                                    <span class="input-group-text"><label><input
                                        {{ (old('m_s_prepaid_status')==1 || $s_prepaid_status==1) ?'checked':(isset($merchant)?($merchant->m_s_prepaid_status==1?'checked':''):'')}}
                                        value="1" type="radio" name="m_s_prepaid_status" id="s_prepaid_rtr" {{ $p1_status }}>  On RTR?</label>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php
                    $status_count=count($underwriting_company);
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group synd-march">
                            <label for="exampleInputEmail1">Underwriting Fee (%)<font color="#FF0000"> * </font></label>
                            <div class="input-group">
                                {!! Form::select('underwriting_fee',$syndication_fee_values,isset($merchant)? number_format($merchant->underwriting_fee,2) :(isset($default_lender->underwriting_fee)?number_format($default_lender->underwriting_fee,2):old('underwriting_fee')),['class'=>'form-control' ,'pattern'=>"^-?[0-9]\d*(\.\d+)?$", 'min'=>'0','max'=>'5','id'=>'underwriting_fee',$p1_status]) !!}
                                <div class="mrch">
                                    <span class="input-group-text">%</span>
                                    @if($underwriting_company)
                                    @foreach($underwriting_company as $key =>$value)
                                    @php
                                    $status=isset($underwriting_status)?(in_array($key,!empty($underwriting_status)?($underwriting_status):
                                    (!empty(old('underwriting_status'))?old('underwriting_status'):[] )
                                    )):(in_array($key,(old('underwriting_status'))?( old('underwriting_status')):[]));
                                    $checked=(isset($status)?(($status==$key)?'checked':''):'');
                                    @endphp
                                    <span class="input-group-text">
                                        <label>
                                            <input type="checkbox" name="underwriting_status[]" value="{{ $key }}" {{ $checked }} id="m_underwriting_status_{{$key}}" class="m_underwriting_status" {{ $p1_status }}/>
                                            {{ $value }}
                                        </label>
                                    </span>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            <!-- <span class="errors_msg1" id="underwriting_fee_error"></span> -->
                        </div>
                    </div>
                    <!-- <div class="col-md-4"> -->
                    <!-- <div class="form-group"> -->
                    <!-- <label for="exampleInputEmail1">Phone </label> -->
                    <!-- {!! Form::text('phone',isset($merchant)? $merchant->phone : old('phone'),['class'=>'form-control','id'=>'merchantPhone','placeholder' => 'Phone']) !!} -->
                    <!-- </div> -->
                    <!-- </div> -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Experian Intelliscore</label>
                            <div class="input-group">
                                {!! Form::number('experian_intelliscore',isset($merchant)? $merchant->experian_intelliscore : old('experian_intelliscore'),['class'=>'form-control','pattern'=>"^-?[0-9]\d*(\.\d+)?$",$p_status,'autocomplete'=>"off"]) !!}
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Experian Financial Score</label>
                            <div class="input-group">
                                {!! Form::number('experian_financial_score',isset($merchant)? $merchant->experian_financial_score : old('experian_financial_score'),['class'=>'form-control','pattern'=>"^-?[0-9]\d*(\.\d+)?$",$p_status,'autocomplete'=>"off"]) !!}
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    @php
                    $market_place=isset($merchant->marketplace_status)?$merchant->marketplace_status:'';
                    $mer_mail=isset($user)? $user->email :'';
                    @endphp
                    <div class="col-md-4">
                        <div class="form-group synd-march">
                            <label for="achPull">ACH Pull</label>
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                        <input type="checkbox" name="ach_pull" value="1" id="achPull" {{ isset($merchant)? (($merchant->ach_pull) ? 'checked' : '' ):'' }} />
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check This</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($funded_amount_investors)
                    <div class="col-md-12 btn-wrap btn-right">
                        <div class="col-md-4">
                            <div class="form-group synd-march">
                                <p>
                                    After investment "Commission" edit is not possible
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-12 btn-wrap btn-right">
                        <!-- /.box-body -->
                        <div class="btn-box">
                            @if(@Permissions::isAllow('Merchants','View'))
                            <a class="btn btn-success" href="{{URL::to('admin/merchants')}}">List Merchants</a>
                            @endif
                            @if($action=="create")
                            @if(@Permissions::isAllow('Merchants','Create'))
                            {!! Form::submit('Create',['class'=>'btn btn-primary pull-right create_or_update','id'=>'merchant_create']) !!}
                            @endif
                            @else
                            @if(@Permissions::isAllow('Merchants','Edit'))
                            {!! Form::submit('Update',['class'=>'btn btn-primary pull-right create_or_update','id'=>'merchant_edit']) !!}
                            @endif
                            @if(@Permissions::isAllow('Merchants','View'))
                            <a class="btn btn-danger" href="{{URL::to('admin/merchants/view/'.$merchant_id)}}">View Merchant</a>
                            @endif
                            @php $user_1=isset($user)?$user->email:'' @endphp
                            @if($user_1)
                            <!-- href="{{URL::to('admin/merchants/reset-password?id='.$merchant->id)}}" -->
                            <!--  <a class="btn btn-info" id="reset_confirmation">Reset Password</a> -->
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(! isset($merchant) || $merchant->bankAccounts()->count() == 0)
        <div class="modal fade" id="bankAcoountModal" tabindex="-1" role="dialog" aria-labelledby="bankAcoountModallabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="bankAcoountModallabel">Add Bank Account</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="bankAcoountModalCancel"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="accountHolderName">Account Holders Name <span class="validate_star">*</span></label>
                            {!! Form::text('account_holder_name','',['class'=>'form-control','placeholder'=>'Account holder name', 'id'=>'accountHolderName','autocomplete'=>"off"]) !!}
                        </div>
                        <div class="form-group">
                            <label for="routingNumber">Routing <span class="validate_star">*</span></label>
                            {!! Form::text('routing_number','',['class'=>'form-control','placeholder'=>'Enter Bank Routing Number', 'id'=>'routingNumber','autocomplete'=>"off"]) !!}
                            <div id="routingNumberError" class="validate_star"></div>
                        </div>
                        <div class="form-group">
                            <label for="bankName">Bank Name <span class="validate_star">*</span></label>
                            {!! Form::text('bank_name','',['class'=>'form-control','placeholder'=>'Bank name will be fetched from routing number', 'id'=>'bankName', 'readonly','autocomplete'=>"off"]) !!}
                        </div>
                        <div class="form-group">
                            <label for="accountNumber">Account Number <span class="validate_star">*</span></label>
                            {!! Form::text('account_number','',['class'=>'form-control','placeholder'=>'Enter Account Number', 'id'=>'accountNumber', 'onkeypress'=>'return onlyNumberKey(event)', 'minlength'=>'4','autocomplete'=>"off"]) !!}
                            <div id="accountNumberError" class="validate_star"></div>
                        </div>
                        <div class="form-group">
                            <div class="input-group check-box-wrap"> 
                                <label>Bank Type <span class="validate_star">*</span></label>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="input-group-text nested">
                                            <div class="main">
                                                {{ Form::checkbox('type[]',Bank::DEBIT,true,['id'=>'debit','class'=>'bank_type checkType','disabled']) }}
                                                {{ Form::label('debit', ucfirst('Debit'),['for'=>'debit']) }}
                                            </div>
                                            <div class="sub">
                                                {{ Form::checkbox('default_debit',1,true,['id'=>'default_debit','class'=>'checkType', 'disabled']) }}
                                                {{ Form::label('default_debit', ucfirst('Set As Default Debit')) }} &emsp;&emsp;&emsp;&emsp;
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="input-group-text nested">
                                            <div class="main">
                                                {{ Form::checkbox('type[]',Bank::CREDIT,false,['id'=>'credit','class'=>'bank_type checkType']) }}
                                                {{ Form::label('credit', ucfirst('Credit'),['for'=>'credit']) }}
                                            </div>
                                            <div class="sub">
                                                {{ Form::checkbox('default_credit',1,false,['id'=>'default_credit','class'=>'checkType', 'disabled']) }}
                                                {{ Form::label('default_credit', ucfirst('Set As Default Credit')) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:void(0)" class="btn btn-default" id="bankAcoountModalCancel" data-bs-dismiss="modal">Cancel</a>
                        <a href="javascript:void(0)" class="btn btn-success" id="bankAcoountModalSubmit" data-bs-dismiss="modal">Confirm</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
        {!! Form::close() !!}
    </div>
    <!-- /.box -->
</div>
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
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-left:10px">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Error Box</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span id="errorBox"></span>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" class="btn btn-primary" data-bs-dismiss="modal">Cancel</a>
                <!--  <a href="javascript:void(0)" class="btn btn-primary" id="submit" data-bs-dismiss="modal">Yes</a> -->
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script src='{{ asset("js/jquery-mask.min.js")}}' type="text/javascript"></script>
<script type="text/javascript">
$(document).on('submit', 'form', function() {
    $(this).find('button:submit, input:submit').attr('disabled', 'disabled');
});
</script>
<script type="text/javascript">
    $('#origination_fee').keypress(function(event) {
      if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
        ((event.which < 48 || event.which > 57) &&
          (event.which != 0 && event.which != 8))) {
        event.preventDefault();
      }
      var text = $(this).val();
      if ((text.indexOf('.') != -1) &&
        (text.substring(text.indexOf('.')).length > 2) &&
        (event.which != 0 && event.which != 8) &&
        ($(this)[0].selectionStart >= text.length - 2)) {
        event.preventDefault();
      }
    });
</script>
<script>
function IsAlphaNumeric(e) {
    var specialKeys = new Array();
     specialKeys.push(8);  //Backspace
     specialKeys.push(9);  //Tab
     specialKeys.push(46); //Delete
     specialKeys.push(36); //Home
     specialKeys.push(35); //End
     specialKeys.push(37); //Left
     specialKeys.push(39); //Right
         var keyCode = e.keyCode == 0 ? e.charCode : e.keyCode;
         var ret = ((keyCode >= 48 && keyCode <= 57) || (keyCode >= 65 && keyCode <= 90) || keyCode == 32 || (keyCode >= 97 && keyCode <= 122) || (specialKeys.indexOf(e.keyCode) != -1 && e.charCode != e.keyCode));
         
         return ret;
     }

function OnlyPercentage(evt) {
    var val1;
    if (!(evt.keyCode == 46 || (evt.keyCode >= 48 && evt.keyCode <= 57))) {
        return false;
    } else {
        return true;
    }
    var parts = evt.srcElement.value.split('.');
    if (parts.length > 2)
    return false;
    if (evt.keyCode == 46)
    return (parts.length == 1);
    if (evt.keyCode != 46) {
        var currVal = String.fromCharCode(evt.keyCode);
        val1 = parseFloat(String(parts[0]) + String(currVal));
        if(parts.length==2)
        val1 = parseFloat(String(parts[0])+ "." + String(currVal));
    }
    if (val1 > 100)
    return false;
    if (parts.length == 2 && parts[1].length >= 2) return false;
}
$(document).ready(function () {
    $(".company_per").keypress(function (evt) {
        var theEvent = evt || window.event;
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
        if (key.length == 0) return;
        var regex = /^[0-9.,\b]+$/;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }
        if(evt.which == 46 && $(this).val().indexOf('.') != -1) {
            evt.preventDefault();
        } // prevent if already dot
    });   
    $(".company_per").on("input", function() {
        if (/^0/.test(this.value)) {
            this.value = this.value.replace(/^0/, "")
        }
    });
    $('#email').on('change',function()
    { if(!$('#notification_email').val())
    {
        var email= $('#email').val();
        $('#notification_email').val(email);}
    });
    $("#max_participant_fund").on("input", function() {
        $max_per=($("#max_participant_fund").val()/$("#funded").val())*100;
        if($max_per>100)
        {
            $("#max_participant_fund_per").val(parseFloat($max_per).toFixed(2));
            // $("#error_message_for_max_participant_fund_per").text('Percentage should not be greater than 100');
            $('#merchant_edit').prop('disabled', true);
            $('#merchant_create').prop('disabled', true);
        }
        else
        {
            $("#max_participant_fund_per").val(parseFloat($max_per).toFixed(2));
            $('#merchant_edit').prop('disabled', false);
            $('#merchant_create').prop('disabled', false);
        }
    });
    $("#max_participant_fund_per").on("input", function() {
        if (/^0/.test(this.value)) {
            this.value = this.value.replace(/^0/, "")
        }
        $max_per=this.value;
        if($max_per>100)
        {
            $('#merchant_edit').prop('disabled', true);
            $('#merchant_create').prop('disabled', true);
        }
        else
        {
            $('#merchant_edit').prop('disabled', false);
            $('#merchant_create').prop('disabled', false);
        }

    });
    if($('#marketplace').val()==1)
    {
        //document.getElementById('notify_investor_div').style.display='block';
        $("#notify_investor_div").css("display","block");
    }else{
        //document.getElementById('notify_investor_div').style.display='none';
        $("#notify_investor_div").css("display","none");
    }
    // verifyMerchantUser();
    setTimeout(function () {
        $('.password-container').find('input').val('');
    }, 200);
    $('[name=email]').on('change', function () {
        verifyMerchantUser();
    });
    $("#credit").click(function(){
        if(this.checked){
            $('#default_credit').attr('disabled',false);
        } else {
            $('#default_credit').attr('disabled',true);
            $('#default_credit').prop('checked',false);
        }
    });
});
function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}
function verifyMerchantUser() {
    $.ajax({
        method: 'POST',
        url: '{{ url('admin/merchants/user') }}?_token={{ csrf_token() }}',
        data: 'email=' + encodeURIComponent($('[name=email]').val()) + '&user_id={{ (isset($user->id)) ? $user->id : 0 }}',
        dataType: 'json'
    }).done(function(response) {
        let userId = response.user_id ? response.user_id : 0;
        $('[name=user_id]').val(userId);
        if( userId > 0 ) {
            $('.edit-user-btn').show();
            // $(".reset_password").css("display","none");
            $('.edit-user-btn').attr('href', '{{ url('admin/merchants/user/') }}/' + userId);
            // $('.edit-user-btn').attr('href', '{{ url('admin/merchants/show-merchant-users') }}');
            $('.password-container').hide().find('input').val('');
        } else {
            $('.edit-user-btn').hide();
            $('.password-container').show();
        }
    }).fail(function (response) {
        $('.edit-user-btn').hide();
    });
}
function calculatePercentage(cur_id){
    var total_percentage = 0;
    var total_company = $('#company_count').val();
    // alert(total_company);
    var index = cur_id.split("_").pop();
    var cur_per = $("#"+cur_id).val();
    var cur_amount = (cur_per/100)*$("#max_participant_fund").val();
    var remain_per = 100-cur_per;
    var remain_company = total_company-1;
    var common_per = remain_per/remain_company;
    var common_amount = (common_per/100)*$("#max_participant_fund").val();
    // alert(cur_per);
    // if(remain_per>=0)
    // {
        $("#company_max_"+index).val(cur_amount.toFixed(2));
    // }
    // else
    // {
    //     $("#company_max_"+index).val(0);
    // }

    if(!cur_per){
        $("#"+cur_id).val(0);  
    }
    
    $('.perntages_class').each(function() {
        var id = $(this).attr('id');
        if(id != undefined) {
            var per_index = id.split("_").pop();
            var percentage = eval($("#"+id).val());
            if(percentage == undefined){
                percentage = 0;
            }
            // alert(percentage);
            // if(percentage==0)
            // {
            //    $("#error_message_for_company_per_"+index).text('');
            //     $('#merchant_edit').prop('disabled', false);
            //  $('#merchant_create').prop('disabled', false);
            // }
            if(per_index<=index && isNaN(percentage)==false){
                total_percentage = eval(total_percentage)+eval(percentage);
            }
            if((total_percentage).toFixed(2) >100){
                
                // $("#error_message_for_company_per_"+index).text('Percentage should not be greater than 100');
                // $('#merchant_create').prop('disabled', true);
                // $('#merchant_edit').prop('disabled', true);
            }else{
                if(cur_per>100)
                {
                    $("#error_message_for_company_per_"+index).text('Percentage should not be greater than 100');
                    $('#merchant_create').prop('disabled', true);
                    $('#merchant_edit').prop('disabled', true);
                }
                if((total_percentage).toFixed(2) <=100)
                {
                    $('#merchant_edit').prop('disabled', false);
                    $('#merchant_create').prop('disabled', false);
                    $("#error_message_for_company_per_"+index).text('');  
                }
            }
            // if((total_percentage)!=0){
            //       $("#company_per_"+index).val(0);
            // }
            var common_per = (100-total_percentage)/(total_company-index);
            if(per_index > index){
                if((total_percentage).toFixed(2) <100){
                    $("#error_message_for_company_per_"+per_index).text('');
                    $('#merchant_edit').prop('disabled', false);
                    $('#merchant_create').prop('disabled', false);
                }
                if(!common_per && total_percentage>100)
                {
                    $("#error_message_for_company_per_"+per_index).text('Percentage should not be greater than 100');
                    $('#merchant_edit').prop('disabled', true);
                    $('#merchant_create').prop('disabled', true);
                }
                else
                {
                    if(total_percentage<=100)
                    {
                        $("#error_message_for_company_per_"+per_index).text('');
                        $('#merchant_edit').prop('disabled', false);
                        $('#merchant_create').prop('disabled', false);
                    }
                }
                // if(common_per!=0 && common_per>0)
                // {
                //      $("#"+id).val(common_per.toFixed(2));
                // }
                // else
                // {
                //     $("#"+id).val(0);
                // }
                var common_amount = (common_per/100)*$("#max_participant_fund").val();
                
                // if(common_amount>0)
                // {
                //     $("#company_max_"+per_index).val(common_amount.toFixed(2));
                // }
                // else
                // {
                //     $("#company_max_"+per_index).val(0);
                // }
            }
            else
            {
                // $("#error_message_for_company_per_"+index).text('');
                //var common_amount = (common_per/100)*$("#max_participant_fund").val();
                //alert(total_percentage);
                //$("#company_max_"+per_index).val(common_amount.toFixed(2));
                //alert('h0000iii');
            }
        }
    });
}
function calculateMaxFund(cur_id){
    var total_percentage = 0;
    var total_company = $('#company_count').val();
    var index = cur_id.split("_").pop();
    var cur_amount = $("#"+cur_id).val();
    var cur_per = (cur_amount/$("#max_participant_fund").val())*100;
    sum=0;
    $('.company_max').each(function() {
        sum += Number($(this).val());
    });
    // if(eval(cur_amount) > eval($("#max_participant_fund").val())){
    //     $('#merchant_edit').prop('disabled', true);
    //     $('#merchant_create').prop('disabled', true);
    //     $("#error_message_for_company_per_"+index).text('Amount should not be greater than maximum participant amount');
    // }else{
    //     $('#merchant_edit').prop('disabled', false);
    //     $('#merchant_create').prop('disabled', false);
    //     $("#error_message_for_company_per_"+index).text('');
    // }
    if(cur_per)
    {
        $("#company_per_"+index).val(cur_per.toFixed(2));
    }
    else
    {
        $("#company_per_"+index).val(0);
    }
    if(!cur_amount){
        $("#"+cur_id).val(0);  
    }
    
    var remain_per = 100-cur_per;
    var remain_company = total_company-1;
    var common_per = remain_per/remain_company;
    var common_amount = (common_per/100)*$("#max_participant_fund").val();
    var cur_per_id = "company_per_"+index;
    // alert(cur_per_id);
    $('.company_per').each(function() {
        // alert($("#max_participant_fund").val());
        // alert(cur_amount);
        // if($("#max_participant_fund").val() < sum)
        // {
        //     if(index!=1)
        //     {
        //         $("#error_message_for_company_per_"+index).text('percentage should not be greater than 100');    
        //         $('#merchant_edit').prop('disabled', true);
        //         $('#merchant_create').prop('disabled', true);
        //     }
        //     else
        //     {
        //         $('#merchant_edit').prop('disabled', false);
        //         $('#merchant_create').prop('disabled', false);
        //         $("#error_message_for_company_per_"+index).text('');
        //     }
        // }
        //  else
        // { 
        //     $('#merchant_edit').prop('disabled', false);
        //     $('#merchant_create').prop('disabled', false);
        //     $("#error_message_for_company_per_"+index).text('');
        // }
        // if($("#max_participant_fund").val()==cur_amount )
        // {
        //     if(index!=1)
        //     {
        //         $("#error_message_for_company_per_"+index).text('percentage should not be greater than 100');    
        //         $('#merchant_edit').prop('disabled', true);
        //         $('#merchant_create').prop('disabled', true);
        //     }
        //     else
        //     {
        //         $('#merchant_edit').prop('disabled', false);
        //         $('#merchant_create').prop('disabled', false);
        //         $("#error_message_for_company_per_"+index).text('');
        //     }
            
        // }
        // else
        // { 
        //     $('#merchant_edit').prop('disabled', false);
        //     $('#merchant_create').prop('disabled', false);
        //     $("#error_message_for_company_per_"+index).text('');
        // }
        var id = $(this).attr('id');
        if(id != undefined) {
            var per_index = id.split("_").pop();
            var percentage = $("#"+id).val();
            //alert(percentage);
            if(per_index<=index){
                total_percentage = eval(total_percentage)+eval(percentage);
            }
            // if((total_percentage).toFixed(2) >100){
            //     // $("#error_message_for_velocity1_per").text('percentage should not be greater than 100');
            // }
            // else
            // {
            //     $("#error_message_for_company_per_"+per_index).text('');
            //     $("#error_message_for_velocity1_per"+index).text('');
            // }
            if(per_index > index){
                // var common_per = (100-total_percentage)/(total_company-index);
                // if(common_per>=0)
                // {
                //     $("#"+id).val(common_per.toFixed(2));
                // }
                // else{
                //     $("#"+id).val(0);
                // }
                // var common_amount = (common_per/100)*$("#max_participant_fund").val();
                // if(common_amount>=0)
                // {
                //     $("#company_max_"+per_index).val(common_amount.toFixed(2));
                // }
                // else
                // {
                //     $("#company_max_"+per_index).val(0);
                // }
            }
            else
            {
                //$("#company_per_"+index).val(0);
            }
            //  if(percentage==100){
            //   $("#company_per_"+index).val(percentage);
            // }
        }
    });
}
// function calculatePercentageold(cur_id){
// var total_company = $('#company_count').val();
// var index = cur_id.split("_").pop();
// var cur_per = $("#"+cur_id).val();
// var cur_amount = (cur_per/100)*$("#max_participant_fund").val();
// var remain_per = 100-cur_per;
// var remain_company = total_company-1;
// var common_per = remain_per/remain_company;
// var common_amount = (common_per/100)*$("#max_participant_fund").val();
// $("#company_max_"+index).val(cur_amount.toFixed(2));
// $('.perntages_class').each(function() {
//   var id = $(this).attr('id');
//   if(id != undefined && id!=cur_id) {
//    var per_index = id.split("_").pop();
//    $("#"+id).val(common_per.toFixed(4));
//    $("#company_max_"+per_index).val(common_amount.toFixed(2));
//   }
// });
// }
//   function calculateMaxFundold(cur_id){
//   var total_company = $('#company_count').val();
//   var index = cur_id.split("_").pop();
//   var cur_amount = $("#"+cur_id).val();
//   var cur_per = (cur_amount/$("#max_participant_fund").val())*100;
//   if(cur_amount > $("#max_participant_fund").val()){
//       alert("sdfs");
//   }
//   $("#company_per_"+index).val(cur_per);
//   var remain_per = 100-cur_per;
//   var remain_company = total_company-1;
//   var common_per = remain_per/remain_company;
//   var common_amount = (common_per/100)*$("#max_participant_fund").val();
//   var cur_per_id = "company_per_"+index;
//     $('.company_per').each(function() {
//     var id = $(this).attr('id');
//     if(id != undefined && id != cur_per_id) {
//      var per_index = id.split("_").pop();
//      $("#"+id).val(common_per.toFixed(4));
//      $("#company_max_"+per_index).val(common_amount.toFixed(2));
//     }
//   });
// }
$(document).ready(function () {
    if($('#funded').val()==0)
    {
        $("#max_participant_fund_per").prop("readonly", true);
        $("#max_participant_fund").prop("readonly", true);
    }
    $(".company_per").on("input", function() {
        if (/^0/.test(this.value)) {
            this.value = this.value.replace(/^0/, "")
        }
    })
    $('#origination_feev').keydown("click",function(event) {
        if ( event.keyCode == 46 || event.keyCode == 8 ) {
            // let it happen, don't do anything
        }
        else {
            // Ensure that it is a number and stop the keypress
            if (event.keyCode < 48 || event.keyCode > 57 ) {
                event.preventDefault();
            }
        }
    });
    //window.history.back();
    var URL_confirmMail = "{{ URL::to('admin/merchants/reset-password') }}";
    var merchant_id="{{ $merchant_id }}";
    var URL_checkBankAccount = "{{ URL::to('admin/merchants/check-bank-account') }}";
    var URL_createBankAccount = "{{ URL::to('admin/merchants/'.$merchant_id.'/bank_accounts/create') }}";
    $('#achPull').click(function(){
        if (this.checked) {
            var achPullCheckbox = this
            if(merchant_id){
                $.ajax({
                    type:'POST',
                    data: {'_token': _token,'merchant_id':merchant_id},
                    url:URL_checkBankAccount,
                    success:function(data)
                    {
                        bankStatus = data.status
                        if (data.status == 1) {
                            return true
                        }
                        if (data.status == 2) {
                            $('#bankAcoountModal').modal('show');
                        }
                    }
                });
                return true;
            }
            $('#bankAcoountModal').modal('show');
            return true;
        }
    })
    $('#bankAcoountModal').on('hidden.bs.modal', function () {
        checkBox = $('#achPull')
        if(checkBox.is(':checked')) {
            if($('#accountHolderName').val() && $('#bankName').val() && $('#accountNumber').val() && $('#routingNumber').val()) {
            } else {
                checkBox.prop('checked', false);
            }
        }
    })
    // $('#accountNumber').on('change', function() {
    //     if ($('#accountNumber').val().length < 4){
    //         $('#accountNumberError').text('Invalid Account Number')
    //         $('#accountNumber').val('')
    //     } else {
    //         $('#accountNumberError').text('')
    //     }
    // })
    URL_checkRoutingNumber = 'https://www.routingnumbers.info/api/data.json?rn='
    $('#routingNumber').on('change', function() {
        routingNumber =  $(this).val()
        $.ajax({
            type:'GET',
            url:URL_checkRoutingNumber+routingNumber,
            success:function(data)
            {
                if (data.code == 200) {
                    $('#bankName').val(data.customer_name)
                    $('#routingNumberError').text('')
                } else {
                    $('#routingNumber').val('')
                    $('#bankName').val('')
                    $('#routingNumberError').text('Invalid Routing Number')
                }
            }
        })
    })
    $('.company_max').on('input', function () {
        this.value = this.value.match(/^\d+\.?\d{0,2}/);
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
    $('#marketplace').on('change',function()
    {
        var funded = $("#funded").val();
        if($('#marketplace').val()==1)
        {
            document.getElementById('notify_investor_div').style.display='block';
            if(funded <=0){
                $("#error_message_for_marketplace").text('Funded amount should be greater than 0 if marketplace is "yes"');
                $('#merchant_edit').prop('disabled', true);
                $('#merchant_create').prop('disabled', true);
            }else{
                $('#merchant_edit').prop('disabled', false);
                $('#merchant_create').prop('disabled', false);
                $("#error_message_for_marketplace").text('');
                $("#error_message_for_funded").text('');
            }
        }
        else
        {
            document.getElementById('notify_investor_div').style.display='none';
            $('#merchant_edit').prop('disabled', false);
            $('#merchant_create').prop('disabled', false);
            $("#error_message_for_marketplace").text('');
            $("#error_message_for_funded").text('');
        }
    });
    $('#funded').on('change',function()
    {
        var funded = $("#funded").val();
        if(funded!=0)
        {
            $("#max_participant_fund_per").prop("readonly", false);
            $("#max_participant_fund").prop("readonly", false);
        }
        else
        {
            $("#max_participant_fund_per").prop("readonly", true);
            $("#max_participant_fund").prop("readonly", true);
            $("#max_participant_fund_per").val(0);
            $(".company_per").val(0);
        }
        max_participant_fund = $("#funded").val()*$("#max_participant_fund_per").val()/100;
        $("#max_participant_fund").val(max_participant_fund);
        for ( var i = 1;i <= count; i++ ) {
            company_per= $("#company_per_"+i).val();
            company_max = company_per/100*max_participant_fund;
            $('#company_max_'+i).val(company_max.toFixed(2));
        }
        if($('#marketplace').val()==1)
        {
            if(funded <=0){
                $("#error_message_for_funded").text('Funded amount should be greater than 0 if marketplace is "yes"');
                $('#merchant_edit').prop('disabled', true);
                $('#merchant_create').prop('disabled', true);
            }else{
                $('#merchant_edit').prop('disabled', false);
                $('#merchant_create').prop('disabled', false);
                $("#error_message_for_funded").text('');
                $("#error_message_for_marketplace").text('');
            }
        }
        else
        {
            $('#merchant_edit').prop('disabled', false);
            $('#merchant_create').prop('disabled', false);
            $("#error_message_for_funded").text('');
            $("#error_message_for_marketplace").text('');
        }
    });
    // $('#marketplace').on('change',function()
    //    {
    //         if($('#marketplace').val()==1)
    //         {
    //            $('#marketplace_permission').css('display','block');
    //         }
    //         else
    //         {
    //            $('#marketplace_permission').css('display','none');
    //         }
    //    });
    //
    var count='<?php echo $count ?>';
    // $('#velocity1_max').change(function () {
    //     liq_available = $('#max_participant_fund').val()-$('#velocity1_max').val();
    //     $('#velocity2_max').val(liq_available.toFixed(2));
    //     $('#velocity2_max').change();
    // });
    $('.company_per').on('click',function()
    {
        console.log('commented line 584 @ create.blade.php');
        // var row = $(this).closest('.row_test');
        // var per=row.find('.company_per').val();
        // $("#company_per_"+count).val(Math.round(100-per));
        // $("#max_participant_fund").change();
    });
    $('#max_participant_fund').on('change',function () {
        var company_per=0;
        max_participant_fund_val = $("#max_participant_fund").val();
        var company_max=0;
        for ( var i = 1;i <= count; i++ ) {
            if(max_participant_fund_val!=0)
            {
                company_per= $("#company_per_"+i).val();
                company_max = company_per/100*max_participant_fund_val;
                $('#company_max_'+i).val(company_max.toFixed(2));
                $('#company_max_'+i).change();
            }
            else
            {
                $('#company_max_'+i).val(0);
                $('#company_max_'+i).change();
            }
            if(max_participant_fund_val==0)
            {
                $('#max_participant_fund_per').val(0);
            }
        }
        // test=[];
        // test=$(".company_per").val();
        // alert(test);
        // velocity1_per = $("#velocity1_per").val();
        //max_participant_fund_val = $("#max_participant_fund").val();
        // velocity1_max = velocity1_per/100*max_participant_fund_val;
        //$('#velocity1_max').val(velocity1_max.toFixed(2));
        // $('#velocity1_max').change();
    });
    // $('#velocity2_max').on('change',function () {
    //     liq_available = $('#max_participant_fund').val()-$('#velocity2_max').val();
    //     $('#velocity1_max').val(liq_available.toFixed(2));
    // });
    $("#max_participant_fund_per").change(function(){
        max_participant_fund = $("#funded").val()*$("#max_participant_fund_per").val()/100;
        if(max_participant_fund!=0)
        {
            $("#max_participant_fund").val(max_participant_fund.toFixed(2));
            $("#max_participant_fund").change();
            $("#participant_per").change();
        }
        else
        {
            $("#max_participant_fund").val(0);
        }
        var company_max=0;
        var company_per=0;

        for ( var i = 1;i <= count; i++ ) {
            if(max_participant_fund!=0)
            {
                company_per= $("#company_per_"+i).val();
                company_max = company_per/100*max_participant_fund;
                $('#company_max_'+i).val(company_max.toFixed(2));
                $('#company_max_'+i).change();
            }
            else
            {
                $('#company_max_'+i).val(0);
                $('#company_max_'+i).change();
            }
            
        }
    });
    function calculateRTR(){
        var rtr = $("#funded").val() * $("#factorRate").val();
        if(rtr){
            return $("#rtr").val(rtr.toFixed(2));
        }
    }
    $('#factorRate').keyup(calculateRTR);
    $('#funded').keyup(calculateRTR);
    $('#funded').change(calculateRTR);
    // $("#velocity1_per").change(function(e){
    //     // var diff = 100-$("#participant_per").val();
    //     $("#velocity2_per").val( Math.round(100-$("#velocity1_per").val()));
    //     $("#max_participant_fund").change();
    // });
    // $("#velocity2_per").change(function(){
    //     $("#velocity1_per").val( Math.round(100-$("#velocity2_per").val()));
    //     $("#max_participant_fund").change();
    // });
    // $("#velocity2_per").change(function(){
    //     $("#velocity1_per").val( Math.round(100-$("#velocity2_per").val()));
    //     $("#max_participant_fund").change();
    // });
    // $('.company_per').change(function()
    //  {
    //      alert('hii');
    //  });
    $("#participant_per").change(function(){
        //alert('hii');
        participant_max = ($("#participant_per").val()/100)*$("#max_participant_fund").val();
        //if(participant_max)
        // {
        $("#participant_max").val(participant_max.toFixed(2));
        $("#max_participant_fund").change();
        // }
        //$("#velocity1_per").val( Math.round(100-$("#participant_per").val()));
        // $("#velocity2_per").val( 0);
    });
    $("#merchantPhone").keyup(function(){
        var phno = formatPhoneNumber($("#merchantPhone").val());
        if(phno!=null){
            document.getElementById("merchantPhone").value = phno;
        }
    });
    $("#merchantCellPhone").keyup(function(){
        var phno = formatPhoneNumber($("#merchantCellPhone").val());
        if(phno!=null){
            document.getElementById("merchantCellPhone").value = phno;
        }
    });
    function formatPhoneNumber(phoneNumberString) {
        var cleaned = ('' + phoneNumberString).replace(/\D/g, '')
        var match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/)
        if (match) {
            return '(' + match[1] + ') ' + match[2] + '-' + match[3]
        }
        return null
    }
    // $('.company_maxsdsdsds').blur(function(){
    //     var total_company = $('#company_count').val();
    //     var cur_id = $(this).attr('id');
    //     var index = cur_id.split("_").pop();
    //     var cur_amount = $("#"+cur_id).val();
    //     var cur_per = (cur_amount/$("#max_participant_fund").val())*100;
    //     $("#company_per_"+index).val(cur_per);
    //     var remain_per = 100-cur_per;
    //     var remain_company = total_company-1;
    //     var common_per = remain_per/remain_company;
    //     var common_amount = (common_per/100)*$("#max_participant_fund").val();
    //     var cur_per_id = "company_per_"+index;
    //       $('.company_per').each(function() {
    //       var id = $(this).attr('id');
    //       if(id != undefined && id != cur_per_id) {
    //        var per_index = id.split("_").pop();
    //        $("#"+id).val(common_per);
    //        $("#company_max_"+per_index).val(common_amount.toFixed(2));
    //       }
    //     });
    //      });
    // $('#underwriting_feejj').on('change',function () {
    //     var merchant_id = $('#merchant_id').val();
    //     var post_url = "{{ URL::to('admin/merchants/payment-check-for-merchant') }}";
    //     var _token = '{{csrf_token()}}';
    //        $.ajax({
    //                              type:'POST',
    //                              data: {'merchant_id': merchant_id,'_token':_token},
    //                              url:post_url,
    //                              success:function(data)
    //                              {
    //                                 if(data.status==1){
    //                                   document.getElementById('underwriting_fee_error').style.dispaly="";
    //                                   document.getElementById("underwriting_fee_error").innerHTML=  'You cannot edit Underwriting Fee';
    //                                   document.getElementById("underwriting_fee").disabled = true;
    //                                   //$('#underwriting_fee').val(2);
    //                                 }
    //                                 else{
    //                                   document.getElementById('underwriting_fee_error').style.dispaly="none";
    //                                   document.getElementById("underwriting_fee_error").innerHTML=  '';
    //                                   document.getElementById("underwriting_fee").disabled = false;
    //                                 }
    //                              }
    //                           });
    //     });
    jQuery.validator.addMethod("twoDigitDecimal", function(value, element) {

        var regex = /^\d*(\.\d{0,2})?$/;

        return this.optional(element) || regex.test(value);

    }, "Please Enter 2 decimal digit");
    jQuery.validator.addMethod("numbersWithComma", function(value, element) {
        var regex = /^[0-9,.]+$/
        return this.optional(element) || regex.test(value);
    }, "Please Enter A Valid Amount.Only Positive Numbers Are Allowed.");
    jQuery.validator.addMethod("zipcodeFormat", function(value, element) {
        var regex = /(^\d{5}$)/
        return this.optional(element) || regex.test(value);
    }, "Please Enter 5 Digit Value Code");
    jQuery.validator.addMethod("checkNumeric", function(value, element) {
        var regex = /^\-?([0-9]+(\.[0-9]+)?|Infinity)$/
        return this.optional(element) || jQuery.isNumeric(value);
    }, "Please Enter A Valid Numeric Number");
    jQuery.validator.addMethod("usPhoneFormat", function(value, element) {
        var regex =  /^(\([0-9]{3}\) |[0-9]{3}-)[0-9]{3}-[0-9]{4}$/
        return this.optional(element) || regex.test(value);
    }, "Please Enter valid number.Ex:(417) 555-1234");
    jQuery.validator.addMethod("checkNumeric", function(value, element) {
        var regex = /^\-?([0-9]+(\.[0-9]+)?|Infinity)$/
        return this.optional(element) || jQuery.isNumeric(value);
    }, "Please Enter A Valid Numeric Number");
    jQuery.validator.addMethod("dateRange", function(value, element, params) {
        try {
            var date = moment(value, "{{ \FFM::defaultDateFormat('format') }}").format('YYYY-MM-DD');
            date = new Date(date);
            if (date >= params.from && date <= params.to) {
                return true;
            }
        } catch (e) {}
        return false;
    }, 'Please Enter a date between 01/01/2017 and 31/12/2025');
    jQuery.validator.addMethod("date",function(value, element, params) {
        return moment(params).isValid();
    });
    jQuery.validator.addMethod("decimal", function(value, element) {
        var regex =  /^\d*[]?\d*$/;
        return this.optional(element) || regex.test(value);
    }, "Please Enter numeric number");
    jQuery.validator.addMethod("precisionPointValidation", function(value, element, params){
        return this.optional(element) || /^(10|\d)(\.\d{1,2})?$/.test(value)
    }, 'Please enter decimal with a precision of 2');
    jQuery.validator.addMethod("checkCompanySum", function(value, element, params) {
        let sum = 0;
        let max_ptct = $('#max_participant_fund').val();
        $('.company_max').each(function(){
            sum+= Number($(this).val());
        })
        return parseFloat(sum) <= parseFloat(max_ptct);
    }, "Total must not be more than maximum participant amount");
    $("#merchant_create_form").click(function(){
        var market_place_status = $('#marketplace').val();
        var fromDate = new Date("2017-02-01");
        var toDate = new Date("2025-12-31");
        $('#merchant_create_form').validate({ // initialize the plugin
            errorClass: 'errors_msg1',
            rules: {
                first_name: {
                    required: true,
                    maxlength:50
                },
                name: {
                    required: true,
                    maxlength:50
                },
                // business_en_name: {
                //     required: true,
                //     maxlength:50
                // },
                email :{
                    //   required: true,
                    email: true,
                },
                merchant_email:{
                    //   required: true,
                    email: true,
                },
                centrex_advance_id :{
                    maxlength:16,
                },
                sub_status_id:{
                    required: true
                },
                m_s_prepaid_status:
                {
                    required: function(element) {
                        if($('#m_syndication_fee').val()!=0)
                        return true;
                        else
                        return false;
                    },
                },
                'underwriting_status[]':{
                    required: function(element) {
                        if($('#underwriting_fee').val()!=0)
                        return true;
                        else
                        return false;
                    },
                },
                factor_rate:{
                    // required: function(element) {
                    //           if($('#marketplace').val()==0)
                    //               return true;
                    //           else
                    //               return false;
                    //     },
                    required: true,
                    range:[1,2]
                },
                experian_intelliscore:{
                    range:[0,100]
                },
                experian_financial_score:{
                    range:[0,100]
                },
                origination_fee:{
                    range:[0,100]
                },
                open_item:{
                    required: true
                },
                m_syndication_fee:{
                    checkNumeric: true
                },
                max_participant_fund:{
                    required: true,
                    checkNumeric: true,
                    min: <?= ($assigned_investors_funded_amount ?? 1 > 1)?$assigned_investors_funded_amount : 1 ?>
                    //           max: function() {
                    //     return parseInt($('#funded').val());
                    // }
                },
                max_participant_fund_per:{
                    range: [0,100]
                },
                velocity1_per:{
                    range: [0,100]
                },
                velocity2_per:{
                    range: [0,100]
                },
                participant_per:{
                    range: [0,100]
                },
                credit_score:{
                    checkNumeric: true,
                    range: [350,850]
                },
                m_mgmnt_fee:{
                    required: true
                },
                password: {
                    // required: function(element) {
                    //             if( ($('#merchant_id').val()==0) && $('#email').val())
                    //                 return true;
                    //             else
                    //                 return false;
                    //       },
                    maxlength: 255,
                    minlength: 6
                },
                password_confirmation: {
                    // required: function(element) {
                    //            if($('#merchant_id').val()==0 &&  $('#email').val())
                    //                return true;
                    //            else
                    //                return false;
                    //      },
                    equalTo: "#inputPassword",
                    maxlength: 255,
                    minlength: 6,
                },
                funded: {
                    required: function(element) {
                        if($('#marketplace').val()==0)
                        return true;
                        else
                        return false;
                    },
                    twoDigitDecimal: true,
                    numbersWithComma:true,
                    min: <?= ($assigned_investors_funded_amount ?? 1 > 1)?$assigned_investors_funded_amount : 1 ?>,
                },
                date_funded1: {
                    required: function(element) {
                        if($('#marketplace').val()==0)
                        return true;
                        else
                        return false;
                    },
                    date: function(element){
                        return $('#date_funded').val();
                    },
                    dateRange: {
                        from: fromDate,
                        to: toDate
                    }
                },
                pmnts:{
                    // required: function(element) {
                    //           if($('#marketplace').val()==0)
                    //               return true;
                    //           else
                    //               return false;
                    //     },
                    required: true,
                    decimal:true,
                    range: [1,999],
                },
                commission:{
                    required: function(element) {
                        if($('#marketplace').val()==0)
                        return true;
                        else
                        return false;
                    },
                    range:[0,50],
                    twoDigitDecimal: true,
                    checkNumeric:true
                },
                lender_id:{
                    required:true,
                },
                state_id:{
                    required:true,
                },
                industry_id:{
                    required:true,
                },
                zip_code:{
                    zipcodeFormat : true,
                },
                phone : {
                    usPhoneFormat : true
                },
                cell_phone : {
                    usPhoneFormat : true
                }
            },
            messages: {
                first_name: {required : "Enter First Name",maxlength:"maximum 50 characters are allowed"},
                name: {required : "Enter Name",maxlength:"maximum 50 characters are allowed"},
                email: {email:"Enter valid mail"},
                //  business_en_name:{required : "Enter Buisness Entity Name",maxlength:"maximum 50 characters are allowed"},
                m_s_prepaid_status: {required :"Enter Prepaid Status" },
                'underwriting_status[]':"Enter Underwriting Status",
                // funded:{required : "Enter Fund"
                //  }
                date_funded1:{required :"Enter Funded Date",date :'Please enter valid date' },
                pmnts:{required : "Enter Valid Number",range: "Enter a number between 1 and 999" },
                commission:{
                    required : "Enter Commission",
                },
                m_mgmnt_fee:{
                    required : "Enter Management Fee",
                },
                factor_rate:{
                    required : "Enter Factor Rate",
                },
                lender_id:{
                    required : "Select Lender",
                },
                industry_id:{
                    required : "Select Industry",
                },
                state_id : {
                    required : "Select State",
                },
                password:{
                    required : "Please Enter Password",
                    minlength: "Please enter at least 6 characters.",
                    maxlength: "Password can be max 255 characters long.",
                },
                password_confirmation: {
                    required: "You must confirm your password.",
                    minlength: "Please enter at least 6 characters.",
                    maxlength: "Password can be max 255 characters long.",
                    equalTo: "Your Password Must Match."
                },
                centrex_advance_id:{
                    maxlength: "Centrex advance id can be maximum 16 digit long.",
                }
                //velocity1_max:"Sum of Velocity and VP should be equal to maximum participation.",
            },
        });
    });
    $('.company_max').on('focusout', function(e){
        var max_ptct = parseFloat($('#max_participant_fund').val());
            if(parseFloat($(this).val()) > 0){
            $(this).rules("add",{
                max: max_ptct,
                checkCompanySum: true
            })
            }

    });

    $("#inputManagementFee").change(function(){
        $('#inputManagementFee-error').hide();
    });
    $("#lender_id").change(function(){
        $('#lender_id-error').hide();
    });
    $("#state_id").change(function(){
        $('#state_id-error').hide();
    });
    $("#industry_id").change(function(){
        $('#industry_id-error').hide();
    });
    $("#datepicker1").change(function(){
        $('#datepicker-error').hide();
    });
    /*
    Fee from lender
    */
    $("#lender_id").change(function(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/lender/lenderFee',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {lender_id:$("#lender_id").val()},
            /* remind that 'data' is the response of the AjaxController */
            success: function (data) {
                $('#inputManagementFee').val(data.management_fee).change();
                $('#m_syndication_fee').val(data.syndication_fee).change();
                $('#underwriting_fee').val(data.underwriting_fee).change();
                if(data.s_prepaid_status==2)
                $( "#s_prepaid_amount" ).prop( "checked", true );
                if(data.s_prepaid_status==1)
                $( "#s_prepaid_rtr" ).prop( "checked", true );
                if(data.s_prepaid_status==0)
                $( "#s_prepaid_none" ).prop( "checked", true );
                var str=JSON.parse(data.underwriting_status);
                if(str!=0)
                {
                    var str_array = str.toString().split(',');
                    if(str_array)
                    {
                        for (var i in str_array){
                            $( "#m_underwriting_status_"+str_array[i]).prop( "checked", true );
                            //alert(str_array[i]);
                        }
                    }
                }
                else
                {
                    $('.m_underwriting_status').prop( "checked", false );
                }
                //m_underwriting_status_
                // if(data.underwriting_status==2)
                // $( "#m_underwriting_status_vp" ).prop( "checked", true );
                // if(data.underwriting_status==1)
                // $( "#m_underwriting_status_velocity" ).prop( "checked", true );
                // if(data.underwriting_status==0)
                // $( "#m_underwriting_status_none" ).prop( "checked", true );
            }
        });
    });
});
var isReadOnly = document.getElementById('factorRate').hasAttribute('readonly');
if(!isReadOnly){
    $('#factorRate').focus(()=>{
        $('#factorRate').mask("0.00");
    });
}
function onlyNumberKey(evt) {
    var ASCIICode = (evt.which) ? evt.which : evt.keyCode
    if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
        return false;
        return true;
}
</script>
@stop
@section('styles')
<style>
.errors_msg {
    color: red;
}
.errors_msg1 {
    color: red;
}
</style>
<style type="text/css">
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
.input-group-text {
    background-color: #fff;
}
</style>
<link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
