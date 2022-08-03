@extends('layouts.admin.admin_lte')
@section('content')

    @if(($subadmin_permission))

        <div class="row">
            <div class="inner admin-dsh header-tp">
                <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ Auth::user()->name }}</h3>
                <a href="#" class="help-link">
                    <i class="fa fa-question-circle" aria-hidden="true"></i>
                    <div class="tool-tip">{{ Auth::user()->name }}</div>

                </a>
            </div>
            @else
                <div class="inner admin-dsh header-tp">

                    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$title}}</h3>
                    <a href="#" class="help-link">
                        <i class="fa fa-question-circle" aria-hidden="true"></i>
                        <div class="tool-tip">Instruction</div>
                    </a>
                </div>
            @endif

            @php config('app.investor_app_id'); @endphp
            @if(!Auth::user()->hasRole(['company']))
                <div class="form-box-styled" >
                    <div class="serch-bar">
                        <form method="get" action="">
                            <div class="row">
                                @foreach($company as $key => $com)
                                    <div class="col-md-4 report-input">
                                        <div class="input-group check-box-wrap">
                                            <div class="input-group-text">
                                                <label class="chc">
                                                    @if(in_array($key, $filter_company_data))
                                                        <input  id="company[]" name="company[]" type="checkbox" value="{{$key}}" checked/>
                                                    @else
                                                        <input  id="company[]" name="company[]" type="checkbox" value="{{$key}}"  {{ old('company') ? 'checked' : '' }}/>
                                                    @endif
                                                    <span class="checkmark checkk00"></span>
                                                    <span class="chc-value">{{$com}}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col-md-4">
                                    <div class="btn-box " style="margin-bottom: 25px;">
                                        <div class="input-group">
                                            <input type="submit" value="Apply Filter" class="btn btn-success" id="date_filter">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="container">
                <div class="row" >
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr1 total_rtr-widget">
                            <div class="inner">
                                <h3>{{ FFM::dollar($total_a_rtr)}}</h3>
                                <p>Total RTR</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-briefcase"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr1 expected_rtr-widget">
                            <div class="inner"><h3>{{ FFM::dollar($expected_rtr) }}</h3>
                                <p>Expected RTR</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-briefcase"></i>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="small-box bg-clr2 investor-widget">
                            <div class="inner">
                                <h3>{{$investor_count}}</h3>
                                <p>Investors</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-user-circle-o"></i>
                            </div>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr3 merchant-widget">
                            <div class="inner">
                                <h3>{{$merchant_count}}</h3>

                                <p>Merchants</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-handshake-o"></i>
                            </div>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr4  liquidity-widget">
                            <div class="inner">
                                <h3>{{FFM::dollar($cash_in_hands)}}</h3>

                                <p>Liquidity</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
                    @if(!Auth::user()->hasRole(['company']))
                        @if($subadmin_liquidity)
                            @foreach($subadmin_liquidity as $key=>$value)
                                <div class="col-md-4 col-sm-6 col-xs-12">
                                    <!-- small box -->
                                    <div class="small-box bg-clr4" style="background-color: #FFC0CB!important;">
                                        <div class="inner">
                                            <h3>{{FFM::dollar($value['liquidity'])}}</h3>
										<?php



										if($cash_in_hands!=0)
										{
											$valocity1=100-($cash_in_hands-$value['liquidity'])/$cash_in_hands*100;
										}
										else
										{
											$valocity1=0;
										}

										?>
                                        <!-- for admin -->
                                            <p> {{ $value['company'] }}  ({{ FFM::percent($valocity1) }}) </p>
                                        </div>
                                        <div class="icon">
                                            <i class="fa fa-money"></i>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif

                <!-- ./col -->
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr5 total_invested_amount-widget">
                            <div class="inner">
                                <h3>{{FFM::dollar($invested_amount)}}</h3>

                                <p>Total Amount Invested</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-archive"></i>
                            </div>
                        </div>
                    </div>        <!-- ./col -->
                    <div class="col-md-4 col-sm-6 col-xs-12">

                        <div class="small-box bg-clr5 current_invested_amount-widget">
                            <div class="inner">
                                <h3 title="">{{FFM::dollar($c_invested_amount)}}</h3>

                                <p>Current Invested</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-archive"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr6 blended_rate-widget">
                            <div class="inner">
                                <h3>{{FFM::percent($blended_rate)}}</h3>

                                <p>Blended Rate</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-circle-o-notch"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr7 ctd-widget" style="background-color: #96ed4a96 !important;">
                            <div class="inner">
                                <h3 title="Paid Fee: {{$all_paid_fee}}">{{FFM::dollar($ctd_after_fee)}}</h3>
                                <p>CTD</p>
                            </div>
                            <div class="icon">
                                <img src="{{asset('images/money.png')}}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr7 velocity_distribution-widget" style="background-color: #4bfcd7b5!important;">
                            <div class="inner">
                                <h3>{{FFM::dollar($velocity_distribution)}}</h3>

                                <p>Velocity Distribution</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-line-chart"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr7 investor_distribution-widget" style="background-color: #df4bfcb8!important;">
                            <div class="inner">
                                <h3>{{FFM::dollar($investor_distribution)}}</h3>

                                <p>Investor Distribution</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-cubes"></i>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr7 pactolus_distribution-widget" style="background-color: #4bfcd7b5!important;">
                            <div class="inner">
                                <h3>{{FFM::dollar($pactolus_distribution)}}</h3>

                                <p>Pactolus Distribution</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-line-chart"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">

                        <div class="small-box bg-clr7 average_daily_balance-widget" style="background-color: #FF69B4!important;">
                            <div class="inner">
                                <h3>{{FFM::dollar($average)}}</h3>

                                <p>Average Daily Balance</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-dollar"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="small-box bg-clr7 investor_portfolio-widget" style="background-color: #FF6347!important;">
                            <div class="inner">
                                <h3>{{FFM::dollar($total_credit)}}</h3>
                                <p>Investor Portfolio</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-credit-card"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="small-box bg-clr7 default_rate-widget" style="background-color: #9370DB!important;">
                            <div class="inner">
                                <h3 title="{{$default_amount}}">{{FFM::percent($default_rate)}}</h3>
                                <p>Default Rate</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-percent"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr1 over_payment-widget">
                            <div class="inner">
                                <h3>{{FFM::dollar($overpayment)}}</h3>
                                <p>Over Payment</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-briefcase"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr4 portfolio_value-widget" style="background-color: #008000!important;">
                            <div class="inner">
                                <h3>{{FFM::dollar($portfolio_value)}}</h3>
                                <p>Portfolio Value</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
					<?PHP // @endif ?>
                </div>
            </div>


            @stop


@section('scripts')
{{--<script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>--}}
<script src="{{asset('bower_components/chart.js/Chart.js')}}"></script>
    {{--<script type="text/javascript">
        $(function(){
            $('.link').hover(
                function(){
                    $(this).next().show();
                },
                function(){
                    $(this).next().hide();
                }
            )
        })
    </script>--}}
@stop

@section('styles')
    <link href="{{ asset('/css/optimized/admin_dashboard.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/optimized/admin_dashboard2.css') }}" rel="stylesheet" type="text/css" />
@stop


