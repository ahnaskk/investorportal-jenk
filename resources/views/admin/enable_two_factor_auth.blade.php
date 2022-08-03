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
                  


<div class="flex-center">
    <h3 class="head-two-factor fact-head">Enable Two-step verification</h3>
    <!-- <span class="two-factor-tag">ENABLED</span> -->
</div>
<h4> Install a verification app on your phone</h4>
<p style="max-width: 800px;">
Download and install a verification App such as Google Authenticator from App Store/Play Store in your cell phone. Scan the following QR code using the Authenticator App, then enter the 6 digit code that appears on your Authenticator App and select Connect Phone. Two factor authentication is now enabled. When two factor authentication is enabled, you will be prompted for a secure, random token from your cell phone's Authenticator app.

</p>


<h4>1.Scan this QR code with your verification app</h5>
<div class="qr-code">
       {!!  $qrcode !!}      
    </div>

<p>Once your app reads the QR code, you'll get a 6-digit code.</p>


<h4>2.Enter the 6-digit code here</h4>

<p>Enter the code from the app below. Once connected, we'll remember your phone so you can use it each time you log in.</p>

<div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('admin/two-factor-auth-settings') }}">
                        @csrf

                     

                        

                        <div class="">                            

                            <div class="col-md-6">
                                <input id="code" type="code" class="form-control" name="code" placeholder="Please enter 6 digit code" required>&nbsp;<span id="errmsg"></span>

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




              
            </div>
        </div>
        <!-- /.box-body -->
    </div>
  </div>



@stop

@section('scripts')
<script type="text/javascript">
$(document).ready(function () { 
  $("#code").keypress(function (e) {
     //if the letter is not digit then display error and don't type anything
  if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)){ 
      //display error message
      $("#errmsg").html("Digits Only").show().fadeOut("slow");
      return false;
    }
   });
});
</script>
@stop

@section('styles')
<link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />
<style type="text/css">
    #errmsg{
         color: red;
    }
     </style>
@stop