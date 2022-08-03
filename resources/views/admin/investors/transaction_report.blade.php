<?php use App\Library\Helpers\InvestorTransaction as InvestorTransactionHelper; ?>
@extends('layouts.admin.admin_lte')
@section('content')
<?php
$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
?>
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Transaction Report</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::investors::transactionreport') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <div class="alert alert-success" id="success-alert" style="display: none;">
            <strong>Success!</strong>
             Successfully Deleted.
         </div>
        <div class="loadering-statement" style="display:none;">
            <div class="loader"></div><br>
            <h5 class="alert alert-warning"><b>Please wait for a while!!</b></h5>
        </div>
        <div class="box-body">
            <div class="form-box-styled" >
                {{Form::open(['route'=>'admin::investors::transactionreportdownload'])}}
                <div class="serch-bar">
                    <div  class="row g-0">
                        <div class="merchant-ass">           
                            <div class="col-md-4 check-click checktime1" >
                                <div class="form-group">
                                    <div class="input-group check-box-wrap">
                                        <div class="input-group-text">
                                            <label class="chc">
                                                <input  id="date_type" name="date_type" type="checkbox" value="true"/> <span class="checkmark chek-mm"></span>
                                                <span class="checkmark chek-m"></span>
                                                <span class="chc-value">Check this</span>
                                            </label>
                                        </div>
                                    </div>
                                    <span class="help-block">Filter Based On Transaction Added Date (Investment Date by Default)</span>
                                </div>
                            </div>
                            <div class="date-star" id="test" style="display:block">
                                <div class="col-md-4" style="height: 86px; margin-bottom: -2px;">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                        </div>
                                        <input class="form-control from_date1 datepicker" autocomplete="off" id="date_start1" name="start_date1" value="{{$date_start}}"  placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                        <input type="hidden" name="start_date" id="date_start" value="{{$date_start}}" class="date_parse">
                                        <span id="invalid-date_start"/>
                                    </div>
                                    <span class="help-block">From Date</span>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                        </div>
                                        <input class="form-control to_date1 datepicker" autocomplete="off" id="date_end1" value="{{$date_end}}" name="end_date1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                        <input type="hidden" name="end_date" id="date_end" value="{{ $date_end }}" class="date_parse">
                                    </div>
                                    <span class="help-block">To Date</span>
                                </div>
                            </div>
                            <div id="time_filter" class="check-time" style="display:none">
                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="col-md-6 serch-timeer-one">
                                            <div class="input-group serch-two">
                                                <div class="input-group-text">
                                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                                </div>
                                                <input class="form-control from_date2 datepicker" id="date_start11" value="{{$date_start}}" name="date_start1" autocomplete="off" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                                <input type="hidden" name="date_start" id="date_start1" value="{{$date_start}}" class="date_parse">
                                            </div>
                                            <span class="help-block">From Date</span>
                                        </div>
                                        <div class="col-md-6 serch-timeer">
                                            <!-- <div class="input-group"> -->
                                            <!-- <div class="input-group-text"> -->
                                            <!-- <span class="glyphicon glyphicon-time" aria-hidden=" true"></span> -->
                                            <!-- </div> -->
                                            <!-- <input class="timepicker form-control from_time" type="text" id="time_start" name="time_start" placeholder="00:00:00"> -->
                                            <!-- </div> -->
                                            <div class="input-group clockpicker">
                                                <input type="text" class="form-control" value="00:00" id="time_start" name="time_start">
                                                <span class="input-group-text">
                                                    <span class="glyphicon glyphicon-time"></span>
                                                </span>
                                            </div>
                                            <span class="help-block">From Time</span>
                                        </div>
                                        <div class="col-md-6 serch-timeer-one">
                                            <div class="input-group serch-two">
                                                <div class="input-group-text">
                                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                                </div>
                                                <input class="form-control to_date2 datepicker" id="date_end11" value="{{$date_end}}" name="date_end1" autocomplete="off" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text"/>
                                                <input type="hidden" name="date_end" id="date_end1" value="{{$date_end}}" class="date_parse">
                                            </div>
                                            <span class="help-block">To Date</span>
                                        </div>  
                                        <div class="col-md-6 serch-timeer">
                                            <div class="input-group">
                                                <!-- <div class="input-group-text"> -->
                                                <!-- <span class="glyphicon glyphicon-time" aria-hidden="true"></span> -->
                                                <!-- </div> -->
                                                <!-- <input class="timepicker form-control to_time" type="text" id="time_end" name="time_end" placeholder="00:00:00"> -->
                                                <!-- </div> -->
                                                <div class="input-group clockpicker">
                                                    <input type="text" class="form-control" value="00:00" id="time_end" name="time_end">
                                                    <span class="input-group-text">
                                                        <span class="glyphicon glyphicon-time"></span>
                                                    </span>
                                                </div>
                                                <span class="help-block">To Time</span>
                                            </div> 
                                        </div>
                                    </div>
                                </div>        
                            </div>
                        </div>
                        <!-- assigned-filter-investor -->
                        <div class="col-sm-12">           
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- <div class="col-md-5 col-sm-6"> -->
                    <!-- <div class="input-group"> -->
                    <!-- <div class="input-group-text"> -->
                    <!-- <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span> -->
                    <!-- </div> -->
                    <!-- <input class="form-control"  value="{{$date_start}}" id="date_start" name="date_start" placeholder="MM-DD-YYYY" -->
                    <!-- type="date"/> -->
                    <!-- </div> -->
                    <!-- <span class="help-block">From Date</span> -->
                    <!-- </div> -->
                    <!-- <div class="col-md-5 col-sm-6"> -->
                    <!-- <div class="input-group"> -->
                    <!-- <div class="input-group-text"> -->
                    <!-- <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span> -->
                    <!-- </div> -->
                    <!-- <input class="form-control" id="date_end" name="date_end" placeholder="MM-DD-YYYY" type="date" value="{{$date_end}}" /></div> -->
                    <!-- <span class="help-block">To Date</span> -->
                    <!-- </div> -->
                </div>
                <div class="row">
                    @if(!Auth::user()->hasRole(['company']))
                    <div class="col-md-4 col-sm-6">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>
                            {{Form::select('owner',$companies,'',['class'=>'form-control js-company-placeholder','id'=>'owner','placeholder'=>'Select Company'])}}
                        </div>
                        <span class="help-block">Company</span>
                    </div>
                    <div class="col-md-4 col-sm-6">
                                <div class="input-group check-box-wrap">

                                    <div class="input-group-text">
                                        <label class="chc">
                                            <input type="checkbox" name="velocity_owned" value="1" id="velocity_owned"/>
                                            <span class="checkmark chek-m"></span>
                                            <span class="chc-value">Click Here</span>
                                        </label>
                                    </div>
                                    <span class="help-block">Velocity Owned </span>
                                </div>
                             </div>
                    @endif
                    <div class="col-md-4 col-sm-6">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>
                            {{Form::select('investors[]',$investors,1,['class'=>'form-control js-investor-placeholder-multiple  ','id'=>'investors','multiple'=>'multiple'])}}
                        </div>
                        <span class="help-block">Investors</span>
                    </div>
                    </div>
                <div class="row">
                    <div class="col-md-4 report-input">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>
                            {!! Form::select('investor_type[]',$investor_types,isset($investor)? $investor->investor_type: old('investor_type'),['class'=>'form-control js-investor-type-placeholder-multiple','id'=> 'investor_type','multiple'=>'multiple']) !!}
                        </div>
                        <span class="help-block">Investor Type </span>
                    </div> 
               
                    <div class="col-md-4 col-sm-6">
                        <div class="input-group">
                            <div class="input-group-text">
                            </div>
                            {{Form::select('transaction_type',['0'=>'Select Transaction Type','1' => 'Debit', '2' => 'Credit'],"",['class'=>'form-control','id'=>'transaction_type'])}}
                        </div>
                        <span class="help-block">Transaction Type</span>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="input-group" >
                            <div class="input-group-text">
                                <span class="fa fa-list-alt" aria-hidden="true"></span>
                            </div>
                            {{Form::select('categories[]',$categories,'',['class'=>'form-control js-category-placeholder-multiple','id'=>'categories','multiple'=>'multiple'])}}
                        </div>
                        <span class="help-block">Transaction Categories</span>
                    </div>
                    </div>
                    <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="input-group" >
                            <div class="input-group-text">
                                <span class="fa fa-list-alt" aria-hidden="true"></span>
                            </div>
                            {{Form::select('status', $statuses, 1,['class'=>'form-control','id'=>'status'])}}
                        </div>
                        <span class="help-block">Status</span>
                    </div>
                   
                        <div class="col-md-4 col-sm-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                </div>
                                {{Form::select('merchant',$allMerchants,"",['class'=>'form-control js-merchant-placeholder','placeholder'=>'Select Merchant','id'=>'merchant'])}}
                            </div>
                            <span class="help-block">Merchant</span>
                        </div>
                       
                    <div class="col-md-12">
                        <div class="btn-wrap btn-right"> 
                            <div class="btn-box"> 
                                <input type="button" value="Apply Filter" class="btn btn-primary" id="apply" name="Apply Button">
                                @if(@Permissions::isAllow('Transaction Report','Download')) 
                                {{Form::submit('Download report',['class'=>'btn btn-success','id'=>'form_filter'])}}
                                @endif
                            </div>     
                        </div>     
                    </div>     
                </div>

            </div>
            {{Form::close()}}
            <div id="example2_wrapper" class="grid dataTables_wrapper form-inline dt-bootstrap">
                <div class="grid">
                    <div class="grid table-responsive"> 
                        @if(@Permissions::isAllow('Transaction Report','Delete'))
                        <a href="#" class="btn  btn-danger delete_multi" style="margin: 0 0 20px" id="delete_multi_transaction"><i class="glyphicon glyphicon-trash"></i> Delete <span style="display: none;" id="i_count"></span>  Selected </a>
                        @endif
                        <a href="#" class="btn  btn-primary update_multi" style="margin: 0 0 20px" id="update_multi_transaction"><i class="glyphicon glyphicon-edit"></i> Update Selected </a>
                        {!! $tableBuilder->table(['class' => 'table table-bordered transactionReport'],true) !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
<div class="modal fade" id="updateTranModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Transactions Edit</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- form start -->
                {{Form::open(['route'=>'admin::investors::update_multiple_transaction', 'method'=>'POST','id'=>"updateTranModalForm"])}}
                <input type = "hidden" name="tran_ids" id="tran_ids" value="" class="">
                <div class="modal_error" id = "modal_error">
                    
                </div>
                <div class="form-group">
                    <div class="date-star" id="date-star">
                        
                       <div class="col-md-12 report-input">
                        <span class="help-block">Transaction Category </span>
                            <div class="input-group">
                            {{Form::select('new_transaction_category',$categories,'',['class'=>'form-control','id'=>'new_transaction_category'])}}
                            </div>
                            <span id="invalid-new_transaction_category" class='errors'></span>
                        </div>
                        <div class="col-md-12 report-input" style="display:none;" id="new_transaction_type_div">
                        <span class="help-block">Transaction Type </span>
                            <div class="input-group">
                            {{Form::hidden('tran_type','',['class'=>'form-control','id'=>'tran_type'] )}}
                            {{Form::select('new_transaction_type',['1' => 'Debit', '2' => 'Credit'],"",['class'=>'form-control','id'=>'new_transaction_type','disabled'=>'disable'])}}
                            </div> 
                        </div>
                        <div class="col-md-12 report-input">
                        <span class="help-block">Investment Date </span>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control new_inv_date1 datepicker" id="new_inv_date1"  value="" name="new_inv_date" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"/>
                                <input type="hidden" class="date_parse" name="new_inv_date" id="new_inv_date" value="">
                            </div>
                            <span id="invalid-new_inv_date" class='errors'></span>
                        </div>

                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- /.box-body -->
                        <div class="box-footer">
                            {!! Form::button('Update',['class'=>'btn btn-primary','id'=>'tran_edit_btn']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
                <!-- /.box -->
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@stop
@section('scripts')
<script src="{{ asset('/js/moment.min.js') }}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script>
{!! $tableBuilder->scripts() !!}
<script src="{{ asset('/js/custom/report.js') }}"></script> 
<script type="text/javascript"> 
function isValidTimeString(timestr){
  var hours = timestr.split(":")[0];
  var minutes = timestr.split(":")[1];
  if(parseInt(hours) >= 0 && parseInt(hours) < 24 && parseInt(minutes) >= 0 && parseInt(minutes) <=59 ){
    return true;
  }
  return false;
};
var redirectUrl = "{{ URL::to('admin/investors/transaction-report') }}";
var URL_TransactionDelete = "{{ URL::to('admin/investors/delete_transactions') }}";
$('#checkAllButtont').on('click',function() {
    if($(this).is(':checked',true)) {
        $(".delete_transactions").prop('checked', true);
    } else {
        $('.delete_transactions').prop('checked', false);
    }
});
function uncheckMainTransaction() {
        var uncheck = 0;
        $('input:checkbox.delete_transactions').each(function() {
            if (!this.checked) {
                uncheck = 1;
                $('#checkAllButtont').prop('checked', false);
            }
        });
        if (uncheck == 0) {
            $('#checkAllButtont').prop('checked', true);
        }
    }
$(document).ready(function(){       
    TransactionTypeEditEnabledList=["{!! InvestorTransactionHelper::getTransactionTypeEditEnabledList() !!}"];
    $(".js-merchant-placeholder").select2({
        placeholder: "Select Merchant",
    });
    $('#update_multi_transaction').on('click', function() {
        var el = this;
        var id_arr = [];
        var count=0;
        var pending = 0;
        $('.delete_transactions:checked').each(function() {
            if($.isNumeric($(this).val())){
                id_arr.push($(this).val());
                count=count+1;
            } 
            
        });
        $('#tran_ids').val(id_arr);
        if(count>0){
        $('#updateTranModal').modal('show'); 
        }else{
            alert('Please select atleast one record to update.');   
        }
    });
    $('#new_inv_date1').on('blur',function(){
        var investment_date = $('#new_inv_date').val();
        var from = '2016-01-01';
        var to = '2026-12-31';
        var error = 0;
        if(investment_date!=''){
            if (investment_date < from || investment_date > to) {
                var error =1;
                document.getElementById('invalid-new_inv_date').innerHTML = "Please Enter date between 01-01-2016 and 12-31-2026";
           }else{
            $('#modal_error').html('');
            document.getElementById('invalid-new_inv_date').innerHTML = "";
           }
        }

    });
    $('#tran_edit_btn').on('click',function() {
        var investment_date = $('#new_inv_date').val();
        var from = '2016-01-01';
        var to = '2026-12-31';
        var error = 0;
        if(investment_date!=''){
            if (investment_date < from || investment_date > to) {
                var error =1;
                document.getElementById('invalid-new_inv_date').innerHTML = "Please Enter date between 01-01-2016 and 12-31-2026";
           }else{
            document.getElementById('invalid-new_inv_date').innerHTML = "";
           }
        }
        if(($('#new_transaction_category').val()==0) && ($('#new_inv_date').val()=='')){
            var error =1;
            error_msg='<div class="alert alert-danger alert-dismissable col-ssm-12" ><button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>';

            error_msg+='Please select atleast one field to update.</div>';
            $('#modal_error').html(error_msg);
        }
        if(error==0){
            $('#updateTranModalForm').submit();
        }
       
       
    });
    $('#new_transaction_type').change(function(){
        var transaction_type=$(this).val();
        $('#tran_type').val(transaction_type);
    });
    $('#delete_multi_transaction').on('click', function() {
    var el = this;
    var id_arr = [];
    var count=0;
    var pending = 0;
    $('.delete_transactions:checked').each(function() {
        if($.isNumeric($(this).val())){
            id_arr.push($(this).val());
            count=count+1;
        } else {
            alert("Cannot Delete Pending Transactions");
            pending = 1;
            return false;
        }
        
    });
        if(pending == 0){
            if (count > 0) {
                if (confirm('Do you really want to delete the selected ('+ count +') items?')) {
                    $(".loadering").css("display", "block");
                    $.ajax({
                        type: 'POST',
                        data: {
                            'multi_id': id_arr,
                            '_token': _token
                        },
                        url: '{{ route("admin::investors::delete_transactions") }}',
                        success: function(data) {
                            if (data.status == 1) {
                                window.LaravelDataTables.dataTableBuilder.draw();
                                $("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
                                    $("#success-alert").slideUp(500);
                                });
                            }
                        }
                    });
                }
            } else {
                alert('Please select atleast one record to delete.');
            }
        }
    });
    
    
    $('#time_start,#time_end').mask('00:00');
    $('#time_start,#time_end').change(function(){
        var timestr = $(this).val();
        if (! isValidTimeString(timestr)) {
            // entered invalid time
            $(this).val('00:00');
        }
    });
    $('#new_transaction_category').change(function() {
        var transaction_type=$('#new_transaction_type').val();
        $('#tran_type').val(transaction_type);
        var category = $(this).val();
        if(category!=0){
            $('#modal_error').html('');
            document.getElementById('new_transaction_type_div').style.display = "block";
            document.getElementById('invalid-new_transaction_category').innerHTML = "";
        }else{
            document.getElementById('new_transaction_type_div').style.display = "none";   
        }
        if(category==1 || category==13 || category==18){
            $('#new_transaction_type').val(2).change();
           
        } else {
            
            if($.inArray(category, TransactionTypeEditEnabledList) == -1){
                $('#new_transaction_type').val(1).change();
            }
            
        }
    });
    $('#new_transaction_category').change(function(){
        var transaction_category=$(this).val();
        if($.inArray(transaction_category, TransactionTypeEditEnabledList) !== -1){
            $('#new_transaction_type').attr('disabled',false);
        } else {
            $('#new_transaction_type').attr('disabled',true);
        }
    });
    var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
    $('.from_date1,.from_date2').on("change changeDate", function(){
        var val = $(this).val();
        if(val && moment(val, default_date_format).isValid())
        {
            let year = moment(val, default_date_format).year();
            if(year.toString().length == 1 || year.toString().length == 2) {
                year = moment(year, 'YY').format('YYYY');
            }
            var newDate = moment(val, default_date_format).set('year', year).format(default_date_format);
            var currentDate = moment(val, default_date_format).set('year', year);
            var futureYear = moment(currentDate).add(1, 'Y');
            $('.from_date1,.from_date2').val(newDate);
            $('.from_date1,.from_date2').datepicker('update');
            $('.from_date1,.from_date2').siblings('.date_parse').val(currentDate.format('YYYY-MM-DD'));
            if($('.to_date1').val()) {
                // $('.to_date1, .to_date2').datepicker('setEndDate', futureYear.format(default_date_format));
                var toDate = moment($('.to_date1').val(), default_date_format);
                var fromDateMoment = moment([ currentDate.format('YYYY'), currentDate.format('M') - 1, currentDate.format('DD')]);
                var toDateMoment = moment([ toDate.format('YYYY'), toDate.format('M') - 1, toDate.format('DD') ]);
                var diff = toDateMoment.diff(fromDateMoment, 'days');
                if(diff > 365) {
                    // $('.to_date1, .to_date2').val(futureYear.format(default_date_format)).datepicker('update');
                    // $('.to_date1').trigger('change');
                }
            }
        }else {
            $('.to_date1, .to_date2').datepicker('setEndDate', null);
            $('.from_date1,.from_date2').siblings('.date_parse').val('');
        }
    })
    $('.to_date1,.to_date2').on("change changeDate", function(){
        var val = $(this).val();
        if(val && moment(val, default_date_format).isValid())
        {
            let year = moment(val, default_date_format).year();
            if(year.toString().length == 1 || year.toString().length == 2) {
                year = moment(year, 'YY').format('YYYY');
            }
            var newDate = moment(val, default_date_format).set('year', year).format(default_date_format);
            var currentDate = moment(val, default_date_format).set('year', year);
            var previousYear = moment(currentDate).subtract(1, 'Y');
            $('.to_date1,.to_date2').val(newDate);
            $('.to_date1,.to_date2').datepicker('update');
            $('.to_date1,.to_date2').siblings('.date_parse').val(currentDate.format('YYYY-MM-DD'));
            // if($('.from_date1').val()) {
            //   $('.from_date1, .from_date2').val(previousYear.format(default_date_format)).datepicker('update');
            // }
        }else {
            $('.to_date1,.to_date2').siblings('.date_parse').val('');
        }
    })
});
$('#date_type').click(function(){
    if($(this).is(':checked')){
        $('#time_filter').show();
        $('#test').hide();
        $("#test").css("display", "none");
    } else {
        $('#time_filter').hide();
        $("#test").css("display", "block");
        $('#test').show();
    }
});
$('.from_date1').change(function () {
    var from_date1 = $('.from_date1').val();
    $(".from_date2").val(from_date1);
});
$('.to_date1').change(function () {
    var to_date1 = $('.to_date1').val();
    $(".to_date2").val(to_date1);
});
$('.from_date2').change(function () {
    var from_date2 = $('.from_date2').val();
    $(".from_date1").val(from_date2);
});
$('.to_date2').change(function () {
    var to_date2 = $('.to_date2').val();
    $(".to_date1").val(to_date2);
});
$(document).ready(function() {
    var URL_getInvestor = "{{ URL::to('admin/getCompanyWiseInvestors') }}";
    $('#owner').change(function(e) {
        var company=$('#owner').val();
        var velocity_owned = $("input[name=velocity_owned]:checked").val();
        var investors = [];
        if(company!=0 || velocity_owned==1)
        {
            $.ajax({
                type: 'POST',
                data: {'company':company, '_token': _token,'velocity_owned' : velocity_owned},
                url: URL_getInvestor,
                success: function (data) {
                    var result=data.items;
                    for(var i in result) {
                        investors.push(result[i].id);
                    }
                    $('#investors').attr('selected','selected').val(investors).trigger('change.select2');
                },
                error: function (data) {
                    //alert('hi');
                }
            });
        } else {
            $('#investors').attr('selected','selected').val([]).trigger('change.select2');  
        }
    });
    
        
});
</script>
<script src="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.min.js') }}"></script>
<script type="text/javascript">
$('.clockpicker').clockpicker({ donetext: 'Done'});
</script> 
@stop
@section('styles')
<style type="text/css">
.select2-dropdown {  
  z-index: 10060 !important;/*1051;*/
}
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
</style>
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/bower_components/clockpicker/bootstrap-clockpicker.css') }}" rel="stylesheet" type="text/css" />  
@stop
