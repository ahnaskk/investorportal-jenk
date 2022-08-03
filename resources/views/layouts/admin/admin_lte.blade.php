<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $page_title ?? "Investor Portal" }}</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no' name='viewport'>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('/css/optimized/header.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/optimized/alert.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bootstrap.min.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/bootstrap-glyphicons.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/select2.min.css?ver=5') }}" rel="stylesheet" type="text/css" />
    @yield('styles')
    <link href="{{ asset('/css/optimized/custom.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/toastr.min.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/global/main.css?ver=5')}}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .select2-selection__rendered {
            display: block !important;
        }
        .select2-search--inline {
            float: none !important;
            margin-left: 8px;
        }
    </style>
    <link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
</head>
<?php use Illuminate\Support\Facades\Auth;
use App\Settings;
use Illuminate\Support\Facades\DB;
use App\User;

?>
<body class="skin-blue sidebar-mini">
    <div class="wrapper demo">
        <div id="spinnerWait" style="display:none; z-index: 100000000; width:2000px;height:2000px;position:fixed;top: 50%;left:50%;padding:2px;"><img src="{{ url('images/spinner.gif') }}" width="100" height="100" /><br>Loading..</div>
        @include('layouts.admin.partials.lte_header')
        <!-- Header -->
        <!-- Sidebar -->
        @include('layouts.admin.partials.lte_sidebar')
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    {{ $page_title ?? "" }}
                    <small>{{ $page_description ?? null }}</small>
                </h1>
            </section>
            <!-- Main content -->
            <section class="content">
                <?php   $restore_status=session('restore');
                $db=session('DB_DATABASE');
                ?>
                @if($restore_status)
                <div class="alert alert-success">
                    <strong> Now Portal is using Restored Database  {{ $db }} </strong>
                </div>
                @endif
                <!-- Your Page Content Here -->
                <?php 
                $role_id = User::join('user_has_roles', 'user_has_roles.model_id', 'users.id')->where('users.id',Auth::user()->id)->value('user_has_roles.role_id');
                $two_factor_required = DB::table('roles')->where('id',$role_id)->value('two_factor_required'); ?>

                @if(Auth::user()->two_factor_secret==null && $two_factor_required==1)
                @if(!Request::is('admin/enable-two-factor-auth') && !Request::is('admin/two-factor-authentication'))
                
                <div class="modal fade" id="confirmTwofactor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-left:10px">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-body text-center">
                            <button type="button" class="close"  data-bs-dismiss="alert" aria-hidden="true" id="close" onclick="$('#confirmPayment').modal('hide');">&times;</button>
                                <span id="paymentbox"></span>
                                <b>Protect your account by enabling two factor authentication</b>
                            </div>
                            <div class="modal-footer">
                                <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                                <a href="{{ route('admin::two-factor-authentication') }}" class="btn btn-primary">Enable</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @endif

                
                @yield('content')
            </section><!-- /.content -->
        </div><!-- /.content-wrapper -->
        <!-- Footer -->
        @include('layouts.admin.partials.lte_footer')
    </div><!-- ./wrapper -->
    <!-- REQUIRED JS SCRIPTS -->
    <script src="{{asset('js/custom/helper.js')}}"></script>
    <!-- jQuery 2.1.3 -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>



    <!-- Bootstrap 3.3.2 JS -->
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
   
    <!-- <script src="{{ asset ('bower_components/select2/dist/js/select2.min.js') }}"></script> -->
    <script src="{{ asset ('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset ('bower_components/fastclick/lib/fastclick.js') }}" type="text/javascript"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset ('bower_components/admin-lte/dist/js/adminlte.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('bower_components/admin-lte/dist/js/demo.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('vendor/notyf/notyf.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/pusher.min.js') }}"></script>
    <script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>
    <script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
    <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTablesSelect.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="{{ asset('/js/moment.min.js') }}"></script>
    <script src="{{ asset('/js/jquery-mask.min.js') }}"></script>

    <script src="{{ asset('/js/checkboxes.js') }}"></script>





    <script type="text/javascript">
    var pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster')}}',
        encrypted: true
    });
    var dashboardChannel = pusher.subscribe('{{ request()->getHost() }}-dashboard-job-success-{{ Auth::user()->id }}');
    dashboardChannel.bind('pusher:subscription_succeeded', function(members) {});
    dashboardChannel.bind('App\\Events\\DashboardJobSuccessEvent', function(data) {
        console.log('pusher success ', data);
        toastrShow('Success', data.message);
    });
    function toastrShow(text,title) {
        setTimeout(function() {
            toastr.options = {
                closeButton: true,
                "preventDuplicates": true,
                "progressBar": false,
                showMethod: 'slideDown',
                "positionClass": "toast-top-center",
                "showEasing": "swing",
                timeOut: 6000
            };
            toastr.success(title, text);
        }, 1300);
    }
    var URL_getMerchants = "{{ URL::to('admin/getSelect2Merchants') }}";
    var URL_getInvestors = "{{ URL::to('admin/getSelect2Investors') }}";
    var url = window.location;
    // for treeview
    $('ul.treeview-menu a').filter(function() {
        var pathArray = window.location.pathname.split( '/' );
        if(pathArray[2]=="merchant_batches"){
            $('.batches').addClass('active');
        }
        return this.href == url;
    }).closest('.treeview').addClass('active');
    $('select').select2();
    $(document).ready(function(){
        var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
        var mask_format = maskDateFormat(default_date_format)
        moment.parseTwoDigitYear = function (yearString) {
        return parseInt(yearString) + (parseInt(yearString) > 19 ? 2000 : 2000);
    }
        $('.datepicker').each(function(){
            var val = $(this).val();
            var moment_date = moment(val).format(default_date_format);
            if(val) { 
                $(this).val(moment_date);
                $(this).siblings('.date_parse').val(moment(val).format('YYYY-MM-DD')).attr('autocomplete', 'off');
            }
            $(this).mask(mask_format);
        });
        
        $('.datepicker').not('[readonly]').datepicker({
            format: default_date_format.toLowerCase(),
            clearBtn: true,
            todayBtn: "linked"
        });
        $('.datepicker,.multi-datepicker').on('keypress input change paste', function (event) {
            var val = $(this).val();
            var regex = new RegExp("^[a-zA-Z]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (regex.test(key)) {
            event.preventDefault();
            return false;
            }
        });    
        $('.datepicker').not('[readonly]').on("change changeDate", function(){
            var val = $(this).val();
            if(val && moment(val, default_date_format).isValid())
            {
                let year = moment(val, default_date_format).year();
                if(year.toString().length == 1 || year.toString().length == 2) {
                    year = moment(year, 'YY').format('YYYY');
                }
                var newDate = moment(val, default_date_format).set('year', year).format(default_date_format);
                $(this).val(newDate);
                $(this).datepicker('update');
                $(this).siblings('.date_parse').val(moment(val, default_date_format).set('year', year).format('YYYY-MM-DD'));
            }else {
                $(this).siblings('.date_parse').val('');
            }
        })
    });
    var notyf = new Notyf({delay:20000
    });
    {{--var pusher = new Pusher('{{config('settings.pusher_app_key')}}',{--}}
    {{--cluster: '{{config('settings.pusher_cluster')}}'--}}
    {{--});--}}
    {{--var channel = pusher.subscribe('admin');--}}
    {{--channel.bind('admin.notified', function(data) {--}}
    {{--console.log(data);--}}
    {{--noti_li='<li><a href="#"><h5>'+data.title+'<small><i class="fa fa-clock-o"></i> <span class="timeago" title="'+data.timestamp+'">now</span></small></h5><p class="notify">'+data.content+'</p></a></li>';--}}
    {{--$('#notification_head').prepend(noti_li);--}}
    {{--$('#notification_count').html(parseInt($('#notification_count').html(), 10)+1)--}}
    {{--/*    $('#notification_count').prepend(noti_li);*/--}}
    {{--notyf.confirm(data.content);--}}
    {{--});--}}
    /*Time ago function*/
    (function timeAgo(selector) {
        var templates = {
            prefix: "",
            suffix: " ago",
            seconds: "less than a mins",
            minute: "about a mins",
            minutes: "%d mins",
            hour: "about an hour",
            hours: "about %d hours",
            day: "a day",
            days: "%d days",
            month: "about a month",
            months: "%d months",
            year: "about a year",
            years: "%d years"
        };
        var template = function (t, n) {
            return templates[t] && templates[t].replace(/%d/i, Math.abs(Math.round(n)));
        };
        var timer = function (time) {
            if (!time) return;
            time = time.replace(/\.\d+/, ""); // remove milliseconds
            time = time.replace(/-/, "/").replace(/-/, "/");
            time = time.replace(/T/, " ").replace(/Z/, " UTC");
            time = time.replace(/([\+\-]\d\d)\:?(\d\d)/, " $1$2"); // -04:00 -> -0400
            time = new Date(time * 1000 || time);
            var now = new Date();
            var seconds = ((now.getTime() - time) * .001) >> 0;
            var minutes = seconds / 60;
            var hours = minutes / 60;
            var days = hours / 24;
            var years = days / 365;
            return templates.prefix + (
                seconds < 45 && template('seconds', seconds) || seconds < 90 && template('minute', 1) || minutes < 45 && template('minutes', minutes) || minutes < 90 && template('hour', 1) || hours < 24 && template('hours', hours) || hours < 42 && template('day', 1) || days < 30 && template('days', days) || days < 45 && template('month', 1) || days < 365 && template('months', days / 30) || years < 1.5 && template('year', 1) || template('years', years)) + templates.suffix;
            };
            var elements = document.getElementsByClassName('timeago');
            for (var i in elements) {
                var $this = elements[i];
                if (typeof $this === 'object') {
                    $this.innerHTML = timer($this.getAttribute('title') || $this.getAttribute('datetime'));
                }
            }
            // update time every minute
            setTimeout(timeAgo, 60000);
        })();
    var selector = '.slid-drp';
    $(selector).on('click', function(){
        $(selector).removeClass('active');
        $('.treeview').removeClass('active');
        $(this).addClass('active');
    });
    var _token = '{{csrf_token()}}';
    function Navigate(){
        window.location.replace('merchants');
        return false;
    }



    


      
    </script>
    @yield('scripts')
    <script type="text/javascript">
    $(document).ready(function(){
        $('#confirmTwofactor').modal('show');

        $(document).ajaxStart(function(e)    { $("#spinnerWait").show(); });
        $(document).ajaxComplete(function(e) { $("#spinnerWait").hide(); });
    });
    </script>

        <!-- Hotjar Tracking Code for https://investorportal.vgusa.com -->
    @if(config('app.env')=='production')    
    <script>
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:2547033,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>
    @endif



</body>
</html>
