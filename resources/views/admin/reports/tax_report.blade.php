@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{ $page_title }}</div>     
    </a>
    
</div>
{{ Breadcrumbs::render('admin::reports::tax_report') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <div class="box-body"> 
            <div class="form-box-styled" >
                <div class="serch-bar">
                    <form method="POST" action="{{ route('admin::reports::tax-report-export') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-2">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                    </div>
                                    <input class="form-control from_date1 datepicker" autocomplete="off" id="start_date1" name="start_date1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                    <input type="hidden" name="start_date" id="start_date" class="date_parse">
                                </div>
                                <span class="help-block">From Date</span>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                    </div>
                                    <input class="form-control to_date1 datepicker" autocomplete="off" id="end_date1" name="end_date1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                    <input type="hidden" name="end_date" id="end_date" class="date_parse">
                                </div>
                                <span class="help-block">To Date</span>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon" aria-hidden="true"></span>
                                    </div>
                                    {!! Form::select('label[]',$labels,'',['class'=>'form-control js-label-placeholder-multiple','id'=>'label','multiple'=>'multiple']) !!}  
                                </div>
                                <span class="help-block">label</span>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                    </div>
                                    {{Form::select('sub_status_ids[]',$sub_statuses,[1,5],['class'=>'form-control js-status-placeholder-multiple','id'=>'sub_status_ids','placeholder'=>'Select Status','multiple'=>'multiple'])}}
                                </div>
                                <span class="help-block">Status</span>
                            </div>
                            
                        </div>    
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                    </div>
                                    {{Form::select('merchant_ids[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchant_ids','multiple'=>'multiple'])}}
                                </div>
                                <span class="help-block">Merchants</span>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                    </div>
                                    {{ Form::select('lender_ids[]',$lenders,null,['class'=>'form-control js-lender-placeholder-multiple','id'=>'lender_ids','multiple'=>'multiple']) }}
                                </div>
                                <span class="help-block">Lender</span>
                            </div>
                            <div class="col-md-4">
                                <div class="btn-box " style="margin-bottom: 25px;">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="apply_filter">
                                    <input type="submit" value="Download" class="btn btn-primary">
                                </div> 
                            </div>
                        </div>                         
                    </form>
                </div>
            </div>
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="loadering" style="display:none;">
                    <div class="loader"></div><br>
                </div>
                <div class="row">
                    <div class="col-sm-12 grid table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true);$tableBuilder->parameters(['stateSave'=>true]) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
<script src="{{ asset('/js/custom/placeholder.js') }}"></script> 
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function(){
    $('#apply_filter').click(function (e) { e.preventDefault();
        table.draw();
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
<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('/css/libs-font-awesome.min.css') }}">
@stop