@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Merchant Investor Edit </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Merchant Investor Edit</div>
    </a>
</div>
{{ Breadcrumbs::render('merchantInvestorEdit',$merchant_arr) }}
<div class="col-md-12">
    <div class="box box-primary">
        {!! Form::open(['route'=>'admin::merchant_investor::update', 'method'=>'POST','id'=>'update_form']) !!}
        <input type="hidden" name="id" value="{{$merchant->id}}" />
        @include('layouts.admin.partials.lte_alerts')
        <div class="box-body ">
            <div class="merchant-head ">
                <div class="row">
                    <div class="col-md-6">
                        <label for="exampleInputEmail1"><span>Merchant Name:</span> {{ $merchant_arr->name }}</label>
                    </div>
                    <div class=" col-md-6">
                        <div class="commission">
                            <label for="exampleInputEmail1">Factor Rate : {{ number_format($MerchantUser->Merchant->factor_rate,2) }}</label>
                        </div>
                        <div class="commission">
                            <label for="exampleInputEmail1">Commission : {{ FFM::percent($merchant_arr->commission) }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="exampleInputEmail1">Investor Name</label>
                    <select id="user_id" name="user_id" class="form-control" disabled>
                        <option>Select An Investor</option>
                        @foreach($investors as $investor)
                        <option data-management-fee="{{$investor->management_fee}}"
                            data-synd-fee='{{$investor->global_syndication}}' data-name='{{$investor->name}}'
                            {{old("user_id")==$investor->id?'selected': ($merchant->user_id==$investor->id?'selected':'') }}
                            value="{{$investor->id}}">{{$investor->name}}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="exampleInputEmail1">Management Fee</label>
                    <div class="input-group">
                        {!! Form::select('mgmnt_fee',$syndication_fee_values,isset($merchant)?
                        number_format($merchant->mgmnt_fee,2) :
                        old('mgmnt_fee'),['class'=>'form-control','id'=>'mgmnt_fee',$p1_status]) !!}
                        <span class="input-group-text">%</span>
                        @if($p1_status)
                        <input type="hidden" name="mgmnt_fee" value="{{ isset($merchant)? number_format($merchant->mgmnt_fee,2) : old('mgmnt_fee') }}">
                        @endif
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="exampleInputEmail1">Funding Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        {!! Form::number('amount',$amount,['class'=>'form-control','id'=>'amount',$p2_status,'step'=> .01 ,'min'=> 1,'required' ]) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-2">
                    <label for="exampleInputEmail1">Underwriting Fee</label>
                    <div class="input-group">
                        {!! Form::select('underwriting_fee',$syndication_fee_values,isset($merchant)? number_format($under_writing_fee_per ,2): old('underwriting_fee'),['class'=>'form-control table_change','id'=>'underwriting_fee',$p1_status]) !!}
                        @if($p1_status)
                        <input type="hidden" name="underwriting_fee" value="{{ isset($merchant) ? number_format($under_writing_fee_per ,2): old('underwriting_fee') }}">
                        @endif
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <label for="exampleInputEmail1">Upsell Commission</label>
                    <div class="input-group">
                        {!! Form::select('up_sell_commission_per',$upsell_commission_values,$up_sell_commission_per,['class'=>'form-control table_change','id'=>'up_sell_commission_per','pattern'=>"^-?[0-9]\d*(\.\d+)?$",$p1_status]) !!}
                        @if($p1_status)
                        <input type="hidden" name="up_sell_commission_per" value="{{ $up_sell_commission_per }}">
                        @endif
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="form-group col-md-8 synd-march">
                    <label for="exampleInputEmail1">Syndication Fee </label>
                    <div class="input-group">
                        {!! Form::select('syndication_fee',$syndication_fee_values,isset($merchant)? number_format($merchant->syndication_fee_percentage,2) : old('syndication_fee'),['class'=>'form-control table_change col-md-4','id'=>'syndication_fee',$p1_status]) !!}
                        @if($p1_status)
                        <input type="hidden" name="syndication_fee" value="{{ isset($merchant)? number_format($merchant->syndication_fee_percentage,2) : old('syndication_fee') }}">
                        @endif
                        <div class="mrch">
                            <span class="input-group-text">%</span>
                            <!-- <span class="input-group-text"><label>
                            <input {{old('s_prepaid_status') == 0 ?'checked':''}}  value="0" type="radio" class="form-check-input"  name="s_prepaid_status" id="s_prepaid_none"/> None?</label></span> -->
                            <span class="input-group-text col-md-4">
                                <label>
                                    <input {{old('s_prepaid_status')==2?'checked':(isset($merchant)?($merchant->s_prepaid_status==2?'checked':''):'')}} value="2" type="radio" class="table_change" name="s_prepaid_status" id="s_prepaid_amount" {{ $p1_status }}> On Funding Amount?
                                </label>
                            </span>
                            <span class="input-group-text col-md-4">
                                <label>
                                    <input {{old('s_prepaid_status')==1?'checked':(isset($merchant)?($merchant->s_prepaid_status==1?'checked':''):'')}} value="1" type="radio" class="table_change" name="s_prepaid_status" id="s_prepaid_rtr" {{ $p1_status }}> On RTR?
                                </label>
                            </span>
                        </div>
                    </div>
                    <input type="hidden" name="merchant_id" value="{{$merchant->merchant_id}}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 btn-wrap btn-right">
                    <div class="btn-box">
                        <div class="col-md-3">
                            <?php $title="Maximum participant percentage will be change";?>
                            {!! Form::submit('Force Update',['class'=>'btn btn-danger ub-bt','name'=>'force_update','value'=>1,$p2_status,'id'=>'force_update_button']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::submit('Update',['class'=>'btn btn-primary ub-bt','',$p2_status]) !!}
                        </div>
                        <div class="col-md-3">
                            <a class="btn btn-info" href="{{URL::to('admin/merchants/view',$merchant->merchant_id)}}">Go Back To Merchant View</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4 synd-march">
                    <table class="table table-list-search table-bordered text-capitalize">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-right">Before</th>
                                <th class="text-right">After</th>
                                <th class="text-right">Change</th>
                            </tr>
                            <tr>
                                <th>Liquidity</th>
                                <th class="text-right">{{ FFM::dollar($liquidity) }}</th>
                                <th class="text-right" id="table_liquidity">{{ FFM::dollar(0) }}</th>
                                <th class="text-right" id="table_liquidity_change">{{ FFM::dollar(0) }}</th>
                            </tr>
                            <tr>
                                <th>Investment</th>
                                <?php $investor_investment=$MerchantUser->investment_total; ?>
                                <th class="text-right">{{ FFM::dollar($investor_investment) }}</th>
                                <th class="text-right" id="table_total_investment">{{ FFM::dollar(0) }}</th>
                                <th class="text-right" id="table_total_investment_change">{{ FFM::dollar(0) }}</th>
                            </tr>
                            <tr>
                                <th>Company Share</th>
                                <?php $company_share=$MerchantUser->CompanyAmount->max_participant;  ?>
                                <th class="text-right">{{ FFM::dollar($company_share) }}</th>
                                <th class="text-right" id="table_company_max_participant">{{ FFM::dollar(0) }}</th>
                                <th class="text-right" id="table_company_max_participant_change">{{ FFM::dollar(0) }}</th>
                            </tr>
                            <tr>
                                <th>Other Investment</th>
                                <?php $other_investment = $MerchantUser->CompanyOtherInvestors->sum('amount'); ?>
                                <th class="text-right">{{ FFM::dollar($other_investment) }}</th>
                                <th class="text-right">{{ FFM::dollar($other_investment) }}</th>
                                <th class="text-right">-</th>
                            </tr>
                            <tr>
                                <th>Company Invested Share</th>
                                <?php $investor_funded  = $MerchantUser->amount; ?>
                                <?php $total_investment = $other_investment+$investor_funded; ?>
                                <th class="text-right">{{ FFM::dollar($total_investment) }}</th>
                                <th class="text-right" id="table_company_invested_share">{{ FFM::dollar(0) }}</th>
                                <th class="text-right" id="table_company_invested_share_change">{{ FFM::dollar(0) }}</th>
                            </tr>
                            <tr>
                                <th>Merchant Funded</th>
                                <th class="text-right">{{ FFM::dollar($MerchantUser->Merchant->funded) }}</th>
                                <th class="text-right" id="table_funded">{{ FFM::dollar(0) }}</th>
                                <th class="text-right" id="table_funded_change">{{ FFM::dollar(0) }}</th>
                            </tr>
                            <tr>
                                <th>Merchant Maximum Participant Share</th>
                                <th class="text-right">{{ FFM::dollar($MerchantUser->Merchant->max_participant_fund) }}</th>
                                <th class="text-right" id="table_max_participant_fund">{{ FFM::dollar(0) }}</th>
                                <th class="text-right" id="table_max_participant_fund_change">{{ FFM::dollar(0) }}</th>
                            </tr>
                            <tr>
                                <th>Investor Funded</th>
                                <th class="text-right">{{ FFM::dollar($investor_funded) }}</th>
                                <th class="text-right" id="table_investor_funded">{{ FFM::dollar(0) }}</th>
                                <th class="text-right" id="table_investor_funded_change">{{ FFM::dollar(0) }}</th>
                            </tr>
                            <tr>
                                <th>Investor RTR</th>
                                <th class="text-right">{{ FFM::dollar($MerchantUser->invest_rtr) }}</th>
                                <th class="text-right" id="table_invest_rtr">{{ FFM::dollar(0) }}</th>
                                <th class="text-right" id="table_invest_rtr_change">{{ FFM::dollar(0) }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="form-group col-md-4 synd-march">
                    <p>Force update will change</p>
                    <ul>
                        <li>Company Available Share</li>
                        <li>Merchant Maximum Participant Share</li>
                    </ul>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
@section('scripts')
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="text/javascript">
$('#update_form').submit(function (e) {
    if(new_liqduidity<0){
        if(!confirm("Liquidity will be $"+addCommas(new_liqduidity))){
            return false;
        }
    }
    if(new_liqduidity<0){
        if(!confirm("Liquidity will be $"+addCommas(new_liqduidity))){
            return false;
        }
    }
    var s_prepaid_status=$('input[name="s_prepaid_status"]').is(":checked");
    if(!s_prepaid_status){
        alert('Please choose syndication fee method of calculation(On Funding Amount/On RTR)')
        return false;
    }
})
$('#up_sell_commission_per').focus(() => {
    $('#up_sell_commission_per').mask("0.00");
});
$("#up_sell_commission_per").keypress(function (evt) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    if (key.length == 0) return;
    var regex = /^[0-9.,\b]+$/;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault) theEvent.preventDefault();
    }
    if (evt.which == 46 && $(this).val().indexOf('.') != -1) {
        evt.preventDefault();
    } // prevent if already dot
});

$('#update_form').validate({ // initialize the plugin
    errorClass: 'errors',
    rules: {
        amount: {
            min: 1,
            checkNumeric: true
        }
    },
    messages: {
        amount: {
            checkNumeric: "Please enter a valid number."
        }
    }
});
</script>
<script type="text/javascript">
//Date picker
$('#datepicker').datepicker({
    autoclose: true,
    format: "yyyy-mm-dd",
    clearBtn: true,
    todayBtn: "linked"
});
$('#user_id').change(function () {
    $investor_id = $('#user_id').val();
    $merchant_id = '<?php echo $merchant->merchant_id ?>';
    if ($investor_id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/admin/investors/investorFee',
            type: 'POST',
            /* send the csrf-token and the input to the controller */
            data: {
                user_id: $("#user_id").val(),
                merchant_id: $merchant_id
            },
            success: function (data) {
                $('#mgmnt_fee').val(data.management_fee).change();
                $('#syndication_fee').val(data.syndication_fee).change();
                if (data.s_prepaid_status == 2)
                $("#s_prepaid_amount").prop("checked", true);
                if (data.s_prepaid_status == 1)
                $("#s_prepaid_rtr").prop("checked", true);
                if (data.s_prepaid_status == 0)
                $("#s_prepaid_none").prop("checked", true);
            }
        });
    }
});
$('#user_id').change(function () {
    mgmnt_fee = $(this).find(':selected').data('management-fee');
    syndication_fee = $(this).find(':selected').data('synd-fee');
    participant_name = $(this).find(':selected').data('name');
    $('#mgmnt_fee').val(mgmnt_fee);
    $('#syndication_fee').val(syndication_fee);
    $('#participant_name').val(participant_name);
});
$("#commission_per").keypress(function (e) {
    if (e.which != 46 && e.which != 45 && e.which >= 37 && !(e.which >= 48 && e.which <= 57)) {
        return false;
    }
});
function addCommas(nStr) {
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}
$(document).on('keyup','#amount',function(){
    after_change_function();
});
$(document).on('change','#amount',function(){
    after_change_function();
});
$(document).on('change','.table_change',function(){
    after_change_function();
});
after_change_function();
function after_change_function() {
    var factor_rate                   = {{ $MerchantUser->Merchant->factor_rate }};
    var commission_amount_percentage  = {{ $MerchantUser->Merchant->commission }};
    var under_writing_fee_percentage  = $('#underwriting_fee').val();
    var s_prepaid_status              = $('input[name="s_prepaid_status"]').prop('checked');
    var pre_paid_percentage           = $('#syndication_fee').val();
    var up_sell_commission_percentage = $('#up_sell_commission_per').val();

    var old_funded    = {{ $investor_funded }};
    var new_funded    = $('#amount').val();
    var funded_change = (parseFloat(new_funded)-parseFloat(old_funded)).toFixed(2);

    var old_company_share    = {{ $company_share }};
    var new_company_share    = (parseFloat(old_company_share)-parseFloat(old_funded)+parseFloat(new_funded)).toFixed(2);
    var change_company_share = (parseFloat(new_company_share)-parseFloat(old_company_share)).toFixed(2);

    var other_investment              = {{ $other_investment }};
    var old_company_invested_share    = (parseFloat(other_investment)+parseFloat(old_funded)).toFixed(2);
    var new_company_invested_share    = (parseFloat(other_investment)+parseFloat(new_funded)).toFixed(2);
    var change_company_invested_share = (parseFloat(new_company_invested_share)-parseFloat(old_company_invested_share)).toFixed(2);

    var old_invest_rtr    = {{ $MerchantUser->invest_rtr }}
    var new_invest_rtr    = parseFloat(new_funded*factor_rate).toFixed(2);
    var change_invest_rtr = (parseFloat(new_invest_rtr)-parseFloat(old_invest_rtr)).toFixed(2);

    var other_company_share         = {{ $MerchantUser->Merchant->max_participant_fund-$company_share }};
    var old_max_participant_fund    = {{ $MerchantUser->Merchant->max_participant_fund }};
    var new_max_participant_fund    = (parseFloat(other_company_share)+parseFloat(new_company_share)).toFixed(2);
    var change_max_participant_fund = (parseFloat(new_max_participant_fund)-parseFloat(old_max_participant_fund)).toFixed(2);

    var old_merchant_funded = {{ $MerchantUser->Merchant->funded }};
    var new_merchant_funded = old_merchant_funded;
    if(new_max_participant_fund>old_merchant_funded){
        var new_merchant_funded = new_max_participant_fund;
    }
    var change_merchant_funded = (parseFloat(new_merchant_funded)-parseFloat(old_merchant_funded)).toFixed(2);

    var commission_amount  = parseFloat(commission_amount_percentage * new_funded/100).toFixed(2);
    var under_writing_fee  = parseFloat(under_writing_fee_percentage * new_funded/100).toFixed(2);
    if(s_prepaid_status){
        var pre_paid = parseFloat(pre_paid_percentage * new_funded/100).toFixed(2);
    } else {
        var pre_paid = parseFloat(pre_paid_percentage * new_invest_rtr/100).toFixed(2);
    }
    var up_sell_commission = parseFloat(up_sell_commission_percentage * new_funded/100).toFixed(2);

    old_investment        = {{ $investor_investment }};
    var new_investment    = parseFloat(new_funded)+parseFloat(commission_amount)+parseFloat(under_writing_fee)+parseFloat(pre_paid)+parseFloat(up_sell_commission);
    new_investment        = (new_investment).toFixed(2);
    var change_investment = (parseFloat(new_investment)-parseFloat(old_investment)).toFixed(2);

    available_liquidity   = {{ round($liquidity+$investor_investment,2); }};
    old_liqduidity        = {{ round($liquidity,2); }};
    new_liqduidity        = (parseFloat(available_liquidity,2)-parseFloat(new_investment,2)).toFixed(2);
    var change_liqduidity = (parseFloat(new_liqduidity)-parseFloat(old_liqduidity)).toFixed(2);
    $('#table_liquidity').text('$'+addCommas(new_liqduidity));
    $('#table_liquidity_change').text('$'+addCommas(change_liqduidity));

    $('#table_total_investment').text('$'+addCommas(new_investment));
    $('#table_total_investment_change').text('$'+addCommas(change_investment));

    $('#table_funded').text('$'+addCommas(new_merchant_funded));
    $('#table_funded_change').text('$'+addCommas(change_merchant_funded));

    $('#table_max_participant_fund').text('$'+addCommas(new_max_participant_fund));
    $('#table_max_participant_fund_change').text('$'+addCommas(change_max_participant_fund));

    $('#table_company_max_participant').text('$'+addCommas(new_company_share));
    $('#table_company_max_participant_change').text('$'+addCommas(change_company_share));

    $('#table_company_invested_share').text('$'+addCommas(new_company_invested_share));
    $('#table_company_invested_share_change').text('$'+addCommas(change_company_invested_share));

    $('#table_investor_funded').text('$'+addCommas(new_funded));
    $('#table_investor_funded_change').text('$'+addCommas(funded_change));

    $('#table_invest_rtr').text('$'+addCommas(new_invest_rtr));
    $('#table_invest_rtr_change').text('$'+addCommas(change_invest_rtr));

    $('#force_update_button').attr('title','');
    if(change_max_participant_fund>0){
        $('#force_update_button').attr('title','{{$title}}');
    }
}
</script>
@stop

@section('styles')
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
<link href="{{ asset('/css/optimized/merchant_view_edit.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
