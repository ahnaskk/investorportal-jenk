<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $page_title ?? "Investor Portal Dashboard" }}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="{{ asset("/bower_components/bootstrap/dist/css/bootstrap.min.css") }}" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="{{ asset("bower_components/font-awesome/css/font-awesome.min.css") }}" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="{{ asset("/bower_components/Ionicons/css/ionicons.min.css") }}" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="{{ asset("/bower_components/admin-lte/dist/css/AdminLTE.min.css")}}" rel="stylesheet" type="text/css" />
        <link href="<?php echo e(asset("/bower_components/select2/dist/css/select2.min.css")); ?>" rel="stylesheet" type="text/css" />

        
     <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect.
    -->
    <link href="{{ asset("/bower_components/admin-lte/dist/css/skins/skin-blue.min.css")}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset("/bower_components/admin-lte/dist/css/skins/header-vishnu-edit.css") }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset ("vendor/notyf/notyf.min.css") }}"rel="stylesheet" type="text/css" />


    <link href="{{ asset('sabil.css')}}" rel="stylesheet" type="text/css" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

<!-- <link href="{{ asset('/css/optimized/header.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/alert.css') }}" rel="stylesheet" type="text/css" />
 -->  
    @yield('styles')
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body class="skin-blue">
<div class="wrapper">

    @include('layouts.investor.partials.lte_header')
            <!-- Header -->

            <!-- Sidebar -->
    @include('layouts.investor.partials.lte_sidebar')

            <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper body-wrap grid">
        <!-- Content Header (Page header) -->
        
            <div class="col-md-12 col-sm-12">
                <section class="content-header title-investor">
                    <h1>
                        {{ $page_title ?? "" }}
                        <small>{{ $page_description ?? null }}</small>
                    </h1>
                </section>
            </div>

        

        <!-- Main content -->
        <section class="content graph-sec grid">
            <!-- Your Page Content Here -->
            @yield('content')
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <!-- Footer -->
    @include('layouts.investor.partials.lte_footer')

</div><!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 2.1.3 -->
<script src="{{ asset ("bower_components/jquery/dist/jquery.min.js") }}"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="{{ asset ("bower_components/bootstrap/dist/js/bootstrap.min.js") }}" type="text/javascript"></script>

<script src="{{ asset ("bower_components/jquery-slimscroll/jquery.slimscroll.min.js") }}"></script>

<script src="{{ asset ("bower_components/fastclick/lib/fastclick.js") }}" type="text/javascript"></script>

<!-- AdminLTE App -->
<script src="{{ asset ("bower_components/admin-lte/dist/js/adminlte.min.js") }}" type="text/javascript"></script>

<script src="{{ asset ("bower_components/admin-lte/dist/js/demo.js") }}" type="text/javascript"></script>

<script src="{{ asset ("vendor/notyf/notyf.min.js") }}" type="text/javascript"></script>

<script src="{{ asset ('js/updated/pusher.min.js') }}" type="text/javascript"></script>
<script src="<?php echo e(asset ("bower_components/select2/dist/js/select2.min.js")); ?>"></script>



<script type="text/javascript">
      $('select').select2();

    var notyf = new Notyf({delay:5
      });

     var pusher = new Pusher("{{config('settings.pusher_app_key')}}",{
           cluster: "{{config('settings.pusher_cluster')}}"
       });

     



    var channel = pusher.subscribe('investor');

    channel.bind('investor.notified', function(data) {
  
        //console.log(data);
               noti_li='<li><a href="#"><h5>'+data.title+'<small><i class="fa fa-clock-o"></i> <span class="timeago" title="'+data.timestamp+'">now</span></small></h5><p class="notify">'+data.content+'</p></a></li>';
        $('#notification_head').prepend(noti_li);
        $('#notification_count').html(parseInt($('#notification_count').html(), 10)+1)


        notyf.confirm(data.content);
    });



</script>

@yield('scripts')

</body>
</html>
