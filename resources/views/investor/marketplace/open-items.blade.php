@extends('layouts.investor.admin_lte')

@section('content')
    <section class="content graph-sec">
        <!-- <div class="row">
            <div class="col-md-12">
                 <div class="box box-success">
                    <div class="box-body">
                        <div class="chart">
                            <canvas id="barChart" style="height:230px"></canvas>
                        </div>
                    </div>
                 </div>
            </div>
        </div> -->
        <div class="row">
            <div class="col-md-12 user-table">

          <!--   <div class="col-md-2 pull-right pad">
                {{Form::open(['route'=>'investor::export::merchant::list','method'=>'POST'])}}
                {{Form::submit('download',['class'=>'btn btn-success'])}}
                {{Form::close()}}
            </div> -->

        </div>
        <div class="row">
            <div class="col-md-12">
                {!! $tableBuilder->table() !!}
            </div>
        </div>
    </section>
@stop


@section('scripts')
    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}" type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}" type="text/javascript"></script>

    <script src="{{asset('/bower_components/chart.js/Chart.js')}}"></script>
    


    {!! $tableBuilder->scripts() !!}
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
@stop