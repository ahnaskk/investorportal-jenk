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
        <div class="tool-tip">Profitability Report</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::reports::profitability2') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-body">
            <div class="form-box-styled">
                <div class="row" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'admin::reports::profitability2-export','id'=>'investor-form'])}}
                        <div class="serch-bar">
                        </div>
                    </div>
                    <div class="col-md-2 report_rate">
                        <div class="form-group px-2">
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
                    <div class="col-md-2 report_rate hidden">
                        <div class="form-group px-2">
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                        {{Form::checkbox('all_investors',null,null,['id'=>'all_investors', 'autocomplete' => 'off'])}}
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check this</span>
                                    </label>
                                </div>
                                <span class="help-block">Filter with all investors </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 report_rate">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::text('from_date1',$date_start,['class'=>'form-control datepicker','id'=>'from_date1', 'placeholder' => \FFM::defaultDateFormat('format'), "autocomplete" => 'off'])}}
                            <input type="hidden" name="from_date" value="{{ $date_start }}" id="from_date" class="date_parse">
                        </div>
                        <span class="help-block">From Date </span>
                    </div>    
                    <div class="col-md-2 report_rate">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::text('to_date1',$date_end,['class'=>'form-control datepicker','id'=>'to_date1', 'placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete' => 'off'])}}
                            <input type="hidden" name="to_date" value="{{ $date_end }}" id="to_date" class="date_parse">
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
                    <div class="col-md-2 invest-ment">
                        <div class="btn-box inhelpBlock ">
                            <input type="button" value="Apply Filter" class="btn btn-primary" id="apply"
                            name="Apply Button">
                            <div class="blockCust pull-right" style="padding-bottom: 15px">
                            </div>
                            {{Form::submit('Download',['class'=>'btn btn-success','id'=>'form_filter'])}}
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
    </div>
</div>
<!-- /.box-body -->
@stop
@section('scripts')  
<script src="{{ asset('/js/moment.min.js') }}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];
$('#apply').click(function (e) {
    e.preventDefault();
    table.draw();
});
</script> 
@stop
@section('styles')
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
