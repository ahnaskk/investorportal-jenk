@extends('layouts.admin.admin_lte')
@section('content')
@php
$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
@endphp
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Payment Reports</div>     
    </a>  
</div>
{{ Breadcrumbs::render('admin::reports::payments') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-body">
            <div class="form-box-styled" >
                {{Form::open(['route'=>'admin::reports::payment-export','id'=>'payment-form'])}}
                <input type="hidden" name="row_merchant" id="row_merchant" value="">
                <div  class="row">
                    <div class="col-md-12 report-input">
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text">
                                <label class="chc">
                                    <input  id="date_type" name="date_type" type="checkbox" value="true" />
                                    <span class="checkmark checkk00"></span> 
                                    <span class="chc-value">Check this</span>
                                </label>
                            </div>         
                        </div>
                        <span class="help-block">Filter Based On Payment Added Date (Payment Date By Default)</span>
                    </div>
                </div>
                <div class="row g-0">
                    <div class="date-star" id="date-star">
                        <div class="col-md-6 report-input">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control from_date1 datepicker" id="date_start1" value="{{ $date_start }}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" autocomplete="off" type="text" />
                                <input type="hidden" name="date_start" id="date_start" value="{{ $date_start }}" class="date_parse">
                            </div>
                            <span class="help-block">From Date</span>
                        </div>
                        <div class="col-md-6 report-input">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input class="form-control to_date1 datepicker" id="date_end1" value="{{ $date_end }}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" autocomplete="off" type="text"/>
                                <input type="hidden" name="date_end" id="date_end" value="{{ $date_end }}" class="date_parse">
                            </div>
                            <span class="help-block">To Date</span>
                        </div>
                    </div>
                    <div id="time_filter" class="check-time" style="display:none;">                 
                        <div class="col-md-3 serch-timeer">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control from_date2 datepicker" autocomplete="off" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value="{{ $date_start }}"/>
                                <input type="hidden" name="date_start" id="date_start" class="date_parse" value="{{ $date_start }}">
                            </div>
                            <span class="help-block">From Date</span>
                        </div>
                        <div class="col-md-3 serch-timeer">
                            <div class="input-group">
                                <!-- <div class="input-group-text"> -->
                                <!-- <span class="glyphicon glyphicon-time" aria-hidden=" true"></span> -->
                                <!-- </div> -->
                                <!-- <input class="timepicker form-control from_time" type="text" id="time_start" name="time_start"> -->
                                <div class="input-group clockpicker">
                                    <input type="text" class="form-control" value="00:00" id="time_start" name="time_start">
                                    <span class="input-group-text">
                                        <span class="glyphicon glyphicon-time"></span>
                                    </span>
                                </div>
                            </div>
                            <span class="help-block">From Time</span>
                        </div>
                        <div class="col-md-3 serch-timeer">
                            <div class="input-group serch-two">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input class="form-control to_date2 datepicker" autocomplete="off" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value="{{ $date_end }}"/>
                                <input type="hidden" name="date_end" id="date_end" value="{{ $date_end }}" class="date_parse">
                            </div>
                            <span class="help-block">To Date</span>
                        </div>    
                        <div class="col-lg-3 serch-timeer">
                            <div class="input-group">
                                <div class="input-group clockpicker">
                                    <input type="text" class="form-control" value="00:00" id="time_end" name="time_end">
                                    <span class="input-group-text">
                                        <span class="glyphicon glyphicon-time"></span>
                                    </span>
                                </div>
                                <!-- <div class="input-group-text"> -->
                                <!-- <span class="glyphicon glyphicon-time" aria-hidden="true"></span> -->
                                <!-- </div> -->
                                <!-- <input class="timepicker form-control to_time" type="text" id="time_end" name="time_end"> -->
                            </div>
                            <span class="help-block">To Time</span>
                        </div> 
                    </div> 
                    @if($historic_status==1)
                    <div class="col-md-4 report-input">
                        <div class="input-group inp-grp check-box-wrap">
                            <div class="input-group-text">
                                <label class="chc">
                                    <input id="historic_status" name="historic_status" type="checkbox" value="1" />
                                    <span class="checkmark checkk00"></span>
                                    <span class="chc-value">Check this</span>
                                </label>
                            </div>
                        </div>
                        <span class="help-block">Historic Status (Based on To Date)</span>
                    </div>
                    @endif
                    <!-- <div class="col-md-3 serch-timeer-one"> -->
                    <!-- <div class="input-group serch-two"> -->
                    <!-- <div class="input-group-text"> -->
                    <!-- <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> -->
                    <!-- </div> -->
                    <!-- <input class="form-control to_date2" id="date_end" name="date_end" placeholder="MM-DD-YYYY" type="date" value="{{$edate}}"/> -->
                    <!-- </div> -->
                    <!-- <span class="help-block">To Date</span> -->
                    <!-- </div>     -->
                    <!-- <div class="col-lg-3 serch-timeer"> -->
                    <!-- <div class="input-group"> -->
                    <!-- <div class="input-group-text"> -->
                    <!-- <span class="glyphicon glyphicon-time" aria-hidden="true"></span> -->
                    <!-- </div> -->
                    <!-- <input class="timepicker form-control to_time" type="text" id="time_end" name="time_end"> -->
                    <!-- </div> -->
                    <!-- <span class="help-block">To Time</span> -->
                    <!-- </div>  -->
                </div>
                <div class="row">
                    <div class="col-md-4 report-input">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::select('merchant_id[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchant_id','multiple'=>'multiple'])}}
                            <!--<input class="form-control" id="merchant_id" name="merchant_id" placeholder="Enter merchant id" type="text"/> -->
                        </div>
                        <span class="help-block">Merchants</span>
                    </div>
                    <div class="col-md-4 report-input">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>
                            {{ Form::select('lenders[]',$lenders,null,['class'=>'form-control js-lender-placeholder-multiple','id'=>'lenders','multiple'=>'multiple']) }}
                        </div>
                        <span class="help-block">Lenders</span>
                    </div>
                    <div class="col-md-4 report-input">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>
                            {{Form::select('investors[]',$investors,'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}
                        </div>
                        <span class="help-block">Investors</span>
                    </div>  
                </div>
                <div class="row">
                    <div class="col-md-4 report-input">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="fa fa-credit-card" aria-hidden="true"></span>
                            </div>
                            {{ Form::select('payment_type',[''=>'All','credit'=>'Credit','debit'=>'Debit'],'',['class'=>'form-control','id'=>'payment_type','placeholder'=>'Select Payment Type']) }}    
                        </div>
                        <span class="help-block">Payment Type</span>
                    </div>
                    <div class="col-md-4 report-input">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="fa fa-credit-card" aria-hidden="true"></span>
                            </div>
                            {{ Form::select('payout_frequency[]',$recurrence_types,'',['class'=>'form-control js-payout-frequency-placeholder-multiple','id'=>'payout_frequency','multiple'=>'multiple'])
                        }}    
                    </div>
                    <span class="help-block">Payout Frequency</span>
                </div>
                <!-- <div class="col-md-4 report-input"> -->
                <!-- <div class="input-group inp-grp check-box-wrap"> -->
                <!-- <div class="input-group-text"> -->
                <!-- <label class="chc"> -->
                <!-- <input  id="balance_report" name="balance_report" type="checkbox" value="true" /> -->
                <!-- <span class="checkmark checkk00"></span> -->
                <!-- <span class="chc-value">Check this</span> -->
                <!-- </label> -->
                <!-- </div>          -->
                <!-- </div> -->
                <!-- <span class="help-block">Filter Based On Balance</span> -->
                <!-- </div> -->
                <div class="col-md-4 report-input">
                    <div class="input-group">
                        <div class="input-group-text">
                            <i class="fa fa-cog" aria-hidden="true"></i>
                        </div>
                        {{Form::select('statuses[]',$sub_statuses,0,['class'=>'form-control js-status-placeholder-multiple','id'=>'statuses','placeholder'=>'Select Status','multiple'=>'multiple'])}}
                    </div>
                    <span class="help-block">Status </span>
                </div>  
                <div class="col-md-4 report-input">
                    <div class="input-group">
                        <div class="input-group-text">
                            <i class="fa fa-money" aria-hidden="true"></i>
                        </div>
                        {!! Form::select('advance_type[]',['daily_ach'=>'Daily ACH','weekly_ach'=>'Weekly ACH','credit_card_split'=>'Credit Card Split','variable_ach'=>'Variable ACH','lock_box'=>'Lock Box','hybrid'=>'Hybrid'],isset($merchant)? $merchant->advance_type : old('advance_type'),['id'=>'advance_type','class'=>'form-control js-advtype-placeholder-multiple', 'multiple'=>'multiple']) !!}
                    </div>
                    <span class="help-block">Advance Type </span>
                </div> 
                @if(!Auth::user()->hasRole(['company']))
                <div class="col-md-4 report-input">
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                        </div>
                        {{Form::select('owner[]',$companies,'',['class'=>'form-control js-company-placeholder-multiple','id'=>'owner','multiple'=>'multiple'])}}
                    </div>
                    <span class="help-block">Company</span>
                </div>
                <div class="col-md-4">
                    <div class="input-group check-box-wrap">
                        
                        <div class="input-group-text">
                            <label class="chc">
                                <input type="checkbox" name="velocity_owned" value="1" id="velocity_owned"/>
                                <span class="checkmark chek-m"></span>
                                <span class="chc-value">Click Here</span>
                            </label>
                        </div>
                        <span class="help-block">Velocity Owned </span>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-4 report-input">
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                        </div>
                        {!! Form::select('investor_type[]',$investor_types,isset($investor)? $investor->investor_type: old('investor_type'),['class'=>'form-control js-investor-type-placeholder-multiple','id'=> 'investor_type','multiple'=>'multiple']) !!}
                    </div>
                    <span class="help-block">Investor Type </span>
                </div> 
                <div class="col-md-4 report-input">
                    <div class="input-group">
                        <div class="input-group-text">
                            <!--  <span class="glyphicon glyphicon-c" aria-hidden="true"></span> -->
                        </div>
                        {!! Form::select('label[]',$label,'',['class'=>'form-control js-label-placeholder-multiple','id'=>'label','multiple'=>'multiple']) !!}  
                    </div>
                    <span class="help-block">Label</span>
                </div>
                <div class="col-md-4 report-input">
                    <div class="input-group">
                        <div class="input-group-text">
                            <!--  <span class="glyphicon glyphicon-c" aria-hidden="true"></span> -->
                        </div>
                        {!! Form::select('investor_label[]',$label,'',['class'=>'form-control js-label-placeholder-multiple','id'=>'investor_label','multiple'=>'multiple']) !!}  
                    </div>
                    <span class="help-block">Investor Label</span>
                </div>
            </div>
            <div class="row d-block">
                <div class="col-md-4 report-input">
                    <div class="input-group inp-grp check-box-wrap">
                        <div class="input-group-text">
                            <label class="chc">
                                <input id="overpayment" name="overpayment" type="checkbox" value="1" />
                                <span class="checkmark checkk00"></span>
                                <span class="chc-value">Check this</span>
                            </label>
                        </div>         
                    </div>
                    <span class="help-block">Filter Based On Overpayment</span>
                </div>
                <div class="col-md-4 report-input">
                    <div class="input-group inp-grp check-box-wrap">
                        <div class="input-group-text">
                            <label class="chc">
                                <input id="report_totals" name="report_totals" type="checkbox" value="1" />
                                <span class="checkmark checkk00"></span>
                                <span class="chc-value">Check this</span>
                            </label>
                        </div>
                    </div>
                    <span class="help-block">Include Report Totals</span>
                </div>
                <div class="col-md-4 report-input">
                    <div class="input-group">
                        <div class="input-group-text">
                        </div>
                        {!! Form::select('mode_of_payment',$payment_methods,'',['placeholder'=>'Payment Method','class'=>'form-control js-payment-method-placeholder','id'=>'payment-method']) !!}
                    </div>
                    <span class="help-block">Payment Method</span>
                </div>
                <div class="col-md-4 report-input">
                    <div class="input-group">
                        <div class="input-group-text">
                        </div>
                        {!! Form::text('transaction_id','',['placeholder'=>'Transaction ID','class'=>'form-control js-payment-method-placeholder','id'=>'transaction_id']) !!}
                    </div>
                    <span class="help-block">Transaction ID</span>
                </div>
                <div class="col-md-4 report-input">
                    
                    <div class="input-group">
                        <div class="input-group-text">
                            <!--  <span class="glyphicon glyphicon-c" aria-hidden="true"></span> -->
                        </div>
                        {!! Form::select('rcode[]',$rcodes,old('rcode'),['class'=>'form-control js-rcode-placeholder-multiple','id'=> 'rcode','multiple'=>'multiple']) !!}
                    </div>
                    <span class="help-block"> <input type="button" id="select_all" name="select_all" value="Select All" class="btn btn-success btn-xs"><input type="button" id="unselect" name="unselect" value="Unselect" class="btn btn-success btn-xs" style="display:none;"> Rcode </span>  
                </div>
                <div class="col-md-4 report-input">
                    {{ Form::radio('active_status','', true,['class' => 'active_status' , 'id' => 'label_all']) }}
                    <label class="inline" for="label_all">All</label>  
                    {{ Form::radio('active_status','1', false,['class' => 'active_status' , 'id' => 'label_enable']) }}
                    <label class="inline" for="label_enable">Enable</label>
                    {{ Form::radio('active_status','2', false,['class' => 'active_status' , 'id' => 'label_disable']) }}
                    <label class="inline" for="label_disable">Disable</label>
                    <span class="help-block">Disable/Enable Investors</span>
                </div>
            </div>
            <!-- Download Each Payments separately -->
            <div class="row" > 
                <div class="col-md-4 check-click checktime1">
                    <div class="input-group check-box-wrap">
                        <div class="input-group-text">
                            <label class="chc chc01">
                                <input  id="export_individual_checkbox" name="export_individual_checkbox" type="checkbox" value="true"  /> 
                                <span class="checkmark chek-mm"></span>
                                <span class="chc-value">Check this</span>
                            </label>
                        </div>   
                    </div>
                    <span class="help-block">Download Each Payments separately</span>
                </div>
                <div class="col-md-4 check-click checktime1">
                    <div class="input-group check-box-wrap">
                        <div class="input-group-text">
                            <label class="chc chc01">
                                <input  id="export_checkbox" name="export_checkbox" type="checkbox" value="true" checked="checked" /> 
                                <span class="checkmark chek-mm"></span>
                                <span class="chc-value">Check this</span>
                            </label>
                        </div>   
                    </div>
                    <span class="help-block">Download Without Details</span>
                </div>
                <div class="col-md-4 report-input">
                    <div class="input-group inp-grp check-box-wrap">
                        <div class="input-group-text">
                            <label class="chc">
                                <input id="agentfee" name="agentfee" type="checkbox" value="1" />
                                <span class="checkmark checkk00"></span>
                                <span class="chc-value">Check this</span>
                            </label>
                        </div>         
                    </div>
                    <span class="help-block">Filter Based On Agent Fee</span>
                </div>
            </div>
            <div class="row">
                <div class="btn-box" >
                    <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                    name="student_dob">
                    @if(@Permissions::isAllow('Payment Report','Download')) 
                    {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter','name'=>'download'])}}
                    @endif
                    <button type="submit" name="download" value="download-syndicate" class='btn btn-primary'>Download Syndicates</button>
                    <!--     <a href="{{ url('/admin/reports/payment-copy-report') }}" class="btn btn-info" id="" target="_blank" id="copy">Copy</a> -->
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                </div>
            </div>
            {{Form::close()}}
        </div>
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
            <div class="row">
                <div class="col-sm-12">
                    <div class="table-container grid table-responsive" > 
                        {!! $tableBuilder->table(['class' => 'table table-bordered paymentReport'], true) !!}
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src="{{ asset('/js/moment.min.js') }}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
{!! $tableBuilder->scripts() !!}
<script src="{{ asset('/js/custom/report.js') }}"></script>
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script>   
<script src="{{ asset('/js/custom/payment.js') }}"></script>
<script src="{{ asset('/js/custom/common.js') }}"></script>
<script src="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.min.js') }}"></script>
<script type="text/javascript">
$('.clockpicker').clockpicker({ donetext: 'Done'});
$('#time_start,#time_end').mask('00:00');
$('#agentfee').click(function(){
    var URL_getMerchantsForAgentFee = "{{ URL::to('admin/getMerchantsForAgentFee') }}";
    var agentfee = $("input[name=agentfee]:checked").val();
    var merchantsList = [];
    if(agentfee){
        $.ajax({
            type: 'POST',
            data: {'_token': _token},
            url : URL_getMerchantsForAgentFee,
            success: function (data) {
                var merchants = data.items;
                $('#merchant_id').empty();
                $.each(merchants,function(key,value){
                    merchantsList.push(value.id);
                    $('#merchant_id').append($("<option></option>").attr("value",value.id).text(value.name));
                });
                $('#merchant_id').attr('selected','selected').val(merchantsList).trigger('change.select2');
            },
            error: function (data) {
                //alert('hi');
            }
        });
    } else {
        $('#merchant_id').attr('selected','selected').val([]).trigger('change.select2');  
    }
});
$('#time_start,#time_end').change(function(){
    var timestr = $(this).val();
    if (! isValidTimeString(timestr)) {
        // entered invalid time
        $(this).val('00:00');
    }
});
$('input[name="transaction_id"]').keyup(function(e)
{
    if (/\D/g.test(this.value))
    {
        this.value = this.value.replace(/\D/g, '');
    }
});
</script>
<script type="text/javascript">
function isValidTimeString(timestr){
    var hours = timestr.split(":")[0];
    var minutes = timestr.split(":")[1];
    if(parseInt(hours) >= 0 && parseInt(hours) < 24 && parseInt(minutes) >= 0 && parseInt(minutes) <=59 ){
        return true;
    }
    return false;
};
function changeData(dt){
    dt.innerHTML = dt.title;
}
function changeData1(dt){
    dt.innerHTML = dt.title.substring(0,3)+'...';
}
$('#select_all').click(function() {
    $('#rcode option').prop('selected',true).trigger("change.select2");
    document.getElementById("unselect").style.display = "block";
    document.getElementById("select_all").style.display = "none";
});
$("#unselect").click(function(e){
    $('#rcode').val('').trigger("change.select2");
    document.getElementById("unselect").style.display = "none";
    document.getElementById("select_all").style.display = "block";
});  
var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
$('.from_date1,.from_date2').on('change changeDate', function(){
    var val = $(this).val();
    if(val && moment(val, default_date_format).isValid())
    {
        let year = moment(val, default_date_format).year();
        if(year.toString().length == 1 || year.toString().length == 2) {
            year = moment(year, 'YY').format('YYYY');
        }
        var newDate = moment(val, default_date_format).set('year', year).format(default_date_format);
        $('.from_date1,.from_date2').val(newDate);
        $('.from_date1,.from_date2').datepicker('update');
        $('.from_date1,.from_date2').siblings('.date_parse').val(moment(val, default_date_format).set('year', year).format('YYYY-MM-DD'));
    }else {
        $('.from_date1,.from_date2').siblings('.date_parse').val('');
    }
    if($('.from_date1').val()=="" || $('.from_date2').val()==""){
        
        $('#time_start').val('00:00');
        $( "#time_start" ).prop('disabled', true);
    }
    else{
        $( "#time_start" ).prop('disabled', false);
    }
});
$('.to_date1,.to_date2').on('change changeDate', function(){
    var val = $(this).val();
    if(val && moment(val, default_date_format).isValid()) {
        let year = moment(val, default_date_format).year();
        if(year.toString().length == 1 || year.toString().length == 2) {
            year = moment(year, 'YY').format('YYYY');
        }
        var newDate = moment(val, default_date_format).set('year', year).format(default_date_format);
        $('.to_date1,.to_date2').val(newDate);
        $('.to_date1,.to_date2').datepicker('update');
        $('.to_date1,.to_date2').siblings('.date_parse').val(moment(val, default_date_format).set('year', year).format('YYYY-MM-DD'));
    }else {
        $('.to_date1,.to_date2').siblings('.date_parse').val('');
    }
    if($('.to_date1').val()=="" || $('.to_date2').val()==""){
        $('#time_end').val('00:00');
        $( "#time_end" ).prop('disabled', true);
    }else{
        $( "#time_end" ).prop('disabled', false);  
    }
});
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/Payment_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.css') }}" rel="stylesheet" type="text/css" />
<style type="text/css">
th.hidden-column, td.hidden-column {
    display: none;
}
div {
    line-height: 20px;
}
#data {
    width: 100px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
#data:hover{
    overflow: visible; 
    white-space: normal; 
    width: auto;
    position: absolute;
    background-color:#FFF;
}
#data:hover+div {
    margin-top:20px;
}
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
.btn-box {float: none;}
.col-md-12.report-input {padding: 0 15px;}
.btn-group-xs>.btn,.btn-xs{padding:1px 5px;font-size:12px;line-height:1.5;border-radius:3px}
.navbar-btn.btn-xs{margin-top:14px;margin-bottom:14px}
.btn-group-xs>.btn .badge,.btn-xs .badge{top:0;padding:1px 5px}
</style>
@stop
