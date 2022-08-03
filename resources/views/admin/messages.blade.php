<?php use App\Models\Message; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}}</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_description}}</div>
    </a>
</div>
{{ Breadcrumbs::render('admin::messages::lists') }}
<div class="col-md-12">
    <div class="box box-primary">
        <div class="box-title no-padding">
            <div class="row">
                <div class="col-md-12">
                </div>
            </div>
        </div>
        <div class="box-body no-padding">
            <div class="row g-0">
                <div class="date-star col-md-6" id="test" style="display:block">
                    <div class="col-md-6" style="height: 86px; margin-bottom: -2px;">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            {{Form::text('from_date1',date('Y-m-d'),['class'=>'form-control datepicker table_change','id'=>'from_date1','placeholder'=>FFM::defaultDateFormat('format'), 'autocomplete' => 'off'])}}
                            <input type="hidden" name="from_date" value="{{ date('Y-m-d') }}" id="from_date" class="date_parse">
                            <span id="invalid-date_start"/>
                        </div>
                        <span class="help-block">{{Form::label('from_date','From Date')}}</span>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            {{Form::text('to_date1',date('Y-m-d',strtotime('+1 day')),['class'=>'form-control datepicker table_change','id'=>'to_date1','placeholder'=>FFM::defaultDateFormat('format'), 'autocomplete' => 'off'])}}
                            <input type="hidden" name="to_date" class="date_parse" value="{{ date('Y-m-d',strtotime('+1 day')) }}" id="to_date">
                        </div>
                        <span class="help-block">{{Form::label('to_date','To Date')}}</span>
                    </div>
                </div>
                <div class="col-md-4" style="margin-bottom: -2px;">
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                        </div>
                        {{Form::select('status',[''=>'All']+Message::statusOptions(),Message::PENDING,['class'=>'form-control table_change select2_class','id'=>'status'])}}
                    </div>
                    <span class="help-block">{{Form::label('status','Status')}}</span>
                </div>
                <div class="col-md-2" style="margin-bottom: -2px;">
                    <div class="input-group">
                        <button type="button" style="width:100%" id="getButton" class="btn btn-info">Apply</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table>
                        <thead>
                            <tr>
                                <th>* Send Only Pending Messages</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="box-body no-padding">
            <div class="table-responsive grid">
                {!! $tableBuilder->table(['class' => 'table table-bordered'], true);$tableBuilder->parameters([
                'drawCallback' => 'function(){recheck()}',]) !!}
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
function recheck(){
    if($("#checkAllButtont").prop('checked')){
        $(".single_check").prop('checked', true);
    }
}
function uncheckMain(){
    var uncheck = 0;
    $('input:checkbox.single_check').each(function () {
        if(!this.checked){
            uncheck = 1;
            $('#checkAllButtont').prop('checked', false);
        }
    });
    if(uncheck==0){
        $('#checkAllButtont').prop('checked', true);
    }
}
$(document).ready(function(){
    var table = window.LaravelDataTables["dataTableBuilder"];
    $('#getButton').click(function(){
        table.draw();
    });
    $(document).on('click','#sendButton',function(e){ e.preventDefault();
        var url_address = '{{ route("admin::messages::send") }}';
        var selectedId=[];
        merchants='';
        $('.single_check:checked').each(function() {
            selectedId.push($(this).val());
            if(selectedId.length<=10){
                merchants = merchants + $(this).attr('name') + "<br>";
            }
        });
        if(selectedId.length>10){
            moreCount=selectedId.length-10;
            merchants = merchants +"+ other "+moreCount+" merchants.";
        }
        merchants =$.parseHTML(merchants);
        if(!selectedId.length){ Swal.fire( 'Warning!', 'Please Select Any Row To Send it', 'warning' ); return false }
        Swal.fire({
            title: 'Do you really want to send SMS to '+selectedId.length+' merchants',
            html: merchants,
            type: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Send it!'
        }).then((result) => {
            if (result.value) {
                var data= {
                    _token    : "<?= csrf_token() ?>",
                    from_date : $('#from_date').val(),
                    to_date   : $('#to_date').val(),
                    selectedId: selectedId,
                };
                $.post(url_address,data, function(response) {
                    var modals = [];
                    if(response.success_response) { modals.push({title: response.success_count+' Success!', html: response.success_response,icon:'success' }); }
                    if(response.failed_response)  { modals.push({title: response.failed_count+' Error!'   , html: response.failed_response ,icon:'error'   }); }
                    swal.queue(modals);
                    table.draw();
                },"json");
            }
        });
    });
    $(document).on('click','.singleSend',function(e){ e.preventDefault();
        var modal_name = $(this).attr('modal_name');
        Swal.fire({
            title: 'Are You Sure?',
            text: 'Do you really want to send an sms to '+modal_name+'?',
            type: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Send it!'
        }).then((result) => {
            if (result.value) {
                var table_id = $(this).attr('table_id');
                var url_address = '{{ url("admin/messages/send") }}/'+table_id;
                $.get(url_address, function(response) {
                    var modals = [];
                    if(response.success_response) { modals.push({title: response.success_count+' Success!', html: response.success_response,icon:'success' }); }
                    if(response.failed_response)  { modals.push({title: response.failed_count+' Error!'   , html: response.failed_response ,icon:'error'   }); }
                    swal.queue(modals);
                    table.draw();
                },"json");
            }
        });
    });
    function format ( d ) {
        var table='<table class="table">';
        table+='<tr>';
        table+=      '<td>'+d.message+'</td>';
        table+='</tr>';
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
    $('#checkAllButtont').on('click',function() {
        if($(this).is(':checked',true)) {
            $(".single_check").prop('checked', true);
        } else {
            $('.single_check').prop('checked', false);
        }
    });
    
});
</script>
@stop
@section('styles')
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
<style media="screen">
td.details-control {
    background: url('{{asset("img/icons/details_open.png")}}') no-repeat center center;
    cursor: pointer;
}
tr.shown td.details-control {
    background: url('{{asset("img/icons/details_close.png")}}') no-repeat center center;
}
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
