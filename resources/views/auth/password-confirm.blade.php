@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="cener-flex">
            <div class="panel panel-default bg-grey">

                <div class="wrapper-outer">
                    <div class="logo-top">
                        <div class="login-logo"><img src="{{ asset('images/investor-new-logo.png') }}"> </div>
                    </div>
                    <form class="form-horizontal two-factor-form" role="form" method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                     

                        

                        <div class="two-factor-panel">
                            <div class="panel-heading">Confirm Password</div>

                            <label for="password" class="mb-1">Password</label>

                            <div class="eq-width">
                                <input id="password" type="password" class="form-control" name="password" placeholder="">
                                <span class="info op7">This is the password you use to log in to your account</span>
                               <!--  @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif -->
                            </div>
                        </div>

                        <div class="">
                            <div class="">
                                <button type="submit" class="eq-width">
                                    <i class="fa fa-btn fa-refresh"></i> Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('styles')
<style>
    .bg-grey{
        background-color: #f6f8fb;
        border: none;
        box-shadow: none;
    }
    .op7{
        opacity: 0.7;
    }
    span.info{
        margin-top: 10px;
        display: block;
    }
    .eq-width{
        width: 100%;
    }
    .two-factor-form{
        margin-top: 40px;
        padding: 40px;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        border-radius: 5px;
        box-shadow: 0px 0px 22px -15px #c9c9cd;
    }
    .logo-top{
        display: flex;
        justify-content: center;
    }
    .panel-heading{
        text-align: center;
        padding: 0;
        font-size: 2rem;
        color: #3a3a46;
        margin-bottom: 2rem;
    }
    .wrapper-outer{
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .two-factoe-panel > p{
        color: #a0a1a6;
        margin-bottom: 2rem;
    }
    .eq-width > input{
        border: 1px solid #d3d3d6;
    }
    button.eq-width{
        background: #2b33c6;
        color: #fff;
        border: 1px #2b33c6;
        border-radius: 4px;
        margin-top: 15px;
        height: 34px;
        display: block;
    }
    .center-flex{
        display: flex;
        justify-content: center;
        width: 300px;
    }
</style>
@stop
