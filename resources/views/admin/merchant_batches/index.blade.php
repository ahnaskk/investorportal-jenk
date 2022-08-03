@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Merchant Batches</div>     
      </a>
      
  </div>
  <div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>
        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-10"></div>
                     @if(@Permissions::isAllow('Merchant Batches','Create'))  
                    <div class="col-sm-2" style="padding-bottom:15px">

                        <a href="{{route('admin::merchant_batches::create')}}" class="btn btn-primary" style="float: right;">Add Batches</a>

                    </div>
                    @endif
                </div>
                <div class="row merchant-batch">
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

{!! $tableBuilder->scripts() !!}

@stop

@section('styles')
     <link href="{{ asset('/css/optimized/merchant_batches.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop