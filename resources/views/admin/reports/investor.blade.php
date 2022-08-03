@extends('layouts.admin.admin_lte')
@section('content')
<?php
$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
?>
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Investment Report</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::reports::investor') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-body">
            <div class="form-group">
                <div class="filter-group-wrap" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'admin::reports::investor-export','id'=>'investor-form'])}}
                        <div class="serch-bar">
                            <div  class="row g-0">
                                <div class="merchant-ass">           
                                    <div class="col-md-4 check-click checktime1" >
                                        <div class="form-group">
                                            <div class="input-group check-box-wrap">
                                                <div class="input-group-text">
                                                    <label class="chc">
                                                        <input  id="date_type" name="date_type" type="checkbox" value="true"/> <span class="checkmark chek-mm"></span>
                                                        <span class="checkmark chek-m"></span>
                                                        <span class="chc-value">Check this</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <span class="help-block">Filter Based On Merchant Added Date (Funded Date by Default)</span>
                                        </div>
                                    </div>
                                    <div class="date-star" id="test" style="display:block">
                                        <div class="col-md-4" style="height: 86px; margin-bottom: -2px;">
                                            <div class="input-group">
                                                <div class="input-group-text">
                                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                                </div>
                                                <input class="form-control from_date1 datepicker" autocomplete="off" id="date_start1" name="start_date1" value="{{$date_start}}"  placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                                <input type="hidden" name="date_start" id="date_start" value="{{ $date_start }}" class="date_parse">
                                                <span id="invalid-date_start"/>
                                            </div>
                                            <span class="help-block">From Date </span>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <div class="input-group-text">
                                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                                </div>
                                                <input class="form-control to_date1 datepicker" autocomplete="off" id="date_end1" value="{{$date_end}}" name="end_date1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                                <input type="hidden" name="end_date" id="date_end" value="{{$date_end}}" class="date_parse">
                                            </div>
                                            <span class="help-block">To Date</span>
                                        </div>
                                    </div>
                                    <div id="time_filter" class="check-time" style="display:none;">
                                        <div class="col-sm-12">
                                            <div class="row">
                                                <div class="col-md-3 serch-timeer-one">
                                                    <div class="input-group serch-two">
                                                        <div class="input-group-text">
                                                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                                        </div>
                                                        <input class="form-control from_date2 datepicker" id="date_start11" value="{{$date_start}}" name="date_start" autocomplete="off" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                                        <input type="hidden" name="date_start" id="date_start1" value="{{$date_start}}" class="date_parse">
                                                    </div>
                                                    <span class="help-block">From Date</span>
                                                </div>
                                                <div class="col-md-3 serch-timeer">
                                                    <!-- <div class="input-group"> -->
                                                    <!-- <div class="input-group-text"> -->
                                                    <!-- <span class="glyphicon glyphicon-time" aria-hidden=" true"></span> -->
                                                    <!-- </div> -->
                                                    <!-- <input class="timepicker form-control from_time" type="text" id="time_start" name="time_start" placeholder="00:00:00"> -->
                                                    <!-- </div> -->
                                                    <div class="input-group clockpicker">
                                                        <input type="text" class="form-control" value="00:00" id="time_start" name="time_start">
                                                        <span class="input-group-text">
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </span>
                                                    </div>
                                                    <span class="help-block">From Time</span>
                                                </div>
                                                <div class="col-md-3 serch-timeer-one">
                                                    <div class="input-group serch-two">
                                                        <div class="input-group-text">
                                                            <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                                        </div>
                                                        <input class="form-control to_date2 datepicker" id="date_end11" value="{{$date_end}}" autocomplete="off" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                                        <input type="hidden" name="date_end" id="date_end1" value="{{ $date_end }}" class="date_parse">
                                                    </div>
                                                    <span class="help-block">To Date</span>
                                                </div>  
                                                <div class="col-md-3 serch-timeer">
                                                    <div class="input-group">
                                                        <!-- <div class="input-group-text"> -->
                                                        <!-- <span class="glyphicon glyphicon-time" aria-hidden="true"></span> -->
                                                        <!-- </div> -->
                                                        <!-- <input class="timepicker form-control to_time" type="text" id="time_end" name="time_end" placeholder="00:00:00"> -->
                                                        <!-- </div> -->
                                                        <div class="input-group clockpicker">
                                                            <input type="text" class="form-control" value="00:00" id="time_end" name="time_end">
                                                            <span class="input-group-text">
                                                                <span class="glyphicon glyphicon-time"></span>
                                                            </span>
                                                        </div>
                                                        <span class="help-block">To Time</span>
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>        
                                    </div>
                                </div>
                                <!-- assigned-filter-investor -->
                                <div class="col-sm-12">           
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="margin-bottom: -2px;">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                    </div>
                                    {{Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}
                                </div>
                                <span class="help-block">Merchants</span>
                            </div>
                            <div class="col-md-4" style="margin-bottom: -2px;">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                    </div>
                                    {{Form::select('investors[]',$investors,$selected_investor,['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}
                                </div>
                                <span class="help-block">Investors </span>
                            </div>
                            <div class="col-md-4" style="margin-bottom: -2px;">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                    </div>
                                    {{Form::select('lenders[]',$lenders,null,['class'=>'form-control js-lender-placeholder-multiple','id'=>'lenders','multiple'=>'multiple'])}}
                                </div>
                                <span class="help-block">Lenders </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <i class="fa fa-building" aria-hidden="true"></i>
                                    </div>
                                    {{Form::select('industries[]',$industries,null,['class'=>'form-control js-industry-placeholder-multiple','id'=>'industries','multiple'=>'multiple'])}}
                                </div>
                                <span class="help-block">Industries </span>
                            </div> 
                            <div class="col-md-4" style="margin-bottom: -2px;">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="fa fa-building" aria-hidden="true"></span>
                                    </div>
                                    <select class="form-control js-status-placeholder-multiple" multiple="multiple" name="statuses[]" id="statuses" onchange="filter_change()">
                                        @foreach($sub_statuses as $sub_status)
                                        <option  value='{{$sub_status->id}}'>{{$sub_status->name}} </option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="help-block">Status </span>
                            </div>
                            <div class="col-md-4 report-input">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="fa fa-industry" aria-hidden="true"></span>
                                    </div>
                                    {!! Form::select('advance_type[]',['daily_ach'=>'Daily ACH','weekly_ach'=>'Weekly ACH','credit_card_split'=>'Credit Card Split','variable_ach'=>'Variable ACH','lock_box'=>'Lock Box','hybrid'=>'Hybrid'],isset($merchant)? $merchant->advance_type : old('advance_type'),['id'=>'advance_type','class'=>'form-control js-advtype-placeholder-multiple', 'multiple'=>'multiple']) !!}
                                </div>
                                <span class="help-block">Advance Type </span>
                            </div>  
                            <div class="col-md-4 check-click checktime1" >
                                <div class="form-group">
                                    <div class="input-group check-box-wrap">
                                        <div class="input-group-text">
                                            <label class="chc">
                                                <input  id="export_checkbox" name="export_checkbox" type="checkbox" value="true" checked="checked" /> <span class="checkmark chek-mm"></span>
                                                <span class="checkmark chek-m"></span>
                                                <span class="chc-value">Check this</span>
                                            </label>
                                        </div>
                                    </div>
                                    <span class="help-block">Download Without Details</span>
                                </div>
                            </div>
                            @if(!Auth::user()->hasRole(['company']))
                            <div class="col-md-4" style="margin-bottom: -2px;">
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
                                        <span class="fa fa-industry" aria-hidden="true"></span>
                                    </div>
                                    {!! Form::select('investor_type[]',$investor_types,isset($investor)? $investor->investor_type: old('investor_type'),['class'=>'form-control js-investor-type-placeholder-multiple','id'=> 'investor_type', 'multiple'=>'multiple']) !!}
                                </div>
                                <span class="help-block">Investor Type </span>
                            </div> 
                        
                            <div class="col-md-4 report-input">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="fa fa-industry" aria-hidden="true"></span>
                                    </div>
                                    {!! Form::select('sub_status_flag[]', $substatus_flags, isset($merchant)? $merchant->sub_status_flag : old('sub_status_flag'),['class'=>'form-control js-substatus-flag-placeholder-multiple','id'=>'sub_status_flag','multiple'=>'multiple']) !!}
                                </div>
                                <span class="help-block">Sub status Flag</span>
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
                        <div class="row">
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
                        <input type="hidden" name="row_merchant" id="row_merchant" value="">
                        <!-- <div class="col-md-4 check-click checktime1"> -->
                        <!-- <div class="input-group"> -->
                        <!-- <div class="input-group-text"> -->
                        <!-- <label class="chc chc01"><input  id="vp_advanced" name="vp_advanced" -->
                        <!-- type="checkbox" value="true"/> <span class="checkmark chek-mm"></span> -->
                        <!-- </label> -->
                        <!-- </div>    -->
                        <!-- </div> -->
                        <!-- <span class="grid inputInfoLg small">VP advanced</span> -->
                        <!-- </div> -->
                        <div class="btn-wrap btn-right">
                            <div class="btn-box inhelpBlock ">
                                <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
                                <div class="blockCust pull-right">
                                    @if(@Permissions::isAllow('Investment Report','Download')) 
                                    {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter','name'=>'download'])}}
                                    @endif
                                    <button type="submit" name="download" value="syndicate-report-download" class='btn btn-primary'>Syndicate report download</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <table class="table table-bordered dataTable">
                                <thead>
                                    <tr>
                                        <th colspan="2"></th>
                                    </tr>
                                    <tr>
                                        <th colspan="2"></th>
                                    </tr> 
                                    <!-- <tr hidden> -->
                                    <!-- <th>Auto Invest collected amount</th> -->
                                    <!-- <th>Disabled</th> -->
                                    <!-- </tr> -->
                                    <tr>
                                        <th>Amounts</th>
                                        <th>Above Zero With 2 Decimal Places</th>
                                    </tr>
                                    <tr>
                                        <th>Fees and Commissions</th>
                                        <th>Above Zero With 2 Decimal Places</th>
                                    </tr>
                                    <tr>
                                        <th>Share</th>
                                        <th>Represented as a percentage of total funding</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2"></th>
                                    </tr>
                                    <!-- <tr> -->
                                    <!-- <th colspan="2"></th> -->
                                    <!-- </tr> -->
                                </thead>
                            </table>
                        </div> 
                        <div class="col-md-7">
                            <table class="table table-bordered dataTable">
                                <thead>
                                    <tr>
                                        <th colspan="2">Total Invested is the amount invested by an investor or company, including all invested related charges or fees </th>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Syndication Fee is the fee charged from the investors according to their syndication status</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2">An Under Writing Fee is a non-recurring initial fee charged from investors who involves in the MCA business</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Anticipated Management Fee is the Management Fee expected to be collected from the remaining payments in the business</th>
                                    </tr>
                                    <!-- <tr> -->
                                    <!-- <th colspan="1"></th> -->
                                    <!-- </tr> -->
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
            {{Form::close()}}
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class=" grid table-responsive">
                    {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true) !!}
                    <div class="blockCust pull-right" style="padding-bottom: 15px">
                        <!--{{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}-->
                    </div>
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
<script type="text/javascript">
    window.state = {
        investorReport:true
    }
</script>
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>  
<script src="{{ asset('/js/custom/investment.js') }}"></script> 
<script src="{{ asset('/js/custom/common.js?v=17.02') }}"></script>
<script src="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.min.js') }}"></script>
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
<script type="text/javascript">
$('.clockpicker').clockpicker({ donetext: 'Done'});

$(document).on('mouseover','.funded_amount',function(){
    $(this).prop('title', 'Net Investment Amount');
});
$(document).on('mouseover','.total_invested',function(){
    $(this).prop('title', 'Gross Investment Amount');
});
$(document).ready(function(){
    $('#time_start,#time_end').mask('00:00');
    $('#time_start,#time_end').change(function(){
        var timestr = $(this).val();
        if (! isValidTimeString(timestr)) {
            // entered invalid time
            $(this).val('00:00');
        }
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
    });
    $('.to_date1,.to_date2').on('change changeDate', function(){
        var val = $(this).val();
        if(val && moment(val, default_date_format).isValid())
        {
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
    });
   
    
});
function isValidTimeString(timestr){
  var hours = timestr.split(":")[0];
  var minutes = timestr.split(":")[1];
  if(parseInt(hours) >= 0 && parseInt(hours) < 24 && parseInt(minutes) >= 0 && parseInt(minutes) <=59 ){
    return true;
  }
  return false;
};
</script> 
@stop
@section('styles')
<style type="text/css">
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
</style>
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/report_custom.css?ver=5') }}" rel="stylesheet" type="text/css" /> 
<link href="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.css') }}" rel="stylesheet" type="text/css" />  
@stop
