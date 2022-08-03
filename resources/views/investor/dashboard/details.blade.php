@extends('layouts.investor.admin_lte')

@section('content')
<div class="col-md-12">
<section class="content graph-sec">
    <div class="box box-primary">
        <div class="row">
            <div class="col-md-12">
                <div class="row dash-det-row">
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Merchant</label>
                            {{$merchant->name}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">MID</label>
                            {{$m_id}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Sub Status</label>
                            {{$merchant->payStatus}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Factor Rate</label>
                            {{$merchant->factor_rate}}
                        </div>
                    </div>


                </div>
                <div class="row dash-det-row">
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Funded</label>
                            {{FFM::dollar($merchant->funded)}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">RTR</label>
                            {{FFM::dollar($merchant->rtr)}}
                        </div>
                    </div>

                
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Balance</label>
                              @if($overpayment)

                                 {{FFM::dollar(0.00) }}

                              @else
                                   {{FFM::dollar($balance)}}

                              @endif
                           
                        </div>
                    </div>

                   
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Date Funded</label>
                            {{FFM::date($merchant->date_funded)}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Commission(%)</label>
                            {{FFM::percent($investment->commission_per+$investment->up_sell_commission_per)}}
                        </div>
                    </div>
                 
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Payments</label>
                            {{$merchant->pmnts}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Payment Amount</label>
                            {{FFM::dollar($merchant->payment_amount)}}
                        </div> 
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">OverPayment</label>

                           @if($overpayment!=0)

                               {{FFM::dollar($investor_overpayment)}}

                           @else

                              {{FFM::dollar(0.00)}}

                           @endif
                         
                        </div>
                    </div>
                    <!-- <div class="col-md-3">
                    <div class="dash-det-box">
                            <label for="exampleInputEmail1">Lender</label>
                            {{isset($merchant->lendor->name)?$merchant->lendor->name:''}}
                        </div>
                        </div>
 -->            <div class="col-md-12"><hr class="m-b-5"></div>

                </div>
                
                <?PHP  ?>
                <div class="row dash-det-row">
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">CTD</label>
                            {{FFM::dollar($ctd_sum)}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Participant Funded</label>
                            {{FFM::dollar($investment->amount)}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Participant RTR</label>
                            {{FFM::dollar($investment->invest_rtr)}}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Paid to Participant</label>
                            {{FFM::dollar($paid_to_participant)}}
                        </div>
                    </div>

                     <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Participant Net Balance</label>

                            <!-- $investment->total_mangt_fee -->

                            @if($overpayment>0)
                               
                                {{FFM::dollar(0.00) }}
                              @else
                              
              {{FFM::dollar(($investment->invest_rtr-$investment->total_mangt_fee)-$paid_to_participant)}}

                     @endif
                           
                        </div>
                    </div>

                    <!-- $investment->invest_rtr-$paid_to_participant -->
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Participant Balance</label>


                              @if($overpayment>0)
                               
                                {{FFM::dollar(0.00) }}
                              @else

                               {{FFM::dollar($investment->invest_rtr-$investment->paid_participant_ishare)}}

                              @endif
                         
                        </div>
                    </div>
            
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Management Fee Paid</label>
                            {{FFM::dollar($total_mgmnt_paid)}}
                        </div>
                    </div>

                     
                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Management Fee</label>
                            {{FFM::percent($investment->mgmnt_fee)}}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Pre-paid</label>
                            {{FFM::dollar($investment->pre_paid)}}
                        </div>    
                    </div>

                    

                   <div class="col-md-3">
                        <div class="dash-det-box">
                            <label for="exampleInputEmail1">Participant Share</label>
                            {{FFM::percent($investment->amount/$merchant->funded*100)}}
                        </div>
                    </div>

                   
                </div>
                
                <!-- AREA CHART -->
         <!-- temp disabled graph       <div class="box">
                    <div class="box-body">
                        <div class="chart">
                            <canvas id="areaChart" style="height:250px"></canvas>
                        </div>
                    </div>

                </div> -->
            </div>
        </div>
    </div>

    <div class="box box-primary p-t-20">
    <div class="row">
        <div class="col-md-10">
                  
       </div>


        <div class="col-md-2 ">
            <div class="pull-right" >
    
                {{Form::open(['route'=>'investor::export::merchant::details','method'=>'POST'])}}
                {{Form::submit('download',['class'=>'btn btn-success'])}}
                {{Form::hidden('id',$merchant->id)}}
                {{Form::close()}}
           
            </div>
        </div>

    </div>

    <hr>
        
       <div class="table-container">
        {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}

    </div>

</div>
</div>
</section>
</div>
@stop


@section('scripts')
<script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"
type="text/javascript"></script>

<script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"
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
        labels: [{!! '"' .implode('","',$graph_lable).'"' !!}],

        datasets: [{
            label: "ACH WORK", //always smae payment type
            backgroundColor: 'rgba(255, 99, 132,0.2)',
            borderColor: 'rgba(88, 99, 132,0.2)',
            data: [{{implode(',',$graph_data)}}],
        }
        ]
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


@stop
@section('styles')
<style type="text/css">

@import url("{{ asset('/css/optimized/inbox.css?ver=5') }}");
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
    <link rel="stylesheet" href="{{ asset ('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    @stop
