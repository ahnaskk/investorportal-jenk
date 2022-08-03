@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}}</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_description}}</div>
    </a>
</div>
{{ Breadcrumbs::render('admin::reports::fees') }}
<div class="col-md-12">
    <div class="box box-primary">
        <div class="box-title no-padding">
            <div class="row">
                <div class="col-md-12">
                </div>
            </div>
        </div>
        <div class="box-body no-padding">
            <div class="row">
                <div class="date-star" id="test" style="display:block">
                    {{Form::open(['route'=>'admin::reports::investor-liquidity-log-download','autocomplete'=>'on'])}}
                    <div class="col-md-2" style="height: 86px; margin-bottom: -2px;">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            {{Form::text('from_date1',date('Y-m-d'),['class'=>'form-control datepicker table_change','id'=>'from_date1' , 'autocomplete'=> 'off'])}}
                            <input type="hidden" name="from_date" value="{{ date('Y-m-d') }}" id="from_date" class="date_parse">
                            <span id="invalid-date_start"/>
                        </div>
                        <span class="help-block">{{Form::label('from_date','From Date')}}</span>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            {{Form::text('to_date1',date('Y-m-d',strtotime('+1 day')),['class'=>'form-control datepicker table_change','id'=>'to_date1', 'autocomplete' => 'off'])}}
                            <input type="hidden" name="to_date" value="{{ date('Y-m-d',strtotime('+1 day')) }}" class="date_parse" id="to_date">
                        </div>
                        <span class="help-block">{{Form::label('to_date','To Date')}}</span>
                    </div>
                    <div class="form-group col-md-2">
                        {!! Form::select('company_id',[''=>'All']+$company,'',['class'=>'form-control table_change','id'=>'company_id']) !!}
                        <label for="company_id">Company</label>
                    </div>
                    <div class="form-group col-md-2">
                        {!! Form::select('investor_id',[],'',['class'=>'form-control table_change js-investor-placeholder-multiple','id'=>'investor_id']) !!}
                        <label for="company_id">Investor</label>
                    </div>
                    <div class="form-group col-md-1">
                        <div class="btn-box inhelpBlock">
                            <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
                        </div>
                    </div>
                    <div class="form-group col-md-1">
                        <div class="btn-box inhelpBlock">
                            {{Form::submit('download',['class'=>'btn btn-primary'])}}
                        </div>
                    </div>
                    <div class="form-group col-md-1">
                        <div class="btn-box inhelpBlock">
                            <a href="{{url(route('admin::reports::investor-liquidity-log-create'))}}" class="btn btn-info">Create</a>
                        </div>
                    </div>
                    <div class="form-group col-md-1">
                        <div class="btn-box inhelpBlock">
                            <a href="{{url(route('admin::reports::investor-liquidity-log-truncate'))}}" class="btn btn-info">Remove All</a>
                        </div>
                    </div>
                    {{Form::close()}}
                </div>
            </div>
        </div>
        <div class="box-body no-padding">
            <div class="table-responsive grid">
                {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
</script>
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
$(document).ready(function(){
    table = window.LaravelDataTables["dataTableBuilder"];
    $('#apply').click(function(){
        table.draw();
    });    
    
});
</script>
@stop
@section('styles')
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
<style media="screen">
.pointer_cursor { cursor: pointer; }
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
</style>
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/merchant_view.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
