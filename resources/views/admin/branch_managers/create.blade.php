@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($title) ? $title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Create A Branch Manager</div>
        </a>
    </div>

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary branch_mng box-sm-wrap">
            <!-- form start -->
            @if ($action == 'Create')
                {!! Form::open(['route' => 'admin::branch_managers::storeCreate', 'method' => 'POST', 'id' => 'branch_form']) !!}
            @else
                {!! Form::open(['route' => ['admin::branch_managers::update', 'id' => $branch_manager->id], 'method' => 'POST', 'id' => 'edit_branch_form']) !!}
            @endif
            <div class="box-body box-body-sm">
                @include('layouts.admin.partials.lte_alerts')
                <div class="form-group">
                    <label for="exampleInputEmail1">Name <span class="validate_star">*</span></label>
                    {!! Form::text('name', isset($branch_manager->name) ? $branch_manager->name : old('name'), ['class' => 'form-control', 'id' => 'nameFieldId', 'placeholder' => 'Enter Name']) !!}
                </div>
                <?php $userId = Auth::user()->id; ?>
                {!! Form::hidden('creator_id', $userId) !!}
                <div class="form-group">
                    <label for="exampleInputEmail1">Email Id <span class="validate_star">*</span></label>
                    {!! Form::email('email', isset($branch_manager->email) ? $branch_manager->email : old('email'), ['class' => 'form-control', 'placeholder' => 'Enter investor email']) !!}
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Password <span class="validate_star">*</span></label>
                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Enter password ', 'id' => 'inputPassword', 'minlength' => 6]) !!}
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">Confirm Password <span class="validate_star">*</span></label>
                    {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Enter Password', 'minlength' => 6]) !!}
                </div>
                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        @if (@Permissions::isAllow('Branch Manager', 'View'))
                            <a href="{{ URL::to('admin/branch_manager') }}" class="btn btn-success">View Branch Manager</a>
                        @endif
                        @if ($action == 'Create')
                            @if (@Permissions::isAllow('Branch Manager', 'Create'))
                                {!! Form::submit('Create', ['class' => 'btn btn-primary bran-mng-bt']) !!}
                            @endif
                        @else
                            @if (@Permissions::isAllow('Branch Manager', 'Edit'))
                                {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}
                            @endif
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
        $("#nameFieldId").on("input", function() {
            var regexp = /[^a-zA-Z ]*$/;
            if ($(this).val().match(regexp)) {
                $(this).val($(this).val().replace(regexp, ''));
            }
        });

        $(document).ready(function() {
            $('#branch_form').validate({ // initialize the plugin
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
                    password: {
                        required: true,
                        maxlength: 255,
                        minlength: 6

                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#inputPassword",
                        maxlength: 255,
                        minlength: 6,
                    },
                },
                messages: {
                    name: "Enter Name",
                    email: {
                        required: "Enter Email Id",
                    },
                    required: "Enter your password",
                    minlength: "Your password must contain more than 6 characters.",
                    password_confirmation: {
                        required: "You must confirm your password",
                        minlength: "Your password must contain more than 6 characters",
                        equalTo: "Passwords Must Match" // custom message for mismatched passwords
                    },
                },
            });
            $('#edit_branch_form').validate({ // initialize the plugin
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
                },
                messages: {
                    name: "Enter Name",
                    email: {
                        required: "Enter Email Id",
                    },
                },
            });
        });
    </script>
    <link href="{{ asset('/css/optimized/create_new_branch_manager.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
