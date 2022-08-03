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
                <div class="col-md-3" style="margin-bottom: -2px;">
                    <div class="input-group">
                        {{ Form::checkbox('expected_existing_participant_share',1,true,['id'=>'expected_existing_participant_share','class'=>'table_change']) }}&emsp;
                    </div>
                    <span class="help-block">{{Form::label('expected_existing_participant_share','Exclude Rows without Expected-Existing Difference')}}</span>
                </div>
                <div class="col-md-3" style="margin-bottom: -2px;">
                    <div class="input-group">
                        {{ Form::checkbox('diff',1,true,['id'=>'diff','class'=>'table_change']) }}&emsp;
                    </div>
                    <span class="help-block">{{Form::label('diff','Exclude Rows without Existing - Actual Difference')}}</span>
                </div>
                <!-- <div class="col-md-2" style="margin-bottom: -2px;"> -->
                <!-- <div class="input-group"> -->
                <!-- <a href="{{route('PennyAdjustment::RemoveZeroParticipantAmount')}}" class="btn btn-info">Remove</a> -->
                <!-- </div> -->
                <!-- <span class="help-block">Remove Zero Participant Amount</span> -->
                <!-- </div> -->
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
    function format(d) {
        var table='<table class="table" style="width:100%">';
        table+='<thead>';
        table+='<tr>';
        table+=      '<th>User</th>';
        table+=      '<th class="text-right">Participant Share</th>';
        table+=      '<th class="text-right">Mgmnt Fee</th>';
        table+=      '<th class="text-right">Syndication Fee</th>';
        table+=      '<th class="text-right">Investor Share</th>';
        table+='</tr>';
        table+='</thead>';
        table+='<tbody>';
        $.each(d.List,function(key,value){
            console.log(value['payment']);
            table+='<tr>';
            table+=      '<td>'+value['User']+'</td>';
            table+=      '<td class="text-right">'+value['participant_share']+'</td>';
            table+=      '<td class="text-right">'+value['mgmnt_fee']+'</td>';
            table+=      '<td class="text-right">'+value['syndication_fee']+'</td>';
            table+=      '<td class="text-right">'+value['investor_share']+'</td>';
            table+='</tr>';
        });
        table+='</tbody>';
        table+='</table>';
        return table;
    }
    $('#dataTableBuilder tbody').on('click', 'td.details-control ', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });
});
</script>
@stop
@section('styles')
<style media="screen">
td.details-control {
    background: url('{{asset("img/icons/details_open.png")}}') no-repeat center center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('{{asset("img/icons/details_close.png")}}') no-repeat center center;
}
.pointer_cursor { cursor: pointer; }
</style>
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/merchant_view.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
