@extends('layouts.admin.admin_lte')

@section('content')
       <div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Reconcile Report</h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Reconcile Report</div>     
      </a>
      
  </div>
<div class="col-md-12">
<div class="box">
      <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>


    <div class="box-body">


       
                            <div class="form-box-styled" >
                                {{Form::open(['route'=>'admin::investors::transactionreportdownload'])}}
                                <div class="row">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                            </div>
                                            <input class="form-control datepicker" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                                   type="text" autocomplete="off"/>
                                            <input type="hidden" name="date_start" id="date_start" class="date_parse">
                                        </div>
                                        <span class="help-block">From Date</span>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                            </div>
                                            <input class="form-control datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                                   type="text" autocomplete="off"/>
                                            <input type="hidden" name="date_end" id="date_end" class="date_parse">
                                        </div>
                                        <span class="help-block">To Date</span>
                                    </div>
                                            
                                    <div class="col-md-4 col-sm-6">
                                        <div class="input-group">
                                         
             {{Form::select('lenders[]',$lenders,null,['id'=>'lenders','multiple'=>'multiple','class'=>'js-lender-placeholder-multiple'])}}                              </div>
                                        <span class="help-block">Lender</span>
                                    </div>

                                    <div class="col-md-1 col-sm-6 rans-re">
                                        <div class="btn-box btn-left">
                                <!--<div class="pull-right" style="padding-bottom: 15px">

                                    {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}

                                </div>-->
                                <div class="input-group pull-right">
                                    <input type="button" value="Apply Filter" class="btn btn-primary" id="date_filter"
                                           name="student_dob">

                                </div>
                                
                            </div>
                                    </div>
                                </div>
                            </div>
                           
                     
                        
                        {{Form::close()}}
         
        <div class="form-group">
          
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

          
                    <div class="table-container" > 
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



{!! $tableBuilder->scripts() !!}

<script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function(){

     $(".js-lender-placeholder-multiple").select2({
            placeholder: "Select Lender(s)"
         });



        $('#date_filter').click(function (e) {
            e.preventDefault();
            table.draw();
        });

    });


</script>


@stop

@section('styles')

<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop
