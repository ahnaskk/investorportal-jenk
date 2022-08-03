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
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::reports::liquidity-log') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-body"> 
            <div class="form-box-styled" >
                {{Form::open(['route'=>'admin::reports::liquidity-log-export','id'=>'liquidtyLog'])}}

                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                            </div>
                            <input class="form-control from_date1 datepicker" id="date_start1" value="{{ $date_start }}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"/>
                            <input type="hidden" name="date_start" id="date_start" value="{{ $date_start }}" class="date_parse">
                        </div>
                        <span class="help-block">From Date</span>
                        <span id="invalid-date_start" />
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                            </div>
                            <input class="form-control to_date1 datepicker" id="date_end1" value="{{ $date_end }}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off"/>
                            <input type="hidden" name="date_end" id="date_end" value="{{ $date_end }}" class="date_parse">
                        </div>
                        <span class="help-block">To Date</span>
                        <span id="invalid-date_end" />
                    </div>
                </div>                        
                <div class="row px-1">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::select('merchant_id[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchant_id','multiple'=>'multiple'])}}                            
                        </div>
                        <span class="help-block">Merchants</span>
                    </div>
                    @if(!Auth::user()->hasRole(['company']))
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::select('owner[]',$companies,$active_companies,['class'=>'form-control js-company-placeholder','id'=>'owner','placeholder'=>'Select Company', 'multiple' => 'multiple'])}}
                        </div>
                        <span class="help-block">Company</span>
                    </div>
                    <div class="col-md-4">
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
                    </div>
                <div class="row px-1">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>
                            {{Form::select('investors[]',$investors,$active_company_users,['class'=>'form-control js-investor-placeholder-multiple selectliquidiyinv','id'=>'investor','multiple'=>'multiple'])}}
                        </div>
                        <span class="help-block">Investors</span>
                    </div>
                
                    <div class="col-md-4 report-input">
                        <div class="input-group">
                            <div class="input-group-text">
                            </div>
                            {!! Form::select('accountType[]',$Roles,[\App\User::INVESTOR_ROLE],['class'=>'form-control js-account-type-placeholder','id'=>'accountType','multiple'=>'multiple']) !!}  
                        </div>
                        <span class="help-block">Account Type</span>
                    </div>
                    <div class="col-md-4 report-input">
                        <div class="input-group">
                            <div class="input-group-text">
                            </div>
                            {!! Form::select('label[]',$label,'',['class'=>'form-control js-label-placeholder','id'=>'label','multiple'=>'multiple']) !!}  
                        </div>
                        <span class="help-block">Label</span>
                    </div>
                    </div>
                <div class="row px-1">
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                            </div>
                            {{Form::select('description[]',$descriptions,'',['class'=>'form-control js-description-placeholder','id'=>'description','multiple'=>'multiple'])}}
                        </div>
                        <span class="help-block">Description</span>
                    </div>
                
                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc chc01">
                                        <input  id="groupbypay" name="groupbypay" type="checkbox" checked="checked" /> 
                                        <span class="checkmark chek-mm"></span>
                                        <span class="chc-value">Check this</span>
                                    </label>
                                </div>   
                            </div>
                            <span class="help-block">Group by Transactions</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="btn-wrap btn-right ">
                        <div class="btn-box">
                            <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                            name="student_dob">
                            {{Form::submit('Download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                        </div>
                    </div>                               
                </div>                               
            </div>
            {{Form::close()}}
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-12 grid table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
<div class="modal right fade myModal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Investors List</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="investors_fire">
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
{!! $tableBuilder->scripts() !!}
<script src="{{ asset('/js/custom/report.js') }}"></script>  
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script>   
<script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function(){
    $('#date_filter').on('click',function (e) {
        e.preventDefault();
        if($("#liquidtyLog").valid())table.draw();
    });
    jQuery.validator.addMethod("dateValidation",function(value, element, params) {
        var fromDate = new Date("2000-01-01");
        var toDate = new Date(value); 
        return toDate < fromDate
    },'To Date must be great');
    jQuery.validator.addMethod("date",function(value, element, params) {
        return moment(params).isValid();
    });
    $("#liquidtyLog").validate({
        errorClass: 'errors_msg',
        rules:{
            date_start1: {  
                date : function(){
                    if($('#date_start').val()) {
                        return $('#date_start').val();
                    }
                },
            },
            date_end1: {  date : function(){
                if($('#date_end').val()) {
                    return $('#date_end').val();
                }
            }}
        },
    });
    var URL_getRoleUsers = "{{ URL::to('admin/getRoleUsers') }}";
    $("#accountType").change(function(e) {
        var role_id = $(this).val();
        var company = $('#owner').val();
        var velocity_owned = $("input[name=velocity_owned]:checked").val();
        if (role_id.length || velocity_owned==1) {
            $.ajax({
                type: 'POST',
                url: URL_getRoleUsers,
                data: {'_token': _token, 'company': company, 'role_id': role_id,'velocity_owned':velocity_owned},
                success: function (data) {
                    $('#investor').attr('selected','selected').val(data).trigger('change.select2');
                }
            });
        } else {
            $('#investor').attr('selected','selected').val([]).trigger('change.select2');
            $('#owner').attr('selected', 'selected').val([]).trigger('change.select2');
        }
    });
    var URL_getInvestor = "{{ URL::to('admin/getCompanyWiseInvestors') }}";
    var investorRole = "{{ \App\User::INVESTOR_ROLE }}";
    $('#owner').change(function(e) {
        var company=$('#owner').val();
        var role_id = $('#accountType').val();
        var velocity_owned = $("input[name=velocity_owned]:checked").val()
        if (company.length) {
            if (!role_id.length) {
                $('#accountType').attr('selected', 'selected').val([investorRole]).trigger('change.select2')
            }
            $.ajax({
                type: 'POST',
                data: {'changeid': 1, 'company':company, '_token': _token, 'role_id': role_id,'velocity_owned':velocity_owned},
                url: URL_getRoleUsers,
                success: function (data) {
                    $('#investor').attr('selected','selected').val(data).trigger('change.select2');         
                },
                error: function (data) {
                }
            });
        } else {
            $('#investor').attr('selected','selected').val([]).trigger('change.select2');
            
        }
    });
    // $('#velocity_owned').change(function(e) {
    //     var company=$('#owner').val();
    //     var role_id = $('#accountType').val();
    //     var velocity_owned = $("input[name=velocity_owned]:checked").val();
    //     if (company.length || velocity_owned==1) {
    //         if (!role_id.length) {
    //             $('#accountType').attr('selected', 'selected').val([investorRole]).trigger('change.select2')
    //         }
    //         $.ajax({
    //             type: 'POST',
    //             data: {'changeid': 1, 'company':company, '_token': _token, 'role_id': role_id,'velocity_owned':velocity_owned},
    //             url: URL_getRoleUsers,
    //             success: function (data) {
    //                 $('#investor').attr('selected','selected').val(data).trigger('change.select2');         
    //             },
    //             error: function (data) {
    //             }
    //         });
    //     } else {
    //         $('#investor').attr('selected','selected').val([]).trigger('change.select2');
            
    //     }
    // });
});
var URL_getMerchants = "{{ URL::to('admin/getSelect2MerchantsWithDeleted') }}";
</script>

<script type="text/javascript">
$(document).ready(function(){
    $("* [data-toggle='tooltip']").tooltip({
        html: true, 
        placement: 'bottom'
    });
   
    $(".js-description-placeholder").select2({
        placeholder: "Select Description"
    });
    $(".js-label-placeholder").select2({
        placeholder: "Select Label"
    });
    $(".js-account-type-placeholder").select2({
        placeholder: "Select Account Type(s)"
    });
    
});
var URL_batch = "{{ URL::to('admin/investors/investorsLogList') }}";
function investor_list(batch_id,merchant_id,id)
{
    if(batch_id)
    {
        var j=1;
        var html='';
        var url='';
        var investors = $('#investor').val();
        var velocity_owned = $("input[name=velocity_owned]:checked").val();
        $.ajax({
            type:'POST',
            data: {'batch_id': batch_id, '_token': _token, 'merchant_id': merchant_id, 'id': id,'velocity_owned':velocity_owned},
            url: URL_batch,
            success:function(data)
            {
                if(data.status==1)
                {
                    var result = data.result;
                    if (investors.length) {
                        investors = investors.map(function (x) {
                            return parseInt(x);
                        });
                        result = data.result.filter(function(user){
                            return investors.includes(user.member_id);
                        });
                    }
                    $.each(result, function (i, val) {
                        url = "{{ url('admin/investors/portfolio/') }}";
                        url = url + '/' + val.member_id;
                        if (val.user_deleted_at) {
                            html += '<div class="col-md-12">' + j + '.' + val.user_name.toUpperCase() + '</div>';
                        } else {
                            html += '<div class="col-md-12"><a href=' + url + '>' + j + '.' + val.user_name.toUpperCase() + '</a></div>';
                        }
                        j = j + 1;
                    });
                    $('.investors_fire').html(html);
                }
            }
        });
    }
    $('.myModal').modal('show');
} 
</script>
<script type="text/javascript" src="{{ asset('/js/custom/bootstrap-tooltip.js') }}"></script>
@stop
@section('styles')
<style>
.errors_msg {
    color: red;      
}
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
</style>
<link href="{{ asset('/css/optimized/Liquidity_Log.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/merchants.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
