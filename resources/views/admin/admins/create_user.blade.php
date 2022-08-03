@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ isset($page_title) ? $page_title : '' }} </div>
        </a>

    </div>
    @if ($action == 'create')
        {{ Breadcrumbs::render('admin::roles::create-user') }}
    @else
        {{ Breadcrumbs::render('userRolesEdit') }}
    @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">


            <!-- form start -->
            @if ($action == 'create')
                {!! Form::open(['route' => 'admin::admins::save_user_role_data', 'method' => 'POST', 'id' => 'crete_admin_form']) !!}
            @else
                {!! Form::open(['route' => ['admin::admins::update_user_role', 'id' => $lender->id], 'method' => 'POST', 'id' => 'edit_editor_form']) !!}
            @endif


            <div class="box-body box-body-sm">
                @include('layouts.admin.partials.lte_alerts')
                <div class="form-group">
                    <label for="exampleInputEmail1">Name <font color="#FF0000"> * </font></label>
                    {!! Form::text('name', isset($lender) ? $lender->name : old('name'), ['class' => 'form-control', 'id' => 'viewerNameId', 'placeholder' => 'Enter Name', 'minlength' => '1', 'maxlength' => '255']) !!}
                </div>
                <?php $userId = Auth::user()->id; ?>


                {!! Form::hidden('creator_id', $userId) !!}

                <div class="form-group">

                    <label for="exampleInputEmail1">Email Id <font color="#FF0000"> * </font></label>
                    {!! Form::email('email', isset($lender) ? $lender->email : old('email'), ['class' => 'form-control', 'placeholder' => 'Enter Email Id']) !!}
                </div>


                <div class="form-group">
                    <label for="exampleInputPassword1">Password  <font color="#FF0000"> * </font></label>
                    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Enter Password', 'id' => 'password']) !!}
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">Confirm Password  <font color="#FF0000"> * </font></label>
                    {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Enter Password ']) !!}
                </div>

                <div class='form-group'>
                    <label for="exampleInputPassword1">Assign roles <font color="#FF0000"> </font></label>
                    <ul class="list-group list-group-horizontal role-styles-ul">

                        @foreach ($roles as $role)
                            @if ($action == 'create')
                                <li class="list-group-item flex-fill role-styles"> {{ Form::checkbox('roles[]', $role->id,'',['id' => "$role->name"]) }}
                                    {{ Form::label($role->name, ucfirst($role->name)) }}
                                </li>
                            @else
                                <li class="list-group-item flex-fill role-styles">
                                    @if ($lender->hasAnyRole([$role]))
                                        {{ Form::checkbox('roles[]', $role->id, true, ['class' => 'checkRoles','id' => "$role->name"]) }}
                                        {{ Form::label($role->name, ucfirst($role->name)) }}
                                    @else
                                        {{ Form::checkbox('roles[]', $role->id, '', ['class' => 'checkRoles','id' => "$role->name"]) }}
                                        {{ Form::label($role->name, ucfirst($role->name)) }}
                                    @endif
                                </li>
                            @endif


                        @endforeach


                    </ul>
                </div>

                <!-- /.box-body -->

                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        @if (@Permissions::isAllow('Users', 'View'))
                            <a href="{{ URL::to('admin/role/show-user-role') }}" class="btn btn-success">View Users</a>
                        @endif
                        @if (@Permissions::isAllow('Merchants', 'View'))
                            <a href="{{ URL::to('admin/merchants') }}" class="btn btn-success">View Merchants</a>
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
        </div>
        <!-- /.box -->


    </div>


@stop
@section('scripts')

    <script src='{{ asset('js/jquery_validate_min.js') }}' type="text/javascript"></script>
    <script>
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
                        required: "Enter Email Id",
                    },
                    password: {
                        required: "Enter Password",
                        minlength: "Please enter atleast 6 characters.",
                        maxlength: "Password can be max 255 characters long.",
                    },
                    password_confirmation: {
                        required: "Please Confirm Password",
                        equalTo: "Passwords Do Not Match",
                        minlength: "Please enter atleast 6 characters.",
                        maxlength: "Password can be max 255 characters long.",
                    },

                }

            });
            $('#edit_editor_form').validate({ // initialize the plugin
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
                        required: "Enter Email Id",
                    },

                    password: {
                        minlength: "Please enter atleast 6 characters.",
                        maxlength: "Password can be max 255 characters long.",
                    },
                    password_confirmation: {
                        equalTo: "Passwords Do Not Match",
                        minlength: "Please enter atleast 6 characters.",
                        maxlength: "Password can be max 255 characters long.",
                    },


                }

            });

            $('.checkRoles').click(function() {
                $('.checkRoles').not(this).prop('checked', false);
            });
        });

    </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
