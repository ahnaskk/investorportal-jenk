@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

      <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_title}}</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('reserved_liquidity_list',$investors) }}

   <div class="box">
    <div class="box-body">
      @include('layouts.admin.partials.lte_alerts')
    <div class="grid">
        <div class="filter-group-wrap" >
         <div class="filter-group grid" >
           <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
           <div class='top-btn-wrap btn-wrap btn-right  '>
                <div class="btn-box">
                    <a href="{{route('admin::investors::create-reserve-liquidity',$id)}}" class="btn btn-primary"
                        id="cy_create_transactions">Create</a>
                </div>
                
            </div>
              <div class="row">
                <div class="col-sm-12">

                    <div class="table-container" > 
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                    </div>

                </div>
            </div>
          </div>
      
          </div>
       </div>
    </div>

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