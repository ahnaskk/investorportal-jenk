@extends('layouts.admin.admin_lte')
@section('content')
<?PHP
$activate_status='';
if(old('active_status'))
$activate_status= 'checked';
if ((!old('email')) && ( old('active_status') !== NULL || old('active_status') != '1') && (isset($investor)) && ($investor->active_status) )
$activate_status ='checked';
?>
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip"> @if($action=="create") Create All Type of Accounts  @else  Edit Account @endif</div>
    </a>
</div>
@if($action=="create")
{{ Breadcrumbs::render('investorcreate') }}
@else
{{ Breadcrumbs::render('investoredit') }}
@endif
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
        <!-- form start -->
        @if($action=="create")
        {!! Form::open(['route'=>'admin::investors::storeCreate', 'method'=>'POST','id'=>'create_investor_form','class'=>'invetor_form']) !!}
        @else
        {!! Form::open(['route'=>['admin::investors::update','id'=>$investor->id], 'method'=>'POST','id'=>'edit_investor_form','class'=>'invetor_form']) !!}
        @endif
        @include('layouts.admin.partials.lte_alerts')
        {{ Form::hidden('oldtag', '', ['id' => 'oldtag' ]) }}
        <div class="box-body">
            <div class="row">
                <div class="form-group col-md-3">
                    <label for="exampleInputEmail1">Syndicate Company Name <span class="validate_star">*</span></label>
                    {!! Form::text('name',isset($investor)? $investor->name : old('name'),['class'=>'form-control','placeholder'=>'Enter Syndicate Company Name','required','id'=> 'inputName','maxlength'=>"255",'minlength'=>"1"]) !!}
                    <span id="invalid-inputName" />
                </div>
                <?php $userId=Auth::user()->id;?>
                @if (!isset($investor))
                {!! Form::hidden('creator_id',$userId) !!}
                @endif
                <div class="form-group col-md-3">
                    <label for="exampleInputEmail1">Contact Person <span class="validate_star">*</span></label>
                    {!! Form::text('contact_person',isset($investor)? $investor->contact_person : old('contact_person'),['class'=>'form-control','placeholder'=>'Enter Contact Person','required','id'=> 'investorContactPerson','maxlength'=>"255",'minlength'=>"1"]) !!}
                    <span id="invalid-investorContactPerson" />
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="Cell Phone">Cell Phone</label>
                        {!! Form::text('cell_phone',isset($investor)? $investor->cell_phone : old('cell_phone'),['class'=>'form-control numbers','id'=>'investorCellPhone','placeholder' => 'Cell Phone']) !!}
                        <span id="invalid-investorCellPhone" />
                    </div>
                </div>
                <div class="form-group col-md-3 synd-march">
                    <label for="exampleInputEmail1">Syndication Fee (%) <span class="validate_star">*</span> </label>
                    <div class="input-group">
                        {!! Form::select('global_syndication',$fee_values,isset($investor)? is_numeric($investor->global_syndication)?number_format($investor->global_syndication,2):null: old('global_syndication'),['class'=>'form-control','placeholder'=>"Lender's fee",'id'=> 'inputGlobalSyndication']) !!}
                        <span id="invalid-inputGlobalSyndication" />
                        <div class="mrch">
                            <span class="input-group-text">%</span>
                            <span class="input-group-text">
                                <label>
                                    <input {{old('s_prepaid_status')==2?'checked':(isset($investor)?($investor->s_prepaid_status==2?'checked':''):('checked')  )}} value="2" type="radio" name="s_prepaid_status" id="s_prepaid_amount"> On Funding Amount?
                                </label>
                            </span>
                            <span class="input-group-text">
                                <label>
                                    <input {{old('s_prepaid_status')==1?'checked':(isset($investor)?($investor->s_prepaid_status==1?'checked':''):'')}} value="1" type="radio" name="s_prepaid_status" id="s_prepaid_rtr"> On RTR?
                                </label>
                            </span>
                        </div>
                    </div>
                </div>
                <!--todo no interest payment for Equity -->
                <div class="form-group col-md-3">
                    <label for="exampleInputEmail1">Management Fee (%) <span class="validate_star">*</span></label>
                    {!! Form::select('management_fee',$fee_values,isset($investor)? is_numeric($investor->management_fee)?number_format($investor->management_fee,2):null: old('management_fee'),['class'=>'form-control','placeholder'=>"Lender's management fee",'id'=> 'inputManagementFee']) !!}
                    <span id="invalid-inputManagementFee" />
                </div>
                @if(!isset($investor))
                <div class="form-group col-md-3">
                    <label for="exampleInputEmail1">Account Type <span class="validate_star">*</span></label>
                    {!! Form::select('role_id',$Roles,isset($investor)? $investor->role_id: \App\User::INVESTOR_ROLE,['class'=>'form-control','placeholder'=>'Select Account Type','required','id'=> 'inputRoleId']) !!}
                    <span id="invalid-inputRoleId" />
                </div>
                @else
                <div class="form-group col-md-3">
                    <label for="exampleInputEmail1">Account Type <span class="validate_star">*</span></label>
                    <input type="text" class="form-control" disabled value="{{$Roles[$investor->role_id]}}">
                </div>
                @endif
                @php $company=isset($investor)?$investor->company:''; @endphp
                <div class="form-group col-md-3">
                    <label for="exampleInputEmail1">Investor Type <span class="validate_star">*</span></label>
                    {!! Form::select('investor_type',$investor_types,isset($investor)? $investor->investor_type: old('investor_type'),['class'=>'form-control','placeholder'=>'Select Investor','required','id'=> 'inputInvestorType','data-parsley-required-message' => 'Investor Type Field Is Required']) !!}
                    <span id="invalid-inputInvestorType" />
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Agreement Date</label>
                        {!! Form::text('agreement_date1',isset($investor)? $investor->agreement_date : old('agreement_date'),['class'=>'form-control datepicker','placeholder'=>\FFM::defaultDateFormat('format'),'id'=>'datepicker1','autocomplete'=>"off"]) !!}
                        <input type="hidden" class="date_parse" name="agreement_date" value="{{isset($investor)? $investor->agreement_date : old('agreement_date')}}" id="datepicker">
                    </div>
                </div>
            </div>
            <!-- <div class="row">
                <div class="form-group col-md-3" id="ROIRateDiv" style="display:none;">
                    <label for="interest_rate">ROI Rate (%) <span class="validate_star">*</span></label>
                    {!! Form::select('interest_rate',$roi_rates,isset($investor) ? number_format($investor->interest_rate,2) :old('interest_rate'),['class'=>'form-control','placeholder'=>'Enter interest rate','required','id'=> 'inputInterestRate']) !!}
                    <span id="invalid-inputInterestRate" />
                </div>
            </div> -->
            <div class="row">
                <div class="form-group  col-md-3">
                    <label for="exampleInputEmail1" title="This is the email will be used to login this account.">Login Email</label>
                    {!! Form::email('email',isset($investor) ? $investor->email :old('email'),['class'=>'form-control','placeholder'=>"Enter Investor's Email Id",'id'=> 'inputEmail','autocomplete'=>'off']) !!}
                    <span id="invalid-inputEmail" />
                    <label >
                        <input type="checkbox" name="email_notification" value="1" id="email_notification"/>
                        Send Password by Email
                    </label>
                </div>
                <div class="form-group col-md-3">
                    <label for="exampleInputPassword1">Password</label>
                    {!! Form::password('password',['class'=>'form-control','placeholder'=>'Enter password ','id'=> 'inputPassword','data-parsley-required-message' => 'Password Field Is Required','autocomplete'=>'off']) !!}
                    <span id="invalid-inputPassword" />
                </div>
                <div class="form-group col-md-3">
                    <label for="exampleInputPassword1">Confirm Password </label>
                    {!! Form::password('password_confirmation',['class'=>'form-control','placeholder'=>'Enter password ','id'=> 'inputConfirmPassword','data-parsley-required-message' => 'Confirm Password Field Is Required','autocomplete'=>'off']) !!}
                    <span id="invalid-inputConfirmPassword" />
                </div>
                <?php
                $velocity_owned=isset($investor->velocity_owned)?$investor->velocity_owned:'';
                ?>
                <div class="form-group col-md-3">
                        <div class="input-group check-box-wrap">
                            <label>Velocity Owned</label>
                            <div class="input-group-text">
                                <label class="chc">
                                    <input type="checkbox" name="velocity_owned" value="1" {{ ($velocity_owned)?'checked':'' }} id="velocity_owned"/>
                                    <span class="checkmark chek-m"></span>
                                    <span class="chc-value">Click Here</span>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php if(auth()->check()): ?>
                    <!--todo no interest payment for Equity -->
                </div>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">Company <span class="validate_star">*</span></label>
                        @if($company_permission)
                        {{Form::select('company',$companies,$user_id,['class'=>'form-control js-company-placeholder','id'=>'company','placeholder'=>'Select Companies'])}}
                        @else
                        {{Form::select('company',$companies,isset($investor)?$investor->company:old('company'),['class'=>'form-control js-company-placeholder','id'=>'company','placeholder'=>'Select Companies'])}}
                        @endif
                        <span id="invalid-company" />
                    </div>
                    <div class="form-group col-md-3 notificationEmail">
                        <label title="All portfolio related emails will be forwarded to these multiple emails. " for="exampleInputEmail1">Notification Emails</label>
                        {!! Form::email('notification_email',isset($investor) ? $investor->notification_email : old('notification_email'),['class'=>'form-control','id'=> 'notification_email','data-role'=>'tagsinput']) !!}
                        <span id="invalid-notification_email" />
                    </div>
                    <div class="form-group col-md-3">
                        <label>Auto syndication payment</label>
                        @php
                        $auto_syndicate_payment=isset($investor->auto_syndicate_payment)?$investor->auto_syndicate_payment:old('auto_syndicate_payment');
                        @endphp
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text">
                                <label class="chc">
                                    <input type="checkbox" name="auto_syndicate_payment" value="1" {{ ($auto_syndicate_payment)?'checked':'' }} id="auto_syndicate_payment"/>
                                    <span class="checkmark chek-m"></span>
                                    <span class="chc-value">Click Here</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">Payout Frequency <span class="validate_star" style="display:none;">*</span></label>
                        {!! Form::select('notification_recurence',$recurrence_types,isset($investor) ? $investor->notification_recurence :'3',['class'=>'form-control','placeholder'=>'Select Recurrence','required','id'=> 'notification_recurence']) !!}
                        <span id="invalid-notification_recurence" />
                    </div>
                    <div class="form-group col-md-3">
                    <label for="exampleInputEmail1">Beneficiary</label>
                    {!! Form::text('beneficiary',isset($investor)? $investor->beneficiary : old('beneficiary'),['class'=>'form-control','placeholder'=>'Enter Beneficiary','id'=> 'beneficiary','maxlength'=>"255",'minlength'=>"1"]) !!}
                    <span id="invalid-investorContactPerson" />
                    </div>
                    @php
                    $filetype=isset($investor->file_type)?$investor->file_type:'';
                    @endphp
                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">File Type <span class="validate_star">*</span></label>
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text">
                                <label class="radio-box">
                                    <input @if($action=="create") checked @endif {{old('file_type')==1?'checked':(isset($investor->file_type)?($investor->file_type==1?'checked':''):'')}} value="1" type="radio" name="file_type" id="file_type">
                                    <span>PDF</span>
                                </label>
                                <label class="radio-box">
                                    <input {{old('file_type')==2?'checked':(isset($investor->file_type)?($investor->file_type==2?'checked':''):'')}} value="2" type="radio" name="file_type" id="file_type">
                                    <span> CSV</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endrole
                    @php
                    $liquidity=isset($investor->liquidity_exclude)?$investor->liquidity_exclude:'';
                    $whole_portfolio=isset($investor->whole_portfolio)?$investor->whole_portfolio:'';
                    $auto_generation=isset($investor->auto_generation)?$investor->auto_generation:old('auto_generation');
                    $auto_invest=isset($investor->auto_invest)?$investor->auto_invest:'';
                    @endphp
                    <div class="form-group col-md-3">
                        <div class="input-group check-box-wrap">
                            <label>Include Whole Portfolio</label>
                            <div class="input-group-text">
                                <label class="chc">
                                    <input type="checkbox" name="whole_portfolio" value="1" {{ ($whole_portfolio)?'checked':'' }} id="whole_portfolio"/>
                                    <span class="checkmark chek-m"></span>
                                    <span class="chc-value">Click Here</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label >Auto Invest Collected Amount From </label>
                        <div class="input-group">
                            @php $label_data=isset($investor->label)?json_decode($investor->label):''; @endphp
                            {!! Form::select('label[]',$label,isset($investor)? $label_data: old('label'),['id'=> 'inputlabel','multiple'=>'multiple']) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-3">     
                        <label class="checkbox-inline" title="Warning: Merchant names will be exposed to this account, if chosen!!">
                            Details to be displayed in User Account  
                        </label>
                        @if($action=="create")
                        <input type="checkbox" checked data-toggle="toggle" data-on="Merchant id" data-off="Name" name="show_name_mid" id="show_name_mid"> 
                        @else
                        <input type="checkbox" @if($investor->display_value=='mid')checked @endif data-toggle="toggle" data-on="Merchant id" data-off="Name" name="show_name_mid" id="show_name_mid"> 
                        @endif
                    </div>
                    <div class="form-group  col-md-3">
                        <label for="exampleInputEmail1">Enable / Disable  </label>
                        <?php $status=isset($investor->active_status)?$investor->active_status:1; ?>
                        <input 
                        {{$activate_status}}
                        data-onstyle="success" data-toggle="toggle" type="checkbox"  name="active_status" value="1"  id="active_status"   class="badgebox">
                    </div>
                    <!-- Bank account can be added only from investor edit feature  -->
                    @if($action=="edit")
                    @if($bank)
                    <div class="form-group  col-md-3">
                        <label for="funding_status" >Enable Investment Portal</label>
                        <input data-onstyle="success" data-toggle="toggle" type="checkbox"  name="funding_status"   id="funding_status"   class="badgebox"
                        @if(old('funding_status')) checked @endif
                        @if(!old('email'))
                        @if( old('funding_status') !== NULL || old('funding_status') != 'on')
                        @if(isset($investor)) @if($investor->funding_status)
                        checked
                        @endif
                        @endisset
                        @endif
                        @endif
                        >
                    </div>
                    @else
                    <div class="form-group  col-md-3">
                        <a title="No bank account added. Funding portal cannot be activated."  target="_blank" href="{{url('admin/investors/bank_details/'.$investor->id)}}" class="btn btn-danger"> Add bank account. </a>
                    </div>
                    @endif
                    @endif
                    <div class="form-group col-md-3">
                        <label for="exampleInputEmail1">Login <span class="validate_star">*</span></label>
                        <div class="input-group check-box-wrap">
                            <div class="input-group-text">
                                <label class="radio-box">
                                    <input {{old('login_board')=='old'?'checked':(isset($investor->login_board)?($investor->login_board=='old'?'checked':''):'')}} value="old" type="radio" name="login_board" id="login_board">
                                    <span>OLD</span>
                                </label>
                                <label class="radio-box">
                                    <input @if($action=="create") checked @endif {{old('login_board')=='new'?'checked':(isset($investor->login_board)?($investor->login_board=='new'?'checked':''):'')}} value="new" type="radio" name="login_board" id="login_board">
                                    <span> NEW</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 btn-wrap btn-right">
                        <div class="btn-box">
                            @if($action=="create")
                            <a href="{{URL::to('admin/investors')}}" class="btn btn-success">View Accounts</a>
                            @else
                            @if(Permissions::isAllow('Investors','View'))
                            <a href="{{URL::to('admin/investors/portfolio')}}/{{$investor->id}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> Portfolio</a>
                            @endif

                            @endif
                            @if($action=="create")
                            {!! Form::submit('Create',['class'=>'btn btn-primary']) !!}
                            @else
                            {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="active_status_clone">
            <input type="hidden" id="funding_status_clone">
            {!! Form::close() !!}
        </div>
    </div>
    @stop
    @section('scripts')
    <script src="{{ asset ('js/bootstrap-toggle.min.js') }}"></script>
    <script src="{{ asset ('bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.min.js') }}" type="text/javascript"></script>
    <script>
    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }
    $(document).ready(function () {
        $('.invetor_form').submit(function(){
            var email = $('#inputEmail').val();
            var not_email = $("#notification_email").val();
            if(not_email == '' || not_email == null){
                $("#notification_email").val(email);
                return;
            }
            return;
        })
        var company='{{$company}}';
        (function(){
            $("#active_status_clone").val($("#active_status").val())
            $("#funding_status_clone").val($("#funding_status").val())
        })();
        $("#active_status").change(function(e){
            if($("#active_status_clone").val() == 1) $("#active_status_clone").val(0)
            else $("#active_status_clone").val(1)
        })
        $("#funding_status").change(function(e){
            if($("#funding_status_clone").val() == 1) $("#funding_status_clone").val(0)
            else $(this).val(1)
        })
        $(".js-company-placeholder").select2({
            placeholder: "Select Company"
        });
        $("#company").change(function(){
            $('#company-error').hide();
        });
        $("#notification_email").change(function(){
            $('#notification_email-error').hide();
        });
        $("#notification_recurence").change(function(){
            $('#notification_recurence-error').hide();
        });
        $("#inputEmail").change(function(){
            $('#inputEmail-error').hide();
        });
        $("#inputEmail").blur(function(){
            var _this = $(this);
            var oldtag = $('#oldtag').val();
            if(oldtag != ''){
                $('#notification_email').tagsinput('remove', oldtag);
            }
            $('.notificationEmail').find(':input').val(_this.val());
            $('#oldtag').val(_this.val());
        });
        $("#inputGlobalSyndication").change(function(){
            $('#inputGlobalSyndication-error').hide();
        });
        $("#inputInvestorType").change(function(){
            var _this = $(this);
            if(_this.val() == "5"){
                $("#company").val("284").change();
            }else{
                if(company)
                {
                    $("#company").val(company).change();
                }
                else
                {
                    $("#company").val("").change();
                }
            }
            $('#inputInvestorType-error').hide();
            if($('#inputInvestorType').val()==5)
            {
                $('#auto_generation').prop( "checked", true );
            }
            else
            {
                $('#notification_recurence-error').hide();
                $('#auto_generation').prop( "checked", false );
            }
        });
        $("#inputManagementFee").change(function(){
            $('#inputManagementFee-error').hide();
        });
        var regex4 = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;// Email address
        $('#notification_email').tagsinput({
            width: 'auto',
            pattern: regex4
        });
        $('#create_investor_form').validate({
            errorClass: 'errors',
            rules: {
                name: {
                    required: true  ,
                    maxlength: 255,
                },
                email: {
                    required: true,
                    email: true,
                    maxlength: 255,
                },
                investor_type:{ required: true, },
                interest_rate:{ required: true, },
                notification_email:{
                    email: true,
                },
                notification_recurence:
                {
                    required: function(element) {
                        if($('#inputInvestorType').val()==5)
                        return true;
                        else
                        return false;
                    },
                },
                password: {
                    maxlength: 255,
                },
                password_confirmation: {
                    equalTo: "#inputPassword",
                    maxlength: 255,
                },
                company:{ required: true, },
                phone : { usPhoneFormat : true },
                cell_phone : { usPhoneFormat : true }
            },
            messages: {
                name: "Enter Name",
                email: { required :"Enter Email Id"},
                password:{ required:"Enter Password",
                minlength: "Please enter at least 6 characters.",
                maxlength: "Password can be max 255 characters long.",
            },
            password_confirmation: {
                required: "You must confirm your password.",
                minlength: "Please enter at least 6 characters.",
                maxlength: "Password can be max 255 characters long.",
                equalTo: "Your Password Must Match."
            },
            investor_type:"Select Investor Type",
            interest_rate:"Enter Interest Rate",
            notification_email: { required :"Enter Emails for Notifications"},
            notification_recurence:"Select Notification Recurrence",
            company:"Select Company"
        },
        errorPlacement: function(error, element) {
            error.appendTo('#invalid-' + element.attr('id'));
        }
    });
    $('#edit_investor_form').validate({
        errorClass: 'errors',
        rules: {
            name: {
                required: true,
                maxlength: 255,
            },
            email: {
                required: true,
                email: true,
                maxlength: 255,
            },
            notification_email: {
                email: true,
                maxlength: 255,
            },
            password: {
                maxlength: 255,
                minlength: 6
            },
            password_confirmation: {
                equalTo: "#inputPassword",
                maxlength: 255,
                minlength: 6,
            },
            investor_type:{ required: true, },
            interest_rate:{ required: true, },
            notification_recurence:
            {
                required: function(element) {
                    if($('#inputInvestorType').val()==5)
                    return true;
                    else
                    return false;
                },
            },
            phone : { usPhoneFormat : true },
            cell_phone : { usPhoneFormat : true }
        },
        messages: {
            name: "Enter Name",
            email: { required :"Enter Email Id"},
            password:{
                minlength: "Please enter at least 6 characters.",
                maxlength: "Password can be max 255 characters long.",
            },
            password_confirmation: {
                equalTo:"Password Confirmation Does Not Match",
                minlength: "Please enter at least 6 characters.",
                maxlength: "Password can be max 255 characters long.",
            },
            investor_type:"Select Investor Type",
            interest_rate:"Enter Interest Rate",
            notification_email: { required :"Enter Emails for Notifications", },
            notification_recurence:"Select Notification Recurrence"
        },
        errorPlacement: function(error, element) {
            error.appendTo('#invalid-' + element.attr('id'));
        }
    });
    $("input.numbers").keypress(function(event) {
        return /\d/.test(String.fromCharCode(event.keyCode));
    });
    jQuery.validator.addMethod("usPhoneFormat", function(value, element) {
        var regex =  /^(\([0-9]{3}\) |[0-9]{3}-)[0-9]{3}-[0-9]{4}$/
        return this.optional(element) || regex.test(value);
    }, "Please Enter valid number.Ex:(417) 555-1234");
    $("#investorCellPhone").keyup(function(){
        var phno = formatPhoneNumber($("#investorCellPhone").val());
        if(phno!=null){
            document.getElementById("investorCellPhone").value = phno;
        }
    });
    $("#investorPhone").keyup(function(){
        var phno = formatPhoneNumber($("#investorPhone").val());
        if(phno!=null){
            document.getElementById("investorPhone").value = phno;
        }
    });
    function formatPhoneNumber(phoneNumberString) {
        var cleaned = ('' + phoneNumberString).replace(/\D/g, '')
        var match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/)
        if (match) {
            return '(' + match[1] + ') ' + match[2] + '-' + match[3]
        }
        return null
    }
});
</script>
@stop
@section('styles')
<style type="text/css">
li.breadcrumb-item.active{
    color: #2b1871!important;
}
li.breadcrumb-item a{
    color: #6B778C;
}
.select2-selection__rendered {
    display: inline !important;
}
.select2-search--inline {
    float: none !important;
}
#invalid-inputGlobalSyndication {
    flex: 1.7;
}
.mrch {
    width: 100% !important;
    display: flex;
}
.mrch .input-group-text {
    flex: 1;
    justify-content: center;
}
</style>
<link href="{{ asset('/css/optimized/create_new_investor.css?ver=6') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel='stylesheet'/>
<link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
