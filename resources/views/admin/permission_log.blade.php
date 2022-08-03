@extends('layouts.admin.admin_lte')

@section('styles')
    <link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/optimized/Payment_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <style>
        .orange{
            color: orangered;
        }
        .blue {
            color: #0A246A;
        }
        li.breadcrumb-item.active{
          color: #2b1871!important;
        }
        li.breadcrumb-item a{
          color: #6B778C;
       }
       .input-group .date {
           flex: 1 1 auto;
       }
    </style>
@stop

@section('content')

    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Permission Log</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Permission Log</div>
        </a>
    </div>
    {{ Breadcrumbs::render('admin::permission-log.get.index') }}
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
            @include('layouts.admin.partials.lte_alerts')
            <div class="box-body">
                <div class="panel-body" id="permission-log">
                    <div class="row">
                        <div class="col-sm-12 px-0">
                            <div class="col-sm-2">
                                <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </span>
                                    <input type="text" class="form-control date datepicker" id="from_date" name="from_date1" placeholder="From" autocomplete="off">
                                    <input type="hidden" name="from_date" class="date_parse">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </span>
                                    <input type="text" class="form-control date datepicker" id="to_date" name="to_date1" placeholder="To" autocomplete="off">
                                    <input type="hidden" name="to_date" class="date_parse">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                {!! Form::select('search_module', \App\Models\PermissionLog::modules(), null, [ 'class' => 'form-control search_select log_search_module', 'placeholder' => 'Select Module']) !!}
                            </div>
                            <div class="col-sm-2">
                                {!! Form::select('search_user', \App\Models\PermissionLog::activity_user(), null, [ 'class' => 'form-control search_select log_search_user', 'placeholder' => 'Select Modified By']) !!}
                            </div>

                            <div class="col-sm-2">
                                {!! Form::select('search_action_user', \App\Models\PermissionLog::activity_role_user(), null, ['class' => 'form-control search_select log_search_action_user', 'placeholder' => 'Select Role/User']) !!}
                            </div>
                            <div class="col-sm-2">
                                {!! Form::select('search_action_type', \App\Models\PermissionLog::type(), null, ['class' => 'form-control search_select log_search_type', 'placeholder' => 'Select Type']) !!}
                            </div>
                            
                        </div>
                        <div class="col-sm-12 px-0 mt-3">
                            <div class="col-sm-2">
                                {!! Form::select('search_action', \App\UserActivityLog::activity_actions(), null, [ 'class' => 'form-control search_select search_action', 'placeholder' => 'Select Action']) !!}
                            </div>
                        </div>
                        <div class="col-sm-12 px-0 mt-3">
                            <div class="btn-wrap">
                                <div class="btn-box">
                                    <button id="apply_filter" type="button" class="btn btn-success">Apply Filter</button>
                                </div>
                            </div>
                        </div>
                        <br>
                        <br>
                        <div class="clearfix"></div>
                        <div class="col-sm-12 mt-3">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover permission_log_table">
                                    <thead>
                                    <tr>
                                        <th>Modified By</th>
                                        <th>Modified At</th>
                                        <th>Change</th>
                                        <th>Module</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function()
        {
            var permissionLogTable = $('.permission_log_table').dataTable({
                responsive: true,
                destroy: true,
                processing: true,
                serverSide: true,
                pageLength: 25,
                "searching": true,
                'pagingType': 'input',
                ajax:{
                    "url": '{{ url('admin/permission-log/records') }}',
                    "data": function (data) {
                        data.module         = $('#permission-log').find('.log_search_module').val();
                        data.user_id        = $('#permission-log').find('.log_search_user').val();
                        data.action         = $('#permission-log').find('.search_action').val();
                        data.action_user    = $('#permission-log').find('.log_search_action_user').val();
                        data.from_date      = $('#permission-log input[name=from_date]').val();
                        data.to_date        = $('#permission-log input[name=to_date]').val();
                        data.type           = $('#permission-log').find('.log_search_type').val();
                    }
                }
            });
            $('#apply_filter').click(function (){
                permissionLogTable.fnFilter('', 0, false);
            });
            let startDt = $('#from_date').val() && new Date($('#from_date').val());
            if(startDt){
                $('#to_date').datepicker('setStartDate', startDt);
            }
            $('#from_date').on('changeDate', function(selected){
                let endDateSelected = $('#to_date').val() && new Date($('#to_date').val());
                let minDate = new Date(selected.date.valueOf());
                if(endDateSelected && endDateSelected < minDate){
                    $("#to_date").datepicker('update', "");
                }
                $('#to_date').datepicker('setStartDate', minDate);
            })
        });
        function dateGroupDiv(divFor) {
            $(divFor).datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: false,
                autoclose: true,
                format: 'mm/dd/yyyy',
                todayHighlight: true,
                clearBtn: true,
            })
        }
    </script>

@stop