<div class="box-body box-body-sm">
    @include('layouts.admin.partials.lte_alerts')
    <div class="form-group">
        <label for="branch">Branch <span color="#FF0000"> * </span></label>
        {!! Form::text('branch',isset($faq)? $faq->branch : old('branch'),['id'=>'branch','class'=>'form-control','placeholder'=>'Enter Branch']) !!}
    </div>
	<?php $userId = Auth::user()->id;?>
    {!! Form::hidden('creator_id',$userId) !!}



	<!-- /.box-body -->

    <div class="btn-wrap btn-right">
        <div class="btn-box">

            {!! Form::submit('PULL',['class'=>'btn btn-primary','id'=>'pull']) !!}
					<a href="/dashboard" id="cancel" class="btn btn-danger">Cancel</a>
            <p style="display: none" class="please-wait">Please wait</p>


        </div>
    </div>
    {!! Form::close() !!}
</div>

@section('scripts')
    <script>
        $('#pull').click(function (e) {
            $(this).hide();
            $('#cancel').hide();
            $('.please-wait').show();
        });
    </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css"/>
@stop