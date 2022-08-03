@extends('layouts.admin.admin_lte')
@section('content')
<?php
$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
?>
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Investor Reassignment Report</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Investor Re-assignment Report</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::reports::get-reassign-report') }}
<div class="col-md-12">
    <div class="box">
        @include('layouts.admin.partials.lte_alerts')
        <div class="box-body">
            <div class="form-box-styled" >
                {{Form::open(['route'=>'admin::investors::transactionreportdownload'])}}
                <div class="row">
                    <div class="col-lg-3">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            <input class="form-control datepicker" autocomplete="off" id="date_start1" value="{{ $date_start }}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                            type="text"/>
                            <input type="hidden" name="date_start" id="date_start" value="{{$date_start}}" class="date_parse">
                        </div>
                        <span class="help-block">From Date</span>
                    </div>
                    <div class="col-lg-3">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            <input class="form-control datepicker" autocomplete="off" id="date_end1" value="{{ $date_end }}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                            type="text"/>
                            <input type="hidden" name="date_end" id="date_end" value="{{$date_end}}" class="date_parse">
                        </div>
                        <span class="help-block">To Date</span>
                    </div>
                    <div class="col-lg-3">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}
                        </div>
                        <span class="help-block">Merchants</span>
                    </div>
                    <div class=" col-lg-3">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::select('investors[]',[],"",['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}
                        </div>
                        <span class="help-block">Investors </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-wrap btn-right">
                            <div class="btn-box">
                                <!-- <div class="pull-right" style="padding-bottom: 15px"> -->
                                <!-- {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}} -->
                                <!-- </div>                                          -->
                                <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">                                  
                            </div>
                        </div>
                    </div>
                </div>
                {{Form::close()}}
            </div>
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="table-container" > 
                    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
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
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="text/javascript">
var URL_undoReassign="{{ URL::to('admin/merchants/undo-reassign/') }}";
function undo_function(investor_id,merchant_id) {
    if(merchant_id) {
        $.ajax({
            type:'POST',
            data: {'investor_id': investor_id, 'merchant_id':merchant_id,'_token': _token},
            url:URL_undoReassign,
            success:function(data) {
                if (data.result != 'success') { Swal.fire('Error!', data.result, 'error'); return false; }
                window.location.reload();
            }
        }); 
    }
}
    
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
<link href="{{ asset('/css/optimized/Reassignment_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
@stop
