@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Open Items</h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Open Items</div>     
      </a>
      
  </div>
    <div class="col-md-12">
    <div class="box">

        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12">
                        {!! $tableBuilder->table(['class' => 'table table-bordered '],true) !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop

@section('scripts')

    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}"
            type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}"
            type="text/javascript"></script>


    {!! $tableBuilder->scripts() !!}

@stop

@section('styles')
     <link href="{{ asset('/css/optimized/open_items.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop