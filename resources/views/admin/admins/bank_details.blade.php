@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Create Account</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Create Account</div>
        </a>

    </div>

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-sm-wrap box-primary">


            <!-- form start -->

            @if ($action == 'create')
                @if (@Permissions::isAllow('Bank Details', 'Create'))
                    {!! Form::open(['route' => 'admin::storeBank', 'method' => 'POST', 'id' => 'account_create_form', 'class' => 'bank_form']) !!}
                @endif
            @else
                @if (@Permissions::isAllow('Bank Details', 'Edit'))
                    {!! Form::open(['route' => ['admin::updateBank', 'id' => $bank_details->id], 'method' => 'POST', 'id' => 'account_edit_form', 'class' => 'bank_form']) !!}
                    {{ Form::hidden('edit', $bank_details->id) }}
                @endif
            @endif


            <div class="box-body box-body-sm bank-det">
                @include('layouts.admin.partials.lte_alerts')

                <div class="form-group">
                    <label for="exampleInputEmail1">Bank Name <span class="validate_star">*</span></label>
                    {!! Form::text('name', isset($bank_details) ? $bank_details->bank_name : old('name'), ['class' => 'form-control', 'id' => 'bankNameId', 'placeholder' => 'Enter Your Bank Name']) !!}
                </div>

                <div class="form-group">
                    <label for="exampleInputEmail1">Account Number <span class="validate_star">*</span></label>
                    {!! Form::text('acc_number', isset($bank_details) ? $bank_details->account_no : old('acc_number'), ['class' => 'form-control', 'placeholder' => 'Enter Account Number']) !!}
                </div>

                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        @if (@Permissions::isAllow('Bank Details', 'View'))
                            <a href="{{ URL::to('admin/viewbank') }}" class="btn btn-success">View Accounts</a>
                        @endif
                        @if ($action == 'create')
                            {!! Form::submit('Create', ['class' => 'btn btn-primary']) !!}
                        @else
                            {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}
                        @endif
                    </div>
                </div>

            </div>
            <!-- /.box-body -->


            {!! Form::close() !!}
        </div>
        <!-- /.box -->


    </div>


@stop

@section('scripts')

    <script>
        $("#bankNameId").on("input", function() {
            var regexp = /[^a-zA-Z ]*$/;
            if ($(this).val().match(regexp)) {
                $(this).val($(this).val().replace(regexp, ''));
            }
        });
        $(document).ready(function() {
            $('.bank_form').validate({ // initialize the plugin
                errorClass: 'errors',
                rules: {

                    name: {
                        required: true,
                        maxlength: 255,
                    },
                    acc_number: {
                        required: true,
                        maxlength: 255,
                        digits: true,
                    },



                },
                messages: {

                    name: {
                        required: "Enter Bank Name",
                    },
                    acc_number: {
                        required: "Enter Account No",
                        digits: "Enter Valid Acccount No"

                    },



                },

            });

        });

    </script>

    <link href="{{ asset('/css/optimized/Bank_accounts.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop
