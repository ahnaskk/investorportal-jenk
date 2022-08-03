@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>System Settings</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">System Settings</div>
    </a>
</div>
{{ Breadcrumbs::render('admin::settings::system_settings') }}
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
        @include('layouts.admin.partials.lte_alerts')
        <div class="box-body">
            @if(@Permissions::isAllow('System Settings','View'))
            {!! Form::open(['route'=>'admin::settings::systemupdate', 'method'=>'POST','id'=>'substatus']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title text-capitalize">Agent fee on status</div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Status</label>
                            {!! Form::select('sub_status[]', $substatus,$sys_substaus,['class'=>'form-control js-substatus-flag-placeholder-multiple', 'id'=>'sub_status','multiple'=>'multiple']) !!}
                        </div>
                    </div>
                </div>
                @if(@Permissions::isAllow('System Settings','Edit'))
                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary','id'=>'sub']) !!}
                    </div>
                </div>
                @endif
            </div>
            {!! Form::close() !!}
            @endif
        </div>
        <div class="box-body">
            @if(@Permissions::isAllow('System Settings','View'))
            {!! Form::open(['route'=>'admin::settings::revertdatemodeupdateaction', 'method'=>'POST','id'=>'revert_date']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title text-capitalize">Revert Date</div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group collection-mode">
                            <label>Default Revert Date</label>
                            <input type="checkbox" @if($revert_date_mode) checked @endif data-toggle="toggle" data-on="Current Date" data-off="Payment Date" name="revert_date_mode" id="revert_date_mode" data-title="">
                        </div>
                    </div>
                </div>
                @if(@Permissions::isAllow('System Settings','Edit'))
                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary','id'=>'sub']) !!}
                    </div>
                </div>
                @endif
            </div>
            {!! Form::close() !!}
            @endif
        </div>
        <div class="box-body">
            @if(@Permissions::isAllow('System Settings','View'))
            {!! Form::open(['route'=>'admin::settings::twofactorrequiredupdation', 'method'=>'POST','id'=>'two_factor_required']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title text-capitalize">Two Factor Authentication</div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group collection-mode">
                            <label>Enable Mandatory Two Factor Authentication</label>
                            <input type="checkbox" @if($two_factor_required_mode) checked @endif data-toggle="toggle" data-on="Yes" data-off="No" name="two_factor_required_status" id="two_factor_required_status" data-title="">
                        </div>
                    </div>
                </div>
                @if(@Permissions::isAllow('System Settings','Edit'))
                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary','id'=>'sub']) !!}
                    </div>
                </div>
                @endif
            </div>
            {!! Form::close() !!}
            @endif
        </div>

        <div class="box-body">
            @if(@Permissions::isAllow('Account Settings','View'))
            {!! Form::open(['route'=>'admin::settings::accounts-view-status-update', 'method'=>'POST','id'=>'substatus']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title text-capitalize">Account Settings</div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Show Agent fee account on merchant view</label>
                            @if($settings->show_agent_account==1)
                            <input type="checkbox" checked data-toggle="toggle" data-on="On" data-off="Off" name="agent_fee_on_off" id="agent_fee_on_off" data-title="">  
                            @else
                            <input type="checkbox" data-toggle="toggle" data-on="On" data-off="Off" name="agent_fee_on_off" id="agent_fee_on_off" data-title="">
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Show Overpayment account on merchant view</label>
                            @if($settings->show_overpayment_account==1)
                            <input type="checkbox" checked data-toggle="toggle" data-on="On" data-off="Off" name="overpayment_on_off" id="overpayment_on_off" data-title="">
                            @else
                            <input type="checkbox" data-toggle="toggle" data-on="On" data-off="Off" name="overpayment_on_off" id="overpayment_on_off" data-title="">
                            @endif 
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Edit Investment After Payment</label>
                                @if($settings->edit_investment_after_payment==1)
                                <input type="checkbox" checked data-toggle="toggle" data-on="On" data-off="Off" name="edit_investment_after_payment" id="edit_investment_after_payment" data-title="">
                                @else
                                <input type="checkbox" data-toggle="toggle" data-on="On" data-off="Off" name="edit_investment_after_payment" id="edit_investment_after_payment" data-title="">
                                @endif 
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-9">
                            <div class="form-group">
                                <label>Deduct Agent Fee From</label>
                                <label>
                                <input  value="1" type="radio" @if($deduct_agent_fee_from_profit_only==1) checked @endif name="deduct_agent_fee_from_profit_only" id="profit_only"> Profit Only (If profit is insufficient the remaining amount will be deducted from principal)
                                </label>
                                <label>
                                <input  value="0" type="radio" @if($deduct_agent_fee_from_profit_only==0) checked @endif name="deduct_agent_fee_from_profit_only" id="profit_and_principal"> Both Profit and Principal
                                </label>
                            </div>
                        </div>
                    </div>
                    @if(@Permissions::isAllow('Account Settings','Edit'))
                    <div class="btn-wrap btn-right">
                        <div class="btn-box" >
                            {!! Form::submit('Update',['class'=>'btn btn-primary','id'=>'sub']) !!}
                        </div>
                    </div>
                    @endif
                </div>
                {!! Form::close() !!}
                @endif
            </div>
            <div class="box-body">
                @if(@Permissions::isAllow('System Settings','View'))
                {!! Form::open(['route'=>'admin::settings::paymentmodeupdate', 'method'=>'POST','id'=>'mode']) !!}
                {{ Form::hidden('edit', 'true') }}
                <div class="form-box-styled">
                    <div class="row">
                        <div class="title text-capitalize">Collection Default Mode </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label></label>
                                @if($payment_mode==1)
                                <input type="checkbox" checked data-toggle="toggle" data-on="default_mode" data-off="collection_mode" name="payment_mode_on_off" id="payment_mode_on_off" data-title="">
                                @else
                                <input type="checkbox" data-toggle="toggle" data-on="default_mode" data-off="collection_mode" name="payment_mode_on_off" id="payment_mode_on_off" data-title="">
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(@Permissions::isAllow('System Settings','Edit'))
                    <div class="btn-wrap btn-right">
                        <div class="btn-box" >
                            {!! Form::submit('Update',['class'=>'btn btn-primary','id'=>'sub']) !!}
                        </div>
                    </div>
                    @endif
                </div>
                {!! Form::close() !!}
                @endif
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src="{{ asset('js/bootstrap-toggle.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#substatus').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });
});
</script>
@stop
@section('styles')
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel='stylesheet'/>
<link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/bootstrap-toggle.min.css?ver=5') }}" rel='stylesheet'/>
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('css/bootstrap-datetimepicker.min.css')}}">
<style type="text/css">
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
</style>
@stop
