@extends('layouts.admin.admin_lte')

@section('content')


<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Total Portfolio Earnings</div>     
      </a>
      
  </div>

{{ Breadcrumbs::render('admin::reports::dept-investor-report') }}

    <div class="col-md-12">
      <div class="box">
        <div class="box-body">


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">


                    <div class="grid table-responsive">

                   
                            {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true) !!}
                        
                        <div class="blockCust pull-right" >

                            <!--{{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}-->

                        </div>
                    
                    </div>

            </div>
        </div>
      </div>
  </div>
        <!-- /.box-body -->
   
@stop

@section('scripts')      
{!! $tableBuilder->scripts() !!}
 <script src="{{ asset('/js/custom/report.js') }}"></script> 
@stop

@section('styles')
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<style type="text/css">
  
.dataTables_filter {
display: none;
} 

</style>
@stop
