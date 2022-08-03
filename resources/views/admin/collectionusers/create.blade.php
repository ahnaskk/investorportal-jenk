@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ isset($page_title) ? $page_title : '' }}</div>
        </a>
    </div>
    @if ($action == 'Create')
        {{ Breadcrumbs::render('collectionsUsersCreate') }}
    @else
        {{ Breadcrumbs::render('collectionsUsersEdit') }}
    @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary branch_mng box-sm-wrap">
            @php  $validation=isset($collection_user->id)?'':'<span class="validate_star">*</span>';  @endphp
            <!-- form start -->
            @if ($action == 'Create')
                {!! Form::open(['route' => 'admin::collection_users::storeCreate', 'method' => 'POST', 'id' => 'collection_user_form']) !!}
            @else
                {!! Form::open(['route' => ['admin::collection_users::update', 'id' => $collection_user->id], 'method' => 'POST', 'id' => 'edit_collection_user_form']) !!}
            @endif
            <div class="box-body box-body-sm">
                @include('layouts.admin.partials.lte_alerts')

                <div class="form-group">
                    <label for="exampleInputEmail1">Name <span class="validate_star">*</span></label>
                    {!! Form::text('name', isset($collection_user->name) ? $collection_user->name : old('name'), ['class' => 'form-control', 'id' => 'nameFieldId', 'placeholder' => 'Enter Collection User Name']) !!}
                </div>

                <div class="form-group">
                    <label for="exampleInputEmail1">Email Id <span class="validate_star">*</span></label>
                    {!! Form::email('email', isset($collection_user->email) ? $collection_user->email : old('email'), ['class' => 'form-control', 'placeholder' => 'Enter Collection User email']) !!}
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">Password {!! $validation !!}</label>
                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Enter password ', 'id' => 'inputPassword', 'minlength' => 6]) !!}
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">Confirm Password {!! $validation !!}</label>
                    {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Enter Password', 'minlength' => 6]) !!}
                </div>

                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        @if (@Permissions::isAllow('Collection Users', 'View'))
                            <a href="{{ URL::to('admin/collection_users') }}" class="btn btn-success">View Collection
                                User</a>
                        @endif
                        @if ($action == 'Create')
                            {!! Form::submit('Create', ['class' => 'btn btn-primary bran-mng-bt']) !!}
                        @else
                            {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}
                        @endif
                    </div>
                </div>
            </div>
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
            $('#collection_user_form').validate({ // initialize the plugin
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

                    password: {
                        required: "Please Enter Password",
                        minlength: "Please enter atleast 6 characters.",
                        maxlength: "Password can be max 255 characters long.",
                    },
                    password_confirmation: {
                        required: "You must confirm your password",
                        minlength: "Your password must contain more than 6 characters",
                        equalTo: "Passwords Must Match" // custom message for mismatched passwords
                    },
                },
            });

            $('#edit_collection_user_form').validate({ // initialize the plugin
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
    <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet"
        type="text/css" />

@stop
