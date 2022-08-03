<?php use App\User; ?>
<?php use App\UserDetails; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i><i class="fa fa-user" aria-hidden="true"></i> {{ $investor->name }}  </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{ $investor->name }} </div>
    </a>
</div>
{{ Breadcrumbs::render('portfolio',$investor) }}
<div class="portfolio-action-btn">
    <div class="btn-group">
        @if(Permissions::isAllow('Investors','Delete'))
        {!! Form::open(['route' => ['admin::investors::delete', 'id' => $userId], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")', 'class' => 'btn-form-wrap']).Form::submit('Delete', ['class' => 'invest btn btn-xs btn-danger ']).Form::close() !!}
        @endif
        @if($investor->Roles[0]['id']==User::INVESTOR_ROLE)
        @if(Permissions::isAllow('Investor Ach Debit','View'))
        <a href="{{url('admin/investors/achRequest/'.$userId)}}" class="btn btn-xs btn-info"></i>Transfer To Velocity</a>
        @endif
        @endif
        @if($investor->Roles[0]['id']==User::INVESTOR_ROLE)
        @if(Permissions::isAllow('Investor Ach Credit','View'))
        <a href="{{url('admin/investors/achRequest/Credit/'.$userId)}}" class="btn btn-xs btn-info"></i> Transfer To Bank</a>
        @endif
        @endif
        @if($investor->Roles[0]['id']==User::INVESTOR_ROLE)
        <!--  <a href="{{url('admin/investors/creditcard-payment/'.$userId)}}" class="btn btn-xs btn-default"></i> Credit Card</a> -->
        @endif
        @if(Auth::user()->hasRole('admin'))
        @if($investor_type==1 || $investor_type==3 || $investor_type==4)
        <a href="{{URL::to('admin/investors/investor-pref-return')}}/{{$userId}}" class="btn btn-xs btn-success"><i class="glyphicon glyphicon-view"></i> Pref Return</a>
        @endif
        @endif
        @if(Permissions::isAllow('Investors','Edit'))
        <a href="{{url('admin/investors/edit/'.$userId)}}" class="btn btn-xs btn-primary"></i> Edit</a>
        @endif
        @if($investor->Roles[0]['id']==User::INVESTOR_ROLE)
        @if(Permissions::isAllow('Investors','View'))
        <a href="{{route('admin::investors::transaction::index', ['id' => $userId])}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Transactions </a>
        @endif
        @endif
        @if(Permissions::isAllow('Investors','View'))
        <a href="{{url('admin/merchant_investor/documents_upload/'.$userId)}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Documents </a>
        @endif
        @if(Permissions::isAllow('Investors','View'))
        <a href="{{URL::to('admin/investors/investor-reserve-liquidity')}}/{{$userId}}" class="btn btn-xs btn-success"><i class="glyphicon glyphicon-view"></i> Reserve Liquidity</a>
        @endif
        @if(config('app.env')=='local')
        @if(Permissions::isAllow('Investors','View'))
        <?php $UserDetails=UserDetails::where('user_id',$userId)->first(); ?>
        @if($UserDetails)
        <a href="{{url('admin/audit/UserDetails/'.$UserDetails->id)}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> AuditLog </a>
        @endif
        @endif
        @endif
        @if(Permissions::isAllow('Investors','Edit'))
        <a href="{{route('admin::investors::bank_details', ['id' => $userId])}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Bank</a>
        @endif
        @if(@Permissions::isAllow('Generate PDF','Create'))
        <a href="{{route('admin::pdf_for_investors', ['id' => $userId])}}" class="btn btn-xs btn-success"> Generate Statement</a>
        @endif
        @if(@Permissions::isAllow('Generate PDF','Create'))
        <!-- <a href="{{route('admin::investors::syndication-report', ['id' => $userId])}}" class="btn btn-xs btn-success"> View Syndication Report</a> -->
        @endif
        @if(Permissions::isAllow('Investors','Edit'))
        @if($existing_liquidity!=$actual_liquidity)
        <a href="{{route('admin::investors::liquidity_update', ['id' => $userId])}}" class="btn btn-xs btn-primary" style="display: none"><i class="glyphicon glyphicon-view"></i> Update Liquidity [Difference {{round($existing_liquidity-$actual_liquidity,4)}}] </a>
        @endif
        @endif
    </div>
</div>
@include('layouts.admin.partials.lte_alerts')
<div class="col-md-12 col-sm-12 value-box-wrap">
    @if(!Auth::guest())
    @if(config('app.env')=='dusk' || Auth::user()->id==143)
    <div class="row">
        <div class="box">
            <div class="box-content text-center">
                <div class="col-md-12">
                    <input type="button" id="AllPortfolioTableDataCopyButton" class='btn btn-info' value="Select Table And Copy To Clipboard" onclick="selectElementContents( document.getElementById('AllgoogleSheet') );"> <br> <br>
                    <div class="table-responsive">
                        <div id="AllgoogleSheet">
                            <table id='googleSheetDataTable' class='table table-bordered dataTable' style="width:100%">
                                <thead>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-money "></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Liquidity </span>
                    <!-- <a href="#" class="tooltip"> -->
                    <!-- <i class="fa fa-question-circle" hidden="false"></i> -->
                    <!-- <span class="tooltiptext">This is total amount of money available.</span> -->
                    <!-- </a>   -->
                    <span class="info-box-number g_value">{{FFM::dollar($liquidity)}}<small></small></span>
                </div>
            </div>
        </div>
        <?php $reserved_liquidity = ($reserved_liquidity > 0) ? $reserved_liquidity : 0;
        $available_liquidity = $liquidity-$reserved_liquidity;
        ?>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Available Liquidity</span>
                    <span class="info-box-number g_value">{{FFM::dollar($available_liquidity)}}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Reserved Liquidity</span>
                    <span class="info-box-number g_value">@if($reserved_liquidity>0){{FFM::dollar($reserved_liquidity)}} @else $0.00 @endif</span>
                </div>
            </div>
        </div>

        @if(!in_array($investor->Roles[0]['id'],[User::AGENT_FEE_ROLE]))
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-fuchsia"><i class="fa fa-level-up" aria-hidden="true"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Overpayment</span>
                    <span class="info-box-number g_value">{{FFM::dollar($overpayment)}}<small></small></span>
                </div>
            </div>
        </div>
        @if(!in_array($investor->Roles[0]['id'],[User::OVERPAYMENT_ROLE]))
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fa fa-briefcase"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Total Invested</span>
                    <span class="info-box-number g_value">{{FFM::dollar($invested_amount)}}</span>
                </div>
            </div>
        </div>
        @endif
        @endif
        <!-- fix for small devices only -->
        <div class="clearfix visible-sm-block"></div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Number Of Merchants</span>
                    <span class="info-box-number g_value">{{$merchant_count}}</span>
                </div>
            </div>
        </div>       
        @if(!in_array($investor->Roles[0]['id'],[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]))
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Net Invested Amount</span>
                    <span class="info-box-number g_value">{{FFM::dollar($funded_amount)}}</span>
                </div>
            </div>
        </div>
        <!-- <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Net RTR</span>
                    <span class="info-box-number g_value">{{FFM::dollar($net_rtr)}}</span>
                </div>
            </div>
        </div> -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-percent"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Blended Roi</span>
                    <span class="info-box-number g_value" title="{{FFM::percent($blended_rate)}}">{{FFM::percent($blended_rate)}}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-percent"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">ROI</span>
                    <span class="info-box-number g_value">{{FFM::percent($roi)}} <!-- --  <?PHP /*FFM::percent($roi)*/ ?> --></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Default Rate</span>
                    <span class="info-box-number g_value">{{FFM::percent($default_percentage)}}</span>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Cash to Date (CTD)</span>
                    <span class="info-box-number g_value">{{FFM::dollar($ctd)}}</span>
                </div>
            </div>
        </div>
        @if(!in_array($investor->Roles[0]['id'],[User::AGENT_FEE_ROLE]))
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-fuchsia"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Total RTR</span>
                    <span class="info-box-number g_value">{{FFM::dollar($total_rtr)}}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Projected Portfolio Value</span>
                    <span class="info-box-number g_value">{{FFM::dollar($portfolio_value)}}</span>
                </div>
            </div>
        </div>
        @if(!in_array($investor->Roles[0]['id'],[User::OVERPAYMENT_ROLE]))
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Principal Investment </span>
                    <span class="info-box-number g_value">{{FFM::dollar($principal_investment)}}</span>
                </div>
            </div>
        </div>
        <!-- <div class="col-md-3 col-sm-6 col-xs-12"> -->
        <!-- <div class="info-box"> -->
        <!-- <span class="info-box-icon bg-aqua"><i class="fa fa-dollar"></i></span> -->
        <!-- <div class="info-box-content"> -->
        <!-- <span class="info-box-text g_title">Average Principal Investment </span> -->
        <!-- <span class="info-box-number g_value">{{FFM::dollar($average_principal_investment)}}</span> -->
        <!-- </div> -->
        <!-- </div> -->
        <!-- </div> -->
        <!-- <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Current Invested</span>
                    <span class="info-box-number g_value">{{FFM::dollar($c_invested_amount)}}</span>
                </div>
            </div>
        </div> -->
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Average Daily Balance</span>
                    <span class="info-box-number g_value">{{FFM::dollar($average)}}</span>
                </div>
            </div>
        </div>
        @endif
        @endif
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Profit</span>
                    <span class="info-box-number g_value">{{FFM::dollar($profit + $carry['profit'] )}}</span>
                </div>
            </div>
        </div>
        @if(!in_array($investor->Roles[0]['id'],[User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]))
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Paid To Date</span>
                    <span class="info-box-number g_value">{{FFM::dollar($paid_to_date)}}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Anticipated RTR</span>
                    <span class="info-box-number g_value">{{FFM::dollar($anticipated_rtr)}}</span>
                </div>
            </div>
        </div>
        @if(Permissions::isAllow('Investor Ach Debit','View'))
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-money "></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title" title="Send To Velocity"><a href="{{route('admin::payments::investor-ach-requests.index')}}">Pending To Velocity</a></span>
                    <span class="info-box-number g_value">{{FFM::dollar($pending_debit_ach_request)}}</span>
                </div>
            </div>
        </div>
        @endif
        @if(Permissions::isAllow('Investor Ach Credit','View'))
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-money "></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title" title="Send To User Bank"><a href="{{route('admin::payments::investor-ach-requests.index')}}">Pending To User Bank</a></span>
                    <span class="info-box-number g_value">{{FFM::dollar($pending_credit_ach_request)}}</span>
                </div>
            </div>
        </div>
        @endif
        @if($total_requests)
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-muted"><i class="fa fa-bullhorn"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text g_title">Pending Requests</span>
                    <span class="info-box-number g_value">{{$total_requests}}</span>
                </div>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>
<div class="col-md-12 col-sm-12">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">
                    <div class="chart">
                        <canvas id="barChart" style="height:500px">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-12">
    <!-- <div class="col-md-2 pull-right pad dow-prf"> -->
    <!-- {{Form::open(['route'=>'investor::export::merchant::list','method'=>'POST'])}} -->
    <!-- {{Form::submit('download',['class'=>'btn btn-success'])}} -->
    <!-- {{Form::close()}} -->
    <!-- </div> -->
    <div class="grid box box-padTB">
        <div class="col-md-12">
            <div class="form-group">
                <div class="filter-group-wrap">
                    <div class="filter-group">
                        @if(@Permissions::isAllow('Investors','Download'))
                        {{Form::open(['route'=>'admin::investors::portfolio-download','method'=>'POST'])}}
                        @endif
                        <div class="" style="margin-left:16px;"><label>Status</label></div>
                        <div class="col-md-3 col-sm-12" style="margin-left:3px;">
                            <input type="hidden" name="userId" value="{{ $userId }}">
                            {{Form::select('status[]',$substatus,"",['class'=>'form-control dw','id'=>'status','multiple'=>'multiple'])}}
                        </div>
                        <div class="col-md-3"  style="">
                            <div class="">
                                <input type="button" value="Apply Filter" class="btn btn-success prft-bt" id="status_filter"
                                name="student_dob">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 pull-right pad ">
                    <div class="btn-wrap btn-right">
                        @if(@Permissions::isAllow('Investors','Download'))
                        {{Form::submit('download',['class'=>'btn btn-success pull-right'])}}
                        @endif
                    </div>
                </div>
                {{Form::close()}}
            </div>
            <div id="example2_wrapper" class="grid dataTables_wrapper form-inline dt-bootstrap">
                <div class="col-sm-12">
                    <div class="grid table-responsive">
                        <div class="table-container">
                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- to Copy the portfolio value to clipboard -->
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
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(54, 162, 235, 0.6)'
                ],
                borderColor: [ 
                    'rgba(255, 99, 132, 0.6)',
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
                data                : [<?php foreach ($chart_data as $key => $value): ?>{{ isset($value['funded'])?$value['funded']:0}},<?php endforeach ?>0]
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
                data                : [<?php foreach ($chart_data as $key => $value): ?>{{ isset($value['rtr_month'])?$value['rtr_month']:0}},<?php endforeach ?>0]
            },
            {
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
                data                : [<?php foreach ($chart_data as $key => $value): ?>{{ isset($value['ctd_month'])?$value['ctd_month']:0}},<?php endforeach ?>0]
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
<!-- to Copy the portfolio value to clipboard -->
@if(!Auth::guest())
@if(config('app.env')=='dusk' || Auth::user()->id==143)
<script type="text/javascript">
function selectElementContents(el) {
    var body = document.body, range, sel;
    if (document.createRange && window.getSelection) {
        range = document.createRange();
        sel = window.getSelection();
        sel.removeAllRanges();
        try {
            range.selectNodeContents(el);
            sel.addRange(range);
        } catch (e) {
            range.selectNode(el);
            sel.addRange(range);
        }
    } else if (body.createTextRange) {
        range = body.createTextRange();
        range.moveToElementText(el);
        range.select();
        document.execCommand("copy");
    }
    document.execCommand("copy");
    navigator.clipboard.readText().then(text => {
        console.log('Pasted content: ', text);
    })
}
$(document).ready(function(){
    var titles=[];
    $('.g_title').each(function(){
        var text = $(this).text();
        titles.push(text);
    });
    var values=[];
    $('.g_value').each(function(){
        var text = $(this).text();
        values.push(text);
    });
    var googleSheetData=[];
    $.each( titles, function( key, value ) {
        googleSheetData.push({
            'Title':value,
            'Value':values[key]
        });
    });
    var localStorageData ={
        investor_id:{{$investor->id}},
        Investor   :"{{$investor->name}}",
        company_id :"{{$investor->company}}",
        Table      :googleSheetData,
    }
    // localStorage.removeItem('Investor');
    var OldlocalStorageData = JSON.parse(localStorage.getItem('Investor')) || {};
    OldlocalStorageData[{{$investor->id}}]=localStorageData;
    localStorage.setItem('Investor', JSON.stringify(OldlocalStorageData));
    var investorList={};
    $.each(OldlocalStorageData,function( key, value){
        investorList[value.investor_id]={name:value.Investor,company_id:value.company_id};
    });
    localStorageAllInvestor=JSON.parse(localStorage.Investor);
    var AlltableDataHeader={};
    AlltableDataHeader[0]='#';
    AlltableDataHeader[1]='Company';
    AlltableDataHeader[2]='Investor/Title';
    $.each( titles, function( key, value ) {
        AlltableDataHeader[key+3]=value;
    });
    var AllInvestors={};
    var AlltableData={};
    var i=0;
    $.each( titles, function( key,title ) {
        var SingleTr={};
        i++;
        var investorData={};
        $.each(localStorageAllInvestor,function( investor_id, value){
            var Table=value.Table;
            $.each(Table,function( keyT, valueT){
                if(title==valueT.Title){
                    investorData[investor_id] =valueT.Value;
                    SingleTr[title]           =investorData;
                }
            });
        });
        AlltableData[i]=SingleTr;
    });
    var AlltableHeadTable =$('#googleSheetDataTable thead');
    AlltableHead  ="<tr>";
    $.each( AlltableDataHeader, function( key, value ) {
        AlltableHead +="<td class='text-right'>"+value+"</td>";
    });
    AlltableHead +="</tr>";
    AlltableHeadTable.append(AlltableHead);
    var AlltableBodyTable =$('#googleSheetDataTable tbody');
    $.each( investorList, function( investor_id,investor ) {
        AlltableBody  ="<tr>";
        AlltableBody +="<td class='text-right'>"+investor_id+"</td>";
        AlltableBody +="<td class='text-right'>"+investor.company_id+"</td>";
        AlltableBody +="<td class='text-right'>"+investor.name+"</td>";
        $.each( titles, function( TitleKey,title ) {
            $.each( AlltableData, function( key, value ) {
                if(typeof(value[title]) != "undefined" && value[title] !== null){
                    if(typeof(value[title][investor_id]) != "undefined" && value[title][investor_id] !== null){
                        AlltableBody +="<td class='text-right'>"+value[title][investor_id]+"</td>";
                    }
                }
            });
        });
        AlltableBody +="</tr>";
        AlltableBodyTable.append(AlltableBody);
    });
    $('#googleSheetDataTable').dataTable({
        paging:false,
        bInfo:false,
        searching:false,
        columnDefs: [
            { width: '2000px', 'targets': [2] },
        ],
    });
});
</script>
@endif
@endif
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
.portfolio-action-btn .btn-group>.btn:not(:last-child):not(.dropdown-toggle) {
    border-radius: 4px;
    margin-right: 5px;
}
.select2-selection__rendered {
    display: inline !important;
}
.select2-search--inline {
    float: none !important;
}
</style>
<link href="{{ asset('/css/optimized/portfolio.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
