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
        <div class="tool-tip">Investor Assignment</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('admin::reports::get-investor-assign-report') }}
    <div class="col-md-12">

 <div class="box">
        <div class="box-body">


                    <div class="form-box-styled">
                        {{Form::open(['route'=>'admin::reports::investor-assignment-export'])}}

                       <div class="row">
                        <div class="col-lg-3">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input autocomplete="off" class="form-control datepicker" id="date_start1" name="start_date1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" value="{{$date_start}}"/>
                                <input type="hidden" name="start_date" id="date_start" value="{{$date_start}}" class="date_parse">
                            </div>
                            <span class="help-block">From Date (Assigned Date)</span>
                        </div>
                        <div class="col-lg-3">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input autocomplete="off" class="form-control datepicker" id="date_end1" name="end_date1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" value="{{$date_end}}"/>
                                <input type="hidden" name="end_date" id="date_end" value="{{$date_end}}" class="date_parse">
                            </div>
                            <span class="help-block">To Date</span>
                        </div>

                        <div class="col-lg-3">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                      </div>
                                      
                                     {{Form::select('merchants[]',[],"",['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}
                                </div>
                            <span class="help-block">Merchants</span>
                        </div>

                        <div class=" col-lg-3">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::select('investors[]',[],'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors </span>
                        </div>


                       </div>
                       <div class="row">
                        <div class="col-md-12">
                            <div class="btn-wrap btn-right">
                                <div class="btn-box">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
                             <div class="blockCust pull-right">
                                     @if(@Permissions::isAllow('Investor Assignment Report','Download')) 
                                    {{Form::submit('Download report',['class'=>'btn btn-success','id'=>'form_filter'])}}
                                    @endif
                                </div>
                                </div>
                            </div>
                        </div>
                       



                    
                    {{Form::close()}}
            </div>


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="grid table-responsive">
                    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                    <!--  <div class="blockCust pull-right" style="padding-bottom: 15px">
                        {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                    </div>-->
                </div>
            </div>

        </div>
        <!-- /.box-body -->
    </div>
</div>

@stop

@section('scripts')
<script src="{{ asset('/js/moment.min.js') }}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
{!! $tableBuilder->scripts() !!}
  <script src="{{ asset('/js/custom/report.js') }}"></script>    
  <script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script> 
  <script src="{{ asset('/js/custom/investorSelect2.js') }}"></script>
  
@stop

@section('styles')
<link href="{{ asset('/css/optimized/Investor_Assignment_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
<style type="text/css">  
.dataTables_filter {
display: none;
} 
  
</style>

@stop
