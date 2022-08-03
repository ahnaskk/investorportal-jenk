@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Advance Settings </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Advance Settings</div>
    </a>
</div>
{{ Breadcrumbs::render('admin::settings::index') }}
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
        @include('layouts.admin.partials.lte_alerts')
        <?php $testMails = implode(', ', $emails); ?>
        <div class="box-body">
              @if(@Permissions::isAllow('Settings Basic Info','View'))
            {!! Form::open(['route'=>'admin::settings::index', 'method'=>'POST','id'=>'create_status_form']) !!}
            {{ Form::hidden('edit', 'true') }}
           
            <div class="form-box-styled">
                <div class="row">
                    <div class="title">Basic Settings</div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="exampleInputEmail1">Email <font color="#FF0000"> * </font></label>
                        {!! Form::text('email',isset($testMails)? $testMails : old('rate'),['class'=>'form-control','id'=> 'email','data-parsley-required-message' => 'Email is required','required','data-role'=>'tagsinput']) !!}
                    </div>
                </div>
                <div class="row px-1">
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Force Over Payment? <font color="#FF0000"> * </font></label>
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text">
                                <label class="chc">
                                    {!! Form::checkbox('forceopay' , 1,  $default['forceopay']) !!}
                                    <span class="checkmark chek-m"></span>
                                    <span class="chc-value">Check this</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Default rate <font color="#FF0000"> * </font></label>
                        {!! Form::text('rate',isset($default)? $default['rate'] : old('rate'),['class'=>'form-control','id'=> 'inputRate']) !!}
                        <span id="invalid-rate" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Default Payments <font color="#FF0000"> * </font></label>
                        {!! Form::select('payments',$default_payment,$default['default_payment'],['class'=>'form-control','placeholder'=>'Select default payment','required','id'=> 'default_payment']) !!}
                        <span id="invalid-payments" />
                    </div>
                </div>
                <div class="row px-1">
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Start Date <font color="#FF0000"> * </font></label>
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            <input class="form-control datepicker from_date1" id="date_start1" name="date_start1" placeholder="{{ \FFM::defaultDateFormat('format') }}" autocomplete="off" type="text" value="{{ $default['portfolio_start_date'] }}"/>
                            <input type="hidden" name="date_start" id="date_start" value="{{ $default['portfolio_start_date'] }}" class="date_parse"> 
                            <span id="invalid-date_start" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Hide disabled investors <font color="#FF0000"> * </font></label>
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                        {!! Form::checkbox('hide' , 1,  $default['hide']) !!}
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check this</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='col-sm-4'>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Last Mobile Notification Time <font color="#FF0000"> * </font></label>
                            <div class='input-group date' id='datetimepicker1'>
                                <input type='text' class="form-control" name="mobile_app_from_date1" value="{{ date(\FFM::defaultDateFormat('db').' H:i:s ',strtotime($default['last_mob_notification_time'])) }}" required/>
                                <input type="hidden" name="mobile_app_from_date" class="date_parse" value="{{ date(\FFM::defaultDateFormat('db').' H:i:s ',strtotime($default['last_mob_notification_time'])) }}">
                                <span class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row px-1">
                    <div class="form-group col-md-4">
                        <label for="max_assign_per">Maximum Assign Percentage of Liquidity<font color="#FF0000"> * </font></label>
                        {!! Form::text('max_assign_per',isset($default)? $default['max_assign_per'] : old('max_assign_per'),['class'=>'form-control','id'=> 'max_assign_per']) !!}
                        <span id="invalid-max_assign_per" />
                    </div>
                    <div class="form-group col-md-4">
                        <label for="max_assign_per">Mail Send Permission (On create payment) <font color="#FF0000"> * </font></label>
                        <?php $status=isset($default['send_permission'])?$default['send_permission']:0; ?>
                        @if($status == 1)
                        <input type="checkbox" checked data-toggle="toggle" data-onstyle="success" name="send_permission" value="1" >
                        @else
                        <input type="checkbox" data-toggle="toggle" name="send_permission" value="1" data-onstyle="success">
                        @endif
                        <span id="invalid-send_permission" />
                    </div>
                    <div class="form-group col-md-4">
                        <label>Default Date Display Format<font color="#FF0000"> * </font></label>
                        @php($currentDate=strtotime(now()))
                        @if(isset($default_date_format))
                        @php($date_format = $default_date_format['dbFormat'])
                        @else
                        @php($date_format = "MM-DD-YYYY")
                        @endif
                        <select name="default_date_format" id="defaultDateFormat" class="form-control" required>
                            @foreach($date_formats as $format)
                            <option value="{{ $format->dbFormat }}" @if($date_format == $format->dbFormat) selected @endif>{{ date($format->dbFormat, $currentDate) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row px-1">
                    <div class="col-md-4 form-group">
                        <label>Default Timezone</label>
                        @if(isset($default_timezone))
                        @php($timezone = $default_timezone)
                        @else
                        @php($timezone = "America/New_york")
                        @endif
                        <select name="default_timezone" id="default_timezone" class="form-control" required>
                            @foreach($tzlist as $tz)
                            <option value="{{ $tz }}" @if(strtolower($tz) == strtolower($timezone)) selected @endif>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>

                      <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Agent Fee(%) <font color="#FF0000"> * </font></label>
                        {!! Form::text('agent_fee_per',isset($default)? $default['agent_fee_per'] : old('agent_fee_per'),['class'=>'form-control','id'=> 'inputAgentFeePer']) !!}
                        <span id="invalid-rate" />
                    </div>

                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Historic status ? <font color="#FF0000"> * </font></label>
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text">
                                <label class="chc">
                                    {!! Form::checkbox('historic_status' , 1,  $default['historic_status']) !!}
                                    <span class="checkmark chek-m"></span>
                                    <span class="chc-value">Check this</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                 @if(@Permissions::isAllow('Settings Basic Info','Edit'))

                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                    </div>
                </div>

                @endif


            </div>
             

            {!! Form::close() !!}

             @endif

          @if(@Permissions::isAllow('Settings Default Percentage Rule','View'))
            {!! Form::open(['route'=>'admin::settings::index', 'method'=>'POST','id'=>'create_status_form']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title">Default Percentage Rule</div>
                </div>
                <div class="row">
                    <div class='col-sm-4 form-group'>
                        <label for="max_assign_per">0-60 days (%)<font color="#FF0000"> * </font></label>
                        {!! Form::text('default_percentage_rule[30]',isset($default_percentage_rule[30])?$default_percentage_rule[30]:1,['class'=>'form-control']) !!}
                        <span class="help-block">Default percentage rule 1</span>
                    </div>
                    <div class='col-sm-4 form-group'>
                        <label for="max_assign_per">60-90 days (%)<font color="#FF0000"> * </font></label>
                        {!! Form::text('default_percentage_rule[60]',isset($default_percentage_rule[60])?$default_percentage_rule[60]:1,['class'=>'form-control']) !!}
                        <span class="help-block">Default percentage rule 3</span>
                    </div>
                    <div class='col-sm-4 form-group'>
                        <label for="max_assign_per">90-120 days (%)<font color="#FF0000"> * </font></label>
                        {!! Form::text('default_percentage_rule[90]',isset($default_percentage_rule[90])?$default_percentage_rule[90]:1,['class'=>'form-control']) !!}
                        <span class="help-block">Default percentage rule 2</span>
                    </div>
                    <div class='col-sm-4  form-group'>
                        <label for="max_assign_per">120-150 days (%)<font color="#FF0000"> * </font></label>
                        {!! Form::text('default_percentage_rule[120]',isset($default_percentage_rule[120])?$default_percentage_rule[120]:1,['class'=>'form-control']) !!}
                        <span class="help-block">Default percentage rule 4</span>
                    </div>
                    <div class='col-sm-4 form-group'>
                        <label for="max_assign_per">150+ days (%)<font color="#FF0000"> * </font></label>
                        {!! Form::text('default_percentage_rule[150]',isset($default_percentage_rule[150])?$default_percentage_rule[150]:1,['class'=>'form-control']) !!}
                        <span class="help-block">Default percentage rule 5</span>
                    </div>
                </div>
                  @if(@Permissions::isAllow('Settings Default Percentage Rule','Edit'))
                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                    </div>
                </div>
                @endif
            </div>
            {!! Form::close() !!}
            @endif

            @if(@Permissions::isAllow('Settings System Admin','View'))

            {!! Form::open(['route'=>'admin::settings::index', 'method'=>'POST','id'=>'system_admin']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title text-capitalize">System Admin</div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12">
                        <label for="systemAdminEmails">Emails <font color="#FF0000"> * </font></label>
                        {!! Form::text('system_admin_emails',isset($system_admin_emails)? $system_admin_emails : '',['class'=>'form-control','id'=> 'system_admin_emails','data-parsley-required-message' => 'Email is required','required','data-role'=>'tagsinput']) !!}
                    </div>
                </div>
                   @if(@Permissions::isAllow('Settings System Admin','Edit'))
                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                    </div>
                </div>
                @endif
            </div>
            {!! Form::close() !!}
            @endif
           
             @if(@Permissions::isAllow('Settings Merchant ACH','View'))

            {!! Form::open(['route'=>'admin::settings::index', 'method'=>'POST','id'=>'ach_merchant_form']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title text-capitalize">Merchant ACH</div>
                </div>
                <div class="row">
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_status_time">Status Check Time</label>
                        {!! Form::time('ach_merchant[status_time]',isset($ach_merchant['status_time'])?$ach_merchant['status_time']:'',['class'=>'form-control', 'id'=>'ach_status_time']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_double_check_time">Double Check Time</label>
                        {!! Form::time('ach_merchant[double_check_time]',isset($ach_merchant['double_check_time'])?$ach_merchant['double_check_time']:'',['class'=>'form-control', 'id'=>'ach_double_check_time']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_notification_time">Not Send Notification Time</label>
                        {!! Form::time('ach_merchant[notification_time]',isset($ach_merchant['notification_time'])?$ach_merchant['notification_time']:'',['class'=>'form-control', 'id'=>'ach_notification_time']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_difference_time">Balance Difference Check Time</label>
                        {!! Form::time('ach_merchant[difference_time]',isset($ach_merchant['difference_time'])?$ach_merchant['difference_time']:'',['class'=>'form-control', 'id'=>'ach_difference_time']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_request_time">Request Time</label>
                        {!! Form::time('ach_merchant[request_time]',isset($ach_merchant['request_time'])?$ach_merchant['request_time']:'',['class'=>'form-control', 'id'=>'ach_request_time']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_request_status">Request Automation Status<font color="#FF0000"> * </font></label>
                        <input data-toggle="toggle" data-onstyle="success" type="checkbox" value="1" {{isset($ach_merchant['ach_request_status']) ? (($ach_merchant['ach_request_status'] == 1) ? 'checked': '') : ''}} id="ach_request_status" name="ach_merchant[ach_request_status]">
                    </div>
                    @foreach ($ach_fee_types as $ach_fee)
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="{{$ach_fee['db_name']}}">{{$ach_fee['name']}}<font color="#FF0000"> * </font></label>
                        <input type="number" value="{{isset($ach_merchant[$ach_fee['db_name']]) ? $ach_merchant[$ach_fee['db_name']]: 0}}" id="{{$ach_fee['db_name']}}" name="{{ $ach_fee['input_name'] }}" class="form-control" min="0"  step="0.01" required>
                    </div>
                    @endforeach
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_merchant_credit_lag_days">Credit Lag Days<font color="#FF0000"> * </font></label>
                        {!! Form::number('ach_merchant[ach_merchant_credit_lag_days]',isset($ach_merchant['ach_merchant_credit_lag_days'])?$ach_merchant['ach_merchant_credit_lag_days']:'',['class'=>'form-control', 'id'=>'ach_merchant_credit_lag_days', 'step'=>'1', 'min'=>'1', 'required']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_merchant_double_check_lag_days">Double Check Lag Days<font color="#FF0000"> * </font></label>
                        {!! Form::number('ach_merchant[ach_merchant_double_check_lag_days]',isset($ach_merchant['ach_merchant_double_check_lag_days'])?$ach_merchant['ach_merchant_double_check_lag_days']:'',['class'=>'form-control', 'id'=>'ach_merchant_double_check_lag_days', 'step'=>'1', 'min'=>'1', 'required']) !!}
                    </div>
                </div>
                  @if(@Permissions::isAllow('Settings Merchant ACH','Edit'))
                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                    </div>
                </div>
                @endif
            </div>
            {!! Form::close() !!}
             @endif

             @if(@Permissions::isAllow('Settings ACH Syndicate','View'))

            {!! Form::open(['route'=>'admin::settings::index', 'method'=>'POST','id'=>'ach_investor_form']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title text-capitalize">Investor ACH</div>
                </div>
                <div class="row">
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_syndicate_status">Syndicate Automation Status<font color="#FF0000"> * </font></label>
                        <input data-toggle="toggle" data-onstyle="success" type="checkbox" value="1" {{isset($ach_investor['ach_syndicate_status']) ? (($ach_investor['ach_syndicate_status'] == 1) ? 'checked': '') : ''}} id="ach_syndicate_status" name="ach_investor[ach_syndicate_status]">
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_syndicate_payment_time">Syndicate Payment Send Time</label>
                        {!! Form::time('ach_investor[ach_syndicate_payment_time]',isset($ach_investor['ach_syndicate_payment_time'])?$ach_investor['ach_syndicate_payment_time']:'',['class'=>'form-control', 'id'=>'ach_syndicate_payment_time']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_investor_status_time">Status Check Time</label>
                        {!! Form::time('ach_investor[ach_investor_status_time]',isset($ach_investor['ach_investor_status_time'])?$ach_investor['ach_investor_status_time']:'',['class'=>'form-control', 'id'=>'ach_investor_status_time']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_investor_double_check_time">Double Check Time</label>
                        {!! Form::time('ach_investor[ach_investor_double_check_time]',isset($ach_investor['ach_investor_double_check_time'])?$ach_investor['ach_investor_double_check_time']:'',['class'=>'form-control', 'id'=>'ach_investor_double_check_time']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_investor_credit_lag_days">Credit Lag Days<font color="#FF0000"> * </font></label>
                        {!! Form::number('ach_investor[ach_investor_credit_lag_days]',isset($ach_investor['ach_investor_credit_lag_days'])?$ach_investor['ach_investor_credit_lag_days']:'',['class'=>'form-control', 'id'=>'ach_investor_credit_lag_days', 'step'=>'1', 'min'=>'1', 'required']) !!}
                    </div>
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="ach_investor_double_check_lag_days">Double Check Lag Days<font color="#FF0000"> * </font></label>
                        {!! Form::number('ach_investor[ach_investor_double_check_lag_days]',isset($ach_investor['ach_investor_double_check_lag_days'])?$ach_investor['ach_investor_double_check_lag_days']:'',['class'=>'form-control', 'id'=>'ach_investor_double_check_lag_days', 'step'=>'1', 'min'=>'1', 'required']) !!}
                    </div>
                </div>
                  @if(@Permissions::isAllow('Settings ACH Syndicate','Edit'))
                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                    </div>
                </div>
                 @endif
            </div>
            {!! Form::close() !!}

            @endif
            
            @if(@Permissions::isAllow('Settings Merchant','View'))

            {!! Form::open(['route'=>'admin::settings::index', 'method'=>'POST','id'=>'minimum_investment_value_form']) !!}
            {{ Form::hidden('edit', 'true') }}
            <div class="form-box-styled">
                <div class="row">
                    <div class="title text-capitalize">Merchant</div>
                </div>
                <div class="row">
                    <div class='col-sm-4 form-group text-capitalize'>
                        <label for="minimum_investment_value">Minimum investment Value<font color="#FF0000"> * </font></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-dollar"></i></span>
                            <input type="number" value="{{isset($minimum_investment_value) ? $minimum_investment_value: 0}}" name="minimum_investment_value" class="form-control" min="0"  step="0.01" id="minimum_investment_value">
                        </div>
                    </div>


                      <div class="form-group col-md-4">
                        <label for="max_assign_per">Maximum Investment Percentage<font color="#FF0000"> * </font></label>
                        {!! Form::text('max_investment_per',isset($max_investment_per)? $max_investment_per : old('max_investment_per'),['class'=>'form-control','id'=> 'max_investment_per']) !!}
                        <span id="invalid-max_investment_per" />
                    </div>
                    
                </div>
                 @if(@Permissions::isAllow('Settings Merchant','Edit'))
                <div class="btn-wrap btn-right">
                    <div class="btn-box" >
                        {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                    </div>
                </div>
                @endif
            </div>
            {!! Form::close() !!}

            @endif
        </div>
    </div>
    @stop
    @section('scripts')
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script> -->
    <script src="{{ asset ('bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
    <!-- <script src="//cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/e8bddc60e73c1ec2475f827be36e1957af72e2ea/src/js/bootstrap-datetimepicker.js"></script> -->
    <script src="{{ asset('js/custom/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ asset ("js/bootstrap-toggle.min.js") }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript">
    var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
    // using https://www.jqueryscript.net/time-clock/Date-Time-Picker-Bootstrap-4.html datetimepicker
    $('#datetimepicker1').datetimepicker({
        format:default_date_format +' HH:mm:ss',
        icons: {
            time:'far fa-clock',
            date:'far fa-calendar',
            up:'fas fa-chevron-up',
            down:'fas fa-chevron-down',
            previous:'fas fa-chevron-left',
            next:'fas fa-chevron-right',
            today:'fas fa-crosshairs',
            clear:'fas fa-trash',
            close:'fas fa-times',
        },
    });

$('#minimum_investment_value_form').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) { 
    e.preventDefault();
    return false;
  }
});
 var max_investment_per=50;
 var min_investment_pe=1;
 $('#minimum_investment_value_form').validate({
       errorClass: 'errors',
        rules: {
            max_investment_per: {range: [min_investment_pe,max_investment_per], },   
        },
        messages: {
            
            max_investment_per: "Enter Maximun investment Percentage (" +min_investment_pe+"-"+max_investment_per+")",
           
        },

     });
    var max_assign_per=100;
    var min_assign_per=1;
    $('#create_status_form').validate({
        errorClass: 'errors',
        rules: {
            max_assign_per: { required: true, range: [min_assign_per,max_assign_per], },
            rate:           { required: true, },
            date_start1:     { required: true, },
            payments:       { required: true, }
        },
        messages: {
            rate          : "Enter Rate",
            max_assign_per: "Enter Maximun assign Percentage ("+min_assign_per+"-"+max_assign_per+")",
            date_start1    : "Enter Date Start",
            payments      : "Enter Payments",
        },
    });
    $('#ach_investor_form').validate({
    })
    $('#ach_merchant_form').validate({
        errorClass: 'errors',
        rules: {
            ach_request_time   :{ required: true,},
            ach_status_time :{ required: true, },
            ach_double_check_time   :{ required: true, },
            ach_notification_time   :{ required: true, },
            ach_difference_time :{ required: true, },
        },
    });
    $('#email').on('itemAdded', function(event) {
        if( !isEmail(event.item)) {
            $('#email').tagsinput('remove', event.item);
        }
    });
    $('#system_admin_emails').on('itemAdded', function(event) {
        if( !isEmail(event.item)) {
            $('#system_admin_emails').tagsinput('remove', event.item);
        }
    });
    function isEmail($email) {
        var emailReg = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return emailReg.test( $email );
    }
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/bootstrap-toggle.min.css?ver=5') }}" rel='stylesheet'/>
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{asset('css/bootstrap-datetimepicker.min.css')}}">
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> -->
<style type="text/css">
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
</style>
@stop
