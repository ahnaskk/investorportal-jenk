<div class="box-body box-body-sm">
    @include('layouts.admin.partials.lte_alerts')
    <div class="form-group">
        <label for="title">Title <span color="#FF0000"> * </span></label>
        {!! Form::text('title',isset($faq)? $faq->title : old('title'),['id'=>'title','class'=>'form-control','placeholder'=>'Enter Title']) !!}
    </div>
	<?php $userId = Auth::user()->id;?>
    {!! Form::hidden('creator_id',$userId) !!}


	<div class="form-group">

		<label for="description">Description <span color="#FF0000"> * </span></label>
		{!! Form::textarea('description',isset($faq)? $faq->description : old('description'),['id'=>'description','class'=>'form-control','placeholder'=>'Enter Description']) !!}
	</div>



	<!-- /.box-body -->

    <div class="btn-wrap btn-right">
        <div class="btn-box">

            @if($action=="create")
                {!! Form::submit('Create',['class'=>'btn btn-primary']) !!}
            @else
                {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
            @endif
				@if(@Permissions::isAllow('Viewers','View'))
					<a href="{{$url}}" class="btn btn-danger">Cancel</a>
				@endif

        </div>
    </div>
    {!! Form::close() !!}
</div>

@section('scripts')
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css"/>
@stop