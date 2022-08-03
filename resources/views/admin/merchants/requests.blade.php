@extends('layouts.admin.admin_lte')

@section('content')

 <div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Requests </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Requests</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('merchantRequests') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>
        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-12" style="padding-bottom:15px">
                        <div class="grid">
                            <div class="paymntGnrte">
                                  @if(@Permissions::isAllow('Merchants','View')) 
                                <a href="{{route('admin::merchants::index')}}" class="btn btn-primary">Merchant Lists</a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                       <div class="grid table-responsive"> 
                        {!! $tableBuilder->table(['class' => 'table table-bordered '],true) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop

@section('scripts')

    <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>


        {!! $tableBuilder->scripts() !!}

@stop

@section('styles')
<style type="text/css">
    .adminSelect .select2-hidden-accessible {
    display: none;
    }
    .breadcrumb {
        padding: 8px 15px;
        margin-bottom: 20px;
        list-style: none;
        background-color: #f5f5f5;
        border-radius: 4px;
    }
    .breadcrumb > li {
        display: inline-block;
    }
   li.breadcrumb-item a{
        color: #6B778C;
    }
    .breadcrumb > li + li::before {
        padding: 0 5px;
        color: #ccc;
        content: "/\00a0";
    }
    li.breadcrumb-item.active{
        color: #2b1871!important;
    }

</style>
  <link href="{{ asset('/css/optimized/merchant_request.css?ver=5') }}" rel="stylesheet" type="text/css" /> 
@stop