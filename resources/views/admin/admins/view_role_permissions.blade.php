@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($page_title) ? $page_title : '' }} for <span
                style="text-transform: capitalize; font-weight: 900; margin: 0; letter-spacing: 0.025em; color: #3a4c56; font-size: 18px;">{{ $role->name }}</span>
        </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Permissions for <span
                    style="text-transform: capitalize; color: #3a4c56; font-size: 13px; font-weight: 900; line-height: 15px;">{{ $role->name }}</span>
            </div>
        </a>
    </div>
    {{ Breadcrumbs::render('rolesPermissions') }}
    <div class="col-md-12">
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif
        <!-- general form elements -->
        <div class="box-lg-wrap">
            <!-- form start -->
            @if ($action == 'create')
                <!--         {!! Form::open(['route' => 'admin::admins::save-viewer-data', 'method' => 'POST', 'id' => 'crete_admin_form']) !!} -->
            @else
                {!! Form::open(['route' => ['admin::admins::update_role', 'id' => $role->id], 'method' => 'POST']) !!}
            @endif
            <div class="box-body box-body-lg">

                <div class="grid">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Copy Roles Permissions</label>
                                {{ Form::select('roles', $roles, '', ['class' => 'form-control js-roles-placeholder', 'id' => 'roles', 'placeholder' => 'Select Roles', 'onclick' => 'filter_change()']) }}
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-bordered" style="width: 100%;">
                    <thead>
                        <tr style="height:30px;">
                            <th style="padding-left:30px;"><label for="selectalls">Modules - Select all Modules</label> <input type="checkbox"
                                    class="selectall" id="selectalls" />
                            </th>
                            @foreach ($permissions as $p)
                                <th style="padding-left:30px;"><label for="{{ $p['id'] }}">All</label>
                                    <input type="checkbox" id="{{ $p['id'] }}" class="selectallperm"
                                        onclick="selPerm(this)" />
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modules as $m)
                            <tr style="height:30px;">
                                <td style="padding-left:30px;"> {{ $m->name }} </td>
                                @foreach ($checkvalues as $p)
                                    @if ($p['mid'] == $m->id)
                                        <td style="padding-left:30px;"><label for="{{ $p['pid'] . '/' . $p['mid'].$m->name }}">{{ $p['p_name'] }}</label>
                                            {{ Form::checkbox('permissions[]', $p['pid'] . '/' . $p['mid'], $p['status'] == 'yes' ? true : '', ['class' => 'tt view_all', 'id' => $p['pid'] . '/' . $p['mid'].$m->name ]) }}
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        <a href="{{ URL::to('admin/role') }}" class="btn btn-success">View Roles</a>
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

    <div class="modal fade" id="confirmCopyPermission" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Copy Roles Permissions</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {!! Form::open(['method' => 'POST', 'id' => 'copy_permission']) !!}

                <div class="modal-body">
                    <p>Do you want to copy Roles Permissions now ?</p>
                </div>

                <div class="modal-footer">
                    <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal" id="cancel">Cancel</a>
                    <a href="javascript:void(0)" class="btn btn-success" id="submitCopyPermission"
                        data-bs-dismiss="modal">Yes</a>

                    {!! Form::close() !!}

                </div>
            </div>
        </div>
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

            var URL_confirmCopy = "{{ URL::to('admin/admin/copy_permission') }}";
            $('#roles').on('change', function() {
                $('#confirmCopyPermission').modal('show');
            });

            $('#submitCopyPermission').on('click', function() {
                var s_role_id = $('#roles').val();
                var c_role_id = "{{ $role->id }}";
                (window.location = '?role_id=' + s_role_id);
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

            $(".selectall").click(function() {
                $('[name="permissions[]"]').prop("checked", $(this).prop("checked"));
            });

        });

        function selPerm(obj, abc) {

            var pid = $(obj).attr('id');
            var modules = @json($module_ids, JSON_PRETTY_PRINT);
            var i;
            for (i = 0; i < modules.length; ++i) {
                var value = pid + '/' + modules[i];
                $('[value="' + value + '"]').prop("checked", $(obj).prop("checked"));
            }
        }

        $(document).on('click', '.tt', function() {
            let row_col = $(this).val()
            let col = null
            let this_col_total = 0
            let this_col_checked = 0
            try {
                col = row_col.split('/')[0]
            } catch {}
            $(".tt").each(function(index, el) {
                let curr_col = el.value.split('/')[0]
                if (col == curr_col) {
                    this_col_total++
                    if (el.checked) {
                        this_col_checked++
                    }
                }
            })
            if (this_col_total == this_col_checked) {
                $("#" + col).prop('checked', true)
            } else {
                $("#" + col).prop('checked', false)
            }
        })

    </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/optimized/merchants.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
