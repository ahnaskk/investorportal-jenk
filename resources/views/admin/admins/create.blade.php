@extends('layouts.admin.admin_lte')
@section('content')
    <div class="inner admin-dsh header-tp">
        <!-- <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Edit Admin</h3> -->
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ isset($page_title) ? $page_title : '' }} </div>
        </a>

    </div>
    @if ($action == 'create')
        {{ Breadcrumbs::render('create_admin_user') }}
    @else
        {{ Breadcrumbs::render('edit_admin_user') }}
    @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">
            @php  $validation=isset($admin->id)?'':'<span class="validate_star">*</span>';  @endphp
            <!-- form start -->
            @if ($action == 'create')
                {!! Form::open(['route' => 'admin::admins::storeCreate', 'method' => 'POST', 'id' => 'crete_admin_form']) !!}
            @else
                {!! Form::open(['route' => ['admin::admins::update', 'id' => $admin->id], 'method' => 'POST', 'id' => 'admin_form']) !!}
            @endif
            <div class="box-body box-body-sm">

                @include('layouts.admin.partials.lte_alerts')
                <div class="form-group">
                    <label for="exampleInputEmail1">Name <span class="validate_star">*</span></label>
                    {!! Form::text('name', isset($admin) ? $admin->name : old('name'), ['class' => 'form-control', 'id' => 'nameFieldId', 'placeholder' => 'Enter Name']) !!}
                </div>

                <?php $userId = Auth::user()->id; ?>
                {!! Form::hidden('creator_id', $userId) !!}
                <div class="form-group">
                    <label for="exampleInputEmail1">Email Address <span class="validate_star">*</span></label>
                    {!! Form::email('email', isset($admin) ? $admin->email : old('email'), ['class' => 'form-control', 'placeholder' => 'Enter Email']) !!}
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Password {!! $validation !!}</label>
                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Enter password', 'id' => 'password', 'minlength' => 6]) !!}
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">Confirm Password {!! $validation !!}</label>
                    {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Enter Password', 'minlength' => 6]) !!}
                </div>


                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        @if (@Permissions::isAllow('Admins', 'View'))
                            <a href="{{ URL::to('admin/admin') }}" class="btn btn-success">View Admin</a>
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
        $("#nameFieldId").on("input", function() {
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
                    password: {
                        required: true,
                        maxlength: 255,
                        minlength: 6

                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password",
                        maxlength: 255,
                        minlength: 6,
                    },

                },
                messages: {
                    name: "Enter Name",
                    email: {
                        required: "Enter Email Address",
                    },
                    password: {
                        required: "Please Enter Password",
                        minlength: "Please enter atleast 6 characters.",
                        maxlength: "Password can be max 255 characters long.",
                    },
                    password_confirmation: {
                        required: "Please Confirm Password",
                        equalTo: "Password Confirmation Does Not Match",
                        minlength: "Your password must contain more than 6 characters.",
                        maxlength: "Password can be max 255 characters long.",

                    },

                }

            });


            $('#admin_form').validate({ // initialize the plugin
                errorClass: 'errors',
                rules: {
                    name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    password: {

                        maxlength: 255,
                        minlength: 6

                    },
                    password_confirmation: {

                        equalTo: "#password",
                        maxlength: 255,
                        minlength: 6,
                    },

                },
                messages: {
                    name: "Enter Name",
                    email: {
                        required: "Enter Email Address",
                    },
                    password: {

                        minlength: "Your password must contain more than 6 characters.",
                        maxlength: "Password can be max 255 characters long.",
                    },
                    password_confirmation: {

                        equalTo: "Password Confirmation Does Not Match",
                        minlength: "Your password must contain more than 6 characters.",
                        maxlength: "Password can be max 255 characters long.",

                    },

                }

            });





        });

    </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_user_admin.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
        type="text/css" />
@stop
