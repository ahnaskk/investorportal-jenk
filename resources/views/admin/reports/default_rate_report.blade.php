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
        <div class="tool-tip">Default Rate Report</div>     
      </a>
  </div>
  {{ Breadcrumbs::render('admin::reports::default-rate-report') }}
  <div class="col-md-12">
<div class="box">
    <div class="box-body">

              <div class="filter-group-wrap" >
                            <div class="form-box-styled" > 
               {{Form::open(['route'=>'admin::investors::defaultreportdownload'])}}

                  <div class="row">
                     <div class="col-md-6 report_rate">
                     <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                             {{Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Merchants</span>
                        </div>


                  <div class="col-md-6 report_rate">
                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                             {{Form::select('sub_status[]',$sub_statuses,[4,22,18,19,20],['class'=>'form-control js-status-placeholder-multiple','id'=>'sub_status','multiple'=>'multiple'])}}


                            </div>
                            <span class="help-block">Status</span>           

                </div>
              </div>
              <div class="row">
                @if(!Auth::user()->hasRole(['company']))
               <div class="col-md-6 report_rate">


                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                        {{Form::select('velocity',$companies,'',['class'=>'form-control js-company-placeholder','id'=>'velocity','placeholder'=>'Select Company'])}}

                            </div>
                            <span class="help-block">Company</span>           


                </div>  
                <div class="col-md-6">
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
                @endif             

                

</div>


                  <div class="row">

                    <div class="col-md-6 report_rate">
 <div class="input-group">
  <div class="input-group-text">
    <span class="fa fa-industry" aria-hidden="true"></span>
  </div>
  {!! Form::select('investor_type[]',$investor_types,'',['class'=>'form-control js-investor-type-placeholder-multiple','id'=> 'investor_type', 'multiple'=>'multiple']) !!}


</div>
<span class="help-block">Investor Type </span>
</div> 
            <div class="col-md-6 report_rate">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::select('investors[]',[],1,['class'=>'form-control js-investor-placeholder-multiple ','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                    <span class="help-block">Investors </span>
                        </div>    

                      </div>

                        <div class="row">

                          <div class="col-md-6 report_rate">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                     {{ Form::text('from_date1', $date_start, ['class' => 'form-control datepicker','id'=>'from_date1', 'placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete' => 'off']) }}
                     <input type="hidden" name="from_date" class="date_parse" value="{{ $date_start }}" id="from_date">

                            </div>
                            <span class="help-block">From Date </span>
                        </div>  

                          <div class="col-md-6 report_rate">
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
<div class="col-md-6 report_rate">

                 <?PHP
                 //$lenders=[''=>'Select',1=>'velocity1',2=>'velocity2'];
                 ?>
                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                             {{Form::select('lenders[]',$lenders,'',['class'=>'form-control js-lender-placeholder-multiple','id'=>'lenders','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Lenders</span> 
                  </div>
                        <div class="col-md-6 report_rate">
                            <div class="form-group">
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                        {{Form::checkbox('funded_date',null,null,['id'=>'funded_date'])}}
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check this</span>
                                    </label>

                                </div>
                                <span class="help-block">Filter with Funding Date </span>
                             </div>
                        </div>

                        </div>

                            <div class="form-group  col-md-6">

                      <label for="exampleInputEmail1">Disable/Enable Investors</label>

                      {{ Form::radio('active_status','', true,['class' => 'active_status' , 'id' => 'label_all']) }}
                        <label class="inline" for="label_all">All</label>  

                     {{ Form::radio('active_status','1', false,['class' => 'active_status' , 'id' => 'label_enable']) }}
                        <label class="inline" for="label_enable">Enable</label>

                     {{ Form::radio('active_status','2', false,['class' => 'active_status' , 'id' => 'label_disable']) }}
                        <label class="inline" for="label_disable">Disable</label>


                     <!--  <input type="checkbox" checked="checked" data-toggle="toggle" name="active_status" id="active_status" value="1" data-onstyle="success"> -->

                      </div>

                       <div class="col-md-6">


                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

                   {{Form::select('overpayment',['0'=>'All','1' => 'Excluded', '2' => 'Included'],"",['class'=>'form-control','id'=>'overpayment','placeholder'=>'Select Status'])}}

                            </div>
                            <span class="help-block">Overpayment</span>           


                </div> 
 <div class="row g-0">
  <div class="col-md-12">
                   <div class="col-md-3">


                  <div class="input-group">
                      <div class="input-group-text">
                           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                          </div>

         {{Form::select('days',[0 =>'0-60',61=>'61-90',91=>'91-120',121=>'121-150',150=>'150+'],"",['class'=>'form-control','id'=>'days','placeholder'=>'Select Days'])}}

                            </div>
                            <span class="help-block">Days</span>           


                </div> 
              </div>
              </div>


                                        <div class="col-md-6  btn-wrap btn-right">
                                <!--<div class="pull-right" style="padding-bottom: 15px">

                                    {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}

                                </div>-->
                                <div class="btn-box">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="apply"
                                           name="student_dob">


                                      @if(@Permissions::isAllow('Default Rate Report','Download')) 
                                      {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                                      @endif

                                      </div>
                            </div>
                                    </div>


                        </div>

                        {{Form::close()}}
                    </div>

        <div class="form-group">
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

            <div class="row">
                <div class="col-sm-12">

                    <div class="table-container" > 
                        {!! 
                          $tableBuilder->table(['class' => 'table table-bordered'], true);
                          $tableBuilder->parameters(['stateSave' => true])
                        !!}
                    </div>

                </div>
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
{!! $tableBuilder->scripts() !!}
 <script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
 <script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
 <script src="{{ asset('/js/custom/report.js') }}"></script>
 
@stop

@section('styles')
  <style type="text/css">
   .select2-selection__rendered {
      display: block !important;
    }
    .select2-search--inline {
      float: none !important;
    }
</style>
 <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
 <link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
