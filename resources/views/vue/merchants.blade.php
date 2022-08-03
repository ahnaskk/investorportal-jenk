<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Merchant Portal</title>
        <!-- <link rel="icon" href="{{ URL::asset('assets/images/favicon.ico')}}" type="image/x-icon"> -->
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
        <!-- Styles -->
        <link href="{{ mix('css/app.merchants.css') }}" rel="stylesheet" />
    </head>
    <body>
        <div id="app"></div> 
        <!-- Vue -->
        <script src="{{ mix('vue/manifest.js') }}"></script>
        <script src="{{ mix('vue/vendor.js') }}"></script>
        <script src="{{ mix('vue/app.merchants.js') }}"></script>
        <!-- live reload -->
        @if(config('app.env') == 'local')<script src="http://localhost:35729/livereload.js"></script>@endif
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
