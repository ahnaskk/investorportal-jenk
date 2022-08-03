@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Lender Report</h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Lender Report</div>     
  </a>

</div>
{{ Breadcrumbs::render('admin::reports::lender-report') }}
<div class="col-md-12">
 <div class="box">
  <div class="box-body">

    <div class="form-box-styled" >
      {{Form::open(['route'=>'admin::investors::lenderDelinquentreportdownload'])}}

      <div class="row">
        <div class="col-md-6 report_rate">
         <div class="input-group">
          <div class="input-group-text">
           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
         </div>

         {{Form::select('industry[]',$industries,'',['class'=>'form-control js-industry-placeholder-multiple','id'=>'industry','multiple'=>'multiple'])}}

       </div>
       <span class="help-block">Industry</span>           


     </div>

     <div class="col-md-6 report_rate">                  

      <div class="input-group">
        <div class="input-group-text">
         <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
       </div>

       {{Form::select('lenders[]',$lenders,'',['class'=>'form-control js-lender-placeholder-multiple','id'=>'lenders','multiple'=>'multiple'])}}

     </div>
     <span class="help-block">Lenders</span>           


   </div>
      </div>
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






</div>
<div class="row">
           <!--  <div class="col-md-4 report_rate">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::date('from_date',null,['class'=>'form-control','id'=>'from_date'])}}

                            </div>
                            <span class="help-block">From date </span>
                        </div>    

                        <div class="col-md-4 report_rate">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::date('to_date',null,['class'=>'form-control','id'=>'to_date'])}}

                            </div>
                            <span class="help-block">To date </span>
                          </div> -->
                          <div class="col-sm-12">
                            <div class="btn-wrap btn-right">                              
                              <div class="btn-box ">
                                <input type="button" value="Apply Filter" class="btn btn-success" id="apply"
                                name="student_dob">
                                @if(@Permissions::isAllow('Lender Delinquent','Download')) 
                                {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                                @endif
                              </div>
                            </div>
                          </div>
                        </div>


                      </div>



                      {{Form::close()}}



                      <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                        <div class="row">
                          <div class="col-sm-12 grid table-responsive">

                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                          </div>
                        </div>
                      </div>

                    </div>

                    <!-- /.box-body -->

                  </div>
                  @stop
                  @section('scripts')
                  {!! $tableBuilder->scripts() !!}
                  <script src="{{ asset('/js/custom/report.js') }}"></script>  
                  <script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>  
                  @stop

                  @section('styles')
                  <link href="{{ asset('/css/optimized/Lender_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
                  <link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
                  <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
                  @stop


