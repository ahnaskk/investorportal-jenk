@extends('layouts.admin.admin_lte')

@section('content')

    <div class="inner admin-dsh header-tp">

        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($title)?$title:''}}</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Merchant Story</div>
        </a>

    </div>
    {{ Breadcrumbs::render('merchantStory',$merchant) }}
    <form method="post" class="col-md-12" enctype="multipart/form-data" id="story_form">
        @csrf
        <div class="box">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')

            </div>
            <div class="box-body box-body-sm">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row flex">
                    <div class="col-6 col-sm-12">
                        <div class="form-group c {{ $errors->has('story_caption') ? 'has-error' : ''}}">
                            <label for="story_caption" class="control-label">{{ 'Story Caption' }} <span class="text text-danger"> *</span></label>
                            <input type="text" class="form-control" name="story_caption"  id="story_caption" required value="{{ isset($merchant->story_caption) ? $merchant->story_caption : ''}}">
                            {!! $errors->first('story_caption', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                    <div class="col-6 col-sm-12">
                        <div class="form-group c {{ $errors->has('story_image') ? 'has-error' : ''}}">
                            <label for="story_image" class="control-label">{{ 'Story Image' }} <span class="text text-danger"> *</span></label>
                            <input class="form-control" name="story_image" type="file" id="story_image" value="{{ isset($merchant->story_image) ? $merchant->story_image : ''}}" @isset($merchant->story_image) @else required @endisset >
                            {!! $errors->first('story_image', '<p class="help-block">:message</p>') !!}
                                                <br><br>
                            <div class="img-wrapper">
                                <img id='img-upload' src="@isset($merchant->story_image) {{url(Storage::url($merchant->story_image)) }} @endisset"/>
                            </div>
                        </div>
                    </div>
                </div>



   

                    <div class="form-group c {{ $errors->has('story') ? 'has-error' : ''}}">
                        <label for="story" class="control-label">{{ 'Story' }} <span class="text text-danger"> *</span></label>
                        <textarea class="form-control" rows="5" name="story" type="textarea" id="story" required>{{ isset($merchant->story) ? $merchant->story : ''}}</textarea>
                        {!! $errors->first('story', '<p class="help-block">:message</p>') !!}
                    </div>


                </div>
                <div class="submit-wrapper">
                    <button type="submit" class="btn btn-primary btn-lg">Submit</button>
                </div>
            </div>
            <!-- /.box-body -->
        </div>


    </form>
@stop

@section('scripts')

    <script src="{{ asset('/plugins/ckeditor/ckeditor.js') }}"></script>

    <script defer>

	    function readURL(input) {
		    if (input.files && input.files[0]) {
			    var reader = new FileReader();

			    reader.onload = function (e) {
				    $('#img-upload').attr('src', e.target.result);
			    }

			    reader.readAsDataURL(input.files[0]);
		    }
	    }

	    $("#story_image").change(function () {
		    readURL(this);
	    });
        

	    CKEDITOR.replace( 'story' );
        CKEDITOR.config.allowedContent = true;
           $("#story_form").validate({
                ignore: [],
                rules:{
                    story_caption:{
                        required:true,
                    },
                    @isset($merchant->story_image)

                    @else
                    story_image:{
	                    required:true
                    },
                    @endisset

                    story:{
                        required:true
                    }

                }
            })
        $("#story_form").submit(function(e){
            textbox_data = CKEDITOR.instances.story.getData();
            if(!($("#story_form").valid() && textbox_data)){
                e.preventDefault()
            }
        })
    </script>

@stop

@section('styles')
    <link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />
    <style>
        #img-upload {
            width: 500px;
        }
        .text-danger{
            color: red;
        }
    </style>

@stop