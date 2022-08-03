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

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Investor Transaction Log</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Investor Transaction Log</div>
        </a>
    </div>
     {{ Breadcrumbs::render('investor_transaction_log') }}
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
            @include('layouts.admin.partials.lte_alerts')
            <div class="box-body">
                <div class="panel-body" id="user_activity_log">
                    <div class="row">
                        <div class="col-sm-12 px-0">
                            <div class="col-sm-3">
                                <div class="input-group ">
                                        <span class="input-group-text">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </span>
                                    <div class="date">
                                        <input type="text" class="form-control datepicker" id="from-date" name="from_date1" placeholder="From" autocomplete="off">
                                        <input type="hidden" name="from_date" class="date_parse">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="glyphicon glyphicon-calendar"></i>
                                        </span>
                                    <div class="date">
                                        <input type="text" class="form-control datepicker" id="to-date" name="to_date1" placeholder="To" autocomplete="off">
                                        <input type="hidden" name="to_date" class="date_parse">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                {!! Form::select('search_object_id',[], null, [ 'class' => 'form-control search_select js-investor-placeholder-multiple search_object_id']) !!}
                            </div>
                            <div class="col-sm-2">
                                {!! Form::select('search_user', \App\UserActivityLog::activity_user(), null, [ 'class' => 'form-control search_select log_search_user', 'placeholder' => 'Select User']) !!}
                            </div><div class="col-sm-2">
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
                        <div class="col-sm-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover user_activity_log_table">
                                    <thead>
                                    <tr>
                                        <th>Modified By</th>
                                        <th>Modified At</th>
                                        <th>Change</th>
                                        <th>Amount</th>
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
<script src="{{ asset('/js/custom/investorSelect2.js') }}"></script> 
    <script type="text/javascript">

        $(document).ready(function()
        {
            $(".js-user-placeholder").select2({
                placeholder: "Select a Users"
            });
            $('#date_filter').click(function (e) {
                e.preventDefault();
                table.draw();
            });

            var userActivityLogTable = $('.user_activity_log_table').dataTable({
                responsive: true,
                destroy: true,
                processing: true,
                serverSide: true,
                pageLength: 25,
                "searching": true,
                'pagingType': 'input',
                ajax:{
                    "url": '{{ url('admin/activity-log/records') }}',
                    "data": function (data) {
                        data.object_id      = $('#user_activity_log').find('.search_object_id').val();
                        data.type           = 'investor_transaction';
                        data.user_id        = $('#user_activity_log').find('.log_search_user').val();
                        data.action         = $('#user_activity_log').find('.search_action').val();
                        data.from_date      = $('#user_activity_log input[name=from_date]').val();
                        data.to_date        = $('#user_activity_log input[name=to_date]').val();
                    }
                }
            });
            $('#apply_filter').click(function (){
                userActivityLogTable.fnFilter('', 0, false);
            });
            // $('#user_activity_log .search_object_id,#user_activity_log .log_search_user,#user_activity_log .date, #user_activity_log .search_action').on('change', function () {
            //     userActivityLogTable.fnFilter('', 0, false);
            // });
            // dateGroupDiv('.input-group .date input');
            
        });
        function dateGroupDiv(divFor) {
            $(divFor).datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: false,
                autoclose: true,
                format: 'mm-dd-yyyy',
                todayHighlight: true,
                clearBtn: true,
            })
        }
    </script>

@stop