<div class="box-body box-body-sm">
    @include('layouts.admin.partials.lte_alerts')
    @section('styles')

        <style>
            .select2-container{
                max-width: 100% !important;
            }
        </style>

        @endsection





        <form method="POST" action="{{ isset($edit)?$edit:'' }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data" id="faqForm">
          



        @if(isset($edit))
            @method('PATCH')
        @endif
            @csrf

    <div class="form-group">
        <label for="app">Web/App</label>
        <select name="app" id="app" class="form-control">
            <option @isset($faq) @if($faq->app == 0) selected @endif @endisset value="0">Web</option>
            <option @isset($faq) @if($faq->app == 1) selected @endif @endisset value="1">App</option>
        </select>
    </div>
    <div class="form-group">
        <label for="title">Title <span style="color:#FF0000"> * </span></label>
        {!! Form::text('title',isset($faq)? $faq->title : old('title'),['id'=>'title','class'=>'form-control','placeholder'=>'Enter Title']) !!}
    </div>
	<?php $userId = Auth::user()->id;?>
    {!! Form::hidden('creator_id',$userId) !!}

    <div class="form-group">

        <label for="link">Video Link </label>
        {!! Form::text('link',isset($faq)? $faq->link : old('link'),['id'=>'link','class'=>'form-control','placeholder'=>'Enter Link']) !!}
    </div>

	<div class="form-group">

		<label for="description">Description </label>
		{!! Form::textarea('description',isset($faq)? $faq->description : old('description'),['id'=>'description','class'=>'form-control','placeholder'=>'Enter Description']) !!}
	</div>



	<!-- /.box-body -->

    <div class="btn-wrap btn-right">
        <div class="btn-box">

            @if($action=="create")
                {!! Form::submit('Create',['class'=>'btn btn-primary faqsubmit']) !!}
            @else
                {!! Form::submit('Update',['class'=>'btn btn-primary faqsubmit']) !!}
            @endif
				@if(@Permissions::isAllow('Viewers','View'))
					<a href="{{$url}}" class="btn btn-danger">Cancel</a>
				@endif

        </div>
    </div>
    {!! Form::close() !!}
</div>

@section('scripts')
<script src="{{ asset ('js/form/main.min.js') }}" type="text/javascript"></script>
<script src="{{ asset ('js/merchant/faq/main.min.js') }}" type="text/javascript"></script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css"/>
@stop