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

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Merchant Activity Log</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Merchant Activity Log</div>
        </a>
    </div>
    @if($merchant)
  {{ Breadcrumbs::render('merchantLog',$merchant) }}
  @endif
    <div class="col-md-12">


        <!-- general form elements -->
        <div class="box box-primary">
            @include('layouts.admin.partials.lte_alerts')
            <div class="box-body">
                <div class="panel-body" id="user_activity_log">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="glyphicon glyphicon-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control date datepicker" autocomplete="off" name="from_date1" placeholder="From">
                                    <input type="hidden" name="from_date" class="date_parse">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="glyphicon glyphicon-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control date datepicker" autocomplete="off" name="to_date1" placeholder="To">
                                    <input type="hidden" name="to_date" class="date_parse">
                            </div>
                        </div>

                          <div class="col-sm-2">
                            {!! Form::select('search_type', \App\UserActivityLog::logType(), null, [ 'class' => 'form-control search_select log_search_type', 'placeholder' => 'Select Type']) !!}
                        </div>
                        
                        <div class="col-sm-2">
                            {!! Form::select('search_action', \App\UserActivityLog::activity_actions(), null, [ 'class' => 'form-control search_select search_action', 'placeholder' => 'Select Action']) !!}
                        </div>
                        <div class="col-sm-2">
                            <button id="apply_filter" type="button" class="btn btn-success">Apply Filter</button>
                        </div>
                        <br>
                        <br>
                        <div class="clearfix"></div>
                        <div class="col-sm-12 mt-3">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover user_activity_log_table">
                                    <thead>
                                    <tr>
                                        <th>Modified By</th>
                                        <th>Modified At</th>
                                        <th>Change</th>
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
             var merchant_id="{{ $merchant_id }}";

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
                "searchDelay": 1500,
                'pagingType': 'input',
                ajax:{
                     "url": '{{ url('admin/merchants/records') }}',
                    "data": function (data) {
                        data.merchant_id    = merchant_id;
                        data.type           = $('#user_activity_log').find('.log_search_type').val();
                        data.user_id        = $('#user_activity_log').find('.log_search_user').val();
                        data.action         = $('#user_activity_log').find('.search_action').val();
                        data.from_date      = $('#user_activity_log input[name=from_date]').val();
                        data.to_date        = $('#user_activity_log input[name=to_date]').val();
                    }
                }
            });
            // $('#user_activity_log .log_search_type,#user_activity_log .log_search_user,#user_activity_log .date, #user_activity_log .search_action').on('change', function () {
            //     userActivityLogTable.fnFilter('', 0, false);
            // });
            $('#apply_filter').click(function (){
                userActivityLogTable.fnFilter('', 0, false);
            });
            // dateGroupDiv('.input-group .date');
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