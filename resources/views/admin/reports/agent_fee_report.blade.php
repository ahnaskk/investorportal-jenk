@extends('layouts.admin.admin_lte')
@section('content')
<?php


$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
?>

<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Agent Fee Report</div>     
  </a>
</div>
 <!-- {{ Breadcrumbs::render('admin::reports::investor') }} -->
<div class="col-md-12">

  <div class="box">
    <div class="box-body">    
          <div class="row">


                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                     {{ Form::text('from_date1', $date_start, ['class' => 'form-control datepicker','id'=>'from_date1', 'placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete' => 'off']) }}
                     <input type="hidden" name="from_date" class="date_parse" value="{{ $date_start }}" id="from_date">

                            </div>
                            <span class="help-block">From Date </span>
                        </div>  

                          <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::text('to_date1',$date_end,['class'=>'form-control datepicker','id'=>'to_date1', 'placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete' => 'off'])}}
                                  <input type="hidden" name="to_date" class="date_parse" value="{{ $date_end }}" id="to_date">

                            </div>
                            <span class="help-block">To Date </span>
                        </div>
                        </div>  
<div class="row">



                    <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>
                                {{Form::select('merchant_id[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchant_id','multiple'=>'multiple'])}} 
                            </div>
                            <span class="help-block">Merchants</span> 
                    </div>
                    <div class="col-md-4">
                         <div class="btn-wrap btn-right ">
                            <div class="btn-box">
                                <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
                               

                            </div>
                            
                        </div>                               
                    </div> 
   </div>
 </div>
<input type="hidden" name="row_merchant" id="row_merchant" value="">
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
    <script src="{{ asset('/js/moment.min.js') }}"></script>
    <script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
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
    <style type="text/css">
        li.breadcrumb-item.active{
          color: #2b1871!important;
        }
        li.breadcrumb-item a{
          color: #6B778C;
        }
    </style>
    <link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/optimized/report_custom.css?ver=5') }}" rel="stylesheet" type="text/css" /> 
    <link href="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.css') }}" rel="stylesheet" type="text/css" />  
    @stop
