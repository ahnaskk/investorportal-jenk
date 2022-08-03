@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Equity Investor</div>     
      </a>
      
  </div>

{{ Breadcrumbs::render('admin::reports::equity-investor-report') }}
   <div class="col-md-12">
    <div class="box">
        <div class="box-body">



          <div class="form-box-styled" >
                        {{Form::open(['id'=>'investor-form'])}}



            <div class="row">
                <div class="col-sm-12">
  
                 <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>

                                  {{Form::select('investors[]',[],1,['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors </span>
                        </div>
                 <div class="col-md-6">
                  <div class="invest-ment">
                            <div class="btn-box btn-left ">
                                    <input type="button" value="Apply Filter" class="btn btn-success profit-bt" id="apply"
                                           name="Apply Button">

                                <div class="blockCust pull-right" style="padding-bottom: 15px">

                                </div>
                             </div>
                       </div>
                        </div>
                    
                      </div>
                      </div>
                      

       
                    
                {{Form::close()}}
            </div>


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12 grid table-responsive">

                   
                            {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true); $tableBuilder->parameters(['stateSave'=>true]) !!}
                        
                        <div class="blockCust pull-right" style="padding-bottom: 15px">

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
 <script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
 <script src="{{ asset('/js/custom/report.js') }}"></script>

           {{--<script>
               $.ajax({
                       url: "{{url('admin/reports/equityInvestorReport-update')}}",
                   }).done(function() {
                   var table = window.LaravelDataTables["dataTableBuilder"];
                   table.ajax.reload();
                   });
           </script>--}}
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
