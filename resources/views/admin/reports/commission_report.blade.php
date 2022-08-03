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
        <div class="tool-tip">Upsell Commission Report</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::reports::upsell-commission') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-body">
            <div class="form-group">
                <div class="filter-group-wrap" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'admin::reports::commission-export','id'=>'commission-form'])}}
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
                                            <span class="help-block">From Date</span>
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
                                    {{Form::select('investors[]',$investors,'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}
                                </div>
                                <span class="help-block">Investors </span>
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
                        </div>
                        <div class="row">
                        
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
                            <div class="col-md-4 col-sm-6">
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
                       
                        <input type="hidden" name="row_merchant" id="row_merchant" value="">
                      
                        <div class="btn-wrap btn-right">
                            <div class="btn-box inhelpBlock ">
                                <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
                                <div class="blockCust pull-right">
                                    @if(@Permissions::isAllow('Upsell Commission Report','Download')) 
                                    {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter','name'=>'download'])}}
                                    @endif
                                
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div> 
            {{Form::close()}}
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class=" grid table-responsive">
                    {!! $tableBuilder->table(['class' => 'table table-bordered commissionReport'], true) !!}
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
        commissionReport:$("#commission-form").serializeArray()
    }
</script>
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>  
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script>  
<script src="{{ asset('/js/custom/commission.js') }}"></script> 
<script src="{{ asset('/js/custom/common.js?v=17.02') }}"></script>
<script src="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.min.js') }}"></script>
<script type="text/javascript">
$('.clockpicker').clockpicker({ donetext: 'Done'});
function isValidTimeString(timestr){
  var hours = timestr.split(":")[0]; 
  var minutes = timestr.split(":")[1];
  if(parseInt(hours) >= 0 && parseInt(hours) < 24 && parseInt(minutes) >= 0 && parseInt(minutes) <=59 ){
    return true;
  }
  return false;
};
$(document).ready(function(){
    var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
    $('#time_start,#time_end').mask('00:00');
    $('#time_start,#time_end').change(function(){
        var timestr = $(this).val();
        if (! isValidTimeString(timestr)) {
            // entered invalid time
            $(this).val('00:00');
        }
    });
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
        if(val &&  moment(val, default_date_format).isValid())
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
