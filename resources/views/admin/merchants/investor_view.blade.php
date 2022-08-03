@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Merchant Investor View </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Merchant Investor View</div>
    </a>
</div>
<div class="col-md-12">
    <div class="box box-primary"> <br>
        <div class="grid table-responsive">
            <div class="row">
                <div class="col-md-12">
                    <div id="investmentProgressbar"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-4">
                        <table class="table table-list-search table-bordered text-capitalize">
                            <tbody>
                                <tr>
                                    <th class="text-right">Merchant</th>
                                    <th class=""> <a target="_blank" href="{{url('admin/merchants/view/'.$Self->merchant_id)}}">{{$Self->Merchant}}</a> </th>
                                </tr>
                                <tr>
                                    <th class="text-right">Industry</th>
                                    <th class="">{{$Self->Industry}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">Investor</th>
                                    <th class=""> <a target="_blank" href="{{url('admin/investors/portfolio/'.$Self->investor_id)}}">{{$Self->Investor}}</a> </th>
                                </tr>
                                <tr>
                                    <th class="text-right">company</th>
                                    <th class="">{{$Self->CompanyModal->name}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">Creator</th>
                                    <th class="">{{$Self->Creator}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">SubState</th>
                                    <th class="">{{$Self->SubState}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">date funded</th>
                                    <th class="">{{$Self->date_funded}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">Actual share %</th>
                                    <th class="text-right">{{$Self->share}}</th>
                                </tr>
                                @if($Self->share-$Self->investor_share_percentage)
                                <tr>
                                    <th class="text-right">expected share %</th>
                                    <th class="text-right">{{$Self->investor_share_percentage}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">created at</th>
                                    <th class="">{{$Self->created_at}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">updated at</th>
                                    <th class="">{{$Self->updated_at}}</th>
                                </tr>
                                @endif
                                <tr>
                                    <th class="text-right">Merchant Funded Amount</th>
                                    <th class="text-right">{{$Self->funded}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">prepaid status</th>
                                    <th class="">{{($Self->s_prepaid_status==1)?"RTR":"Amount"}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">status</th>
                                    <th class="">{{$Self->StatusName}}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <table class="table table-list-search table-bordered text-capitalize">
                            <tr>
                                <th class="text-right">amount</th>
                                <th class="text-right">{{$Self->amount}}</th>
                            </tr>
                            <tr>
                                <th class="text-right">commission</th>
                                <th class=""><span class="pull-left">({{$Self->commission_per}}%)</span><span class="pull-right">{{$Self->commission_amount}}</span></th>
                            </tr>
                            <tr>
                                <th class="text-right">under writing Fee</th>
                                <th class=""><span class="pull-left">({{$Self->under_writing_fee_per}}%)</span><span class="pull-right">{{$Self->under_writing_fee}}</span></th>
                            </tr>
                            <tr>
                                <th class="text-right">Up Sell Commission</th>
                                <th class=""><span class="pull-left">({{$Self->up_sell_commission_per}}%)</span><span class="pull-right">{{$Self->up_sell_commission}}</span></th>
                            </tr>
                            <tr>
                                <th class="text-right">syndication fee/Pre Paid</th>
                                <th class=""><span class="pull-left">({{$Self->syndication_fee_percentage}}%)</span><span class="pull-right">{{$Self->pre_paid}}</span></th>
                            </tr>
                            <tr>
                                <th class="text-right">total investment</th>
                                <th class="text-right">${{number_format($Self->total_investment,2)}}</th>
                            </tr>
                            <tr>
                                <th class="text-right">Paid Principal</th>
                                <th class="text-right">${{number_format($Payments->sum('principal'),2)}}</th>
                            </tr>
                            <tr>
                                <th class="text-right">Saved Paid Principal</th>
                                <th class="text-right">${{number_format($Self->paid_principal,2)}}</th>
                            </tr>
                            <tr>
                                <th class="text-right">Saved Paid Profit</th>
                                <th class="text-right">${{number_format($Self->paid_profit,2)}}</th>
                            </tr>
                            <tr>
                                <th class="text-right">Principal Balance</th>
                                <?php $principal_diffrence=$Self->total_investment-$Payments->sum('principal'); ?>
                                <th class="text-right">${{number_format($principal_diffrence,2)}}</th>
                            </tr>
                            <tr>
                                <th class="text-right">completed %</th>
                                <th class="text-right">{{$Self->complete_per}}</th>
                            </tr>
                            <tr>
                                @if($Self->complete_per-$Self->actual_completed_percentage)
                                <th class="text-right">Investor completed %</th>
                                <th class="text-right">{{$Self->actual_completed_percentage}}</th>
                                @else
                                <th>.</th>
                                <th>.</th>
                                @endif
                            </tr>
                        </table>
                        <table class="table table-list-search table-bordered text-capitalize">
                            <tbody>
                                <tr>
                                    <th class="text-right">%</th>
                                    <th class="text-right">Investor rtr</th>
                                    <th class="text-right">paid</th>
                                    <th class="text-right">{{($Self->user_balance_amount>0)?"Overpayment":"Balance"}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">{{$Self->investor_share_percentage}}</th>
                                    <th class="text-right">{{FFM::dollar($Self->invest_rtr)}}</th>
                                    <th class="text-right">{{FFM::dollar($Self->paid_participant_ishare)}}</th>
                                    <th class="text-right">{{FFM::dollar($Self->user_balance_amount*-1)}}</th>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-list-search table-bordered text-capitalize">
                            <tbody>
                                <tr>
                                    <th class="text-center" colspan="4">Management fee</th>
                                </tr>
                                <tr>
                                    <th class="text-right">%</th>
                                    <th class="text-right">expected</th>
                                    <th class="text-right">paid</th>
                                    <th class="text-right">{{($Self->mgmnt_fee_diff>0)?"Overpayment":"Balance"}}</th>
                                </tr>
                                <tr>
                                    <th class="text-right">{{$Self->mgmnt_fee}}</th>
                                    <th class="text-right">{{FFM::dollar($Self->expected_mgmnt_fee_amount)}}</th>
                                    <th class="text-right">{{FFM::dollar($Self->paid_mgmnt_fee)}}</th>
                                    <th class="text-right">{{FFM::dollar($Self->mgmnt_fee_diff*-1)}}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <?php 
                            $overpayment=$Self->paid_participant_ishare-$Self->invest_rtr;
                            if($overpayment<0){ $overpayment=0; }
                            ?>
                            <div id="principal_circle" class="pie_progress" role="progressbar" data-barcolor="#2c97c4" data-barsize="10" aria-valuemin="0" aria-valuemax="{{$Self->total_investment}}">
                                <div class="pie_progress__number">0%</div>
                                <div class="pie_progress__label">Principal</div>
                            </div>
                            <?php $invest_rtr=$Self->invest_rtr; ?>
                            @if(round($Self->invest_rtr,2)==0)
                            <?php $invest_rtr=$Self->paid_participant_ishare; ?>
                            @endif
                            <div id="profit_circle" class="pie_progress" role="progressbar" data-barcolor="#3daf2c" data-barsize="10" aria-valuemin="0" aria-valuemax="{{$invest_rtr-$Self->total_investment-$Self->expected_mgmnt_fee_amount}}">
                                <div class="pie_progress__number">0%</div>
                                <div class="pie_progress__label">Profit</div>
                            </div>
                            <div id="total_circle" class="pie_progress" role="progressbar" data-barcolor="#0dcaf0" data-barsize="10" aria-valuemax="{{round($invest_rtr,2)}}">
                                <div class="pie_progress__number">0%</div>
                                <div class="pie_progress__label">Total</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-12">
                        <table class="table table-list-search table-bordered text-capitalize">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th class="text-right">participant share</th>
                                    <th class="text-right">Management fee</th>
                                    <th class="text-right">Expected Management fee</th>
                                    <th class="text-right">syndication fee</th>
                                    <th class="text-right">overpayment</th>
                                    <th class="text-right">principal</th>
                                    <th class="text-right">profit</th>
                                    <th class="text-right">Net Effect</th>
                                    <th class="text-right">balance</th>
                                </tr>
                            </thead>
                            @if(!empty($Payments->toArray()))
                            <tbody>
                                <tr>
                                    <th class="text-right">{{ count($Payments->toArray()) }}</th>
                                    <th class="text-right">Total</th>
                                    <?php $total_participant_share =$Payments->sum('participant_share'); ?>
                                    <?php $total_mgmnt_fee         =$Payments->sum('mgmnt_fee'); ?>
                                    <?php $total_overpayment       =$Payments->sum('overpayment'); ?>
                                    <?php $total_principal         =$Payments->sum('principal'); ?>
                                    <?php $total_profit            =$Payments->sum('profit'); ?>
                                    <?php $total_net_effect=$total_participant_share-$total_mgmnt_fee-$total_principal-$total_profit; ?>
                                    <th class="text-right">${{number_format($total_participant_share,2)}}</th>
                                    <th class="text-right">${{number_format($total_mgmnt_fee,2)}}</th>
                                    <?php $total_expected_management_fee=$total_participant_share*$Payments[0]->MerchantUser->mgmnt_fee/100; ?>
                                    <th class="text-right">
                                        ${{number_format($total_expected_management_fee,4)}} <br>
                                    </th>
                                    <th class="text-right">${{number_format($Payments->sum('syndication_fee'),2)}}</th>
                                    <th class="text-right">${{number_format($total_overpayment,2)}}</th>
                                    <th class="text-right">${{number_format($total_principal,2)}}</th>
                                    <th class="text-right">${{number_format($total_profit,2)}}</th>
                                    <th class="text-right">${{number_format($total_net_effect,2)}}</th>
                                    <th class="text-right"></th>
                                </tr>
                            </tbody>
                            <tbody>
                                <?php foreach ($Payments as $key => $value): ?>
                                    <tr>
                                        <th class="text-right">{{ $key+1 }}</th>
                                        <th title="{{$value->created_at}}">{{ $value->ParticipentPayment?FFM::date($value->ParticipentPayment->payment_date):"No ParticipentPayment ".$value->participent_payment_id}}</th>
                                        <?php $participant_share =$value->participant_share; ?>
                                        <?php $mgmnt_fee         =$value->mgmnt_fee; ?>
                                        <?php $overpayment       =$value->overpayment; ?>
                                        <?php $principal         =$value->principal; ?>
                                        <?php $profit            =$value->profit; ?>
                                        <?php $net_effect        =$participant_share-$mgmnt_fee-$principal-$profit; ?>
                                        <th class="text-right">${{number_format($participant_share,2)}}</th>
                                        <th class="text-right">${{number_format($mgmnt_fee,2)}}</th>
                                        <?php $expected_management_fee=$participant_share*$value->MerchantUser->mgmnt_fee/100; ?>
                                        <th class="text-right">
                                            ${{number_format($expected_management_fee,4)}}
                                            <br>
                                            <?php $mgmnt_fee_diffrence=$mgmnt_fee-$expected_management_fee; ?>
                                            @if($mgmnt_fee_diffrence)
                                            <i style="color:blue">{{number_format($mgmnt_fee_diffrence,4)}}</i>
                                            @endif
                                        </th>
                                        <th class="text-right">${{number_format($value->syndication_fee,2)}}</th>
                                        <th class="text-right">${{number_format($overpayment,2)}}</th>
                                        <th class="text-right">${{number_format($principal,2)}}</th>
                                        <th class="text-right">${{number_format($profit,2)}}</th>
                                        <th class="text-right">${{number_format($net_effect,2)}}</th>
                                        <th class="text-right">${{number_format($value->balance,2)}}</th>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ url('/js/jquery.stepProgressBar.js') }}"></script>
<script src="{{ url('/js/jquery-asPieProgress.js') }}"></script>
<script type="text/javascript">
Swal.fire('info!', 'This is Only For Debugging Purposes', 'info');
</script>
<script>
$('#investmentProgressbar').stepProgressBar({
    currentValue: '0',
    steps: [
        { topLabel: 'Start', value: 0},
        @if($Self->total_investment+$Self->paid_mgmnt_fee)
        { topLabel: 'Principal', value: {{$Self->total_investment+$Self->paid_mgmnt_fee}}},
        @endif
        @if(round($Self->invest_rtr,2))
        { topLabel: 'RTR', value: {{round($Self->invest_rtr,2)}}},
        @endif
        @if($overpayment)
        { topLabel: 'Overpayment', value: {{$Self->invest_rtr+$overpayment}}},
        @endif
    ],
    unit: '$'
});
$('#investmentProgressbar').stepProgressBar('setCurrentValue', {{$Self->paid_participant_ishare}});
</script>
<script type="text/javascript">
jQuery(function($) {
    $('.pie_progress').asPieProgress({
        namespace: 'pie_progress'
    });
    $('.pie_progress').asPieProgress('start');
    $('#principal_circle').asPieProgress('go', {{round($Self->paid_principal,2)}});
    $('#profit_circle').asPieProgress('go', {{round($Self->paid_profit,2)}});
    $('#total_circle').asPieProgress('go', {{round($Self->paid_participant_ishare,2)}});
});
</script>
@stop
@section('styles')
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="{{ url('/css/jquery.stepProgressBar.css') }}">
<link rel="stylesheet" href="{{ url('/css/asPieProgress.css') }}">
<style media="screen">
.pie_progress {
    width: 200px;
    margin: 10px auto;
}
@media all and (max-width: 768px) {
    .pie_progress {
        width: 80%;
        max-width: 300px;
    }
}
</style>
@stop
