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
        {{-- menu right will be here --}}
    </div>
</header>
<!-- /.Header -->

<!-- content area -->
<section class="payment-content">
    <div class="container">
        <div class="row">
            <div class="caption-left col-md-6">
                <div class="caption-left-box">
                    <h1>
                        <span>Thank you for visiting </span>Velocity Group USA's payment gateway.
                    </h1>
                    <p class="name">Hi {{$user_data->name}}!</p>
                    <p>
                    {{--<p>Please make a one time payment of <span> $</span><span id="final_amount">{{$amount}}</span> (Including 3.75% processing fee.) which will be automatically applied to your account balance for {{$user_data->name}} </p>--}}
                    Please make a payment of <label><span> $</span><span id="final_amount">{{$amount}}</span></label>  (inclusive of the processing fee 3.75%). The required amount will be automatically added to your account balance.</p>
                </div>
            </div>
            <div class="payment-form-right col-md-6">
                <form novalidate data-parsley-validate role="form" action="@if($investor) {{ route('process-stripe-payment-investor') }}  @else {{ route('process-stripe-payment') }} @endif" method="post" class="payment-form require-validation" data-cc-on-file="false"  data-stripe-publishable-key="{{ $stripe_key }}"  id="payment-form">
                    @csrf
                    <input type="hidden" name="user_id" value="{{$id}}">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-group">
                                <label for="amount">Amount <span style="color:#FF0000">*</span></label>
                                <input data-parsley-error-message="Enter Valid Amount Greater Than Or Equal To 1" data-parsley-minlength="1"   required name="amount"  id="amount" autocomplete='off'  class="field"  size='20' type="text" placeholder="00.00" min="1">
                                <input value="{{$amount}}" type="hidden" id="total_amount" name="total_amount">
                            </div>
                        </div>
                    </div>

                    <div class="card-wrapper">
                        <div class="field-group">
                            <label for="name_on_card" >Name on Card <span style="color:#FF0000">*</span></label>
                            <input data-parsley-error-message="Enter Valid Name" data-parsley-minlength="2" required name="name_on_card" id="name_on_card" class="field" type="text">
                        </div>
                            @if(!Auth::user())
                        <div class="field-group">
                            <label for="email">Email</label>
                            <input id="email" name="email" class="field" type="email">
                        </div>
                            @endif
                        <div class="field-group payment-group">
                            <label for="payment-field">Payment Type</label>
                            <div class="payment-type">
                              
                                <img src="{{url('payment/images/payments.png')}}" alt="" class="payment-img" >
                            </div>
                        </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-group">
                                        <label for="amount_to_display">Amount to be paid <span style="color:#FF0000">*</span></label>
                                        <input readonly name="amount_to_display"  id="amount_to_display" autocomplete='off'  class="field"  size='20' type="text" placeholder="00.00"  >
                                    </div>
                                </div>                        </div>
                        <div class="row card-row">

                            <div class="col-md-6 card-box">
                                <div class="field-group">
                                    <label for="card-number">Card Number <span style="color:#FF0000">*</span></label>
                                    <input data-parsley-error-message="Card Number" data-parsley-minlength="16" name="card-number" autocomplete='off' id="card-number"  class="field card-number" size='20' type="text" placeholder="0000-0000-0000-0000" >
                                </div>
                            </div>
                            <div class="col-md-3 card-box">
                                <div class="field-group">
                                    <label for="date-exp">Exp. Date <span style="color:#FF0000">*</span></label>
                                    <input data-parsley-error-message="Enter Valid Exp. Date" data-parsley-minlength="5" required name="date-exp" id="date-exp" class="field" type="text" placeholder="MM/YY">
                                </div>
                            </div>
                            <div class="col-md-3 card-box">
                                <div class="field-group">
                                    <label for="cvv">CVV <span style="color:#FF0000">*</span></label>
                                    <input data-parsley-error-message="Enter Valid CVV" data-parsley-minlength="3"  required id="cvv" class="field card-cvc" type="number" placeholder="***" size='3'>
                                </div>
                            </div>
                        </div>
                        <div class='form-row row'>
                            <div class='col-md-12 error form-group d-none'>
                                <div class='alert-danger alert'>Please correct the errors and try
                                    again.
                                </div>
                            </div>
                        </div>

                        <div class="field-group btn-group">
                            <button id="subthis" class="btn-submit">Submit</button>
                        </div>
                    </div>
                </form>
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

<script src="{{url('payment/js/jquery.min.js')}}"></script>
<script src="{{url('payment/js/parsley.min.js')}}"></script>
<script>
	$('#payment-form').parsley();
</script>

<script src="{{url('payment/js/stripe.js')}}"></script>


<script src="{{url('payment/js/payment.js')}}"></script>
<script defer src="{{url('payment/js/main.min.js')}}"></script>
</body>
</html>
