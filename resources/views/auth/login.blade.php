@extends('layouts.app')

@section('content')

<div class="login-container">

   <div class="col-md-6 login-left">
    <div class="login-wrapper">
        <div class="login-logo"><img src="{{ asset('images/investor-new-logo.png') }}"> </div>
        <div class="panel-heading">Login to your account</div>
        <div class="panel-body">
            <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">                
                @if($errors->any())
                 <!-- <div class="alert alert-danger">   
                <h4>{{$errors->first()}}</h4>
                </div> -->
                @endif
                    <label for="email" class="control-label">E-Mail Id</label>
                    <input id="email" data-cy="email" type="email" class="form-control" name="email" value="{{ old('email') }}">
                    @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>

                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label for="password" class="control-label">Password</label>
                    <input id="password" data-cy="password" type="password" class="form-control" name="password">
                    @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                    @endif
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6 remember">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember"> Remember Me
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 forgot-pass">
                            <a data-cy="link-forgot-pass" class="btn btn-link" href="{{ url('/password/reset') }}">Forgot Your Password?</a>   
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button data-cy="submit" type="submit" class="btn btn-login">
                        Login
                    </button>                            
                </div>
            </form>
        </div>
    </div>
    <div class="foot-login col-md-12">   @include('layouts.admin.partials.lte_footer')</div>
</div>


<div class="col-md-6 login-right">

</div>


<!-- Footer -->


</div>
@endsection

@section('styles')

<style type="text/css">

    .foot-log .main-footer {
        margin: 0 auto;
        text-align: center!important;
        width: 35%;
        text-align: center;
        margin-top: 54px;
    }

    .foot-log {
        margin: 0!important;
        text-align: center;
    }
    .log-admin {
        margin-top: 99px;
    }

</style>
@stop
