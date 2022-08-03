@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip"> Send Investor ACH of {{\FFM::date(date('Y-m-d'))}}</div>
    </a>
</div>
{{ Breadcrumbs::render('admin::investors::syndication-payments') }}
<div class="col-md-12">
    <div class="box box-primary">
        <div class="box-header">
            <div class="row" id="messageDisplayArea" style="display:none">
                <p class="text-center alert alert-info" id="messageDisplay"></p>
            </div>
            <div class="row">
                @if(Session::has('message'))
                <div class="col-md-12">
                    <p class="alert alert-info">{!! Session::get('message') !!}</p>
                </div>
                @endif
                @if(Session::has('error'))
                <div class="col-md-12">
                    <p class="alert alert-danger">{!! Session::get('error') !!}</p>
                </div>
                @endif
            </div>
            <div class="box-body">
                <form action="" id="syndicationFetchForm">
                    <div class="row">
                        <div class="form-group col-md-3">
                            {!! Form::select('notification_recurence',$recurrence_types,$recurrence_type,['class'=>'form-control','id'=>'notification_recurence','multiple','name'=>'notification_recurence']) !!}
                            <label for="recurrence_types">Payout Frequency<span class="validate_star">*</span></label>
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::select('investor_id',[''=>'All']+$investorsList,'',['class'=>'form-control','id'=>'investor_id']) !!}
                            <label for="investor_id">Investor</label>
                        </div>
                        <div class="form-group col-md-1">
                            <button type="button" id="fetchButton" class="btn btn-success">Fetch</button>
                        </div>
                    </div>
                </form>
            </div> 
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered dataTable">
                        <thead>
                            <tr hidden>
                                <th>Auto Invest collected amount</th>
                                <th>Disabled</th>
                            </tr>
                            <tr>
                                <th>Payment Date </th>
                                <th>: {{FFM:: date($paymentDate)}}</th>
                            </tr>
                            <tr>
                                <th colspan="2">Labels included here are ones that are not added in Auto Invest</th>
                            </tr>
                            <tr>
                                <th colspan="2">Banking days only</th>
                            </tr>
                            <tr>
                                <th colspan="2">Only participant investors with PTS are displayed here</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered dataTable">
                        <thead>
                            <tr>
                                <th colspan="2">Reports are sent to all the investors whom ACH successfully requested</th>
                            </tr>
                            <tr>
                                <th colspan="2">Same day ACH Button will be disabled after 12 pm</th>
                            </tr>
                            <tr>
                                <th colspan="2">Weekly Investors will be displayed every Friday or the previous working day, if Friday is a holiday</th>
                            </tr>
                            <tr>
                                <th colspan="2">Monthly Investors will be displayed on the last day of the month or the previous working day, if that day is a holiday</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="table-responsive grid text-capitalize">
                    {!! Form::open(['route'=>['admin::investors::syndication-payments-send','method'=>'POST'],'id'=>'payment_form']) !!}
                    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                    <div class="row">
                        <div class="col-md-3 offset-md-9" >
                            <div class="col-md-6">
                                <button type="submit" name="type" value="normal" class="btn btn-success btn-sm" title="Will be transferred to or from the syndicate whose Auto ACH status is ON"><i class="glyphicon glyphicon-send"></i> Normal Send All</button>
                            </div>
                            @if($same_day_button)
                            <div class="col-md-6">
                                <button type="submit" name="type" value="same_day_" class="btn btn-primary btn-sm" title="Will be transferred to or from the syndicate whose Auto ACH status is ON"><i class="glyphicon glyphicon-sd-video"></i> Same Day Send All</button>
                            </div>
                            @endif
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
{!! $tableBuilder->scripts() !!}
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function() {
    $(document).on('submit', 'form', function(e) {
        if(confirm('Are you sure you want to proceed?')) {
            $(this).find('button:submit, input:submit').css('display', 'none');
            return true
        }
        return false
    });
    $(document).on('click', '.changeAutoSyndicateStatus', function() {
        if(confirm('Are you sure you want to change Auto Syndicate Payment Status?'))
        {
            var investor_id = $(this).attr('investor_id');
            var url_address = '{{url("admin/investors/changeAutoSyndicatePaymentStatus")}}';
            var data = {
                _token: "<?= csrf_token() ?>",
                investor_id: investor_id,
            };
            $.post(url_address, data, function(response) {
                if (response.status == 0) {
                    Swal.fire('Error!', response.message, 'error');
                    return false;
                }
                $('#messageDisplayArea').show();
                $('#messageDisplay').html(response.message);
                table.draw();
            }, "json");
        }
    });
    $(document).on('click', '.singleSendButton', function() {
        if ($(this).closest('tr').find('td input.SyndicateAmount').valid()) {
            if(confirm('Are you sure you want to proceed?')) {
                var SyndicateAmount = $(this).closest('tr').find('td input.SyndicateAmount').val();
                var investor_id = $(this).attr('investor_id');
                var type = $(this).attr('type');
                if(!type) { alert('Need Transaction Type'); return false; }
                var url_address = '{{url("admin/investors/sendSyndicationPaymentSingle")}}';
                var data = {
                    _token: "<?= csrf_token() ?>",
                    investor_id: investor_id,
                    amount: SyndicateAmount,
                    type: type,
                };
                $.post(url_address, data, function(response) {
                    if (response.result != 'success') {
                        Swal.fire('Error!', response.result, 'error');
                        return false;
                    }
                    $('#messageDisplayArea').show();
                    $('#messageDisplay').html(response.message);
                    table.draw();
                }, "json");
            }
        }
    });
    $('#fetchButton').click(function() {
        if($("#syndicationFetchForm").valid()){
            table.draw();
        }
    });
    $("#syndicationFetchForm").validate({
        rules:{
            notification_recurence:{
                required:true
            }
        }
    })
    $('#dataTableBuilder').on('keyup', '.SyndicateAmount', function() {
        tbodytrLoop();
    });
    function dollar_format(nStr) {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return '$' + x1 + x2;
    }
    function tbodytrLoop() {
        var SyndicateAmount = 0;
        $('#dataTableBuilder tbody tr').each(function() {
            var amount = $(this).closest('tr').find('td input.SyndicateAmount').val();
            if (amount && $(this).find('td').hasClass('validSum')) {
                SyndicateAmount = parseFloat(SyndicateAmount) + parseFloat(amount);
            }
        });
        SyndicateAmount = dollar_format(parseFloat(SyndicateAmount).toFixed(2));
        $('#total_syndication_amount').html(SyndicateAmount);
    }
    function format(d) {
        var table = '';
        table += '<table class="table">';
        table += '<tr>';
        table += '<td align="right" width="20%">Last Generation Time</td>';
        table += '<td>' + d.generation_time + '</td>';
        table += '</tr>';
        table += '<tr>';
        table += '<td align="right" width="20%">Notification Recurrence</td>';
        table += '<td>' + d.notification_recurence + '</td>';
        table += '</tr>';
        if (d.label) {
            table += '<tr>';
            table += '<td align="right" width="20%">Label</td>';
            table += '<td>' + d.label + '</td>';
        }
        table += '</tr>';
        table += '<tr>';
        table += '<td align="right" width="20%">Syndication Check</td>';
        table += '<td>' + d.syndication_check + '</td>';
        table += '</tr>';
        if (d.syndication_check) {
            if (!d.cell_phone)
            table += '<tr style="background-color:#fb01011f">';
            else
            table += '<tr>';
            table += '<td align="right" width="20%">Cell Phone</td>';
            table += '<td>' + d.cell_phone + '</td>';
            table += '</tr>';
            if (d.Bank == "Empty")
            table += '<tr style="background-color:#fb01011f">';
            else
            table += '<tr>';
            table += '<td align="right" width="20%">Bank</td>';
            table += '<td>' + d.Bank + '</td>';
            table += '</tr>';
        }
        // table += '<tr>';
        // table += '<td align="right" width="20%">Principal</td>';
        // table += '<td>' + d.principal + '</td>';
        // table += '</tr>';
        // table += '<tr>';
        // table += '<td align="right" width="20%">Profit</td>';
        // table += '<td>' + d.profit + '</td>';
        // table += '</tr>';
        // we can remove it after 10-aug-2021
        // table += '<tr>';
        // table += '<td align="right" width="20%">Participant RTR</td>';
        // table += '<td>' + d.participant_rtr + '</td>';
        // table += '</tr>';
        // table += '<tr>';
        // table += '<td align="right" width="20%">Participant RTR Balance</td>';
        // table += '<td>' + d.participant_rtr_balance + '</td>';
        // table += '</tr>';
        table += "</table>";
        return table;
    }
    $('#dataTableBuilder tbody').on('click', 'td.details-control ', function() {
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
    $('#payment_form').validate({
    })
    jQuery.extend(jQuery.validator.messages, {
        step: "Not more than 2 decimals are accepted.",
    })
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
li.breadcrumb-item.active{
    color: #2b1871!important;
    }
li.breadcrumb-item a{
    color: #6B778C;
    }
    .select2-selection__rendered {
      display: inline !important;
    }
    .select2-search--inline {
      float: none !important;
    }
</style>

<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@endsection
