@extends('layouts.admin.admin_lte')
@section('content') 
<?php $date = date('Y-m-d'); ?>
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Profitability Report</div>     
    </a>
</div>
<div class="col-md-12">
    <div class="box">
        <div class="box-body">
            <div class="form-box-styled">
                <div class="row" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'calicut78io/debug::generate-historical-data-submit','id'=>'historical-form'])}}
                        <div class="serch-bar">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group px-2">
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                        {{Form::checkbox('payment_date',null,null,['id'=>'payment_date'])}}
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check this</span>
                                    </label>
                                </div>
                                <span class="help-block">By Payment Date </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::text('date',$date,['class'=>'form-control datepicker','id'=>'date', 'placeholder' => \FFM::defaultDateFormat('format'), "autocomplete" => 'off'])}}
                            <input type="hidden" name="date" value="{{ $date }}" id="from_date" class="date_parse">
                        </div>
                        <span class="help-block">To Date </span>
                    </div>
                    <div class="col-md-1">
                        <div class="btn-box inhelpBlock ">
                            <input type="submit" value="Proceed" class="btn btn-primary" id="Proceed">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="btn-box inhelpBlock ">
                            <a href="{{ route('calicut78io/debug::CheckAll') }}" class="btn btn-success">Repair</a>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <p>* It will delete from current date to selected date</p>
                        <p>* below table datas will delete using the date </p>
                        <ul>
                            <li>Payment Investors </li>
                            <li>Participent Payment </li>
                            <li>Merchant User </li>
                            <li>Investor Transaction</li>
                        </ul>
                    </div>
                    {{Form::close()}}
                </div>
            </div>
        </div>
        <div class="box-head">
            @include('layouts.admin.partials.lte_alerts')
        </div>
    </div>
</div>
@stop
@section('scripts')  
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
@stop
@section('styles')
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
