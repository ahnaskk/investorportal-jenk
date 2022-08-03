<?php use App\Merchant; ?>
<?php use App\Models\Views\MerchantUserView; ?>
<?php $balance=$overpayment=0; ?>
<?php $max_participant_fund_per = MerchantUserView::select(DB::raw('funded/sum(amount) as max_participant_fund_per'))->where('merchant_id',$MerchantUser[0]->merchant_id)->first()->max_participant_fund_per; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<input type="hidden" name="merchant_name" id="merchant_name" value="{{$this_merchants->name}}">
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Add Payment </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Add Payment </div>
    </a>
</div>
{{ Breadcrumbs::render('addPayment',$merchant) }}
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary box-sm-wrap no-shadow">
        <div class="col-md-6">
            <div class="box-body-sm">
                <div class="box-head">
                    <table class='table table-bordered table-hover table-striped  dataTable'>
                        <thead>
                            <?php 
                            $overpayment=0;
                            $agent_fee=0;
                            $balance=$MerchantUser->sum('invest_rtr')-$MerchantUser->sum('paid_participant_ishare');
                            $balance*=$max_participant_fund_per;
                            if($balance<0){
                                $overpayment=$balance*-1;
                                $overpayment=round($overpayment,2);
                                $balance=0;
                            }
                            ?>
                            <tr>
                                <th colspan="2">RTR</th>
                                <th class="text-right">{{FFM::dollar($MerchantUser->sum('invest_rtr')*$max_participant_fund_per)}}</th>
                            </tr>
                            <tr>
                                <th colspan="2">Syndicate Percentage</th>
                                <th class="text-right">
                                    <?php 
                                    $syndication_percent = ($this_merchants->funded) ? FFM::percent($MerchantUser->sum('amount')/$this_merchants->funded*100) : 0;
                                    ?>
                                    {{FFM::percent($syndication_percent)}}
                                </th>
                            </tr>
                            <tr>
                                <th colspan="2">Paid</th>
                                <th class="text-right">{{FFM::dollar($MerchantUser->sum('paid_participant_ishare')*$max_participant_fund_per)}}</th>
                            </tr>
                            <tr>
                                <th colspan="2">Payment Amount ( Default Amount )</th>
                                <th class="text-right copy" id="syndication_payment_check" title="To Participant ${{number_format($balance,2)}} To OverPayment {{ FFM::dollar($MerchantView->payment_amount-$balance) }}"> <a>{{FFM::dollar($MerchantView->payment_amount)}}</a> </th>
                            </tr>
                            <tr>
                                <th colspan="2"><?php if($overpayment) echo "Over Paid Amount"; else echo "Balance"; ?></th>
                                @if(!$overpayment)
                                <th class="text-right copy" title="${{number_format($balance*$max_participant_fund_per,4)}}" id="pending_payment_check"> <a>{{FFM::dollar($balance)}}</a> </th>
                                @else
                                <th class="text-right" title="${{number_format($balance,4)}}">{{FFM::dollar($overpayment)}} </th>
                                @endif
                            </tr>
                        </thead>
                    </table>
                    <div class="panel with-nav-tabs panel-default">
                        <div class="panel-heading">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#InvestorShareTab" data-toggle="tab">Investor Share</a></li>
                                <li><a href="#TransactionTab" data-toggle="tab">Transactions</a></li>
                            </ul>
                        </div>
                        <div class="panel-body">
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="InvestorShareTab">
                                    {!! $tableBuilder->table(['class' => 'table table-bordered table-hover table-striped','id'=>'investorShareTable'],true) !!}
                                    <table class='table table-bordered table-hover table-striped  dataTable'>
                                        <tr>
                                            <th colspan="2" class="text-right">Payment</th>
                                            <th class="text-right" id="PaymentDisplay">{{FFM::dollar($MerchantView->payment_amount)}} </th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th class="text-right">To Participant</th>
                                            <th class="text-right" id="ToParticipantDisplay">0</th>
                                        </tr>
                                        @if($merchant->agent_fee_applied==1)
                                        <tr>
                                            <th></th>
                                            <th class="text-right">Agent Fee</th>
                                            <th class="text-right" id="AgentFee">{{FFM::dollar($agent_fee)}} </th>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th></th>
                                            <th class="text-right">OverPayment</th>
                                            <th class="text-right" id="OverPaymentDisplay">{{FFM::dollar($overpayment)}} </th>
                                        </tr>
                                    </table>
                                    <p style="color:black">* Single Payment  Investor Share Details </p>
                                </div>
                                <div class="tab-pane fade" id="TransactionTab">
                                    <table class="table table-bordered table-hover table-striped" id="PaymentDataTable">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th class="text-right">Payment</th>
                                                <th class="text-right">Participant Share</th>
                                            </tr>
                                        </thead>
                                        <thead>
                                            <tr>
                                                <td>Total</td>
                                                <td class="text-right">{{FFM::dollar($ParticipentPayment->sum('payment'))}}</td>
                                                <td class="text-right">{{FFM::dollar($ParticipentPayment->sum('final_participant_share'))}}</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ParticipentPayment as $key => $value): ?>
                                                <tr>
                                                    <td>{{FFM::date($value->payment_date)}}</td>
                                                    <td class="text-right">{{FFM::dollar($value->payment)}}</td>
                                                    <td class="text-right">{{FFM::dollar($value->final_participant_share)}}</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box-body-sm">
                <div class="box-head">
                    @include('layouts.admin.partials.lte_alerts')
                </div>
                <!-- form start -->
                {!! Form::open(['route'=>'admin::payments::store', 'method'=>'POST','id'=>"formfield"]) !!}
                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        <input type="button" id="unselect" name="unselect" value="Unselect" class="btn btn-success">
                        <input type="button" id="select_all" name="select_all" value="Select All Investors" class="btn btn-success">
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputGroupBy">Company </label>
                    {!! Form::select('company',$companies,'',['class'=>'form-control js-company-placeholder','placeholder'=>'Select Company','id'=> 'company']) !!}
                </div>
                <?php
                $company=isset($_POST['company'])?$_POST['company']:'';
                $test=isset($_POST['user_id'])?$_POST['user_id']:'';
                ?>
                <div class="form-group">
                    <label for="exampleInputEmail1">Investor <font color="#FF0000"> * </font></label>
                    <select id="user_id" name="user_id[]" class="form-control" multiple="multiple">
                        @foreach($investors as $investor)
                        <option  {{ old("user_id")==$investor->user_id?'selected':''}} value="{{$investor->user_id}}">{{$investor->Investor->name}}</option>
                        @endforeach
                    </select>
                    <span id="error_message_for_investor" class="text-danger"></span>
                </div>
                <div class="form-box-styled">
                    <div class="form-group paym-creat ">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="Payment">Payment</label>
                                {!! Form::text('payment',$payment,['class'=>'form-control accept_digit_only','placeholder'=>'Enter Total Payment','id'=>'payment','min'=>0.01,'step'=>'0.01']) !!}
                            </div>
                            @if($merchant->agent_fee_applied!=1)
                            <div class="col-md-6">
                                <label for="Net Payment">Net Payment</label>
                                {!! Form::text('net_amount',0,['class'=>'form-control accept_digit_only','placeholder'=>'Enter Net Payment','id'=>'net_amount', 'readonly']) !!}
                            </div>
                            @endif
                            <div class="col-md-2" hidden>
                                <div class="col-md-2"> <br>
                                    <label>
                                        {{ Form::checkbox('debit', 'yes', 0,['id'=>'debit_payment_type','class'=>'debit_payment_type payment-check']) }}
                                        <span>Refund</span>
                                    </label>
                                </div>
                            </div>
                            @if($merchant->agent_fee_applied!=1)
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::text('debit_reason','',['class'=>'','id'=>'debit_reason','placeholder'=>"Enter Reason","class"=>"form-control"]) !!}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Date <font color="#FF0000"> * </font></label>
                        {!! Form::text('payment_date1',null,['id'=>'datepicker1', 'class'=>'form-control multi-datepicker','placeholder'=>'Select Payment Date','autocomplete'=>'off', 'readonly'=>true]) !!}
                        <input type="hidden" name="payment_date" id="datepicker" class="date_parse">
                        <span id="error_message_for_payment_date" class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputGroupBy">Rcode </label>
                        {!! Form::select('rcode',$rcodes,old('rcode'),['class'=>'form-control js-company-placeholder','placeholder'=>'Select Rcode','id'=> 'rcode']) !!}
                        <span id="error_message_for_rcode" class="text-danger"></span>
                    </div>
                    <?php $userId=Auth::user()->id;?>
                    {!! Form::hidden('creator_id',$userId) !!}
                    <div class="form-group">
                        <label for="exampleInputEmail1">Merchant</label>
                        <select id="merchant_id" name="merchant_id" class="form-control">
                            @foreach($merchants as $merchant)
                            <option  {{old('merchant_id')==$merchant->id?'selected':''}} value="{{$merchant->id}}">{{$merchant->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="btn-wrap btn-right">
                        <div class="btn-box">
                            <a href="{{URL::to('admin/merchants/view',$merchant_id)}}" class="btn btn-success">View merchant</a>
                            <input type="button" value="Create" class="btn btn-primary creaate-merc" id="paymentClick"  data-merchant_id="{{ $this_merchants->id }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            // $amount=\FFM::dollar($this_merchants->payment_amount);
                            // $amount = trim(str_replace( '$', '', $amount));
                            // $new_payment  = floatval(str_replace(',', '', $amount));
                            // $new_payment  = number_format(round($new_payment, 1), 2);
                            ?>
                            <!-- id="paymentClick"  -->
                            <!-- /.box-body -->
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirmPayment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-left:10px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <span id="paymentbox"></span>
                    <b>Do you really want to add payments again ?</b>
                </div>
                <div class="modal-footer">
                    <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                    <a href="javascript:void(0)" class="btn btn-primary" id="submit" data-bs-dismiss="modal">Yes</a>
                </div>
            </div>
        </div>
    </div>
    @stop
    @section('scripts')
    <script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{asset('js/custom/helper.js')}}"></script>
    <script src="{{asset('js/custom/bootstrap3.2.0.min.js')}}"></script>
    <!-- <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script> -->
    <script type="text/javascript">
    //Date picker
    $('#formfield').validate({
        messages: {
            payment : {
                step : 'Only two decimal places are allowed!'
            }
        }
    });
    var myDate5 = new Date(new Date().getTime()+(5*24*60*60*1000));
    var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
    var mask_format = maskDateFormat(default_date_format)
    $('#datepicker1').datepicker({
        //  autoclose: true,
        format : default_date_format.toLowerCase(),
        multidate: true,
        endDate: myDate5,
        clearBtn: true,
        todayBtn: "linked"
    });
    $('#datepicker1').val('');
    $('#datepicker1').datepicker('update');
    $('#datepicker1').siblings('.date_parse').val('');
    $('#datepicker1').on("change changeDate", function(){
        var val = $(this).val();
        if(val)
        {
            val = val.split(',');
            var new_arr = val.map(item => {
                let year = moment(item, default_date_format).year();
                if(year.toString().length == 1 || year.toString().length == 2) {
                    year = moment(year, 'YY').format('YYYY');
                }
                return moment(item, default_date_format).set('year', year).format(default_date_format); 
            });
            var new_arr1 = val.map(item => {
                let year = moment(item, default_date_format).year();
                if(year.toString().length == 1 || year.toString().length == 2) {
                    year = moment(year, 'YY').format('YYYY');
                }
                return moment(item, default_date_format).set('year', year).format('YYYY-MM-DD');
            });
            if(new_arr) {
                new_arr = new_arr.join(',');    
                new_arr1 = new_arr1.join(',');
                $(this).val(new_arr);
                $(this).datepicker('update');
                $(this).siblings('.date_parse').val(new_arr1);
                if($(this).valid() == false) {
                    $(this).val('');
                    $(this).datepicker('update');
                    $(this).siblings('.date_parse').val('');
                }
            }
        }else {
            $(this).siblings('.date_parse').val('');
        }
    });
    $("#unselect").click(function(e){
        $('#user_id').val('').trigger("change.select2");
        $('#user_id').change();
        $('#company').val('').trigger("change.select2");
        $('#payment').blur();
    });
    $('#select_all').click(function() {
        $('#company').val(0).change();
        // $('#user_id option').prop('selected',true).trigger("change.select2");
        // document.getElementById("error_message_for_investor").innerHTML = '';
        // $('#user_id').change();
        $('#payment').blur();
    });
    if(!$('#company').val()){
        $('#company').val(0);
    }
    $('#user_id').change(function(e) {
        $('#payment').blur();
    });
    $('#rcode').change(function(e) {
        var rcode  = $('#rcode').val();
        if(rcode !=""){
            $('#payment').val(0).keyup();
            $('#net_amount').val(0).keyup();
            $("#payment").attr('min',0);
            $('#payment').blur();
            document.getElementById("payment").readOnly = true;
            // document.getElementById("net_amount").readOnly = true;
        }else{
            document.getElementById("payment").readOnly = false;
            // document.getElementById("net_amount").readOnly = false;
        }
    });
    $('#payment').keypress(function(event) {
        if(event.which == 46 && $(this).val().indexOf('.') != -1) {
            event.preventDefault();
        } // prevent if already dot
        if(event.which == 44 && $(this).val().indexOf(',') != -1) {
            event.preventDefault();
        } // prevent if already comma
    });
    $('#payment').keyup(function(event) {
        // skip for arrow keys
        if(event.which >= 37 && event.which <= 40){
            event.preventDefault();
        }
        $(this).val(function(index, value) {
            value = value.replace(/,/g,'');
            return (value);
        });
    });
    //Commented since Net amount is readonly now
    // $('#net_amount').keyup(function(event) {
    //     // skip for arrow keys
    //     if(event.which >= 37 && event.which <= 40){
    //         event.preventDefault();
    //     }
    //     $(this).val(function(index, value) {
    //         value = value.replace(/,/g,'');
    //         return (value);
    //     });
    // });
    var URL_getInvestor = "{{ URL::to('admin/getAssignedInvestors') }}";
    $('#company').change(function(e) {
        var company=$('#company').val();
        var merchant_id=$('#merchant_id').val();
        var investors = [];
        if(company) {
            $.ajax({
                type: 'GET',
                data: {'merchantId': merchant_id, 'company':company, '_token': _token},
                url: URL_getInvestor,
                success: function (data) {
                    var result=data.items;
                    if(result.length) {
                        for(var i in result) {
                            investors.push(result[i].id);
                        }
                        if(investors!='') {
                            document.getElementById("error_message_for_investor").innerHTML = '';
                            $('#user_id').attr('selected','selected').val(investors).trigger('change.select2');
                            $('#payment').blur();
                            table.draw();
                        }
                    } else {
                        $('#user_id').val('').trigger("change.select2");
                        $('#user_id').change();
                    }
                },
                error: function (data) {
                    //alert('hi');
                }
            });
        } else {
            $('#user_id').val('').trigger("change.select2");
            $('#payment').blur();
        }
    });
    $('#company').change();
    $('#submit').on('click',function() {
        document.getElementById('paymentClick').disabled = true;
        $('#formfield').submit();
    });
    $('#formfield').keypress(function(event) {
        document.getElementById('paymentClick').disabled = false;
    });
    var URL_paymentCheckBox = "{{ URL::to('admin/payment/paymentCheck') }}";
    $('#user_id').on('change',function(event) {
        document.getElementById("error_message_for_investor").innerHTML = '';
    });
    var error = 0;
    $('#datepicker1').on('change changeDate',function(event) {
        document.getElementById("error_message_for_payment_date").innerHTML = '';
        let paymentDate = $('#datepicker').val().split(',');  
        for(date of paymentDate) {
            if(!moment(date, 'YYYY-MM-DD', true).isValid()) { // checking if selected date is valid or not.
                document.getElementById("error_message_for_payment_date").innerHTML = 'Please enter valid date';
                error += 1;
                break;
            }
            let newDate = new Date(date);
            if(newDate > myDate5) { //checking if given date is above max date.
                document.getElementById("error_message_for_payment_date").innerHTML = 'Please enter valid date';
                error += 1;
                break;
            }
        }
    });
    $('#rcode').on('change',function(event) {
        document.getElementById("error_message_for_rcode").innerHTML = '';
    });
    $('#paymentClick').on('click',function(event) {
        var error =0;
        var paymentDate=$('#datepicker').val();
        var payment =$('#payment').val();
        var rcode=$('#rcode').val();
        var debit=0;
        var msg='';
        var investorId=$('#user_id').val();
        if(investorId==null || investorId == ""){
            document.getElementById("error_message_for_investor").innerHTML = 'Please select investors';
            error += 1;
        } else {
            document.getElementById("error_message_for_investor").innerHTML = '';
            error += 0;
        }
        // alert(error);
        if(paymentDate==null || paymentDate==""){
            document.getElementById("error_message_for_payment_date").innerHTML = 'Please enter payment date';
            error += 1;
        } else if(paymentDate) {
            let dateArray = $('#datepicker').val().split(',');  
            for(date of dateArray) {
                if(!moment(date, 'YYYY-MM-DD', true).isValid()) { // checking if selected date is valid or not.
                    document.getElementById("error_message_for_payment_date").innerHTML = 'Please enter valid date';
                    error += 1;
                    break;
                }
                let newDate = new Date(date);
                if(newDate > myDate5) { //checking if given date is above max date.
                    document.getElementById("error_message_for_payment_date").innerHTML = 'Please enter valid date';
                    error += 1;
                    break;
                }
            }
        }else {
            document.getElementById("error_message_for_payment_date").innerHTML = '';
            error += 0;
        }
        var merchantId=$('#paymentClick').data('merchant_id');
        var company = $('#company').val();
        if(payment<0.01 && rcode =='') {
            $("#payment").attr('min',0.01);
            $('#payment').blur();
            error += 1;
        } else {
            $("#payment").attr('min',0);
            $('#payment').blur();
            error += 0;
        }
        if(payment==0 && rcode =='') {
            document.getElementById("error_message_for_rcode").innerHTML = 'Please Select Rcode';
            error += 1;
        } else {
            document.getElementById("error_message_for_rcode").innerHTML = '';
            error += 0;
        }
        var debit_payment_type = $('#debit_payment_type').is(':checked');
        if(debit_payment_type==false){
            debit_payment_type = "no";
            debit=0;
        } else {
            debit=1;
            debit_payment_type = "yes";
        }
        var html='';
        var merchant_name = $('#merchant_name').val();
        if(error==0 || debit==1){
            $.ajax({
                type: 'POST',
                data: {'merchantId': merchantId,'paymentDate':paymentDate,'_token': _token,'investor_id':investorId,'debit_status':debit_payment_type},
                url: URL_paymentCheckBox,
                success: function (data) {
                    if(data.status == 1) {
                        html+='<div style="padding-left:10px">There is a payment by <b>'+merchant_name+' </b> on '+data.msg+' is already there.</div>';
                        //msg+=' '+ data.count +' payments ?';
                        //$('#paymentcount').html(msg);
                        $('#paymentbox').html(html);
                        $('#confirmPayment').modal('show');
                    } else if(data.status == 0) {
                        document.getElementById('paymentClick').disabled = true;
                        $('#formfield').submit();
                    }
                }
            });
        }
        //     var paymentDate=$('#datepicker').val();
        //     var merchantId=$('#paymentClick').data('merchant_id');
        //     var investorArray=@json($investors);
        //     var investorId=$('#user_id').val();
        //     var html='';
        //    // alert(merchantId);
        // $.ajax({
        //         type: 'POST',
        //         data: {'merchantId': merchantId,'paymentDate':paymentDate,'investorArray':investorArray, '_token': _token,'investor_id':investorId},
        //         url: URL_paymentCheckBox,
        //         success: function (data) {
        //            if(data.status == 1)
        //             {
        //                   $.each(data.result, function (i, val) {
        //                      html+='<div style="padding-left:10px">There is a payment of <b>'+val.investor_name+ '</b> ($'+val.payment+')  already there <b>'+val.investor_name+'</b> ('+val.name+')</div>';
        //                    });
        //                  $('#paymentbox').html(html);
        //                  $('#confirmPayment').modal('show');
        //             }
        //             else if(data.status == 0)
        //             {
        //                   $('#formfield').submit();
        //             }
        //         }
        //  });
        //$('#formfield').submit();
    });
    /*function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}*/
$(".accept_digit_only").keypress(function (evt) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode(key);
    if (key.length == 0) return;
    var regex = /^[0-9.,\b]+$/;
    if (!regex.test(key)) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault) theEvent.preventDefault();
    }
});
</script>
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
var table = window.LaravelDataTables["investorShareTable"];
$(document).ready(function() {
    $('#datepicker1').val('');
    $('#user_id').on('change',function(e) { e.preventDefault();
        table.draw();
    });
    $('#payment').on('blur',function(e) { e.preventDefault();
        if($('#payment').valid()){
            table.draw();
        }
    });
    $('#debit_payment_type').on('change',function(e) { e.preventDefault();
        table.draw();
    });
    $('#net_payment').on('change',function(e) { e.preventDefault();
        table.draw();
    });
});
</script>
<script type="text/javascript">
$('#pending_payment_check').click(function(){
    var balance="{{round($balance,2)}}";
    $('#payment').val(balance).blur();
});
$('#syndication_payment_check').click(function(){
    var balance="{{round($MerchantView->payment_amount,2)}}";
    $('#payment').val(balance).blur();
});
</script>
<script type="text/javascript">
$(document).ready(function(){
    var PaymentDataTable=$('#PaymentDataTable').DataTable( {
        'scrollY'      :'34vh',
        scrollX        :true,
        paging         :false,
        bInfo          :true,
        searching      :false,
        ordering       :true,
        fixedColumns   :true,
    });
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });
});
var Url_NetPaymentCalculation = "{{ route('admin::merchants::NetAmountCalculation') }}";
//Commented since Net amount is readonly now
// $('#net_amount').blur(function(){
//     var net_amount=$(this).val();
//     if(!$(this).val()) {
//         net_amount=0;
//     }
//     var investorId=$('#user_id').val();
//     var merchantId=$('#paymentClick').data('merchant_id');
//     $.ajax({
//         type: 'POST',
//         data: {
//             '_token'     : _token,
//             'merchant_id': "{{$merchant_id}}",
//             'investor_id':investorId,
//             'payment'    :net_amount,
//             'net_payment':true,
//         },
//         url: Url_NetPaymentCalculation,
//         success: function (data) {
//             $('#payment').val(data.payment).blur();
//         }
//     });
// });
$('#payment').blur(function(){
    if($('#payment').valid()) {
        var payment=$(this).val();
        if(!$(this).val()) {
            payment=0;
        }
        var investorId=$('#user_id').val();
        var merchantId=$('#paymentClick').data('merchant_id');
        $.ajax({
            type: 'POST',
            data: {
                '_token'     : _token,
                'merchant_id': "{{$merchant_id}}",
                'investor_id':investorId,
                'payment'    :payment,
            },
            url: Url_NetPaymentCalculation,
            success: function (data) {
                $('#net_amount').val(data.net_amount);
            }
        });
    }
});
</script>
@stop
@section('styles')
<!-- <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css"> -->
<link href="{{ asset('/css/optimized/merchant_payment.css?ver=6') }}" rel="stylesheet" id="bootstrap-css">
<!-- <link href="{{url('payment/css/bootstrap.min.css?ver=5')}}" rel="stylesheet"> -->
<link href="{{ asset('/payment/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap-css"/>
<style media="screen">

table {
    width: 100% !important;
}
.copy {
    cursor: copy;
}
.form-control.multi-datepicker[readonly] {
    cursor: inherit;
    background-color: inherit;
}
</style>
@stop
