<?php use App\Models\InvestorAchRequest; ?>
@extends('layouts.admin.admin_lte')
@section('styles')
<style type="text/css">
    li.breadcrumb-item.active{
    color: #2b1871!important;
    }
    li.breadcrumb-item a{
    color: #6B778C;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {padding:0 !important;line-height:32px;}
</style>
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
@stop
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{ $page_title }}</div>
    </a>
</div>
{{ Breadcrumbs::render('admin::payments::investor-ach-requests.index') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <div class="box-body">
            <div class="form-box-styled" >
                <div class="serch-bar">
                    <div class="text-capitalize">
                        <form action="{{ route('admin::payments::investor-ach-requests.export') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                        </div>
                                        {{Form::text('from_date1',date('Y-m-d'),['class'=>'form-control datepicker table_change','id'=>'from_date1','placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete'=>'off'])}}
                                        <input type="hidden" name="from_date" value="{{ date('Y-m-d') }}" id="from_date" class="date_parse" autocomplete="off">
                                        <span id="invalid-date_start"/>
                                    </div>
                                    <span class="help-block">{{Form::label('from_date','From Date')}}</span>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                        </div>
                                        {{Form::text('to_date1',date('Y-m-d',strtotime('+1 day')),['class'=>'form-control datepicker table_change','id'=>'to_date1','placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete'=>'off'])}}
                                        <input type="hidden" name="to_date" value="{{ date('Y-m-d',strtotime('+1 day')) }}" id="to_date" class="date_parse" autocomplete="off">
                                    </div>
                                    <span class="help-block">{{Form::label('to_date','To Date')}}</span>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                        </div>
                                        {{Form::select('investor_id',[],'',['class'=>'form-control table_change js-investor-placeholder-multiple','id'=>'investor_id'])}}
                                    </div>
                                    <span class="help-block">{{Form::label('investor_id','Investors')}}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                        </div>
                                        {{Form::select('ach_request_status',[''=>'All']+InvestorAchRequest::achRequestStatusOptions(),'',['class'=>'form-control table_change select2_class','id'=>'ach_request_status'])}}
                                    </div>
                                    <span class="help-block">{{Form::label('ach_request_status','Settlement Status')}}</span>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <div class="input-group check-box-wrap">
                                            <div class="input-group-text">
                                                <label class="chc">
                                                    {{ Form::checkbox('order_id',1,false,['id'=>'order_id','class'=>'table_change', 'checked']) }}
                                                    <span class="checkmark chek-m"></span>
                                                    <span class="chc-value">Check this</span>
                                                </label>
                                            </div>
                                        </div>
                                        <span class="help-block">{{ Form::label('order_id', ucfirst('Show only items with Order ID')) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                    </div>
                                    {{Form::select('transaction_method',[''=>'All']+InvestorAchRequest::transactionMethodOptions(),'',['class'=>'form-control table_change select2_class','id'=>'transaction_method'])}}
                                    </div>
                                    <span class="help-block">{{Form::label('transaction_method','Transaction Method')}}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                        </div>
                                        {{Form::select('transaction_type',[''=>'All']+InvestorAchRequest::InvertedtransactionTypeOptions(),'',['class'=>'form-control table_change select2_class','id'=>'transaction_type'])}}
                                    </div>
                                    <span class="help-block">{{Form::label('transaction_type','Transaction Type')}}</span>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                        </div>
                                        {{Form::select('ach_status',[''=>'All']+InvestorAchRequest::achStatusOptions(),'',['class'=>'form-control table_change select2_class','id'=>'ach_status'])}}
                                    </div>
                                    <span class="help-block">{{Form::label('ach_status','Request Status')}}</span>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-group">
                                        <input type="submit"  value="Download" class="btn btn-primary" >
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="loadering" style="display:none;">
                        <div class="loader"></div><br>
                    </div>
                    <div class="table-responsive grid text-capitalize">
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true);
                        $tableBuilder->parameters(['stateSave' => true])
                        !!}
                    </div>
                </div>
            </div>
            <div class="row text-capitalize">
                <div class="col-sm-12">
                    <div class="input-group" style="justify-content: center;text-align:center">
                        <input type="button" value="Check" class="btn btn-success " id="achRequestStatusCheckAllButton" style="width:300px">
                        <span class="help-block" align="center">Check All Settlement Processing Request</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
{!! $tableBuilder->scripts() !!}
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];
if(performance.navigation.type == 2)
{
    $(document).ready(function(e){
        $("#date_filter").click();
    })
}
window.addEventListener( "pageshow", function ( event ) {
  var historyTraversal = event.persisted || 
                         ( typeof window.performance != "undefined" && 
                              window.performance.navigation.type === 2 );
  if ( historyTraversal ) {
    // Handle page restore.
    $(document).ready(function(e){
        var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
        $('.datepicker').each(function(){
            var val = $(this).datepicker("getDate");
            var moment_date = moment(val).format(default_date_format);
            $(this).val(moment_date).datepicker('update');
            $(this).siblings('.date_parse').val(moment(val).format('YYYY-MM-DD'));
        });
        $('#from_date1').trigger('change')
    })
  }
});
$(document).on('click','#achRequestStatusCheckAllButton',function() {
    if(confirm('Do you really want to check all the ACH status?')) {
        var url_address = "{{ url('admin/payment/investor/ach-request-status-check-all') }}";
        var data={
            _token   : "<?= csrf_token() ?>",
            from_date:$('#from_date').val(),
            to_date  :$('#to_date').val(),
        }
        $.post(url_address,data, function(response) {
            if (response.result != 'success') { Swal.fire( 'Error!', response.result, 'error' ); table.draw(); return false; }
            processedCount = response.processedCount ?? 0 ;
            if (processedCount > 0) {
                Swal.fire( 'Success!', processedCount+' Processed Successfully', 'success' );
            } else {
                Swal.fire( 'Error!', 'No requests Processed Successfully', 'error' );
            }
            table.draw();
        }, "json");
    }
});
$(document).on('click','.check_status',function() {
    if(confirm('Do you really want to check single ACH status?')) {
        var table_id = $(this).attr('table_id');
        var url_address = "{{ url('admin/payment/investor/ach-requests-check') }}/" + table_id;
        $.get(url_address, function(response) {
            if (response.result != 'success') {
                Swal.fire( 'Transaction Pending!', 'Response - '+response.result, 'warning' );
                table.draw();
                return false;
            }
            Swal.fire( 'Success!', 'Response - '+response.data.message, 'success' );
            table.draw();
        }, "json");
    }
});
$(document).on('click','.edit',function(){
    var table_id = $(this).attr('table_id');
    var order_id = $(this).attr('order_id');
    Swal.fire({
        title: 'Edit Order ID',
        html:
        '<input id="swal-order_id" class="swal2-input" value="'+order_id+'">' +
        '<p>21038846: updated to Check Settlement</p>'+
        '<p>21039906: updated to Check Settlement</p>'+
        '<p>21039931: updated to Check Settlement</p>'+
        '<p>21039932: updated to Check Return (Payment Stopped)</p>'+
        '<p>21039936: updated to Check Return (Account Closed)</p>'+
        '<p>21039938: updated to Check Return (No Account/Unable to Locate Account)</p>',
        focusConfirm: false,
        inputValidator: (value) => {
            if (!value) { return 'Please Enter Order ID!' }
        },
        preConfirm: () => {
            return [
                document.getElementById('swal-order_id').value,
            ]
        }
    }).then((result) => {
        if (result.value) {
            var url_address = '{{url("admin/investors/investor_ach_request-edit")}}/'+table_id;
            var data={
                _token  : "<?= csrf_token() ?>",
                order_id:result.value[0],
            };
            $.post(url_address,data, function(response) {
                if (response.result != 'success') { Swal.fire( 'Error!', response.result, 'error' ); return false; }
                table.draw();
            }, "json");
        }
    });
});
</script>
<script type="text/javascript">
$(document).on('change','.table_change',function() {
    table.draw();
});
$("#investor_id").select2({
    placeholder: "Search Investor Here",
    width: '100%',
    ajax: {
        url: '<?= route('investor::InvestorAchRequest.GetList') ?>',
        dataType: 'json',
        method: 'post',
        delay: 250,
        data: function(data) {
            return {
                _token    : "<?= csrf_token() ?>",
                search_tag: data.term,
                type      : 'list',
            };
        },
        processResults: function(data, params) {
            params.page = params.page || 1;
            return {
                results: $.map(data.items, function(obj) { return { id: obj.id, text: obj.name }; }),
                pagination: { more: (params.page * 30) < data.total_count }
            };
        },
        cache: false
    },
});
</script>
@stop
