@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Create Role</div>
        </a>

    </div>
    @if ($action == 'create')
        {{ Breadcrumbs::render('admin::roles::create-role') }}
    @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">
            <!-- form start -->
            @if ($action == 'create')
                {!! Form::open(['route' => 'admin::admins::save-role-data', 'method' => 'POST', 'id' => 'crete_role_form']) !!}
            @else
                {!! Form::open(['route' => ['admin::admins::update_role', 'id' => $role->id], 'method' => 'POST']) !!}
            @endif

            <div class="box-body box-body-sm">
                @include('layouts.admin.partials.lte_alerts')
                <div class="form-group">
                    <label for="exampleInputEmail1">Name <font color="#FF0000"> * </font></label>
                    {!! Form::text('name', isset($role) ? $role->name : old('name'), ['class' => 'form-control', 'id' => 'roleName', 'placeholder' => 'Enter Name']) !!}
                </div>
                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        @if (@Permissions::isAllow('Roles', 'View'))
                            <a href="{{ URL::to('admin/role') }}" class="btn btn-success">View Roles</a>
                        @endif
                        @if ($action == 'create')
                            {!! Form::submit('Create', ['class' => 'btn btn-primary']) !!}
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

    <script src='{{ asset('js/jquery_validate_min.js') }}' type="text/javascript"></script>
    <script>
        $("#viewerNameId").on("input", function() {
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
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "#password"
                    },

                },
                messages: {
                    name: "Enter Name",
                    email: {
                        required: "Enter Email Id",
                    },
                    password: "Enter Password",
                    password_confirmation: {
                        required: "Please Confirm Password",
                        equalTo: "Passwords Do Not Match"
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
                },
                messages: {
                    name: "Enter Name",
                    email: {
                        required: "Enter Email Id",
                    },
                }
            });
        });

    </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
