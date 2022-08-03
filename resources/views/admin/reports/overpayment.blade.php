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
        <div class="tool-tip">Overpayment Report</div>     
      </a>
      
  </div>
{{ Breadcrumbs::render('admin::reports::overpayment-report') }}
    <div class="col-md-12">

<div class="box">
  <div class="box-body">
        <div class="form-box-styled" >
          {{Form::open(['route'=>'admin::reports::payment-export','id'=>'payment-form'])}}
<input type="hidden" name="row_merchant" id="row_merchant" value="">
            <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                    </div>
                                    <input class="form-control datepicker" autocomplete="off" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                           type="text"/>
                                    <input type="hidden" name="date_start" id="date_start" class="date_parse">
                                </div>
                                <span class="help-block">From Date</span>
                            </div>
                             <div class="col-md-4 col-sm-6">
                             <div class="input-group">
                              <div class="input-group-text">
                               <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                               </div>
                                <input class="form-control datepicker" autocomplete="off"  id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                <input type="hidden" name="date_end" id="date_end" class="date_parse">
                              </div>
                               <span class="help-block">To Date</span>
                            </div>

 <div class="col-md-4 report-input">
 <div class="input-group">
 <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
    </div>
    
    {{Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}
        </div>







                      <span class="help-block">Merchants</span>
                    </div>

                       <div class="col-md-4 report-input">
                      <div class="input-group">
                        <div class="input-group-text">
                          <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                        </div>

                        {{Form::select('investors[]',[],'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                        </div>
                      <span class="help-block">Investors</span>
                    </div> 

                    <div class="col-md-4 report-input">
                      <div class="input-group">
                        <div class="input-group-text">
                          <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                        </div>

                    {{Form::select('company',$companies,'',['class'=>'form-control js-company-placeholder','id'=>'company','placeholder'=>'Select Company'])}}

                        </div>
                      <span class="help-block">Company</span>
                    </div> 
                    <div class="col-md-4">
                  <div class="input-group check-box-wrap">

                      <div class="input-group-text">
                          <label class="chc">
                              <input type="checkbox" name="velocity_owned" value="1" id="velocity_owned"/>
                              <span class="checkmark chek-m"></span>
                              <span class="chc-value">Click Here</span>
                          </label>
                      </div>
                      <span class="help-block">Velocity Owned </span>
                  </div>
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
                    




               <div class="col-md-6 report_rate px-3">
                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                             {{Form::select('sub_statuses[]',$sub_statuses,[],['class'=>'form-control js-status-placeholder-multiple','id'=>'sub_statuses','multiple'=>'multiple'])}}


                            </div>
                            <span class="help-block">Status</span>           

                </div>              

             
               <div class="row px-3">
                <div class="col-md-12 col-sm-12">
                  <div class="input-group">
                    <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
              

                  </div>

                </div>

               </div>


              {{Form::close()}}
            </div>


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

              <div class="row">
                <div class="col-sm-12">

                  <div class="table-container grid table-responsive" > 
                 {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                  </div>

                </div>
              </div>

          <!-- /.box-body -->
        </div>
      </div>
    </div>

@stop


@section('scripts')
<script src="{{ asset('/js/moment.min.js') }}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
{!! $tableBuilder->scripts() !!}
 <script src="{{ asset('/js/custom/report.js') }}"></script> 
 <script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
  
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
<link href="{{ asset('/css/optimized/Payment_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
