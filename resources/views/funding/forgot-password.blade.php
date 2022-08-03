@extends('funding.includes.app')
@section('content')
    <section class="content-area page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 login-outer">
                    <h1>Reset Password</h1>
                    <form method="post" action="{{ url('/password/email') }}" class="login-wrap" id="fundings-login">
                        @include('layouts.admin.partials.lte_alerts')
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        @csrf
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}">
                        </div>


                        <input type="submit" class="btn btn-submit" id="login" value="Send Password Reset Link">
                        <a style="background-color: #2A4A76" href="{{url('/fundings/login')}}"  class="btn btn-primary" > Back </a>

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