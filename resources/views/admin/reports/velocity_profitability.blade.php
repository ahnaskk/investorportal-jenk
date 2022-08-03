@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{ $page_title }}</div>     
      </a>
      
</div>
{{ Breadcrumbs::render('admin::reports::velocity-profitability') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <div class="box-body"> 
            <div class="form-box-styled" >
                <div class="serch-bar">
                    <form method="POST" action="{{ route('admin::reports::velocity-profitability.download') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                    </div>
                                    <input class="form-control from_date1 datepicker" autocomplete="off" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                    <input type="hidden" name="date_start" id="date_start" class="date_parse">
                                </div>
                                <span class="help-block">From Date</span>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                    </div>
                                    <input class="form-control to_date1 datepicker" autocomplete="off" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                    <input type="hidden" name="date_end" id="date_end" class="date_parse">
                                </div>
                                <span class="help-block">To Date</span>
                            </div>
@if(!Auth::user()->hasRole(['company']))
<div class="col-md-4">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
    </div>
    {{Form::select('company',$companies,'',['class'=>'form-control js-company-placeholder','id'=>'company','placeholder'=>'Select Company'])}}

   </div>
  <span class="help-block">Company </span>
</div>
@endif
<!-- <div class="col-md-4">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
    </div>

    {{ Form::select('lenders[]',$lenders,null,['class'=>'form-control js-lender-placeholder-multiple','id'=>'lenders','multiple'=>'multiple']) }}

  </div>
  <span class="help-block">Lenders</span>
</div> -->
<div class="col-md-4">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
    </div>

  {{Form::select('investors[]',[],'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

  </div>
  <span class="help-block">Investors</span>
</div> 

<div class="col-md-4">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon" aria-hidden="true"></span>
    </div>

{!! Form::select('label[]',$label,'',['class'=>'form-control js-label-placeholder-multiple','id'=>'label','multiple'=>'multiple']) !!} 
   

  </div>
  <span class="help-block">Label</span>
</div> 
    <div class="col-md-4">
                                <div class="btn-box " style="margin-bottom: 25px;">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter">
                                    <input type="submit" value="Download" class="btn btn-primary">
                                </div> 
                            </div>
                        </div>                             
                    </form>
                </div>
            </div>
                    
           
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
            <div class="loadering" style="display:none;">
                    <div class="loader"></div><br>
                </div>
                <div class="row">
                    <div class="col-sm-12 grid table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true);$tableBuilder->parameters(['stateSave'=>true]) !!}
                    </div>
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
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script>
<script src="{{ asset('/js/custom/placeholder.js') }}"></script> 
{!! $tableBuilder->scripts() !!}

<script type="text/javascript">

var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function(){
    $('#date_filter').click(function (e) {
        e.preventDefault();
	    table.ajax.reload();
    });
    
});
</script>


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
<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('/css/libs-font-awesome.min.css') }}">

@stop