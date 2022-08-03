@extends('layouts.admin.admin_lte')

@section('content')


<div class="inner admin-dsh header-tp">

  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Liquidity Report </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Liquidity Report</div>     
  </a> 
</div>
 {{ Breadcrumbs::render('admin::reports::liquidity-report') }}

<div class="col-md-12">
 <div class="box">
  <div class="box-body">


    <div class="form-box-styled" >
      {{Form::open(['route'=>'admin::reports::investor-assignment-export','id'=>'liquidityFilter'])}}

      <div class="row">
        <div class="col-lg-3">
          <div class="input-group">
            <div class="input-group-text">
              <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
            </div>
            <input class="form-control datepicker" autocomplete="off" id="date_start1" value="" name="start_date1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
            <input type="hidden" name="start_date" id="date_start" class="date_parse">
          </div>

          <span id="invalid-date_start" />
          <span class="help-block">From Date </span>

        </div>
        <div class="col-lg-3">
          <div class="input-group">
            <div class="input-group-text">
              <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
            </div>
            <input class="form-control datepicker" autocomplete="off" id="date_end1" name="end_date1" value="" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
            <input type="hidden" name="end_date" id="date_end" class="date_parse">
          </div>
          <span id="invalid-date_end" />
          <span class="help-block">To Date</span>
        </div>

        @if(!Auth::user()->hasRole(['company']))
        <div class="col-md-3">


          <div class="input-group">
            <div class="input-group-text">
             <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
           </div>

           {{Form::select('company',$companies,'',['class'=>'form-control js-company-placeholder','id'=>'company','placeholder'=>'Select Company'])}}

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


      <!--  <div class="col-md-3">


        <div class="input-group">
          <div class="input-group-text">
           <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
         </div>

         {{Form::select('liquidity',[''=>'All','1' => 'Excluded', '0' => 'Included'],"",['class'=>'form-control','id'=>'liquidity'])}}



       </div>
       <span class="help-block">Liquidity</span>           


     </div>  -->




     <div class="form-group  col-md-3">

      <label for="exampleInputEmail1">Enable/Disable Investors</label>

      {{ Form::radio('active_status','', true,['id'=>'all','class' => 'active_status']) }}
      <label for="all" class="inline">All</label>  

      {{ Form::radio('active_status','1', false,['id'=>'enable','class' => 'active_status']) }}
      <label for="enable" class="inline">Enable</label>

      {{ Form::radio('active_status','2', false,['id'=>'disable','class' => 'active_status']) }}
      <label for="disable" class="inline">Disable</label>


    </div>





    <div class="col-lg-3">
      <div class="btn-box inhelpBlock">
       <input type="button" value="Apply Filter" class="btn btn-success" id="apply" name="Apply Button">
       


     </div>
   </div>
 </div>


</div>


{{Form::close()}}



<div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
  <div class="grid table-responsive">
    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
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
@stop

@section('styles')
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('/css/optimized/Liquidity_Report.css?ver=5') }}" rel="stylesheet" type="text/css" /> 
<link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
<style>
  .inline{
    display: inline;
    margin-right:4px;
  }
  .form-box-styled .form-group {
    padding-left: 15px;
  }
</style>
@stop
