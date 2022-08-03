<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Investor Portal</title>
        <!-- <link rel="icon" href="{{ URL::asset('assets/images/favicon.ico')}}" type="image/x-icon"> -->
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
        <script>
            localStorage.setItem('authToken', '{{ $authToken }}');
        </script>
    </head>
    <body>
        <div id="app"></div>
        <!-- Vue -->
        <script src="{{ mix('vue/manifest.js') }}"></script>
        <script src="{{ mix('vue/vendor.js') }}"></script>
        <script src="{{ mix('vue/app.admin.js') }}"></script>
        <!-- live reload -->
        @if(config('app.env') == 'local')
            <script src="http://localhost:35729/livereload.js"></script>
        @endif
    </body>
</html>
