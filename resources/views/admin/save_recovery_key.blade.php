@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">

<h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}}  </h3>
<a href="#" class="help-link">
<i class="fa fa-question-circle" aria-hidden="true"></i>
<div class="tool-tip">Recovery Key</div>     
</a>

</div>

<div class="col-md-12">
<!-- general form elements -->
<div class="box box-primary">
@include('layouts.admin.partials.lte_alerts')
<div class="box-body">
<h3 class="fact-head">You have enabled two step verification,save this emergency recovery key</h3>
<p>
 If you lose access to your phone, you won't be able to log in to your account without this key. Print or write down this key without letting anyone see it. 
<!-- Store these recovery codes in a secured password manager.They can be used to recover access to your account if your two factor authentication device is lost. -->
</p> 
<div class="offset">

<ul class="rcodes">
@foreach((json_decode(decrypt(Auth::user()->two_factor_recovery_codes))) as $code)
<li>{{$code}}</li>
@endforeach
</ul>

</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
    <div class="input-group">      
    {{ Form::select('save_method',[1=>'Print',2=>'Wrote it down'],'',['class'=>'form-control','id'=>'save_method','placeholder'=>'Save options']) }} 
    </div>  
    </div>

    <div class="col-md-6 col-sm-12">
    <div class="input-group">
    <a href="{{URL::to('admin/two-factor-authentication')}}" id=""> 
    <button class="btn btn-success" id="recovery_key_submit" disabled>Saved,Let's Finish</button> </a>
    </div>
    </div>
</div>


</div> 
</div> 





</div>


@stop

@section('scripts')
<script src="{{ asset ('bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

 $("#save_method").change(function () {
    if($(this).val() == 1){
    var recovery_pdf_url = "{{ URL::to('admin/recovery-key-pdf-view') }}";
    download_file(recovery_pdf_url, 'recovery-key.pdf'); 
    }
            if ($(this).val() == 1 || $(this).val()==2) {
                $("#recovery_key_submit").removeAttr("disabled");
                $("#recovery_key_submit").focus();
            } else {
                $("#recovery_key_submit").attr("disabled", "disabled");
            }
        });


 function download_file(fileURL, fileName) {
    // for non-IE
    if (!window.ActiveXObject) {
        var save = document.createElement('a');
        save.href = fileURL;
        save.target = '_blank';
        var filename = fileURL.substring(fileURL.lastIndexOf('/')+1);
        save.download = fileName || filename;
           if ( navigator.userAgent.toLowerCase().match(/(ipad|iphone|safari)/) && navigator.userAgent.search("Chrome") < 0) {
                document.location = save.href; 
// window event not working here
            }else{
                var evt = new MouseEvent('click', {
                    'view': window,
                    'bubbles': true,
                    'cancelable': false
                });
                save.dispatchEvent(evt);
                (window.URL || window.webkitURL).revokeObjectURL(save.href);
            }   
    }

    // for IE < 11
    else if ( !! window.ActiveXObject && document.execCommand)     {
        var _window = window.open(fileURL, '_blank');
        _window.document.close();
        _window.document.execCommand('SaveAs', true, fileName || fileURL)
        _window.close();
    }
}

 </script>

@stop

@section('styles')
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop