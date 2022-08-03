@extends('layouts.investor.admin_lte')

@section('content')


    <div class="inve-prf col-md-12 col-sm-12" style="    margin-bottom: 25px;">
        <div class="row">

            <!-- 
                <a  style="text-decoration: none; border-bottom: 1px solid #ff0000; color: #000000;" target="_blank">

                  <span class="info-box-number">{{FFM::dollar($liquidity)}}<small></small></span>
                </div></a>
                !-- /.info-box-content --
              </div>
              !-- /.info-box --
            </div> -->


            <!-- Trial by Lisa -->
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-money "></i></span>
                  <div class="info-box-content">

                      <span class="info-box-text">Liquidity<a class="tool-transition" id="mouse-over"> <i class="fa fa-question-circle " aria-hidden="true"></i>
                      <span class="tooltiptext">Amount of cash available in your account</span></a></span>

                    <span class="info-box-number">{{FFM::dollar($liquidity)}}<small></small></span>
                  </div>
              </div>
            </div>

           <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fa fa-briefcase"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Amount Invested
                    <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext">Total Amount of fund that is invested</span>  </a>
                  </span>
                  <span class="info-box-number">{{FFM::dollar($invested_amount)}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->


            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-dollar"></i></span>

                    <div class="info-box-content">
                  <span class="info-box-text">CTD
                  <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext">Cash To Date Represent the sum of all cash collected from the inception of your portfolio</span>  </a> </span>
                  <span class="info-box-number">{{FFM::dollar($ctd)}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>




            <!-- /.col -->
   <!--          <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-percent"></i></span>
                  <div class="info-box-content">
                  <span class="info-box-text">Blended</span><span class="info-box-text">Interest Rate
                  <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext"> </span>  </a></span>
                  <span class="info-box-number" title="{{FFM::percent($blended_rate)}}">{{FFM::percent($blended_rate)}}</span>
                 </div>
              
              </div>
     
            </div> -->



       <!--      <div class="col-md-2 col-sm-6 col-xs-12">
                <div class="info-box">
                  <span class="info-box-icon bg-purple"><i class="fa fa-percent"></i></span>
                   <div class="info-box-content">
                   <span class="info-box-text">ROI</span>
                   <span class="info-box-number">-- <?PHP /* FFM::percent($roi);*/ ?> </span>
                  </div>
              </div>
              /.info-box --
            </div> -->

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                   <span class="info-box-icon bg-red"><i class="fa fa-ban"></i></span>
                    <div class="info-box-content">
                    <span class="info-box-text">Default Rate
                      <a class="tool-transition" id="mouse-over">
                      <!-- <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext"></span>  -->
                     </a>
                    </span>
                   <span class="info-box-number">{{FFM::percent($default_percentage)}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>     

           <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-users"></i></span>
                 <div class="info-box-content">
                  <span class="info-box-text">Merchants
                  <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext">Total no of deals in which your portfolio is invested</span>  </a>
                    </span><!-- Number of merchants -->
                  <span class="info-box-number">{{$merchant_count}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>


            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-olive"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Total RTR
                  <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext">RTR or Right to receive represent total amount expected to be collected from the merchant</span>  </a> </span>
                  <span class="info-box-number">{{FFM::dollar($total_rtr)}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>  




             <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Average Daily Balance</span>
                  <span class="info-box-number">{{FFM::dollar($average)}}</span>
                </div>

              </div>

            </div>


       <!--      <div class="col-md-2 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-light-blue-gradient"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Investor Portfolio</span>
                  <span class="info-box-number">{{FFM::dollar($total_credit)}}</span>
                </div>
                !-- /.info-box-content --
              </div>
              !-- /.info-box --
            </div> -->
      <!--       <div class="col-md-2 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-light-pink"><i class="fa fa-percent"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">YTD Return</span>
                  <span class="info-box-number"> -- <?PHP /*FFM::percent($cash_on_cash_return)*/ ?></span>
                </div>
                !-- /.info-box-content --
              </div>
              !-- /.info-box --
            </div> -->
      <!--       <div class="col-md-2 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-light-blue-active"><i class="fa fa-percent"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Cash On Cash Return</span>
                  <span class="info-box-number"><?PHP /*FFM::percent($ytd_return)*/ ?> -- </span>
                </div>
                !-- /.info-box-content --
              </div>
              !-- /.info-box --
            </div> -->


      @if($investor_type==2)

          <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-fuchsia"><i class="fa fa-dollar"></i></span>
                 <div class="info-box-content">
                  <span class="info-box-text">Velocity<a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext"></span>  </a>
                    </span>
                  Distribution<!-- Distribution to Velocity -->
                  <span class="info-box-number">{{FFM::dollar($velocity_dist)}}</span>
                 </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>

           <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-maroon"><i class="fa fa-dollar"></i></span>
                  <div class="info-box-content">
                   <span class="info-box-text">Investor
                    </span>Distribution<a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext"></span>  </a>
                   <span class="info-box-number">{{FFM::dollar($investor_dist)}}</span>
                   </div>
                 <!-- /.info-box-content -->
                </div>
              <!-- /.info-box -->
            </div>        
           @endif
           @if($total_requests)
            <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-muted"><i class="fa fa-bullhorn"></i></span>
                 <div class="info-box-content">
                  <span class="info-box-text">Pending Requests</span>
                  <span class="info-box-number">{{$total_requests}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            @endif
            <!-- /.col -->

             <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-olive"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Portfolio Value<a class="tool-transition" id="mouse-over">
                      <!-- <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext"></span>   -->
                    </a>
                    </span>
                  <span class="info-box-number">{{FFM::dollar($portfolio_value)}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div> 

              <!-- current portfolio  -->
<!-- 
            <div class="col-md-2 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Current Portfolio</span>
                  <span class="info-box-number">{{FFM::dollar($current_portfolio)}}</span>
                </div>
                !-- /.info-box-content --
              </div>
              !-- /.info-box --
            </div> --> 

              <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Principal Investment
                    <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext">The original sum committed to your portfolio</span>  </a></span>
                  <span class="info-box-number">{{FFM::dollar($principal_investment)}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div> 


<?PHP 
/*
 ?>
              <div class="col-md-2 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Pref Return</span>
                  <span class="info-box-number">{{FFM::dollar($debit_interest)}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div> 
<?PHP */ ?>


              <div class="col-md-3 col-sm-6 col-xs-12" style="display:none">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">IRR
                  <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext"></span>  </a> </span>
                  <span class="info-box-number">{{FFM::percent($irr)}}</span>
               </div>

              </div>
            </div> 


            <!--  <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-money "></i></span> <a style="text-decoration: none; border-bottom: 1px solid #ff0000; color: #000000;" target="_blank">
                <div class="info-box-content"> --> <!-- commented old code and added same code as above .... by Lisa Melbith -->


              <!-- <div class="col-md-3 col-sm-6 col-xs-12">
              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Overpayment
                  <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext">
                     </span>  </a></span>
                  <span class="info-box-number">{{FFM::dollar($overpayment)}}<small></small></span>
                </div></a>

              </div>

            </div> -->


               <!-- <div class="col-md-3 col-sm-6 col-xs-12">

              <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-dollar"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Current Invested
                  <a class="tool-transition" id="mouse-over">
                      <i class="fa fa-question-circle " aria-hidden="true"></i>
                     <span class="tooltiptext">Total Amount of fund that is invested as of today</span>  </a> </span>
                  <span class="info-box-number">{{FFM::dollar($c_invested_amount)}}</span>
               </div>

              </div>
            </div>  -->

        </div>
    </div>


    <section class="content graph-sec">
        <div class="col-md-12 col-sm-12">
            <div class="row">
                <div class="col-md-12">
                     <div class="box box-success">
                        <div class="box-body">
                            <div class="chart">
                                <canvas id="barChart" style="height:600px">

                                </canvas>
                            </div>
                        </div>
                     </div>
                </div>
            </div>

            {{Form::open(['route'=>'investor::portfolio-download','method'=>'POST'])}}
            <div class="row">
                <div class="col-md-12 user-table">
                    <div class="col-md-2 pull-right pad"> 

                        {{Form::submit('download',['class'=>'btn btn-success'])}}


                    </div>

                </div>
              <div class="col-md-12 col-sm-12">
                  <div class="grid box box-padTB">
                  <div class="form-group">

                     <!-- {{Form::open(['route'=>'investor::dashboard::index'])}} -->
                      <div class="row">
                        <div class="" style="margin-left:40px;"><label>Status</label></div>
                         <div class="col-md-3" style="margin-left:20px;"> 
                            {{Form::select('status[]',$substatus,"",['class'=>'form-control','id'=>'status','multiple'=>'multiple'])}}  
                        </div>                       
                        <div class="col-md-1"  style="">

                             <input type="button" value="Apply Filter" class="btn btn-success" id="status_filter"
                                           name="student_dob">


                        </div>                           

                </div>                    
                    <!-- {{Form::close()}} -->
            </div>

            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                            <div class="row">
                                <div class="col-sm-12 ">
                                    <div class="table-container">

                       {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}

                                        </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
{{Form::close()}}
        </div>
    </section>
@stop


@section('scripts')
    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}" type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}" type="text/javascript"></script>

    <script src="{{asset('/bower_components/chart.js/Chart.js')}}"></script>
    <script type="text/javascript">

    var ctx = document.getElementById("barChart").getContext('2d');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels  : ['{{ date("M", strtotime("-4 month"))}}','{{ date("M", strtotime("-3 month"))}}', '{{ date("M", strtotime("-2 month"))}}', '{{ date("M", strtotime("-1 month"))}}', '{{ date("M", strtotime("0 month"))}}'],
         datasets: [
                    {
            backgroundColor: [
                'rgba(54, 162, 235, 0.6)','rgba(54, 162, 235, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(54, 162, 235, 0.6)'
            ],
            borderColor: [ 'rgba(255, 99, 132, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(54, 162, 235, 0.6)'

                ],


                        label               : 'Funded',
                        fillColor           : 'blue',
                        strokeColor         : 'blue',
                        pointColor          : 'blue',
                        pointStrokeColor    : '#c1c7d1',
                        pointHighlightFill  : '#fff',
                        pointHighlightStroke: 'rgba(220,220,220,1)',
                      data                : [<?php foreach ($chart_data as $key => $value): ?>{{isset($value['funded'])?$value['funded']:0}},<?php endforeach ?>0]
                    },
                    {

                          backgroundColor: [
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
            ],
            borderColor: [
               'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',
                'rgba(255, 99, 33, 0.6)',

            ],


                        label               : 'RTR',
                        fillColor           : 'black',
                        strokeColor         : 'black',
                        pointColor          : '#fff',
                        pointStrokeColor    : 'black',
                        pointHighlightFill  : '#fff',
                        pointHighlightStroke: 'black',
                        data                : [<?php foreach ($chart_data as $key => $value): ?>{{isset($value['rtr_month'])?$value['rtr_month']:0}},<?php endforeach ?>0]
                    },{

                          backgroundColor: [
                'rgba(20, 200, 132, 0.6)',
                'rgba(20, 200, 132, 0.6)',
                'rgba(20, 200, 132, 0.6)',
                'rgba(20, 200, 132, 0.6)',
                'rgba(20, 200, 132, 0.6)'
            ],
            borderColor: [,
                'rgba(20, 200, 132, 1)',
                'rgba(20, 200, 132, 1)',
                'rgba(20, 200, 132, 1)',
                'rgba(20, 200, 132, 1)',
                'rgba(20, 200, 132, 1)',
                'rgba(20, 200, 132, 1)'
            ],


                        label               : 'CTD',
                        fillColor           : 'red',
                        strokeColor         : 'red',
                        pointColor          : '#fff',
                        pointStrokeColor    : 'red',
                        pointHighlightFill  : '#fff',
                        pointHighlightStroke: 'red',
                        data                : [<?php foreach ($chart_data as $key => $value): ?>{{isset($value['ctd_month'])?$value['ctd_month']:0}},<?php endforeach ?>0]
                    }
                ]
    },


    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});






    </script>


    {!! $tableBuilder->scripts() !!}



    <script type="text/javascript">
        var table = window.LaravelDataTables["dataTableBuilder"];
        $(document).ready(function(){
        $('#status_filter').click(function (e) {
            e.preventDefault();
            table.draw();
        });

    });
    </script>
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset ('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@stop
