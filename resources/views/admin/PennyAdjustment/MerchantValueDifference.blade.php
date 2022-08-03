@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}}</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_description}}</div>
    </a>
</div>
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
                <div class="col-md-4" style="margin-bottom: -2px;">
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                        </div>
                        {{Form::select('merchant_id',[''=>'All']+$merchants,'',['class'=>'form-control table_change select2_class','id'=>'merchant_id'])}}
                    </div>
                    <span class="help-block">{{Form::label('merchant_id','Merchant')}}</span>
                </div>
                <div class="col-md-2" style="margin-bottom: -2px;">
                    <div class="input-group">
                        {{ Form::checkbox('rtr_diff',1,true,['id'=>'rtr_diff','class'=>'table_change']) }}&emsp;
                    </div>
                    <span class="help-block">{{Form::label('rtr_diff','Exclude Rows without RTR Difference')}}</span>
                </div>
                <div class="col-md-2" style="margin-bottom: -2px;">
                    <div class="input-group">
                        {{ Form::checkbox('diff_final_participant_share',1,true,['id'=>'diff_final_participant_share','class'=>'table_change']) }}&emsp;
                    </div>
                    <span class="help-block">{{Form::label('diff_final_participant_share','Exclude Rows without Final Participant Share Difference')}}</span>
                </div>
                <div class="col-md-1" style="margin-bottom: -2px;">
                    <div class="input-group">
                        <a href="{{route('PennyAdjustment::UpdateMerchantValueRTRDifference')}}" class="btn btn-info">Update RTR</a>
                    </div>
                    <span class="help-block">Update the RTR</span>
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
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
</script>
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function(){
    $('.table_change').change(function(){
        table.draw();
    });
});
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/merchant_view.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
