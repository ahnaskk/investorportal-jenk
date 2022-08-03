@extends('layouts.admin.admin_lte')

@section('content')
    <?php 
    $fee = [
    '0' => '0',
    '0.25' => '0.25',
    '0.5' => '0.5',
    '0.75' => '0.75',
    '1' => '1',

    '1.25' => '1.25',

    '1.5' => '1.5',

    '1.75' => '1.75',

    '2' => '2',

    '2.25' => '2.25',

    '2.5' => '2.5',

    '2.75' => '2.75',

    '3' => '3',

    '3.25' => '3.25',

    '3.5' => '3.5',

    '3.75' => '3.75',

    '4' => '4',

    '4.25' => '4.25',

    '4.5' => '4.5',

    '4.75' => '4.75',

    '5' => '5',
    ]; ?>
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">View Lender</div>
        </a>

    </div>

    {{ Breadcrumbs::render('lender_view') }}
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
            <div class="box-body">
                @include('layouts.admin.partials.lte_alerts')
                <div class="row">

                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Name </label>
                        {{ $lender->name }}
                    </div>

                    <?php $userId = Auth::user()->id; ?>
                    {!! Form::hidden('creator_id', $userId) !!}

                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Email Id </label>
                        {{ $lender->email }}
                    </div>
                    <div class="form-group col-md-4">

                        <label for="exampleInputEmail1">Management Fee (%)</label>
                        {{ FFM::percent($lender->management_fee) }}
                    </div>

                   
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Syndication Fee </label>
                        {{ FFM::percent($lender->global_syndication) }}
                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">On Funding Amount? </label>
                        @if ($lender->s_prepaid_status == 2)
                            Yes
                        @else
                            No
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">On RTR? </label>
                        @if ($lender->s_prepaid_status == 1)
                            Yes
                        @else
                            No
                        @endif
                    </div>

                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Underwriting Fee </label>
                        {{ $lender->underwriting_fee }} %
                    </div>

                    @php
                        $underwriting_status = isset($lender->underwriting_status) ? $lender->underwriting_status : '';
                        $underwriting_status = json_decode($underwriting_status);
                    @endphp

                    @if ($underwriting_company)
                        @foreach ($underwriting_company as $key => $value)
                            @php  
                                $status = isset($underwriting_status) ? in_array($key, !empty($underwriting_status) ? $underwriting_status : []) : 0;
                                $checked = isset($status) ? ($status == $key ? 'checked' : '') : '';
                                
                            @endphp
                            <div class="form-group col-md-4">
                                <label for="exampleInputEmail1">{{ $value }} </label>
                                @if ($checked)
                                    Yes
                                @else
                                    No
                                @endif
                            </div>
                        @endforeach
                    @endif
                    <div class="form-group col-md-4">
                        <label for="exampleInputEmail1">Lag Time (In Days)</label>
                        {{ $lender->lag_time ? $lender->lag_time : 0 }}
                    </div>
                </div>
                
                <!-- /.box-body -->

                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        <a href="{{ URL::to('admin/lender') }}" class="btn btn-success">View Lenders</a>
                    </div>
                </div>
            </div>
            <!-- /.box -->
        </div>
    </div>
@stop
@section('scripts')

    <script src='{{ asset('js/jquery_validate_min.js') }}' type="text/javascript"></script>
    <script>
        $("#lenderNameId").on("input", function() {
            var regexp = /[^a-zA-Z ]*$/;
            if ($(this).val().match(regexp)) {
                $(this).val($(this).val().replace(regexp, ''));
            }
        });


        $(document).ready(function() {
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


                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password"
                    },

                },
                messages: {
                    name: "Enter Name",
                    email: {
                        required: "Enter Email Address",
                    },
                    "underwriting_status[]": {
                        required: "Select underwriting status",
                    },
                    password: "Enter your password must contain more than 6 characters",
                    minlength: "Your password must contain more than 6 characters.",
                    maxlength: "Password can be max 12 characters long.",
                    password_confirmation: {
                        required: "Please Confirm Password",
                        equalTo: "Passwords Do Not Match"
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
