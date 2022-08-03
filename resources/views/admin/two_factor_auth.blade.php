@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}}  </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Two Factor Authentication</div>     
      </a>
      
  </div>

    <div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>
        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap two-factor-wrapper">
                 <div class="row">
                    <div class="col-sm-10"></div>
                     <div class="col-sm-12 btn-adm" style="padding-bottom:15px">
                  
                    </div>
                  </div>
                  <input type="hidden" name="status" id="status" value="{{session('status')}}">

<!-- @if (session('status') == 'two-factor-authentication-enabled')
    <div class="mb-4 font-medium text-sm text-green-600">
        Two factor authentication has been enabled.
    </div>
@endif
@if (session('status') == 'two-factor-authentication-disabled')
    <div class="mb-4 font-medium text-sm text-green-600">
        Two factor authentication has been disabled.
    </div>
@endif -->
@if (Auth::user()->two_factor_secret)
<div class="flex-center">
    <h3 class="head-two-factor fact-head">Two-step verification</h3>
    <span class="two-factor-tag">ENABLED</span>
</div>
<h4> Install a verification app on your phone</h4>
<p>You'll need to use a verification app such as Google Authenticator. Install from your app store.</p>
<p>When two factor authentication is enabled,you will be prompted for a secure,random token during authentication.you may retrieve this token from your phone's google authenticator application.</p>
<!-- <form action="/user/two-factor-authentication" method="post" class="two-factor-form">
    {!! csrf_field() !!}
    @if (Auth::user()->two_factor_secret)
    @method('DELETE')     
    <button class="btn btn-danger">Disable</button>     
    @else
     <button class="btn btn-primary">Enable</button>     
    @endif

</form> -->

@if (session('status') == 'two-factor-authentication-enabled')
<!-- <h4> Install a verification app on your phone</h4>
<p>You'll need to use a verification app such as Google Authenticator. Install from your app store.</p>
<p>When two factor authentication is enabled,you will be prompted for a secure,random token during authentication.you may retrieve this token from your phone's google authenticator application.</p> -->
<p>
    Two factor authentication is now enabled.Scan the following QR code using your phone's authenticator application.
</p>


<h4>1.Scan this QR code with your verification app</h5>

<p>Once your app reads the QR code, you'll get a 6-digit code.</p>
 <div class="qr-code">
       {!!  Auth::user()->twoFactorQrCodeSvg() !!}      
    </div>


<h4>2.Enter the 6-digit code here</h4>

<p>Enter the code from the app below. Once connected, we'll remember your phone so you can use it each time you log in.</p>

<div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('admin/two-factor-auth-settings') }}">
                        @csrf

                     

                        

                        <div class="">                            

                            <div class="col-md-6">
                                <input id="code" type="code" class="form-control" name="code" placeholder="Please enter 6 digit code">

                               <!--  @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif -->
                            </div>
                        </div><br><br><br>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-refresh"></i> Connect Phone
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
                <h3 class="fact-head mt-1">Disable two-step verification</h3>
<p>If you disable two-step verification, your account will no longer have the protection of a second login step.</p>
<form action="/user/two-factor-authentication" method="post" class="two-factor-form">
    {!! csrf_field() !!}
    @if (Auth::user()->two_factor_secret)
    @method('DELETE')     
    <button class="btn btn-danger">Disable</button>     
    @else
     <button class="btn btn-primary">Enable</button>     
    @endif

</form>
@else
<h3>Two-step verification <span>DISABLED</span></h3>
<p>Protect your account by adding an extra layer of security. A second login step can keep your account secure, even if your password is compromised. To enable it, all you need is a smart phone.</p>
<p>
    You can enable two factor authentication by clicking the following button.
</p>
<form action="/user/two-factor-authentication" method="post" class="two-factor-form">
    {!! csrf_field() !!}
    @if (Auth::user()->two_factor_secret)
    @method('DELETE')     
    <button class="btn btn-danger">Disable</button>     
    @else
     <button class="btn btn-primary">Enable</button>     
    @endif

</form>
@endif


              
            </div>
        </div>
        <!-- /.box-body -->
    </div>
  </div>



@stop



@section('styles')
     <link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop