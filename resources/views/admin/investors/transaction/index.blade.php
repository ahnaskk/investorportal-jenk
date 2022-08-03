<?php use App\InvestorTransaction; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<?php
$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
?>
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>View Transactions </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">View Transactions</div>
    </a>
</div>
{{ Breadcrumbs::render('transactions',$this_investor) }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-4">
                    <ul class="list-group">
                        <li class="list-group-item"><b>Investor Name</b></li>
                        <li class="list-group-item">{{$this_investor->name}}</li>
                    </ul>
                </div>
                <!-- <div class="col-md-3">
                    <ul class="list-group">
                        <li class="list-group-item"><b>ROI Rate</b></li>
                        <li class="list-group-item">{{FFM::percent($this_investor->interest_rate)}}</li>
                    </ul>
                </div> -->
                <div class="col-md-4">
                    <ul class="list-group">
                        <li class="list-group-item"><b>Number Of investments</b></li>
                        <li class="list-group-item">{{$invest_count}}</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <ul class="list-group">
                        <li class="list-group-item"><b>Liquidity</b></li>
                        <li class="list-group-item">{{FFM::dollar($liquidity)}}</li>
                    </ul>
                </div>
            </div>
            <div class="dashboard-filter trans-filter">
                <div class="form-group filter-group-wrap border-lf clearfix">
                    <div class="filter-group-wrap">
                        {{Form::open(['url'=>route('admin::investors::transaction::export' , ['id' => $investorId])])}}
                        <div class="filter-group ">
                            <div class="row">
                                <!-- <div class="col-md-3">
                                    <div class="form-group">
                                        <div class="input-group check-box-wrap">
                                            <div class="input-group-text">
                                                <label class="chc">
                                                    <input id="date_type" name="date_type" type="checkbox"
                                                        value="true" />
                                                    <span class="checkmark chek-m"></span>
                                                    <span class="chc-value">Check this</span>
                                                </label>
                                            </div>
                                            <span class="help-block">Maturity Date Filter (Investment Date by Default)
                                            </span>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                        </div>
                                        <input class="form-control datepicker" id="date_start1" name="date_start1"
                                            placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"
                                            autocomplete="off" value="{{$date_start}}" />
                                        <input type="hidden" class="date_parse" name="date_start" id="date_start"
                                            value="{{$date_start}}">
                                    </div>
                                    <span class="help-block">From Date</span>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                        </div>
                                        <input class="form-control datepicker" id="date_end1" name="date_end1"
                                            placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"
                                            autocomplete="off" value="{{$date_end}}" />
                                        <input type="hidden" class="date_parse" name="date_end" id="date_end"
                                            value="{{$date_end}}">
                                    </div>
                                    <span class="help-block">To Date</span>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        {{Form::select('status',[''=>'Select Status']+InvestorTransaction::statusOptions(),InvestorTransaction::StatusCompleted,['class'=>'form-control','id'=>'status'])}}
                                    </div>
                                    <span class="help-block">Status</span>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                        </div>
                                        {{Form::select('transaction_type',['0'=>'Select Transaction Type','1' => 'Debit', '2' => 'Credit'],"",['class'=>'form-control','id'=>'transaction_type'])}}
                                    </div>
                                    <span class="help-block">Transaction Type</span>
                                </div>
                            </div>
                            <div class="row">
                                
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                        </div>
                                        {{Form::select('transaction_method',['0'=>'All']+InvestorTransaction::transactionMethodOptions(),"",['class'=>'form-control','id'=>'transaction_method'])}}
                                    </div>
                                    <span class="help-block">Transaction Method</span>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="fa fa-list-alt" aria-hidden="true"></span>
                                        </div>
                                        {{Form::select('categories',$categories,null,['class'=>'form-control js-categories-placeholder-multiple','id'=>'categories'])}}
                                    </div>
                                    <span class="help-block">Transaction Categories</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 btn-wrap btn-right">
                                <div class="btn-box">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                                        name="student_dob">
                                    @if(@Permissions::isAllow('Investors','Download'))
                                    {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                                    @endif
                                </div>
                                {{Form::close()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='top-btn-wrap btn-wrap btn-right  '>
                @if(@Permissions::isAllow('Investors','Edit'))
                <div class="btn-box">
                    <a href="{{route('admin::investors::transaction::create',$investorId)}}" class="btn btn-primary"
                        id="cy_create_transactions">Create Transactions</a>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-sm-12">
                    {!! $tableBuilder->table(['class' => 'table table-bordered '],true) !!}
                </div>
            </div>
        </div>



    </div>
</div>
</div>

@stop
@section('scripts')
<script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript">
</script>
<script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript">
</script>
<script src="{{asset('js/custom/autoFontSize.js')}}"></script>

{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
    var table = window.LaravelDataTables["dataTableBuilder"];
    $(document).ready(function () {
        $('#date_filter').click(function (e) {
            e.preventDefault();
            table.draw();
        });
        $(".js-categories-placeholder-multiple").select2({
            placeholder: "Select A Category"
        });
        new AutoFontSize('.auto-font-size')
        let startDt = $('#date_start').val() && new Date($('#date_start').val());
        if (startDt) {
            $('#date_end1').datepicker('setStartDate', startDt);
        }
        $('#date_start1').on('changeDate', function (selected) {
            let endDateSelected = $('#date_end').val() && new Date($('#date_end').val());
            if($('#date_start').val() && new Date($('#date_start').val())){
                let minDate = new Date(selected.date.valueOf());
            if (endDateSelected && endDateSelected < minDate) {
                $("#date_end1").datepicker('update', "");
            }
            $('#date_end1').datepicker('setStartDate', minDate);
            }else{
                $('#date_end1').datepicker('setStartDate', '');     
            }
        })
    });

    function confirmDelete() {
        var x = confirm("Are you sure that you want to delete?");
        if (x) {
            return true;
        } else {
            return false;
        }
    }
</script>
@stop
@section('styles')
<style type="text/css">
    .adminSelect .select2-hidden-accessible {
        display: none;
    }

    .breadcrumb {
        padding: 8px 15px;
        margin-bottom: 20px;
        list-style: none;
        background-color: #f5f5f5;
        border-radius: 4px;
    }

    .breadcrumb>li {
        display: inline-block;
    }

    li.breadcrumb-item a {
        color: #6B778C;
    }

    .breadcrumb>li+li::before {
        padding: 0 5px;
        color: #ccc;
        content: "/\00a0";
    }

    li.breadcrumb-item.active {
        color: #2b1871 !important;
    }
</style>
<link href="{{ asset('/css/optimized/investor_transactions.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop