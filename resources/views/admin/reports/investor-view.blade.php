@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Investment Report</div>
  </a>
</div>
<div class="col-md-12">
  <div class="box">
    <div class="box-body">
      <div class="form-group">
        <div class="filter-group-wrap" >
          <div class="filter-group" >
            {{Form::open(['route'=>'admin::reports::investor-export','id'=>'investor-form'])}}
            <div class="serch-bar">
              <div  class="row">
                <div class="merchant-ass">
                  <div class="col-md-4 check-click checktime1" >
                    <div class="form-group">
                      <div class="input-group check-box-wrap">
                        <div class="input-group-text">
                          <label class="chc">
                            <input  id="date_type" name="date_type" type="checkbox" value="true"/> <span class="checkmark chek-mm"></span>
                            <span class="checkmark chek-m"></span>
                            <span class="chc-value">Check this</span>
                          </label>
                        </div>
                      </div>
                      <span class="help-block">Filter Based On Merchant Added Date (Funded Date by Default)</span>
                    </div>
                  </div>
                  <div class="date-star" id="test" style="display:block">
                    <div class="col-md-4" style="height: 86px; margin-bottom: -2px;">
                      <div class="input-group">
                        <div class="input-group-text">
                          <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                        </div>
                        <input class="form-control from_date1 datepicker" id="date_start1" name="start_date1" value="{{date('Y-m-d', strtotime('-1 days'))}}"  placeholder="{{\FFM::defaultDateFormat('format')}}" autocomplete="off" type="text"/>
                        <input type="hidden" class="date_parse" name="start_date" id="date_start" value="{{date('Y-m-d', strtotime('-1 days'))}}">
                        <span id="invalid-date_start"/>
                      </div>
                      <span class="help-block">From Date (Assigned Date)</span>
                    </div>
                    <div class="col-md-4">
                      <div class="input-group">
                        <div class="input-group-text">
                          <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                        </div>
                        <input class="form-control to_date1 datepicker" id="date_end1" value="{{date('Y-m-d')}}" name="end_date1" placeholder="{{\FFM::defaultDateFormat('format')}}" autocomplete="off" type="text"/>
                        <input type="hidden" class="date_parse" name="end_date" id="date_end" value="{{date('Y-m-d')}}">
                      </div>
                      <span class="help-block">To Date</span>
                    </div>
                  </div>
                  <div id="time_filter" class="check-time" style="display:none;">
                    <div class="col-sm-12">
                      <div class="row">
                        <div class="col-md-3 serch-timeer-one">
                          <div class="input-group serch-two">
                            <div class="input-group-text">
                              <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            <input class="form-control from_date2 datepicker" id="date_start11" value="{{date('Y-m-d', strtotime('-1 days'))}}" autocomplete="off" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                            <input type="hidden" class="date_parse" name="date_start" id="date_start1" value="{{date('Y-m-d', strtotime('-1 days'))}}">
                          </div>
                          <span class="help-block">From Date</span>
                        </div>
                        <div class="col-md-3 serch-timeer">
                          <div class="input-group clockpicker">
                            <input type="text" class="form-control" value="00:00" id="time_start" name="time_start">
                            <span class="input-group-text">
                              <span class="glyphicon glyphicon-time"></span>
                            </span>
                          </div>
                          <span class="help-block">From Time</span>
                        </div>
                        <div class="col-md-3 serch-timeer-one">
                          <div class="input-group serch-two">
                            <div class="input-group-text">
                              <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            <input class="form-control to_date2 datepicker" id="date_end11" value="{{date('Y-m-d')}}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"/>
                            <input type="hidden" name="date_end" id="date_end1" class="date_parse" value="{{date('Y-m-d')}}">
                          </div>
                          <span class="help-block">To Date</span>
                        </div>
                        <div class="col-md-3 serch-timeer">
                          <div class="input-group">
                            <div class="input-group clockpicker">
                              <input type="text" class="form-control" value="00:00" id="time_end" name="time_end">
                              <span class="input-group-text">
                                <span class="glyphicon glyphicon-time"></span>
                              </span>
                            </div>
                            <span class="help-block">To Time</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4" style="margin-bottom: -2px;">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                  </div>
                  {{Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}
                </div>
                <span class="help-block">Merchants</span>
              </div>
              <div class="col-md-4" style="margin-bottom: -2px;">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                  </div>
                  {{Form::select('investors[]',$investors,$selected_investor,['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}
                </div>
                <span class="help-block">Investors </span>
              </div>
              <div class="col-md-4" style="margin-bottom: -2px;">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                  </div>
                  {{Form::select('lenders[]',$lenders,null,['class'=>'form-control js-lender-placeholder-multiple','id'=>'lenders','multiple'=>'multiple'])}}
                </div>
                <span class="help-block">Lenders </span>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="input-group">
                  <div class="input-group-text">
                    <i class="fa fa-building" aria-hidden="true"></i>
                  </div>
                  {{Form::select('industries[]',$industries,null,['class'=>'form-control js-industry-placeholder-multiple','id'=>'industries','multiple'=>'multiple'])}}
                </div>
                <span class="help-block">Industries </span>
              </div>
              <div class="col-md-4" style="margin-bottom: -2px;">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="fa fa-building" aria-hidden="true"></span>
                  </div>
                  <select class="form-control js-status-placeholder-multiple" multiple="multiple" name="statuses[]" id="statuses" onchange="filter_change()">
                    <option value="0">All</option>
                    @foreach($sub_statuses as $sub_status)
                    <option  value='{{$sub_status->id}}'>{{$sub_status->name}} </option>
                    @endforeach
                  </select>
                </div>
                <span class="help-block">Status </span>
              </div>
              <div class="col-md-4 report-input">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="fa fa-industry" aria-hidden="true"></span>
                  </div>
                  {!! Form::select('advance_type[]',['daily_ach'=>'Daily ACH','weekly_ach'=>'Weekly ACH','credit_card_split'=>'Credit Card Split','variable_ach'=>'Variable ACH','lock_box'=>'Lock Box','hybrid'=>'Hybrid'],isset($merchant)? $merchant->advance_type : old('advance_type'),['id'=>'advance_type','class'=>'form-control js-advtype-placeholder-multiple', 'multiple'=>'multiple']) !!}
                </div>
                <span class="help-block">Advance Type </span>
              </div>
              <div class="col-md-4 check-click checktime1" >
                <div class="form-group">
                  <div class="input-group check-box-wrap">
                    <div class="input-group-text">
                      <label class="chc">
                        <input  id="export_checkbox" name="export_checkbox" type="checkbox" value="true" checked="checked" /> <span class="checkmark chek-mm"></span>
                        <span class="checkmark chek-m"></span>
                        <span class="chc-value">Check this</span>
                      </label>
                    </div>
                  </div>
                  <span class="help-block">Download Without Details</span>
                </div>
              </div>
              @if(!Auth::user()->hasRole(['company']))
              <div class="col-md-4" style="margin-bottom: -2px;">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                  </div>
                  {{Form::select('owner',$companies,'',['class'=>'form-control js-company-placeholder','id'=>'owner','placeholder'=>'Select Company'])}}
                </div>
                <span class="help-block">Owner</span>
              </div>
              @endif
              <div class="col-md-4 report-input">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="fa fa-industry" aria-hidden="true"></span>
                  </div>
                  {!! Form::select('investor_type[]',$investor_types,isset($investor)? $investor->investor_type: old('investor_type'),['class'=>'form-control js-investor-type-placeholder-multiple','id'=> 'investor_type', 'multiple'=>'multiple']) !!}
                </div>
                <span class="help-block">Investor Type </span>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 report-input">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="fa fa-industry" aria-hidden="true"></span>
                  </div>
                  {!! Form::select('sub_status_flag[]', $substatus_flags, isset($merchant)? $merchant->sub_status_flag : old('sub_status_flag'),['class'=>'form-control js-substatus-flag-placeholder-multiple','id'=>'sub_status_flag','multiple'=>'multiple']) !!}
                </div>
                <span class="help-block">Sub status Flag</span>
              </div>
              <div class="col-md-4 report-input">
                <div class="input-group">
                  <div class="input-group-text">
                  </div>
                  {!! Form::select('label',$label,'',['placeholder'=>'Select Label','class'=>'form-control js-label-placeholder','id'=>'label']) !!}
                </div>
                <span class="help-block">Label</span>
              </div>
            </div>
            <input type="hidden" name="row_merchant" id="row_merchant" value="">
            <div class="btn-wrap btn-right">
              <div class="btn-box inhelpBlock ">
                <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
                <div class="blockCust pull-right">
                  @if(@Permissions::isAllow('Investment Report','Download'))
                  {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      {{Form::close()}}
      <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
        <div class=" grid table-responsive">
          {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true) !!}
          <div class="blockCust pull-right" style="padding-bottom: 15px">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop
@section('scripts')
{!! $tableBuilder->scripts() !!}
<script src="{{ asset('/js/custom/report.js') }}"></script>
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
<script src="{{ asset('/js/custom/investment.js') }}"></script>
<script src="{{ asset('/js/custom/common.js?v=17.02') }}"></script>
<script src="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.min.js') }}"></script>
<script type="text/javascript">
$('.clockpicker').clockpicker({ donetext: 'Done'});
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/report_custom.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.css') }}" rel="stylesheet" type="text/css" />
@stop
