@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Lender Payment Generation</h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Lender Payment Generation</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::payments::lender-payment-generation') }}
<div class="col-md-12">
    <div class="box box-primary">
        @include('layouts.admin.partials.lte_alerts')
        <div class="box-body">
            <div class="form-box-styled">      
                <div class="row">
                    {!! Form::open(['route'=>'admin::payments::lender-payment-generation', 'method'=>'POST','id'=>'filter_form']) !!}
                    {{ Form::hidden('pay', 'yes') }}
                    <div class="col-md-4">
                        <div class="input-group rb">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>
                            <select name="lenders[]" id="lenders"  class="form-control" multiple='multiple'>
                                @foreach($lenders as $key=> $lender)
                                <option <?PHP echo in_array($key,old("lenders") ?: $lender_arr)?'selected':0; ?> value="{{$key}}"> {{$lender}}  </option>
                                @endforeach
                            </select>
                        </div>
                        <span class="help-block">Lenders <font color="#FF0000"> * </font></span>
                    </div>
                    <div class='col-md-4'>
                        <div class="input-group rb">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            <!-- {!! Form::date('payment_date',$payment_date,['id'=>'datepicker', 'class'=>'form-control multi-datepicker datepicker','placeholder'=>'Select Payment Date','autocomplete'=>'off']) !!} -->
                            {!! Form::text('payment_date1',$payment_date?$payment_date:old('payment_date'),['id'=>'datepicker11', 'class'=>'form-control multi-datepicker payment_date','placeholder'=>'Select Payment Date','autocomplete'=>'off', 'readonly' => true]) !!}
                            <input type="hidden" name="payment_date" value="{{$payment_date?$payment_date:old('payment_date')}}" id="datepicker" class="date_parse payment_date">
                        </div>
                        <span class="help-block">Payment Date <font color="#FF0000"> * </font></span>
                    </div>
                    <div class='col-md-4'>
                        <div class="input-group rb">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            {!! Form::select('companies',$companies,isset($company)?$company:old('companies'),['class'=>'form-control','placeholder'=>'Select Company','id'=> 'company']) !!}
                        </div>
                        <span class="help-block">Companies <font color="#FF0000"> * </font></span>
                    </div>
                    <div class="col-md-12 btn-wrap btn-right">
                        <div class="btn-box" >
                            {!! Form::submit('View',['class'=>'btn btn-primary cre-cub-btn sub-intr']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        
        <?php $company=isset($_POST['companies'])?$_POST['companies']:''; ?>
        <div id="error_msg" class="box-body"></div>
        @if(count($merchant_details)>0)   
        <!-- {!! Form::open([ 'route'=>'admin::payments::add_payments_for_lenders', 'method'=>'POST', 'id'=>'lender_payment_generation_form' ]) !!} -->
        {!! Form::open([ 'id'=>'lender_payment_generation_form' ]) !!}
        <input type="hidden" name="company" value="{{ $company }}">
        <div class="box-body">
            <div class="form-row-wrap">
                <div class="form-row-head">
                    <div class="form-row-checkbox">
                        <div class="form-group">                  
                            <input type="checkbox" name="select_all" id="select_all">  
                        </div> 
                    </div> 
                    <div class="form-row-box">
                        <div class='col-md-2'>
                            <div class="form-group">
                                <label for="name">Merchant <i class="fa fa-user-o" aria-hidden="true"></i> <font color="#FF0000"> * </font></label>
                            </div> 
                        </div> 
                        <div class='col-md-2'>
                            <div class="form-group">
                                <label for="rate">Payment Date<i class="fa fa-re" aria-hidden="true"></i><font color="#FF0000"> * </font></label>
                            </div> 
                        </div>    
                        <div class='col-md-2'>
                            <div class="form-group">
                                
                                <label for="rate">Weekly Payment($)</label>
                            </div> 
                        </div>
                        <div class='col-md-1'>
                            <div class="form-group">
                                <?php $agent_arr = array_unique(array_column($merchant_details->toArray(), 'agent_fee_applied'));
                                $net_status = 1;
                                if(in_array(1,$agent_arr)){
                                    $net_status = 0;
                                }
                                ?>  
                                <label for="rate">Net Payment <font color="#FF0000"> * </font>
                                    @if($net_status==1)
                                    <input   type="checkbox" name="select_all_net" id="select_all_net">
                                    @endif 
                                </label>
                            </div> 
                        </div>
                        <div class='col-md-1'>
                            <div class="form-group">
                                <label for="rate">Daily payment($)<i class="fa fa-re" aria-hidden="true"></i><font color="#FF0000"> * </font></label>
                            </div> 
                        </div> 
                        <div class='col-md-2'>
                            <div class="form-group">
                                <label for="rate">Rcode<i class="fa fa-re" aria-hidden="true"></i><font color="#FF0000"> * </font></label>
                            </div> 
                        </div> 
                    </div>
                </div>
                {!! Form::hidden('merchant_count',count($merchant_details),['class'=>'form-control']) !!} 
                {!! Form::hidden('pay_date',$payment_date,['class'=>'form-control']) !!} 
                <?php $i=0; ?>
                <div>
                    @foreach($merchant_details as $merchant)
                    @if(isset($merchant->id))
                    <div class="form-row-td first-row" id="interst_div[]">
                        {!! Form::hidden('merchant_id',$merchant->id,['class'=>'form-control merchant_check','required']) !!} 
                        <div class="form-row-checkbox">
                            <div class="form-group">
                                <input type='checkbox' class='select_merchant' name="merchant[{{$merchant->id}}][select_merchant]" value="{{$merchant->id}}">
                            </div>
                        </div> 
                        <div class="form-row-box">
                            <div class='col-md-2'>
                                <div class="form-group">
                                    <?php
                                    if($merchant->investor_count >1){
                                        $mer_cnt = $merchant->investor_count.' Investors';
                                    }
                                    else{
                                        $mer_cnt = $merchant->investor_count.' Investor'; 
                                    }
                                    ?>
                                    <div class="name-inn"> <a href="{{URL::to('admin/merchants/view',$merchant->id)}}">{{strtoupper($merchant->name)}}  -  ({{$mer_cnt}}) ({{ FFM::date($merchant->date_funded) }})</a> </div>
                                </div>
                            </div> 
                            <div class='col-md-2'>
                                <div class="form-group">
                                    {!! Form::text("",$payment_date,['class'=>'form-control multi-datepicker checkDate payment_date1','placeholder'=>'Select Payment Date','autocomplete'=>'off', 'readonly'=>true]) !!}     
                                    <input type="hidden" name="merchant[{{$merchant->id}}][payment_date]" class="date_parse payment_date1" value="{{ $payment_date }}">
                                    
                                    <span id="error_message_for_payment_date" class="text-danger error_message_for_payment_date"></span>                 
                                </div>
                            </div>
                            {!! Form::hidden("merchant[$merchant->id][last_payment_date]",isset($merchant->last_payment_date)?$merchant->last_payment_date:$merchant->date_funded,['class'=>'form-control']) !!}  
                            {!! Form::hidden("merchant[$merchant->id][name]",$merchant->name,['class'=>'form-control']) !!}                    
                            <div class='col-md-2'>
                                <div class="form-group">
                                    <div class="name-inn">
                                        <!-- {!! Form::text("merchant[$merchant->id][amount1]",'',['class'=>'form-control rate name-inv accept_digit_only','id'=>$i.'_amount1']) !!}  -->
                                        <input type="text" class="form-control rate name-inv accept_digit_only" id='{{$i}}_amount1'>
                                        <span id="error_message_for_weekly_payment" class="text-danger error_message_for_weekly_payment"></span>  
                                    </div>
                                </div> 
                            </div>
                            <div class='col-md-1'>
                                <div class="form-group">
                                    <div class="name-inn">
                                        <!--{!! Form::button('Close',['class'=>'btn btn-success rowbutton name-inv','id'=>$i.'_row','name'=>$i.'_row','value'=>""]) !!} -->
                                        <!-- <input  data-toggle="toggle"  disabled="disabled" checked="checked" type="checkbox" data-on="Open" data-off="Close" class="net_payment" data-onstyle="success" data-offstyle="danger"> -->
                                        @if($merchant->agent_fee_applied!=1)
                                        <input   data-toggle="toggle"  value="yes" type="checkbox" data-on="Open" data-off="Close" class="net_payment" data-onstyle="success" data-offstyle="danger" name='merchant[{{$merchant->id}}][net_payment]'>
                                        @endif
                                    </div>
                                </div> 
                            </div>
                            <div class='col-md-1'>
                                <div class="form-group">
                                    {!! Form::text("merchant[$merchant->id][amount]",$merchant->payment_amount,['class'=>'form-control rate1 name-inv accept_digit_only','id'=>$i.'_amount']) !!} 
                                    <span id="error_message_for_daily_payment" class="text-danger error_message_for_daily_payment"></span>  
                                </div>
                            </div>
                            <div class='col-md-2'>
                                <div class="form-group">
                                    {!! Form::select("merchant[$merchant->id][rcode]",$rcodes,'',['class'=>'form-control rcode js-company-placeholder','placeholder'=>'Select Rcode','id'=> $i.'_rcode']) !!}  
                                    <span id="error_message_for_rcode" class="text-danger error_message_for_rcode"></span>           
                                </div>
                            </div>
                            <div class='col-md-2' hidden>
                                <div class="form-group">
                                    <div class="name-inn">
                                        {{ Form::checkbox("merchant[$merchant->id][debit]", 'yes', 0, array('class'=>'debit')) }} Debit?
                                        {!! Form::text("merchant[$merchant->id][debit_reason]",'',['class'=>'debit_reason form-control','id'=>'debit_reason','placeholder'=>"Enter Reason"]) !!}
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                    <?php $i++;?>
                    @endif
                    @endforeach
                </div>
            </div>
            <div class=" btn-wrap btn-right">
                <div class="btn-box">
                    <input type="button" value="Generate Payment" class="btn btn-primary cre-cub-btn sub-intr" id="paymentClick"  data-merchant_id="{{ $merchant->id }}">
                    <!-- {!! Form::submit('Generate Payment',['class'=>'btn btn-primary cre-cub-btn sub-intr']) !!} -->
                </div>
            </div>
        </div>            
        {!! Form::close() !!}
        @endif
        <div id="success-modal" class="modal">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Confirmation</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <span id="paymentbox"></span>
                        <b>Do you really want to add the payment again ?</b>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                        <a href="javascript:void(0)" class="btn btn-primary" id="submit" data-bs-dismiss="modal">Yes</a>
                    </div>
                </div>
            </div>
        </div>
        <div id="processing-modal" class="modal">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Requests</h4>
                    </div>
                    <div class="modal-body text-center">
                        <div class="row">
                            <div id="internetStatus"></div>
                            <p><b style="color:red"> * Please do not refresh or close the window</b> </p>
                            <p><span id="TotalProcessedCount"></span>/<span id="TotalProcessingCount"></span> Completed</p>
                            <table class="table table-striped dataTable no-footer" id="payment_processing_table">
                                <thead>
                                    <tr>
                                        <th class="text-left">Merchant</th>
                                        <th class="text-left">Message</th>
                                    </tr>
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
</div>
@stop
@section('scripts')
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{asset('js/custom/helper.js')}}"></script>
<script type="text/javascript">
$(document).on('click','.select_merchant',function(){
    let checked = 0
    let total = 0
    $('.select_merchant').each(function(){
        total ++ 
        if($(this).is(':checked')){
            checked ++
        }
    })
    if(checked < total ){
        $('#select_all').prop("checked",false)
    }else{
        $('#select_all').prop("checked",true)
    }
    var row = $(this).closest('.first-row');
    generate_button_validation_function(row);
});
function generate_button_validation_function(row){
    var flag = false;
    $('.first-row').each(function(){
        select_merchant = $(this).find("input.select_merchant[type='checkbox']").is(":checked");
        payment_date = $(this).find('.payment_date1').val();
        if(select_merchant && payment_date==''){
            flag = true;
        }
    });
    if(flag) {
        var text='<font color="red">Please select payment date</font>';
        $msg=row.find('.error_message_for_payment_date').html(text);
        error = 1;  
        $("#paymentClick" ).prop( "disabled",true);
    } else {
        var text='';
        $msg=row.find('.error_message_for_payment_date').html(text);
        error = 0;
        $("#paymentClick" ).prop( "disabled",false);
    }
}
$(document).ready(function () {
    
    var myDate5 = new Date(new Date().getTime()+(5*24*60*60*1000));
    var default_date_format = "{{\FFM::defaultDateFormat('format')}}";
    var mask_format = maskDateFormat(default_date_format)
    $('.multi-datepicker').each(function(){
        $(this).datepicker({
            // autoclose: true,
            format : default_date_format.toLowerCase(),
            multidate: true,
            endDate: myDate5,
            clearBtn: true,
            todayBtn: "linked"
        });
        var val = $(this).val();
        if(val) {
            val = val.split(',');
            var new_arr = val.map(item => {
                let year = moment(item, default_date_format).year();
                if(year.toString().length == 1 || year.toString().length == 2) {
                    year = moment(year, 'YY').format('YYYY');
                }
                return moment(item).set('year', year).format(default_date_format); 
            });
            var new_arr1 = val.map(item => {
                let year = moment(item, default_date_format).year();
                if(year.toString().length == 1 || year.toString().length == 2) {
                    year = moment(year, 'YY').format('YYYY');
                }
                return moment(item).set('year', year).format('YYYY-MM-DD');
            });
            if(new_arr) {
                new_arr = new_arr.join(',');    
                new_arr1 = new_arr1.join(',');
                $(this).val(new_arr);
                $(this).datepicker('update');
                $(this).siblings('.date_parse').val(new_arr1);
            }
        }else {
            $(this).siblings('.date_parse').val('');
        }
    });
    // $('#datepicker1').val(moment($('#datepicker1').val(), default_date_format).format(default_date_format));
    $('.multi-datepicker').on("change changeDate", function(){
        var val = $(this).val();
        if(val) {
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
    })
    $.validator.addMethod("date", function(value, element) {
        if(value) {
            value = $('#datepicker').val().split(',');  
            for(date of value) {
                if(moment(date, 'YYYY-MM-DD', true).isValid()) {
                    return true;
                }
            }
            return false;
        }else {
            return false;
        }
    });
    $.validator.addMethod("maxDate", function(value, element, maxDate){
        try {
            if(value) {
                value = $('#datepicker').val().split(',');
                for(date of value) {
                    let newDate = new Date(date);
                    if(newDate > maxDate) {
                        return false;
                        break;
                    }
                }
                return true;
            }
        } catch(e) {
        }
        return false;
    });
    $('#filter_form').validate({
        errorClass: 'errors',
        rules: {
            "lenders[]": { required: true },
            payment_date1: { required: true,date: true, maxDate: myDate5 },
            companies: {required: true}
        },
        messages: {
            payment_date1: {
                maxDate: "Please enter a valid date."
            }
        }
    });
    var error=0;
    var URL_debitCheck = "{{ URL::to('admin/payment/debitPaymentLimit') }}";
    $('.debit').on('change',function() {
        var row = $(this).closest('.first-row');
        var merchant_id=row.find('.merchant_check').val();
        var weekly_payment=row.find('.rate').val();
        var daily_payment=row.find('.rate1').val();
        if(row.find('.debit').is(':checked')) {
            $.ajax({
                type: 'POST',
                data: {'merchant_id': merchant_id,'weekly_payment':weekly_payment,'daily_payment':daily_payment,'_token': _token},
                url: URL_debitCheck,
                success: function (data) { 
                    if(data.status == 1) { 
                        if(weekly_payment) {
                            var text='<font color="red">'+data.msg+'</font>';
                            $msg=row.find('.error_message_for_weekly_payment').html(text);
                        }
                        if(daily_payment) {
                            var text='<font color="red">'+data.msg+'</font>';
                            $msg=row.find('.error_message_for_daily_payment').html(text);
                        }
                        error = 1;  
                        $("#paymentClick" ).prop( "disabled",true);
                    } else {
                        var text='';
                        $msg=row.find('.error_message_for_weekly_payment').html(text);
                        $msg=row.find('.error_message_for_daily_payment').html(text);
                        $("#paymentClick" ).prop( "disabled",false);
                        error = 0;
                    }
                }
            });
        }
    });
    $('.rate,.rate1').on("keypress keydown keyup",function()
    {
        var row = $(this).closest('.first-row');
        var weekly_payment=row.find('.rate').val(); 
        var daily_payment=row.find('.rate1').val();
        var merchant_id=row.find('.merchant_check').val();
        if(row.find('.debit').is(':checked'))
        {
            $.ajax({
                type: 'POST',
                data: {'merchant_id': merchant_id,'weekly_payment':weekly_payment,'daily_payment':daily_payment,'_token': _token},
                url: URL_debitCheck,
                success: function (data) { 
                    if(data.status == 1)
                    { 
                        if(weekly_payment)
                        {
                            var text='<font color="red">'+data.msg+'</font>';
                            $msg=row.find('.error_message_for_weekly_payment').html(text);
                        }
                        if(daily_payment)
                        {
                            var text='<font color="red">'+data.msg+'</font>';
                            $msg=row.find('.error_message_for_daily_payment').html(text);
                        }
                        error = 1;  
                        $("#paymentClick" ).prop( "disabled",true);
                    }
                    else
                    {
                        var text='';
                        $msg=row.find('.error_message_for_weekly_payment').html(text);
                        $msg=row.find('.error_message_for_daily_payment').html(text);
                        $("#paymentClick" ).prop( "disabled",false);
                        error = 0;
                    }
                }
            });
        }
    });
    $('.rate1').on("keypress keydown keyup",function() {
        var row = $(this).closest('.first-row');
        var amount=row.find('.rate1').val();
        var rcode=row.find('.rcode').val();
        var text='';
        if(amount==0 && rcode=='' || amount=='') {
            var text='<font color="red">Please select Rcode</font>';
            $msg=row.find('.error_message_for_rcode').html(text);
            error = 1;  
        } else {
            var text='';
            $msg=row.find('.error_message_for_rcode').html(text);
            error = 0;
        }
    });
    $('.payment_date1').on('change',function() {
        var row = $(this).closest('.first-row');
        generate_button_validation_function(row);
    });
    $('.rcode').on('change',function() { 
        var row = $(this).closest('.first-row');
        var rcode=row.find('.rcode').val();
        if(rcode)
        {row.find('.rate').val(0);}
        row.find('.error_message_for_rcode').html('');
        error = 0;
        var fieldsToDisable = [
            {
                field:'.net_payment',
                root:false
            },
            {
                field:'.rate1',
                root:false
            },
            {
                field:'.rate',
                root:false
            },
            {
                field:'#select_all_net',
                root:true
            }
        ]
        function toggleDisable(toggleStatus){
            for (var field =0; field < fieldsToDisable.length; field ++){
                if(fieldsToDisable[field].field == '.net_payment'){
                    $(fieldsToDisable[field].field).prop('checked',false)
                }
                if(fieldsToDisable[field].root){
                    $(fieldsToDisable[field].field).prop('disabled',toggleStatus)
                }else{
                    row.find(fieldsToDisable[field].field).prop('disabled', toggleStatus);
                }
            }
        }
        if(rcode){
            toggleDisable(true)
        }
        else{
            toggleDisable(false)
        }
    });
    $('#paymentClick').on('click',function(event) {
        var id_arr=[];
        var error_msg='';
        $('.select_merchant:checked').each(function() {
            id_arr.push($(this).val()); 
        });
        if(id_arr.length > 0) {
            var URL_paymentCheckBox = "{{ URL::to('admin/payment/lenderPaymentCheck') }}";
            var form_data = $('#lender_payment_generation_form').serialize();
            var html = '';
            $.ajax({
                type: 'POST',
                data: form_data,
                url: URL_paymentCheckBox,
                success: function (data) { 
                    if(data.status == 1) { 
                        $('#paymentbox').html(data.result);
                        $('#success-modal').modal('show');
                    } else if(data.status == 0 && error==0) {
                        // $('#lender_payment_generation_form').submit(); 
                        generatePayment();   
                    } else if(data.status == 2 && data.error_type==2) {
                        error_msg+='<div class="alert alert-danger alert-dismissable col-ssm-12" ><button type="button" class="close"  data-bs-dismiss="alert" aria-hidden="true" id="close">&times;</button>';
                        $.each(data.result, function (i, val) {
                            error_msg+=val.toUpperCase()+', ';
                        });
                        error_msg+=' payment date less than last payment date or funded date.so that case payment not possible </div>';
                        $('#error_msg').html(error_msg);
                        window.scrollTo(0,0);
                    } else if(data.status == 2 && data.error_type==1) {
                        error_msg+='<div class="alert alert-danger alert-dismissable col-ssm-12" ><button type="button" class="close"  data-bs-dismiss="alert" aria-hidden="true" id="close">&times;</button>';
                        $.each(data.result, function (i, val) {
                            error_msg+=val+', ';
                        });
                        error_msg+=' Please enter amount more than zero </div>';
                        $('#error_msg').html(error_msg);
                        window.scrollTo(0,0);
                    }
                }
            });
        } else {
            error_msg+='<div class="alert alert-danger alert-dismissable col-ssm-12" ><button type="button" class="close"  data-bs-dismiss="alert" aria-hidden="true" id="close">&times;</button>';
            error_msg+='Please select at least one Merchant for payment </div>';
            $('#error_msg').html(error_msg);
            $('html, body').animate({
                scrollTop: ($("#error_msg").offset().top)
            }, 500);
        }
    });
    $('#submit').on('click',function() {
        if(error==0) {
            generatePayment();
        }
    });
    payment_processing_table = $('#payment_processing_table').DataTable({
        "scrollY"    : "555px",
        "scrollX"    : false,
        "paging"     : false,
        "searching"  : false,
        "ordering"   : false,
        "autoWidth"  : false,
        "fixedHeader": {
            "header": false,
            "footer": false
        },
        "columnDefs": [
            { "width": "40%", "targets": 0 },
            { "width": "60%", "targets": 1 },
        ],
    });
    $('#processing-modal').modal({ backdrop: 'static', keyboard: true, });
    TotalProcessedCount  = 0;
    TotalProcessingCount = 0;
    async function generatePayment() {
        TotalProcessedCount  = 0; 
        TotalProcessingCount = 0; 
        $('#TotalProcessedCount').text(TotalProcessedCount);
        $('#TotalProcessingCount').text(TotalProcessingCount);
        $('#processing-modal').modal('show');
        payment_processing_table.clear().draw();
        var form_data = $('#lender_payment_generation_form').serialize();
        var URL_manage_payments_for_lenders = "{{ URL::to('admin/payment/manage_payments_for_lenders') }}";
        MerchantRequests = [];
        $.ajax({
            type: 'POST',
            data: form_data,
            url : URL_manage_payments_for_lenders,
            success: function (data) { 
                $.each(data.list, function (key,value) {
                    var form_data_single=value;
                    var merchant_id=value.merchant_id;
                    var tr='';
                    tr+='<tr id="merchant_id-'+merchant_id+'">';
                    tr+='   <td class="text-left">'+value.name.toUpperCase()+'</td>';
                    tr+='   <td class="text-left"><div class="loader"></div></td>';
                    tr+='</tr>';
                    const tr_value = $(tr);
                    payment_processing_table.row.add(tr_value[0]);
                    payment_processing_table.draw();
                    MerchantRequests.push(form_data_single);
                });
                $('#TotalProcessingCount').text(MerchantRequests.length);
                single_payment_function();
            }
        });
    }
    function single_payment_function() {
        $('#TotalProcessedCount').text(TotalProcessedCount);
        var URL_add_payments_for_lenders = "{{ URL::to('admin/payment/add_payments_for_lenders') }}";
        form_data_single = MerchantRequests.shift();
        if(form_data_single){
            var merchant_id=form_data_single.merchant_id
            $.ajax({
                type       : 'POST',
                data       : form_data_single,
                url        : URL_add_payments_for_lenders,
                tryCount   : 0,
                retryLimit : 3,
                success: function (result) {
                    $('#payment_processing_table tbody tr#merchant_id-'+merchant_id+' td:nth-child(2)').text(result.message);
                    payment_processing_table.draw();
                    TotalProcessedCount++;
                    single_payment_function();
                },
                error: function (jqXHR, exception) {
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    sleep(3000);
                    console.log('Retry after 3 seconds');
                    $.ajax(this);
                    return;
                },
            });
        } else {
            location.reload();           
        }
    }
    window.onbeforeunload = function(e) {
        var TotalProcessedCount  = $('#TotalProcessedCount').text();
        var TotalProcessingCount = $('#TotalProcessingCount').text();
        var pending              = TotalProcessingCount-TotalProcessedCount;
        if (pending) {
            var event = e || window.event;
            var msg = "Potential data loss if you close the window without completing the payment insertion process.";
            if (event) {
                event.returnValue = msg; // IE
            }
            return msg;                  // Everyone else
        }
    };
    // var URL_netPaymentSet = "{{ URL::to('admin/payment/netPaymentSet') }}";
    var URL_netPaymentAll = "{{ URL::to('admin/payment/netPaymentAll') }}";
    var URL_netPayment = "{{ URL::to('admin/payment/netPayment') }}";
    $('.net_payment').on('click',function() {
        var row = $(this).closest('.first-row');
        var merchant_id=row.find('.merchant_check').val();
        var rate=row.find('.rate').val();
        length=row.find('.checkDate').val().split(',').length;
        //var v = $(this).closest("div.test1").find(".merchant_check").val()
        //alert(merchant_id);
        if($(this).is(':checked',true)) {
            if(merchant_id) {
                $.ajax({
                    type: 'POST',
                    data: {
                        '_token'     : _token,
                        'merchant_id': merchant_id,
                        'rate'       : rate,
                        'length'     : length,
                    },
                    url: URL_netPaymentAll,
                    success: function (data) { 
                        error=0;
                        row.find('.error_message_for_rcode').html('');
                        $.each(data.result, function (i, val) {
                            row.find('.rate1').val((val).toFixed(2));
                        });
                        // row.find('.rate1').val((data.net_payment).toFixed(2));
                    }
                });
            }
        } else {
            $.ajax({
                type: 'POST',
                data: {
                    '_token'     : _token,
                    'merchant_id': merchant_id,
                    'rate'       : rate,
                    'length'     : length,
                },
                url: URL_netPayment,
                success: function (data) { 
                    error=0;
                    row.find('.error_message_for_rcode').html('');
                    $.each(data.net_payment, function (i, val) {
                        row.find('.rate1').val((parseFloat(val)).toFixed(2));
                    });
                }
            });
        } 
    });
    $('.rcode').on('change',function() {
        var row = $(this).closest('.first-row');
        var merchant_id=row.find('.merchant_check').val(); 
        var id = $(this).attr('id');
        var rcode_val = $("#"+id).val();
        var index_dt = id.split("_");
        var index = index_dt[index_dt.length - 2];
        if(rcode_val!=''){
            $("#"+index+"_amount").val(0);
            document.getElementById(index+"_amount").readOnly = false;
        } else {
            $.ajax({
                type: 'POST',
                data: {'merchant_id': merchant_id,'_token': _token},
                url: URL_netPayment,
                success: function (data) { 
                    $.each(data.net_payment, function (i, val) {
                        row.find('.rate1').val((parseFloat(val)).toFixed(2));
                    });
                }
            });
            document.getElementById(index+"_amount").readOnly = false;
        }
    });
    $('#select_all_net').on('click',function() {
        if($(this).is(':checked',true)) { 
            //Net value
            //$('.select_merchant').prop('checked', true);
            //$("#select_all").prop('checked', true);
            $(".net_payment").prop('checked', true);
            var merchantIDsWithPayment = $(".select_merchant:checkbox").map(function(){
                length=$(this).closest('.first-row').find('.checkDate').val().split(',').length;
                return {
                    merchant_id:$(this).val(),
                    rate       :$(this).closest('.first-row').find('.rate').val(),
                    length     :length
                };
            }).get();
            var merchantIDs = $(".select_merchant:checkbox").map(function(){
                return $(this).val();
            }).get();
            // console.log(searchIDs);
            $.ajax({
                type: 'POST',
                data: {
                    '_token'     : _token,
                    'merchant_id': merchantIDs,
                    'data'       : merchantIDsWithPayment,
                },
                url: URL_netPaymentAll,
                success: function (data) {
                    $.each(data.result, function (i, val) {
                        // alert(val);
                        $('#'+i+'_amount').val(parseFloat(val).toFixed(2));
                        //row.find('.rate1').val((val).toFixed(2));
                    });
                }
            });
        } else {
            $(".net_payment").prop('checked',false);
            // $('.select_merchant').prop('checked', false);
            // $("#select_all").prop('checked', false); 
            var merchantIDsWithPayment = $(".select_merchant:checkbox").map(function(){
                length=$(this).closest('.first-row').find('.checkDate').val().split(',').length;
                return {
                    merchant_id:$(this).val(),
                    rate       :$(this).closest('.first-row').find('.rate').val(),
                    length     :length
                };
            }).get();
            // var merchantIDs = $(".select_merchant:checkbox:not(:checked)").map(function(){
            //    return $(this).val();
            //  }).get();
            $.ajax({
                type: 'POST',
                data: {
                    '_token'     : _token,
                    'merchant_id': merchantIDs,
                    'data'       : merchantIDsWithPayment
                },
                url: URL_netPayment,
                success: function (data) { 
                    $.each(data.net_payment, function (i, val) {
                        $('#'+i+'_amount').val(parseFloat(val).toFixed(2));
                    });
                }
            });
        }
    });
    $('.rate,.debit_reason').on('keyup',function() {
        var row = $(this).closest('.first-row');
        row.find('.select_merchant').attr('checked', true);
    });
    $('.debit,.net_payment').on('click',function() {
        var row = $(this).closest('.first-row');
        // row.find('.select_merchant').attr('checked', true);
    });
    $('.checkDate').on('change',function() {
        var row = $(this).closest('.first-row');
        row.find('.select_merchant').attr('checked', true);
        var datecheck = row.find('.checkDate').val();
        var value = row.find('.checkDate').val().replace(",", "");
        var words = value.split("-");
        var length=(words.length)/2;
        var newAmount=0;
        var rate=row.find('.rate').val();
        if(rate) {
            var newAmount=parseFloat(rate) / parseInt(length);
            row.find('.rate1').val(newAmount.toFixed(2));
        }
    });
    $('.rate').on('change',function() {
        var row = $(this).closest('.first-row');
        row.find('.select_merchant').attr('checked', true);
        var datecheck = row.find('.checkDate').val();
        var value = row.find('.checkDate').val().replace(",", "");
        var words = value.split("-");
        var length=(words.length)/2;
        var newAmount=0;
        var rate=row.find('.rate').val();
        if(row.find('.net_payment').is(':checked',true)){
            var merchant_id=row.find('.merchant_check').val();
            length=row.find('.checkDate').val().split(',').length;
            $.ajax({
                type: 'POST',
                data: {
                    '_token'     : _token,
                    'merchant_id': merchant_id,
                    'rate'       : rate,
                    'length'     : length,
                },
                url: URL_netPaymentAll,
                success: function (data) { 
                    $.each(data.result, function (i, val) {
                        row.find('.rate1').val((val).toFixed(2));
                    });
                }
            });
        }else{
            var newAmount=parseFloat(rate) / parseInt(length);
            row.find('.rate1').val(newAmount.toFixed(2));
        }
    });
    $('#select_all').on('click',function() {
        if($(this).is(':checked',true)) {
            $(".select_merchant").prop('checked', true);  
        } else {  
            $(".select_merchant").prop('checked',false);  
        } 
    });
    $('.accept_digit_only').keypress(function(event) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
        ((event.which < 48 || event.which > 57) &&
        (event.which != 0 && event.which != 8))) {
            event.preventDefault();
        }
        var text = $(this).val();
        if ((text.indexOf('.') != -1) &&
        (text.substring(text.indexOf('.')).length > 2) &&
        (event.which != 0 && event.which != 8) &&
        ($(this)[0].selectionStart >= text.length - 2)) {
            event.preventDefault();
        }
    });
    function sleep(milliseconds) {
        const date = Date.now();
        let currentDate = null;
        do {
            currentDate = Date.now();
        } while (currentDate - date < milliseconds);
    } 
});
</script>
<script type="text/javascript">
const internetStatus = document.getElementById("internetStatus");
onlineStatusCheck = navigator.onLine;
window.addEventListener('load', function(event){
    detectInternet();
});
window.addEventListener('online', function(event){
    detectInternet();
});
window.addEventListener('offline', function(event){
    detectInternet();
});
function detectInternet(){
    if(navigator.onLine) {
        onlineStatusCheck = true;
        internetStatus.textContent           = "You are back online";
        internetStatus.style.backgroundColor = "green";
    } else {
        onlineStatusCheck = false;
        internetStatus.textContent           = "No Internet Connection";
        internetStatus.style.backgroundColor = "red";
    }
}
</script>
@stop
@section('styles')
<style type="text/css">
.select2-selection__rendered {
    display: inline !important;
}
.select2-search--inline {
    float: none !important;
}
.form-control.multi-datepicker[readonly] {
    background-color: inherit;
}
.loader {
    border: 16px solid #f3f3f3; /* Light grey */
    border-top: 16px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 50px !important;
    height: 50px !important;
    animation: spin 2s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/genarate_interest.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<style type="text/css">
#internetStatus{
    position  : fixed;
    top       : 0px;
    left      : 0px;
    width     : 100%;
    text-align: center;
    color     : white;
}
</style>
@stop
