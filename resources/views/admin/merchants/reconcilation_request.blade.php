@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

      <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_title}}</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('admin::merchants::reconcilation-request') }}
   <div class="box">
    <div class="box-body">
      @include('layouts.admin.partials.lte_alerts')
    <div class="grid">
                        <div class="filter-group-wrap" >
                        <div class="filter-group grid" >
                            <div class="form-box-styled" >
                <div class="serch-bar">
                    <form method="POST" action="{{ route('admin::merchants::reconcilation-request-download') }}">
                        @csrf
                        <div class="row">
                            

<div class="col-md-4">
  <div class="input-group">
    <div class="input-group-text">
      <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
    </div>

    {{Form::select('merchants[]',$merchants,'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}

  </div>
  <span class="help-block">Merchants</span>
</div> 
  <div class="col-md-4">
    <div class="input-group">
      <div class="input-group-text">
        <span class="fa fa-credit-card" aria-hidden="true"></span>
      </div>
      {{ Form::select('reconciliation_status',$reconciliation_status,'',['class'=>'form-control','id'=>'reconciliation_status','placeholder'=>'All']) }}    

    </div>
    <span class="help-block">Status</span>
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


<!--    modal for notes -->                             

          
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

            <div class="row">
                <div class="col-sm-12">

                    <div class="table-container" > 
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
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
 <style type="text/css">
    li.breadcrumb-item.active{
      color: #2b1871!important;
    }
    li.breadcrumb-item a{
       color: #6B778C;
    }
    .select2-selection__rendered {
      display: inline !important;
    }
    .select2-search--inline {
      float: none !important;
    }
  </style>
<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('/css/libs-font-awesome.min.css') }}">

@stop



