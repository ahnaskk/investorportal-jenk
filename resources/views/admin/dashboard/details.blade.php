@extends('layouts.admin.admin_lte')

@section('content')
    <section class="content graph-sec">
        <div class="box box-primary dashboardDetailsView">
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
                        <label for="exampleInputEmail1">Sub Status</label>
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
                        {{FFM::date($merchant->date_funded)}}
                    </div>
                    <div class="col-md-3">
                        <label for="exampleInputEmail1">Commission</label>
                        {{$merchant->commission}}%
                    </div>
                    <div class="col-md-3">
                        <label for="exampleInputEmail1">Payments</label>
                        {{$merchant->pmnts}}
                    </div>


                </div>
                <hr>
                <?PHP  ?>
                <div class="row">
                    <div class="col-md-3">
                        <label for="exampleInputEmail1">Participant Funded</label>
                         {{FFM::dollar($investment->amount)}}
                    </div>
                    <div class="col-md-3">
                        <label for="exampleInputEmail1">Participant RTR</label>
                        {{FFM::dollar($investment->rtr)}}
                    </div>
                    <div class="col-md-3">
                        <label for="exampleInputEmail1">Paid To Participant</label>
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
                        {{FFM::percent($investment->mgmnt_fee_percentage)}}%
                    </div><div class="col-md-3">
                        <label for="exampleInputEmail1">Syndication Fee</label>
                        {{FFM::percent($investment->syndication_fee_percentage)}}
                    </div>    

                  <!--   <div class="col-md-3">
                        <label for="exampleInputEmail1">Syndication Fee Paid</label>
                        {{($paid_syndication_fee)}}
                    </div> -->
                    <div class="col-md-3">
                        <label for="exampleInputEmail1">Participant Share</label>
                        {{FFM::percent($investment->amount/$merchant->funded*100)}}
                    </div>



                </div>

                <?php ?>

                <!-- AREA CHART -->
                <div class="box chartBox">
                    <div class="box-body">
                        <div class="chart">
                            <canvas id="areaChart" style="height:250px"></canvas>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="box box-primary">
            <div class="col-md-12 col-sm-12">
                <!--<div class="col-md-10">
                    <div class="col-md-4">       
                    <form>
                        <span id="fileselector">
                            <label class="btn btn-default" for="upload-file-selector">
                                <input id="upload-file-selector" type="file">
                                <i class="fa_icon icon-upload-alt margin-correction"></i>Document 1
                            </label>
                        </span>
                    </form></div>

                            <div class="col-md-6"> 
                        <form>
                            <span id="fileselector">
                                <label class="btn btn-default" for="upload-file-selector">
                                    <input id="upload-file-selector" type="file">
                                    <i class="fa_icon icon-upload-alt margin-correction"></i>Document 2
                                </label>
                            </span>
                        </form></div>
                </div> -->


                <div class="block pull-right" style="margin-top: 15px;margin-bottom: 15px;">
                    {{Form::open(['route'=>'admin::export::merchant::details','method'=>'POST'])}}
                    {{Form::submit('download',['class'=>'btn btn-success pull-right'])}}
                    {{Form::hidden('id',$merchant->id)}}
                    {{Form::close()}}
                </div>

            </div>

            <div class="col-md-12 col-sm-12">
                
                <div class="table-container">
                    {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                
                </div>
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

    <script src="{{asset('/bower_components/chart.js/Chart.js')}}"></script>
    <script type="text/javascript">
 var table = window.LaravelDataTables["dataTableBuilder"];



$(document).ready(function () {
    

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

});

    </script>



    <script type="text/javascript">
        
    </script>
@stop
@section('styles')
<style type="text/css">@import url({{ asset('/css/font-awesome3.2.1.css') }});

/* 
    FORM STYLING
*/
#fileselector {
    margin: 10px; 
}
#upload-file-selector {
    display:none;   
}
.margin-correction {
    margin-right: 10px;   
}</style>
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
@stop
