<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Velocity Group USA's payment gateway</title>

    <!-- favicon -->
    <link rel="icon" href="{{url('payment/images/favicon.ico')}}" type="image/x-icon">

    <!-- Custom CSS -->
    <link href="{{url('payment/css/bootstrap.css')}}" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{url('payment/css/style.css?ver=5')}}" rel="stylesheet">
    <style>
    .loader {
  border: 16px solid #f3f3f3;
  border-radius: 50%;
  border-top: 16px solid #3498db;
  width: 120px;
  height: 120px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

<!-- Header -->
<header class="payment-header">
    <div class="container">
        <div class="logo">
            <img src="{{url('payment/images/logo.png')}}" class="">
        </div>
        <div class="menu-right">
            <ul>
                <li><a href="#">Home</a></li>
                <li class="user"><a href="#"><img src="{{url('payment/images/avatar.svg')}}" class=""></a></li>
            </ul>
        </div>
    </div>
</header>
<!-- /.Header -->

<!-- content area -->
<section class="payment-content">
    <div class="container">
        <div class="row">
            <div class="caption-left col-md-12">
                <div class="caption-left-box">
                    @isset($error)
                        <h4 style="color:palevioletred">{{$error}}</h4>
                        <a href="{{ URL::previous() }}" class="btn btn-warning">Back to payment</a>
                    @else
                        <h1>
                            <span>Thank you for making payment!! </span>
                        </h1>
                        <p>We will apply this payment to your balance as soon as it settles.  Regards from the Velocity Group USA, Inc. Team </p>
                        <div class="loader" style="align:center;"></div>
                        <script type="text/javascript">
                            @if(session('prev_url'))
			                function closeWindow() {
				                setTimeout(function() {
					                //window.close();
					                window.location = "{{session('prev_url')}}";

				                }, 3000);
			                }
			                @endif

			                window.onload = closeWindow();
                        </script>
                        @endif

                </div>
            </div>

        </div>
    </div>
    
</section>
<!-- /.content area -->

<footer>
    <div class="container">
        <p class="copyright">Copyright © {{date('Y')}} Velocity. All rights reserved.</p>
    </div>
</footer>

<script src="{{url('payment/js/stripe.js')}}"></script>
<script src="{{url('payment/js/jquery.min.js')}}"></script>

<script src="{{url('payment/js/payment.js')}}"></script>
<script>
    $("#amount").keyup(function(){
        $("#final_amount").html($(this).val());
    });
</script>
</body>
</html>
