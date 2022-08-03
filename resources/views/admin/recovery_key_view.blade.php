<div class="col-md-12">
<!-- general form elements -->
<div class="box box-primary">

<div class="box-body">
<h3 class="fact-head">Save this emergency recovery key</h3>
<p>
<!-- If you lose access to your phone, you won't be able to log in to your account without this key. Print, copy or write down this key without letting anyone see it. -->
Store these recovery codes in a secured password manager.They can be used to recover access to your account if your two factor authentication device is lost.
</p> 
<div class="offset">

<ul class="rcodes">
@foreach((json_decode(decrypt(Auth::user()->two_factor_recovery_codes))) as $code)
<li>{{$code}}</li>
@endforeach
</ul>

</div>
</div> 
</div> 
</div>


