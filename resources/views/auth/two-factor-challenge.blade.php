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
                    <form class="form-horizontal two-factor-form" role="form" method="POST" action="{{ url('two-factor-challenge-login') }}" name="two-factor-form" id="two-factor-form">

                        @csrf

                    <!--  <input id="email" type="hidden" class="form-control" name="email" value="1email@iocod.com">
                     <input id="password" type="hidden" class="form-control" name="password" value="admin987987">
 -->
                        

                        <div class="two-factor-panel">
                            <div class="panel-heading">Continue with two-step verification</div>

                            <label for="password" class="mb-1">Code</label>

                            <div class="eq-width">
                                <input id="code" type="code" class="form-control" name="code" placeholder="Please enter 6 digit code">
                   @if ($errors->has('code'))
                    <span class="help-block" style="color: #FF0000;">
                        <strong>{{ $errors->first('code') }}</strong>
                    </span>
                    @endif
                            </div>
                        </div>

                        <div class="">
                            <div class="">
                                <button type="submit" class="eq-width" id = "two_factor_submit">
                                    <i class="fa fa-btn fa-refresh"></i> Submit
                                </button>
                            </div>
                        </div>
                        <span class="help"><a href="{{ url('/login-by-recovery-key') }}">Can't use your phone?</a></span>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .op7{
        opacity: 0.7;
    }
    span.help{
        margin-top: 1rem;
    }
    span.help > a {
        color: #1b219a;
        text-decoration: underline;
    }
    .bg-grey{
        background-color: #f6f8fb;
        border: none;
        box-shadow: none;
    }
    .eq-width{
        width: 100%;
    }
    .two-factor-form{
        width: 498px;
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
    .mb-1{
        margin-bottom: 1rem;
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
 
    $('#two_factor_submit').on('click',function(e)
    { 
        e.preventDefault;
        $(this).attr('disabled','disabled');
        $('form#two-factor-form').submit();
    });

    var loginlink="{{ URL::to('/two-factor-challenge-login') }}";
    
    $('#two_factor_submitnn').on('click',function(e)
{ 
    var code = $('#code').val();
    e.preventDefault();
       $.ajax({
            type: 'POST',
            data: {'code':code, '_token': "{{ csrf_token() }}",'email':'1email@iocod.com','password':'admin987987'},
            url: loginlink,
            success: function (data) {
                $('form#two-factor-form').submit();         
             }
        });

     
   
});










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
