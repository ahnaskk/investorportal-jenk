@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Profitability Report</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::reports::profitability3') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-body">
            <div class="form-box-styled">
                {{Form::open(['route'=>'admin::reports::profitability3-export','id'=>'investor-form'])}}
                <div class="row">
                    <div class="col-md-2 report_rate">
                        <div class="form-group px-1">
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                        {{Form::checkbox('funded_date',null,null,['id'=>'funded_date'])}}
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check this</span>
                                    </label>
                                </div>
                                <span class="help-block">Filter with Funding Date </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 report_rate">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{--{{Form::date('from_date',$date_start,['class'=>'form-control','id'=>'from_date'])}}--}}
                            <input  max="2020-12-31" autocomplete="off" placeholder="{{\FFM::defaultDateFormat('format')}}"  class="form-control datepicker" id="from_date1" name="from_date1" type="text" value="{{date('2020-12-01')}}">
                            <input type="hidden" name="from_date" id="from_date" value="{{date('2020-12-01')}}" class="date_parse">
                        </div>
                        <span class="help-block">From Date </span>
                    </div>   
                    <div class="col-md-2 report_rate">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{--{{Form::date('to_date',$date_end,['class'=>'form-control','id'=>'to_date'])}}--}}
                            <input  max="2020-12-31" autocomplete="off" placeholder="{{\FFM::defaultDateFormat('format')}}"   class="form-control datepicker" id="to_date1" name="to_date1" type="text" value="{{date('2020-12-31')}}" >
                            <input type="hidden" name="to_date" id="to_date" value="{{date('2020-12-31')}}" class="date_parse">
                        </div>
                        <span class="help-block">To Date </span>
                    </div>
                    <div class="col-md-4" hidden>
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::select('merchants[]',$merchants,'',['class'=>'form-control','id'=>'merchants','multiple'=>'multiple'])}}
                        </div>
                        <span class="help-block">Merchants</span>
                    </div>
                    <div class="col-md-2">
                        <div class="btn-wrap btn-left">
                            <div class="btn-box">
                                <input type="button" value="Apply Filter" class="btn btn-primary" id="apply"
                                name="Apply Button">
                                <div class="blockCust pull-right" style="padding-bottom: 15px">
                                </div>
                                {{Form::submit('Download',['class'=>'btn btn-success','id'=>'form_filter'])}}
                            </div>
                        </div>
                    </div>
                    {{Form::close()}}
                </div>
            </div>
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-12 grid table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true) !!}
                        <div class="blockCust pull-right" style="padding-bottom: 15px">
                            <!--{{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop
@section('scripts')    
<script src="{{ asset('/js/moment.min.js') }}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
var error = 0;
var table = window.LaravelDataTables["dataTableBuilder"];
$('#apply').click(function (e) {
    e.preventDefault();
    if($('#investor-form').valid()) {
        table.draw();
    }
});
$(document).ready(function(){
    $('#from_date1').datepicker('setEndDate', new Date($('#from_date1').attr('max')));
    $('#to_date1').datepicker('setEndDate', new Date($('#to_date1').attr('max')));
    $.validator.addMethod("maxDate", function(value, element, newDate){
        try {
            if(newDate) {
                let max = $(element).attr('max');
                let maxDate = new Date(max);
                newDate = new Date(newDate);
                if(newDate > maxDate) {
                    return false;
                }
                return true;
            }
        } catch(e) {
        }
        return false;
    });
    $('#investor-form').validate({
        errorClass: 'errors',
        rules: {
            from_date1: { maxDate: function(){return $('#from_date').val()} },
            to_date1: { maxDate: function(){return $('#to_date').val()} },
        },
        messages: {
            from_date1: {
                maxDate: "Please enter a valid date."
            },
            to_date1: {
                maxDate: "Please enter a valid date."
            }
        }
    });
    
});
</script> 
@stop
@section('styles')
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
