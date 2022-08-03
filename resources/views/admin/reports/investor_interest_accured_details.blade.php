@extends('layouts.admin.admin_lte')

@section('content')

         <?php
                 $date_end = date('Y-m-d');
                 $date_start = date('Y-m-d', strtotime('-1 months', strtotime($date_end)));
         ?>



<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Investor Accrued ROI Details</div>     
      </a>
      
  </div>
    <div class="col-md-12">

    <div class="box">
        <div class="box-body">


           <input type="hidden" value="{{ isset($input)? $input['id'] : ''}}" name="inv_id" id="inv_id">
           <input type="hidden" value="{{ isset($input['sdate'])? $input['sdate'] : ''}}" name="start_date" id="start_date">
           <input type="hidden" value="{{ isset($input['sdate']) ? $input['edate'] : ''}}" name="end_date" id="end_date">
                    
              
    
       


                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="grid table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true) !!}
                        
                        <div class="blockCust pull-right">

                            <!--{{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}-->

                        </div>
                    
                
                </div>
            </div>

               </div>
        </div>
     
@stop

@section('scripts')     
{!! $tableBuilder->scripts() !!}
<script src="{{ asset('/js/custom/report.js') }}"></script> 
@stop
@section('styles')
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
