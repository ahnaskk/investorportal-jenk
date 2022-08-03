@extends('layouts.branchmanager.admin_lte')

@section('content')
    <div class="box">
        <div class="box-head ">
            @include('layouts.branchmanager.partials.lte_alerts')

        </div>
        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-10"></div>
                    <div class="col-sm-2" style="padding-bottom:15px">

                        <a href="{{route('branch::marketplace::create')}}" class="btn btn-primary">Create MarketPlace</a>

                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        {!! $tableBuilder->table() !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
@stop

@section('scripts')

    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}" type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}" type="text/javascript"></script>


        {!! $tableBuilder->scripts() !!}

@stop

@section('styles')
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
@stop