@extends('layouts.admin.admin_lte')
@section('styles')
<link href="{{ asset('/css/optimized/admin_dashboard.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/admin_dashboard2.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('/css/optimized/font-awesome4.7.0.min.css') }}">
@stop
@section('content')
@if(($data['subadmin_permission']))
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
    @if(isset($data['pendingRequests']) && $data['pendingRequests'] > 0 && @Permissions::isAllow('Credit Card Payment','View'))
        <div class="alert alert-success"> <i class="fa fa-exclamation-circle fa-2" aria-hidden="true"></i>   There @if($data['pendingRequests'] == 1) is @else are @endif {{ $data['pendingRequests'] }} pending transactions. Please <a href="{{ \URL::to('/admin/payment/PendingTransactions') }}">click here</a> to view.</div>
    @endif
    @php config('app.investor_app_id'); @endphp
    @if(!Auth::user()->hasRole(['company']))
    <div class="box box-primary sub_adm box-sm-wrap">
        <div class="container-fluid">
            <div class="dashboard-filter" >
                {!! Form::open(['id' => 'company-dashboard-form', 'onsubmit' => 'return getCompanyDashboard();']) !!}
                <div class="dashboard-filter-form">
                    @foreach($data['companies'] as $companyId => $companyName)
                    <div class="col-md-4 report-input">
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text">
                                <label class="chc">
                                    @if(in_array($companyId, $data['companyIds']))
                                    <input  id="company[]" name="company[]" type="checkbox" value="{{ $companyId }}" checked/>
                                    @else
                                    <input  id="company[]" name="company[]" type="checkbox" value="{{ $companyId }}"  {{ old('company') ? 'checked' : '' }}/>
                                    @endif
                                    <span class="checkmark checkk00"></span>
                                    <span class="chc-value company-name">{{ $companyName }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div class="col-md-4 report-input">
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text label-selection-form">
                                {!! Form::select('label',$data['labels'],'',['placeholder'=>'Select Label','class'=>'form-control','id'=>'label']) !!}
                            </div>
                        </div>

                    </div>
                    <div class="col-md-4 report-input">
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text label-selection-form">
                                {!! Form::select('account_filter',$filter_arr,'',['placeholder'=>'Select Type','class'=>'form-control','id'=>'account_filter']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="btn-box ">
                        <input type="submit" value="Apply Filter" class="btn btn-success" id="date_filter">
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            @endif
            <div class="container-fluid">
                <div class="row" >
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr1 total_rtr-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                            <div class="inner"><h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p>Expected RTR</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-briefcase"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="small-box bg-clr2 total_investors-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                        <div class="small-box bg-clr3 total_merchants-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p>Liquidity</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-companies" style="display: contents;">
                    </div>
                    @if(!Auth::user()->hasRole(['company']) and 1 == 2)
                    @if($subadmin_liquidity)
                    @foreach($subadmin_liquidity as $key=>$value)
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr4" style="background-color: #FFC0CB!important;">
                            <div class="inner">
                                <h3>{{FFM::dollar($value['liquidity'])}}</h3>
                                <?php
////fzl laravel8 use Database\Seeders\Role;
                                if($cash_in_hands!=0) {
                                    $valocity1=100-($cash_in_hands-$value['liquidity'])/$cash_in_hands*100;
                                } else {
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
                        <div class="small-box bg-clr5 invested_amount-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p>Total Amount Invested</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-archive"></i>
                            </div>
                        </div>
                    </div>        <!-- ./col -->
                    <!-- <div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="small-box bg-clr5 current_invested_amount-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p>Current Invested</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-archive"></i>
                            </div>
                        </div>
                    </div> -->
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr6 blended_rate-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p>Blended Rate</p>
                            </div>
                            <div class="icon">
                                <i class=" fa fa-circle-o-notch"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr7 ctd_after_fee-widget" style="background-color: #96ed4a96 !important;">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p>Default Rate</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-percent"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr1 overpayment-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
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
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p>Portfolio Value</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
                    @if(Permissions::isAllow('Investor Ach Debit','View'))
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr6 pending_investor_ach_debit_requested_amount-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p title="Send To Velocity">Pending To Velocity</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(Permissions::isAllow('Investor Ach Credit','View'))
                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <!-- small box -->
                        <div class="small-box bg-clr6 pending_investor_ach_credit_requested_amount-widget">
                            <div class="inner">
                                <h3><i class=" fa fa-refresh fa-spin"></i></h3>
                                <p title="Send To User Bank">Pending To User Bank</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
                    @endif
                    <?PHP // @endif ?>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
{{--<script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>--}}
<script src="{{asset('bower_components/chart.js/Chart.js')}}"></script>
<script src="{{asset('js/custom/autoFontSize.js')}}"></script>
<script type="text/javascript">
var transactionResponse;
var dashBoardResponse;
var responseDraw = 1;
$(document).ready(function () {
    getCompanyDashboard();
    new AutoFontSize('.small-box .inner h3',27);
});
function getDashboard() {
    dashBoardResponse = '';
    $('.small-box').find('h3').html('<i class=" fa fa-refresh fa-spin"></i>');
    $.ajax({
        method: 'POST',
        url: '{{ url('admin/dashboard/data') }}?_token={{ csrf_token() }}&draw=' + responseDraw,
        data: $('#company-dashboard-form').serialize(),
        dataType: 'json'
    }).done(function(response) {
        if( responseDraw == response.data.draw ) {
            dashBoardResponse = response;
            $.each(response.data, function (fieldName, value) {
                $(`.${fieldName}-widget`).find('h3').html(value);
            });
        }
    }).fail(function (response) {
        //getDashboard();
    });
}
function getDashboardTransaction() {
    transactionResponse = '';
    $('.small-box').find('h3').html('<i class=" fa fa-refresh fa-spin"></i>');
    $.ajax({
        method: 'POST',
        url: '{{ url('admin/dashboard/transaction') }}?_token={{ csrf_token() }}&draw=' + responseDraw,
        data: $('#company-dashboard-form').serialize(),
        dataType: 'json'
    }).done(function(response) {
        if( responseDraw == response.data.draw ) {
            transactionResponse = response;
            replaceTransactionWidgets(response);
        }
    }).fail(function (response) {
        //getDashboardTransaction();
    });
}
function replaceTransactionWidgets(response) {
    if( $('.total_rtr-widget').find('h3').text() !== '' && response.data.draw && responseDraw == response.data.draw ) {
        var data = response.data ? response.data : {};
        $.each(response.data, function (fieldName, value) {
            if( fieldName == 'ctd_after_fee' || fieldName == 'portfolio_value' ) {
                var totalRTR        = $('.total_rtr-widget').find('h3').text();
                var ctd_after_fee   = data.ctd_after_fee ? data.ctd_after_fee : '0';
                var liquidity       = data.liquidity ? data.liquidity : '0';
                totalRTR = totalRTR ? totalRTR : '0';
                totalRTR = totalRTR.replace('$', '');
                //totalRTR = totalRTR.replaceAll(',', '');
                totalRTR = totalRTR.replace(/,/g, '');
                ctd_after_fee = ctd_after_fee.replace('$', '');
                //ctd_after_fee = ctd_after_fee.replaceAll(',', '');
                ctd_after_fee = ctd_after_fee.replace(/,/g, '');
                var expected_rtr = parseFloat(totalRTR) - parseFloat(ctd_after_fee);
                if( fieldName == 'ctd_after_fee' ) {
                    $(`.${fieldName}-widget`).find('h3').html(value);
                    fieldName = 'expected_rtr';
                    value  = expected_rtr;
                    value = '$' + ( value.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") );
                } else if( fieldName == 'portfolio_value' ) {
                    liquidity = liquidity.replace('$', '');
                    //liquidity = liquidity.replaceAll(',', '');
                    liquidity = liquidity.replace(/,/g, '');
                    value = parseFloat(expected_rtr) + parseFloat(liquidity);
                    value = '$' + ( value.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,") );
                }
            }
            $(`.${fieldName}-widget`).find('h3').html(value);
        });
    } else {
        setTimeout(function () {
            replaceTransactionWidgets(response);
        }, 400);
    }
}
function getCompanyDashboard() {
    responseDraw++;
    getDashboard();
    getDashboardTransaction();
    if($('#label').val()){
        $('.dashboard-companies').html('');
        $('.average_daily_balance-widget').parent().css("display","none");
        $('.investor_portfolio-widget').parent().css("display","none");
        $('.portfolio_value-widget').parent().css("display","none");
        $('.liquidity-widget').parent().css("display","none");
        $('.ctd_after_fee-widget').parent().css("display","block");
        $('.total_rtr-widget').parent().css("display","block");
        $('.expected_rtr-widget').parent().css("display","block");
        $('.total_investors-widget').parent().css("display","block");
        $('.total_merchants-widget').parent().css("display","block");
        $('.invested_amount-widget').parent().css("display","block");
        $('.current_invested_amount-widget').parent().css("display","block");
        $('.blended_rate-widget').parent().css("display","block");
        $('.velocity_distribution-widget').parent().css("display","block");
        $('.investor_distribution-widget').parent().css("display","block");
        $('.pactolus_distribution-widget').parent().css("display","block");
        $('.default_rate-widget').parent().css("display","block");
        $('.pending_investor_ach_debit_requested_amount-widget').parent().css("display","block");
        $('.pending_investor_ach_credit_requested_amount-widget').parent().css("display","block");
        $('.overpayment-widget').parent().css("display","block");
    }
    else{
        $('.average_daily_balance-widget').parent().css("display","block");
        $('.investor_portfolio-widget').parent().css("display","block");
        $('.portfolio_value-widget').parent().css("display","block");
        $('.liquidity-widget').parent().css("display","block");
        $.ajax({
            method: 'POST',
            url: '{{ url('admin/dashboard/company') }}?_token={{ csrf_token() }}&draw=' + responseDraw,
            data: $('#company-dashboard-form').serialize(),
            dataType: 'json'
        }).done(function(response) {
            if( responseDraw == response.draw ) {
                $('.dashboard-companies').html('');
                $.each(response.data, function (index, company) {
                    $('.dashboard-companies').append(
                        `<div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="small-box bg-clr4" style="background-color: #FFC0CB !important;">
                        <div class="inner">
                        <h3>${company.liquidity}</h3>
                        <p> ${company.name}  (${company.velocity}) </p>
                        </div>
                        <div class="icon">
                        <i class="fa fa-money"></i>
                        </div>
                        </div>
                        </div>`
                    );
                });
            }
        }).fail(function (response) {
            //getCompanyDashboard();
        });
    }
    if($('#account_filter').val()=='overpayment'){
        $('.portfolio_value-widget').parent().css("display","block");
        $('.liquidity-widget').parent().css("display","block");
        $('.ctd_after_fee-widget').parent().css("display","block");
        $('.total_rtr-widget').parent().css("display","block");
        $('.overpayment-widget').parent().css("display","block");
        $('.dashboard-companies').html('');
        $('.average_daily_balance-widget').parent().css("display","none");
        $('.expected_rtr-widget').parent().css("display","none");
        $('.total_investors-widget').parent().css("display","none");
        $('.total_merchants-widget').parent().css("display","none");
        $('.invested_amount-widget').parent().css("display","none");
        $('.current_invested_amount-widget').parent().css("display","none");
        $('.blended_rate-widget').parent().css("display","none");
        $('.velocity_distribution-widget').parent().css("display","none");
        $('.investor_distribution-widget').parent().css("display","none");
        $('.pactolus_distribution-widget').parent().css("display","none");
        $('.default_rate-widget').parent().css("display","none");
        $('.investor_portfolio-widget').parent().css("display","none");
        $('.pending_investor_ach_debit_requested_amount-widget').parent().css("display","none");
        $('.pending_investor_ach_credit_requested_amount-widget').parent().css("display","none");
    }
    else if($('#label').val()){
        $('.ctd_after_fee-widget').parent().css("display","block");
        $('.total_rtr-widget').parent().css("display","block");
        $('.expected_rtr-widget').parent().css("display","block");
        $('.total_investors-widget').parent().css("display","block");
        $('.total_merchants-widget').parent().css("display","block");
        $('.invested_amount-widget').parent().css("display","block");
        $('.current_invested_amount-widget').parent().css("display","block");
        $('.blended_rate-widget').parent().css("display","block");
        $('.velocity_distribution-widget').parent().css("display","block");
        $('.investor_distribution-widget').parent().css("display","block");
        $('.pactolus_distribution-widget').parent().css("display","block");
        $('.default_rate-widget').parent().css("display","block");
        $('.pending_investor_ach_debit_requested_amount-widget').parent().css("display","block");
        $('.pending_investor_ach_credit_requested_amount-widget').parent().css("display","block");
        $('.overpayment-widget').parent().css("display","block");
        $('.dashboard-companies').html('');
        $('.average_daily_balance-widget').parent().css("display","none");
        $('.investor_portfolio-widget').parent().css("display","none");
        $('.portfolio_value-widget').parent().css("display","none");
        $('.liquidity-widget').parent().css("display","none");
    }
    else{
        $('.ctd_after_fee-widget').parent().css("display","block");
        $('.total_rtr-widget').parent().css("display","block");
        $('.expected_rtr-widget').parent().css("display","block");
        $('.total_investors-widget').parent().css("display","block");
        $('.total_merchants-widget').parent().css("display","block");
        $('.invested_amount-widget').parent().css("display","block");
        $('.current_invested_amount-widget').parent().css("display","block");
        $('.blended_rate-widget').parent().css("display","block");
        $('.velocity_distribution-widget').parent().css("display","block");
        $('.investor_distribution-widget').parent().css("display","block");
        $('.pactolus_distribution-widget').parent().css("display","block");
        $('.default_rate-widget').parent().css("display","block");
        $('.pending_investor_ach_debit_requested_amount-widget').parent().css("display","block");
        $('.pending_investor_ach_credit_requested_amount-widget').parent().css("display","block");
        $('.overpayment-widget').parent().css("display","block");
        $('.average_daily_balance-widget').parent().css("display","block");
        $('.investor_portfolio-widget').parent().css("display","block");
        $('.portfolio_value-widget').parent().css("display","block");
        $('.liquidity-widget').parent().css("display","block");
        $.ajax({
            method: 'POST',
            url: '{{ url('admin/dashboard/company') }}?_token={{ csrf_token() }}&draw=' + responseDraw,
            data: $('#company-dashboard-form').serialize(),
            dataType: 'json'
        }).done(function(response) {
            if( responseDraw == response.draw ) {
                $('.dashboard-companies').html('');
                $.each(response.data, function (index, company) {
                    $('.dashboard-companies').append(
                        `<div class="col-md-4 col-sm-6 col-xs-12">
                        <div class="small-box bg-clr4" style="background-color: #FFC0CB !important;">
                        <div class="inner">
                        <h3>${company.liquidity}</h3>
                        <p> ${company.name}  (${company.velocity}) </p>
                        </div>
                        <div class="icon">
                        <i class="fa fa-money"></i>
                        </div>
                        </div>
                        </div>`
                    );
                });
            }
        }).fail(function (response) {
            //getCompanyDashboard();
        });
    }
    return false;
}
</script>
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
