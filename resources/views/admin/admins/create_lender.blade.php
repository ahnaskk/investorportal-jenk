@extends('layouts.admin.admin_lte')
@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ isset($page_title) ? $page_title : '' }}</div>
        </a>

    </div>
    @if ($action == 'create')
        {{ Breadcrumbs::render('admin::lenders::create_lenders') }}
    @else
        {{ Breadcrumbs::render('lender_edit') }}
    @endif

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">


            <!-- form start -->
            @if ($action == 'create')
                {!! Form::open(['route' => 'admin::admins::save_lender_data', 'method' => 'POST', 'id' => 'crete_admin_form']) !!}
            @else
                {!! Form::open(['route' => ['admin::admins::update_lender', 'id' => $lender->id], 'method' => 'POST', 'id' => 'edit_admin_form']) !!}
            @endif

            <div class="box-body">
                @include('layouts.admin.partials.lte_alerts')
                <div class="row">

                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Name <font color="#FF0000"> * </font></label>
                        {!! Form::text('name', isset($lender) ? $lender->name : old('name'), ['class' => 'form-control', 'id' => 'lenderNameId', 'placeholder' => 'Enter Name']) !!}
                    </div>

                    <?php $userId = Auth::user()->id; ?>
                    {!! Form::hidden('creator_id', $userId) !!}

                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Email Id <font color="#FF0000"> * </font></label>
                        {!! Form::email('email', isset($lender) ? $lender->email : old('email'), ['class' => 'form-control', 'placeholder' => 'Enter Email Id']) !!}
                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputPassword1">Password <font color="#FF0000"> * </font></label>
                        {!! Form::password('password', [
    'class' => 'form-control',
    'placeholder' => 'Enter password
                    ',
    'id' => 'password',
    'minlength' => 6,
]) !!}
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="exampleInputPassword1">Confirm Password <font color="#FF0000"> * </font></label>
                        {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Enter Password ', 'minlength' => 6]) !!}
                    </div>

                    <div class="form-group col-md-4">

                        <label for="exampleInputEmail1">Management Fee (%)
                        </label>

                        {!! Form::select('management_fee', $fee_values, isset($lender) ? number_format($lender->management_fee,2) : old('management_fee'), ['class' => 'form-control', 'placeholder' => 'Enter Management Fee', 'id' => 'inputManagementFee']) !!}

                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Lag Time (In Days)</label>
                        {!! Form::text('lag_time', isset($lender) ? $lender->lag_time : old('lag_time'), ['class' => 'form-control lag_time', 'placeholder' => 'Enter Lag time']) !!}
                    </div>
                </div>


                <div class="row">
                    

                    <?php $prepaid_status = isset($lender->s_prepaid_status) ? $lender->s_prepaid_status :
                    old('s_prepaid_status'); ?>


                    <div class="form-group col-md-12 synd-march">
                        <label for="exampleInputEmail1">Syndication Fee (%)</label>

                        <div class="input-group">
                            {!! Form::select('global_syndication', $fee_values, isset($lender) ? number_format($lender->global_syndication,2) : old('global_syndication'), ['class' => 'form-control', 'placeholder' => 'Enter Syndication Fee', 'id' => 'inputGlobalSyndication']) !!}
                            <div class="mrch">
                                <span class="input-group-text percenage">%</span>
                                <span class="input-group-text"><label>
                                        <input {{ $prepaid_status == 2 ? 'checked' : '' }} value="2" type="radio"
                                            name="s_prepaid_status"> On Funding Amount?</label>
                                </span>

                                <span class="input-group-text"><label>
                                        <input {{ $prepaid_status == 1 ? 'checked' : '' }} value="1" type="radio"
                                            name="s_prepaid_status"> On RTR?</label></span>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    
                    $underwriting_status = isset($lender->underwriting_status) ? $lender->underwriting_status : '';
                    $underwriting_status = json_decode($underwriting_status);
                    
                @endphp




                <div class="row">

                    <div class="col-md-12">
                        <div class="form-group synd-march">
                            <label for="exampleInputEmail1">Underwriting Fee
                            </label>
                            <div class="input-group">

                                {!! Form::select('underwriting_fee', $fee_values, isset($lender) ? number_format($lender->underwriting_fee,2) : old('underwriting_fee'), ['class' => 'form-control', 'pattern' => "^-?[0-9]\d*(\.\d+)?$", 'min' => '0', 'max' => '5', 'id' => 'underwriting_fee']) !!}

                                <div class="mrch">
                                    <span class="input-group-text">%</span>



                                    @if ($underwriting_company)

                                        @foreach ($underwriting_company as $key => $value)


                                            @php
                                                
                                                $status = isset($underwriting_status) ? in_array($key, !empty($underwriting_status) ? $underwriting_status : []) : 0;
                                                $checked = isset($status) ? ($status == $key ? 'checked' : '') : '';
                                                
                                            @endphp

                                            <span class="input-group-text"><label>

                                                    <input type="checkbox" name="underwriting_status[]"
                                                        value="{{ $key }}" {{ $checked }}
                                                        id="m_underwriting_status_velocity" /> {{ $value }}

                                                </label></span>




                                        @endforeach
                                    @endif

                                </div>
                            </div>
                            <!-- <span class="errors_msg1" id="underwriting_fee_error"></span> -->
                        </div>
                    </div>

                </div>



                <!-- /.box-body -->

                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        @if (@Permissions::isAllow('Lenders', 'View'))
                            <a href="{{ URL::to('admin/lender') }}" class="btn btn-success">View Lenders</a>
                        @endif
                        @if ($action == 'create')
                            {!! Form::submit('Create', ['class' => 'btn btn-primary']) !!}
                        @else
                            {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}
                        @endif
                    </div>
                </div>
                {!! Form::close() !!}

            </div>
            <!-- /.box -->
        </div>


    </div>


@stop
@section('scripts')

    <script src='{{ asset('js/jquery_validate_min.js') }}' type="text/javascript"></script>
    <script>
        $(document).ready(function() {

            $("input.lag_time").keypress(function(event) {
                return /\d/.test(String.fromCharCode(event.keyCode));
            });


            $("#lenderNameId").on("input", function() {
                var regexp = /[^a-zA-Z ]*$/;
                if ($(this).val().match(regexp)) {
                    $(this).val($(this).val().replace(regexp, ''));
                }
            });






            $('#crete_admin_form').validate({ // initialize the plugin
                errorClass: 'errors',
                rules: {
                    name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },

                    lag_time: {
                        number: true,
                    },
                    'underwriting_status[]': {

                        required: function(element) {
                            if ($('#underwriting_fee').val() != 0)
                                return true;
                            else
                                return false;
                        },

                    },
                    password: {
                        required: true,
                        maxlength: 255,
                        minlength: 6
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password",
                        maxlength: 255,
                        minlength: 6
                    },
                    s_prepaid_status: {
                        required: function(element) {
                            if ($('#inputGlobalSyndication').val() != 0)
                                return true;
                            else
                                return false;
                        },
                    },
                    'underwriting_status[]': {
                        required: function(element) {
                            if ($('#underwriting_fee').val() != 0)
                                return true;
                            else
                                return false;
                        },
                    }

                },
                messages: {
                    name: {
                        required: "Enter Name",
                    },
                    email: {
                        required: "Enter Email Address",
                    },
                    "underwriting_status[]": {
                        required: "Select underwriting status",
                    },
                    password: {
                        required: "Please Enter Password",
                        minlength: "Please enter atleast 6 characters.",
                        maxlength: "Password can be max 255 characters long.",
                    },
                    password_confirmation: {
                        required: "Please Confirm Password",
                        equalTo: "Password Confirmation Does Not Match",
                        minlength: "Please enter atleast 6 characters.",
                        maxlength: "Password can be max 255 characters long.",

                    },
                    s_prepaid_status: {
                        required: "Enter Prepaid Status"
                    },

                }

            });


            $('#edit_admin_form').validate({ // initialize the plugin
                errorClass: 'errors',
                rules: {
                    s_prepaid_status: {
                        required: function(element) {
                            if ($('#inputGlobalSyndication').val() != 0)
                                return true;
                            else
                                return false;
                        },
                    },

                },
                messages: {
                    s_prepaid_status: {
                        required: "Enter Prepaid Status"
                    },

                }

            });



        });

    </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_lender.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
        type="text/css" />
@stop
