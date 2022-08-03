@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip"> Add ACH Request</div>
  </a>
</div>
  <div class="col-md-12">
    <div class="box box-primary payment-container payment-success">

      <section class="payment-content">
        <div class="container">
          <div class="row">
            <div class="caption-left col-md-12">
              <div class="caption-left-box caption-success">
                @isset($error)
                  <h4 style="color:palevioletred">{{$error}}</h4>
                  <a href="{{ URL::previous() }}" class="btn btn-warning">Back to payment</a>
                @else
                  <h1>
                    <span>Thank you for making payment!! </span>
                  </h1>
                  <p>We will apply this payment to your balance as soon as it settles.  Regards from the Velocity Group USA, Inc. Team </p>
                  <a href="{{ URL::previous() }}" class="btn btn-warning">Back</a>
                @endif

              </div>
            </div>

          </div>
        </div>
      </section>


    </div>
  </div>
@stop
@section('scripts')
  <script src="{{url('payment/js/stripe.js')}}"></script>
  <script src="{{url('payment/js/payment.js')}}"></script>
  <script src="{{url('payment/js/jquery.min.js')}}"></script>
@stop
@section('styles')
<link href="{{url('payment/css/style.css?ver=5')}}" rel="stylesheet">
<link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />
@endsection
