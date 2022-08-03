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
        <div class="tool-tip">Investor Accrued Pref Return </div>     
      </a>
      
  </div>
    {{ Breadcrumbs::render('admin::reports::investor_interest_accured_report') }}
    <div class="col-md-12">

    <div class="box">
        <div class="box-body">


            <div class="form-group">
                <div class="filter-group-wrap" >
                    <div class="filter-group" >
                        {{Form::open(['route'=>'admin::reports::investor-export','id'=>'investor-form'])}}

                    </div>


                    <div class="form-box-styled">
                      <div class="row">

                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                        {{Form::select('investors[]',[],1,['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors </span>
                        </div>
              

                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input class="form-control datepicker startdate1" autocomplete="off" id="date_start1" value="{{ $date_start }}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text"/>
                                <input type="hidden" name="date_start" id="date_start" value="{{ $date_start }}" class="date_parse">
                            </div>
                            <span class="help-block">From Date</span>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control datepicker enddate1" autocomplete="off" value="{{ $date_end }}" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text"/>
                                <input type="hidden" name="date_end" id="date_end" value="{{ $date_end }}" class="date_parse">
                            </div>
                            <span class="help-block">To Date</span>
                        </div>

                        <div class="col-md-12">
                          <div class="btn-wrap btn-right">
                            <div class="btn-box inhelpBlock ">
                                    <input type="button" value="Apply Filter" class="btn btn-primary" id="apply"
                                           name="Apply Button">

                                <div class="blockCust pull-right">

                                    <!--{{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}-->

                                </div>
                             </div>
                       </div>

                        </div>
      
                      
                      </div>
                      </div>
                      
                        
                        
                        

                      </div>
                </div>
                    
                {{Form::close()}}
    
       


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
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
<script src="{{ asset('/js/moment.min.js') }}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
{!! $tableBuilder->scripts() !!}
<script src="{{ asset('/js/custom/report.js') }}"></script> 
<script>
    $(document).ready(function(){
        var futuredate = new Date();
        let startDt = $('#date_start').val() && new Date($('#date_start').val());
        if(startDt){
            $('#date_end1').datepicker('setStartDate', startDt);
        }
        $('.enddate1, .startdate1').datepicker('setEndDate', futuredate);
        $('#date_start1').on('changeDate', function(selected){
            let endDateSelected = $('#date_end').val() && new Date($('#date_end').val());
            if($('#date_start').val() && new Date($('#date_start').val())){
            let minDate = new Date(selected.date.valueOf());          
            if(endDateSelected && endDateSelected < minDate){
            $("#date_end1").datepicker('update', "");
            }
            $('#date_end1').datepicker('setStartDate', minDate);
            }else{
            $('#date_end1').datepicker('setStartDate', '');    
            }
        })
    });
</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
