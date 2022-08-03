@extends('layouts.admin.admin_lte')

@section('content')
@php
      $date_end = date('Y-m-d');
      $date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
@endphp
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Merchant Liquidity Log</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('admin::reports::liquidity-log-merchant') }}
<div class="col-md-12">
 <div class="box">
        <div class="box-body"> 

  <div class="form-box-styled" >

     {{Form::open(['id'=>'merchantLiquidtyLog'])}}

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
                                <input class="form-control to_date1 datepicker" id="date_end1" value="{{ $date_end }}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="tex" autocomplete="off"/>
                                <input type="hidden" name="date_end" id="date_end" value="{{ $date_end }}" class="date_parse">
                            </div>
                            <span class="help-block">To Date</span>
                             <span id="invalid-date_end" />
                           </div>
                           
                    </div> 

                    <div class="row">

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
                                    {{Form::select('owner',$companies,$active_companies,['class'=>'form-control js-company-placeholder','id'=>'owner','placeholder'=>'Select Company', 'multiple' => 'multiple'])}}

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

                 <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                             <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                              </div>
                    {{Form::select('investors[]',$investors,$active_company_users,['class'=>'form-control js-investor-placeholder-multiple','id'=>'investor','multiple'=>'multiple'])}}

           
                            </div>
                          <span class="help-block">Investors</span>
                        </div>

                    </div>



         <div class="row">
            <div class="col-md-4">
                <div class="input-group">
                    <div class="input-group check-box-wrap">
                        <div class="input-group-text">
                            <label class="chc chc01">
                                <input  id="groupbypay" name="groupbypay" type="checkbox" checked="checked"/> 
                                <span class="checkmark chek-mm"></span>
                                <span class="chc-value">Check this</span>
                            </label>
                        </div>   
                    </div>
                    <span class="help-block">Group by Transactions</span>
                </div>     
            </div>
            <div class="col-md-4 report-input">
                <div class="input-group">
                    <div class="input-group-text">
                    </div>
                    {!! Form::select('accountType[]',$Roles,[\App\User::INVESTOR_ROLE],['class'=>'form-control js-account-type-placeholder','id'=>'accountType','multiple'=>'multiple']) !!}  
                </div>
                <span class="help-block">Account Type</span>
            </div>
             
            <div class="col-md-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                    </div>
                    {{Form::select('description[]',$descriptions,'',['class'=>'form-control js-description-placeholder','id'=>'description','multiple'=>'multiple'])}}
                </div>
                <span class="help-block">Description</span>
            </div>

         </div>

             <div class="col-md-12">
                         <div class="btn-wrap btn-right ">
                            <div class="btn-box">
                                <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                                       name="student_dob">
                               

                            </div>
                            
                        </div>                               
                    </div>  

                  {{Form::close()}}       

         </div>        

            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12 grid table-responsive">

                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                       
                      <!--  <div class="blockCust pull-right" style="padding-bottom: 15px">

                            {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}

                        </div>-->
                    
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>

@stop

@section('scripts')

{!! $tableBuilder->scripts() !!}

<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script> 
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
<script>
  var URL_getMerchants = "{{ URL::to('admin/getSelect2MerchantsWithDeleted') }}";
</script>
 <script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function(){
    $('#date_filter').click(function (e) {
        e.preventDefault();
        if($("#merchantLiquidtyLog").valid())table.draw();
    });
    jQuery.validator.addMethod("date",function(value, element, params) {
      return moment(params).isValid();
    });
    $("#merchantLiquidtyLog").validate({
        errorClass: 'errors_msg',
        rules:{
          date_start1: {  date : function(){
            if($('#date_start').val()) {
              return $('#date_start').val();
            }
          }},
          date_end1: {  date : function(){
            if($('#date_end').val()) {
              return $('#date_end').val();
            }
          }}
        },
        
      })

    $(".js-description-placeholder").select2({
        placeholder: "Select Description"
    });
    $(".js-company-placeholder").select2({
        placeholder: "Select Company(s)"
    });
    $(".js-account-type-placeholder").select2({
        placeholder: "Select Account Type(s)"
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
            $('#investor').attr('selected', 'selected').val([]).trigger('change.select2');
            $('#owner').attr('selected', 'selected').val([]).trigger('change.select2');
        }
    });
    var URL_getInvestor = "{{ URL::to('admin/getCompanyWiseInvestors') }}";
    var investorRole = "{{ \App\User::INVESTOR_ROLE }}";
    $('#owner').change(function(e)
    {
        var company=$('#owner').val();
        var role_id = $('#accountType').val();
        var velocity_owned = $("input[name=velocity_owned]:checked").val();
        var investors = [];
        if (company.length || velocity_owned==1) {
            if (!role_id.length) {
                $('#accountType').attr('selected', 'selected').val([investorRole]).trigger('change.select2')
            }
            $.ajax({
                type: 'POST',
                data: {'changeid': 1, 'company':company,'velocity_owned':velocity_owned, '_token': _token},
                url: URL_getRoleUsers,
                success: function (data) { 
                    $('#investor').attr('selected','selected').val(data).trigger('change.select2');
                },
                error: function (data) {

                }
            });
        } else {
            $('#investor').attr('selected','selected').val('').trigger('change.select2');
            $('#accountType').attr('selected', 'selected').val([]).trigger('change.select2');
        }
    });  
 
   

});


</script>
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
  <link href="{{ asset('/css/optimized/Merchant_Liquidity_Log.css?ver=5') }}" rel="stylesheet" type="text/css" />
   <link href="{{ asset('/css/optimized/Liquidity_Log.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop
