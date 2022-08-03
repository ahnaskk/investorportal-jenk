@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

      <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_title}}</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('admin::merchants::mail-log') }}
   <div class="box">
    <div class="box-body">
      @include('layouts.admin.partials.lte_alerts')
    <div class="grid">
                        <div class="filter-group-wrap" >
                        <div class="filter-group grid" >
                            <div class="form-box-styled" >
                <div class="serch-bar">
                    <form method="POST" action="{{ route('admin::merchants::mail-log-download') }}">
                        @csrf
                        <div class="row">
                            

<div class="col-sm-3 shrink ">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
    </div>

    {{Form::select('merchants[]',$merchants,'',['class'=>'form-control js-merchants-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}

  </div>
  <span class="help-block">Merchants</span>
</div> 
<div class="col-sm-3 shrink">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
    </div>

    {{Form::select('mail_type',$mail_types,'',['class'=>'form-control','id'=>'mail_type', 'placeholder'=>'Select Type'])}}

  </div>
  <span class="help-block">Type</span>
</div> 
<div class="col-sm-3 shrink">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
    </div>

    {{Form::text('date_from1','',['class'=>'form-control datepicker','placeholder' => \FFM::defaultDateFormat('format'), 'id'=>'date_from1','autocomplete' => 'off'])}}
    <input type="hidden" name="date_from" id="date_from" class="date_parse">

  </div>
  <span class="help-block">Start Date</span>
</div> 
<div class="col-sm-3 shrink">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
    </div>

    {{Form::text('date_to1','',['class'=>'form-control datepicker','id'=>'date_to1','placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete' => 'off'])}}
    <input type="hidden" name="date_to" id="date_to" class="date_parse">

  </div>
  <span class="help-block">End Date</span>
</div> 
<div class="col-md-12">
    <div class="btn-box" style="margin-bottom: 25px;float:right;">
        <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter">
        <input type="submit" value="Download" class="btn btn-primary">
    </div> 
</div>
                        </div>                             
                    </form>
                </div>
            </div>


<!--    modal for notes -->                             

          
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

            <div class="row">
                <div class="col-sm-12">

                    <div class="table-container" > 
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true);$tableBuilder->parameters(['stateSave' => true,]); !!}
                    </div>

                </div>
            </div>
        </div>
      
    </div></div></div>

</div>
</div>



@stop

@section('scripts')

{!! $tableBuilder->scripts() !!}

<script type="text/javascript">
  $(".js-merchants-placeholder-multiple").select2({
        placeholder: "Select Merchants"
    });

var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function(){
  
    $('#date_filter').click(function (e) {
        e.preventDefault();
        table.draw();
    });
  
});
</script>


@stop
@section('styles')
<style>
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



