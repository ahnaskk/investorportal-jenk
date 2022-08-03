@extends('funding.includes.app')
@section('content')
    <section class="content-area page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 login-outer">
                    <h1>Login</h1>
                    <form method="post" action="" class="login-wrap" id="fundings-login">
                        @include('layouts.admin.partials.lte_alerts')
                        @csrf
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input data-parsley-error-message="Enter valid email" data-parsley-minlength="10" autocomplete="off" required name="email" type="email" id="email"  class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input data-parsley-error-message="Enter valid password" data-parsley-minlength="5" autocomplete="off"  required name="password" id="password" type="password" class="form-control">
                        </div>
                        <a href="{{url('/fundings/forgot-password')}}" class="forgot-pass">Forgot your password?</a>
                        <input type="submit" class="btn btn-submit" id="login" value="Login">
                        <p class="new-here">New here ? <a href="{{url('fundings/signup')}}">Join us !</a></p>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
<script src="{{url('payment/js/parsley.min.js')}}"></script>
    <script>
		//$('#fundings-login').parsley();


		var fewSeconds = 5;
		$('#login').click(function(e){
			var form = $('#fundings-login');
			form.parsley().validate();
			if (form.parsley().isValid()){
				$('#fundings-login').submit();
				var btn = $(this);
				btn.prop('disabled', true);
				setTimeout(function(){
					btn.prop('disabled', false);
				}, fewSeconds*1000);
			}
		});

    </script>
@endpush