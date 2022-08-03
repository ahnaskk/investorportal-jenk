@extends('layouts.marketplace.admin_lte')

@section('content')
    <div class="container" style="margin-top: 100px">
    <div class="row">
    <section class="content graph-sec">
        <div class="row">
            <div class="col-md-2 pull-right" style="padding-bottom: 50px;">


              <a class="form-control btn btn-success" href="{{URL::to('/admin/marketplace')}}"> Back to marketplace </a>
            </div>


        </div>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="table-container table-responsive invstDocPage">
                    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}

                </div>
            </div>
        </div>
    </section>
@stop


@section('scripts')
    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}"
            type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}"
            type="text/javascript"></script>
       {!! $tableBuilder->scripts() !!}


@stop
