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
                    <form class="form-horizontal two-factor-form" role="form" method="POST" action="{{ url('two-factor-challenge-login') }}" >
                        @csrf

                     

                        

                        <div class="two-factor-panel">
                            <div class="panel-heading">Continue with two-step verification</div>

                            <p class="op7">If you can't use your phone, log in with your emergency recovery key</p>

                            <div class="eq-width">
                                <input id="recovery_code" type="code" class="form-control" name="recovery_code" placeholder="Emergency recovery key">

                    @if ($errors->has('recovery_code'))
                    <span class="help-block" style="color: #FF0000;">
                        <strong>{{ $errors->first('recovery_code') }}</strong>
                    </span>
                    @endif
                            </div>
                            <div class="">
                                <button type="submit" class="eq-width" id = "two_factor_submit" ondblclick="this.disabled = true">
                                    <i class="fa fa-btn fa-refresh"></i> Log in
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
    .eq-width{
        width: 100%;
    }
    .two-factor-form{
        margin-top: 40px;
        /* width: 100px; */
        padding: 40px;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        /* width: 50%; */
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

@section('scripts')


 <script type="text/javascript">

    $('input[type=submit]').one('submit', function() {
     $(this).attr('disabled','disabled');
 });





// var fewSeconds = 5;
//      $("#two_factor_submit").on("click", function() {

//         $(this).attr("disabled", "disabled");
//         setTimeout(function(){
//         $(this).prop('disabled', false);
//     }, fewSeconds);
       
//     });
 </script>

@stop
