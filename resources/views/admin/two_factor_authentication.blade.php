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

@if (Auth::user()->two_factor_secret)
<div class="flex-center">
    <h3 class="head-two-factor fact-head">Two-step verification</h3>
    <span class="two-factor-tag">ENABLED</span>
</div>
<h4> Install a verification app on your phone</h4>
<p>You'll need to use a verification app such as Google Authenticator. Install from your app store.</p>
<p>When two factor authentication is enabled,you will be prompted for a secure,random token during authentication.you may retrieve this token from your phone's google authenticator application.</p>


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
<div class="flex-center">
    <h3 class="head-two-factor fact-head">Two-step verification</h3>
    <span class="two-factor-tag disabled">DISABLED</span>
</div>
<p>Protect your account by adding an extra layer of security. A second login step can keep your account secure, even if your password is compromised. To enable it, all you need is a smart phone.</p>
<p>
    You can enable two factor authentication by clicking the following button.
</p>

    @if (Auth::user()->two_factor_secret)
    <form action="/user/two-factor-authentication" method="post" class="two-factor-form">
    {!! csrf_field() !!}
    @method('DELETE')     
    <button class="btn btn-danger">Disable</button> 
    </form>    
    @else
    
     <a href="{{url('admin/enable-two-factor-auth')}}">
     <button class="btn btn-primary">Enable</button> 
     </a>
     
    @endif


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