@extends('layouts.investor.admin_lte')

@section('content')
    <section class="content graph-sec">
        <div class="box box-primary">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Merchant</label>
                            {{$merchant->name}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Business Entity Name</label>
                            {{$merchant->business_en_name}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">MID</label>
                            {{$merchant->id}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Sub-Status</label>
                            {{$merchant->payStatus}}
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Funded</label>
                            {{FFM::dollar($merchant->funded)}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">RTR</label>
                            {{FFM::dollar($merchant->rtr)}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">CTD</label>
                            {{FFM::dollar($ctd_sum)}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Balance</label>
                            {{$balance}}
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Factor Rate</label>
                            {{$merchant->factor_rate}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Date Funded</label>
                            {{$merchant->date_funded}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Commission(%)</label>
                            {{$merchant->commission}}%
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">PMNTS</label>
                            {{$merchant->pmnts}}
                        </div>


                    </div>
                    <hr></hr>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Participant Funded</label>
                             {{FFM::dollar($merchant->participant_fund)}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Participant RTR</label>
                            {{FFM::dollar($merchant->participant_rtr)}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Paid to Participant</label>
                            {{$paid_to_participant}}
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Management Fee Paid</label>
                            {{$total_mgmnt_paid}}
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Management Fee</label>
                            {{$merchant->mgmnt_fee}}%
                        </div>
                        <div class="col-md-3">
                            <label for="exampleInputEmail1">Participant Share</label>
                            {{$merchant->participant_share}}%
                        </div>


                    </div>

                    <!-- AREA CHART -->
                    <div class="box">
                        <div class="box-body">
                            <div class="chart">
                                <canvas id="areaChart" style="height:250px"></canvas>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2 pull-right pad">
                {{Form::open(['route'=>'investor::export::merchant::details','method'=>'POST'])}}
                {{Form::submit('download',['class'=>'btn btn-success'])}}
                {{Form::hidden('id',$merchant->id)}}
                {{Form::close()}}
            </div>

        </div>
        <div class="row">
            <div class="col-md-12">
                {!! $tableBuilder->table() !!}
            </div>
        </div>
    </section>
@stop


@section('scripts')
    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}"
            type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}"
            type="text/javascript"></script>

    <script src="{{asset('/bower_components/chart.js/Chart.js')}}"></script>
    <script type="text/javascript">




var ctx = document.getElementById('areaChart').getContext('2d');
var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
        labels: ['{{ date("M", strtotime("-2 month"))}}', '{{ date("M", strtotime("-1 month"))}}', '{{ date("M", strtotime("0 month"))}}', '{{ date("M", strtotime("+1 month"))}}'],
              
              datasets: [{
            label: "ACH WORK",
            backgroundColor: 'rgba(255, 99, 132,0.2)',
            borderColor: 'rgba(88, 99, 132,0.2)',
             data: [<?php foreach ($chart_data as $key => $value): ?>{{$value['total_payment'][1]?$value['total_payment'][1]:0}},

             <?php endforeach ?>0],
        }

        /*,{
            label: "Internal Payoff",
            backgroundColor: 'rgba(99, 240, 132,0.2)',
            borderColor: 'rgba(88, 99, 132,0.2)',
             data: [<?php foreach ($chart_data as $key => $value): ?>{{$value['total_payment'][2]?$value['total_payment'][2]:0}},

             <?php endforeach ?>0],
        }*/]
    },

    // Configuration options go here
    options: {

        elements: {
            line: {
                tension: 0, // disables bezier curves
            }
        }

    }
});



    </script>


    {!! $tableBuilder->scripts() !!}
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
@stop
