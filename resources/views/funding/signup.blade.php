@extends('funding.includes.app')
@section('content')

    <section class="content-area page-content">
        <div class="container">
            <div class="row">
                <form action="" method="post" class="col-lg-12 login-outer" id="fundings-signup">
                    @csrf
                    <h1>Login</h1>
                    <div class="login-wrap">
                        @include('layouts.admin.partials.lte_alerts')
                        <div class="form-group">
                            <label for="name">Name <span class="error">*</span></label>
                            <input value="{{ old('name') }}" data-parsley-error-message="Enter valid name" data-parsley-minlength="3"  autocomplete="off" required  name="name" id="name" type="text" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="email">Email<span class="error">*</span></label>
                            <input value="{{ old('email') }}" data-parsley-error-message="Enter valid email" data-parsley-minlength="5" autocomplete="off" required name="email" type="email" id="email"  class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="cell_phone">Phone Number<span class="error">*</span></label>
                            <input onkeypress="return isNumber(event)" value="{{ old('cell_phone') }}" data-parsley-error-message="Enter valid Phone number" data-parsley-minlength="10" autocomplete="off"   required type="text" name="cell_phone" id="cell_phone"  class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="password">Password<span class="error">*</span></label>
                            <input data-parsley-error-message="Enter password with 6  minimum characters" data-parsley-minlength="6" autocomplete="off"  required id="password" name="password" type="password" class="form-control">
                        </div>

                        <input type="submit" class="btn btn-submit" id="register" value="Register">
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
<script src="{{url('payment/js/parsley.min.js')}}"></script>
<script>





	var fewSeconds = 5;
	$('#register').click(function(e){
		var form = $('#fundings-signup');
		form.parsley().validate();

		if (form.parsley().isValid()){
			$('#fundings-signup').submit();
			var btn = $(this);
			btn.prop('disabled', true);
			setTimeout(function(){
				btn.prop('disabled', false);
			}, fewSeconds*1000);
		}
	});

</script>
@endpush
@push('style')
    <link href="{{ asset('/css/global/main.css')}}" rel="stylesheet" type="text/css" />
@endpush