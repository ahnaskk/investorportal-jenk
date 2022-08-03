@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip"> Add Ach Request</div>
  </a>
</div>

    <div class="box box-primary payment-container">

      <section class="payment-content">
        <div class="container">
          <div class="row">
          	<div class="caption-row">
	            <div class="caption-left col-md-6">
	              <div class="caption-left-box">
	                

	                	<div class="caption-left-box">
	                        {{--<p>Please make a one time payment of <span> $</span><span id="final_amount">{{$amount}}</span> which will be automatically applied to your account balance for {{$Merchant->name}} </p>--}}
							<p>Please make a payment of <span> $</span><span id="final_amount">{{$amount}}</span>  (inclusive of the processing fee 3.75%). The required amount will be automatically added to your account balance.</p>
	                    </div>


	              </div>
	            </div>
	            <div class="payment-form-right col-md-6">
	              <form novalidate data-parsley-validate role="form" method="post" class="payment-form require-validation" data-cc-on-file="false"  data-stripe-publishable-key="{{ $stripe_key }}"  id="payment-form">
	                @csrf


							  <div class="field-group">
								  <label for="amount">Amount <span class="error">*</span></label>
								  <input data-parsley-error-message="Enter Valid Amount Greater Than Or Equal To 1" data-parsley-minlength="1" required name="amount"  id="amount" autocomplete='off'  class="field"  size='20' type="text" placeholder="00.00" min="1">
								  <input value="{{$amount}}" type="hidden" id="total_amount" name="total_amount">
							  </div>

							  <div class="card-wrapper">
	                <div class="field-group">
	                  <label for="name_on_card" >Name on Card <span class="error">*</span></label>
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
								  <label for="amount_to_display">Amount to be paid <span class="error">*</span></label>
								  <input readonly name="amount_to_display"  id="amount_to_display" autocomplete='off'  class="field"  size='20' type="text" placeholder="00.00"  >
							  </div>
						  </div>                        </div>

	                <div class="row card-row">

	                  <div class="col-md-6 card-box">
	                    <div class="field-group">
	                      <label for="card-number">Card Number<span class="error">*</span></label>
	                      <input data-parsley-error-message="Card Number" data-parsley-minlength="16" required  name="card-number" autocomplete='off' id="card-number"  class="field card-number" size='20' type="text" placeholder="0000-0000-0000-0000" >
	                    </div>
	                  </div>
	                  <div class="col-md-3 card-box">
	                    <div class="field-group">
	                      <label for="date-exp">Exp. Date <span class="error">*</span></label>
	                      <input data-parsley-error-message="Enter Valid Exp. Date" data-parsley-minlength="5" required  name="date-exp" id="date-exp" class="field" type="text" placeholder="MM/YY">
	                    </div>
	                  </div>
	                  <div class="col-md-3 card-box">
	                    <div class="field-group">
	                      <label for="cvv">CVV <span class="error">*</span></label>
	                      <input data-parsley-error-message="Enter Valid CVV" data-parsley-minlength="3" required id="cvv" class="field card-cvc" type="number" placeholder="***" size='3'>
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
        </div>
      </section>
    </div>
@stop
@section('scripts')
    <script src="{{url('payment/js/parsley.min.js')}}"></script>
	<script>
		$('#payment-form').parsley();
	</script>
  <script src="{{url('payment/js/stripe.js')}}"></script>

  <script src="{{url('payment/js/payment.js')}}"></script>
@stop
@section('styles')
<link href="{{url('payment/css/style.css?ver=5')}}" rel="stylesheet">
<link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css') }}" rel="stylesheet" type="text/css" />
@endsection
