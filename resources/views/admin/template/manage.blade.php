@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($title)?$title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Manage  Template </div>     
      </a>
      
  </div>

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary branch_mng box-sm-wrap">

            
            <!-- form start -->
         @if($action=="assign")
            {!! Form::open(['route'=>'admin::template::assign', 'method'=>'POST','id'=>'template_form']) !!}
              @else
           {!! Form::open(['route'=>'admin::template::remove', 'method'=>'POST','id'=>'template_form']) !!}
            @endif
        
                  <div class="box-body box-body-sm">
                    @include('layouts.admin.partials.lte_alerts')  
                     <div class="form-group">
                    <label for="type">Mail Type<span class="validate_star">*</span></label>
                         {!! Form::select('type',$template_types,isset($template)? $template->type: old('type'),['class'=>'form-control','placeholder'=>'Select Type','required','id'=> 'type']) !!}
                         {{ Form::hidden('id', $template->id) }}
                       
                    </div>               
                  
                    <div class="btn-wrap btn-right">
                        <div class="btn-box">
                        <a href="{{URL::to('admin/template')}}" class="btn btn-success">View Templates</a> 
                           @if($action=="assign")                    
                        {!! Form::submit('Assign to Mail Type',['class'=>'btn btn-primary bran-mng-bt']) !!} 
                          @else
                         {!! Form::submit('Remove from Mail Type',['class'=>'btn btn-primary bran-mng-bt']) !!}   
                         @endif
                       
                                        
                    </div>
                    </div>
        </div>
                
            {!! Form::close() !!}
        </div>
        <!-- /.box -->
    </div>


@stop


@section('scripts')

   <script>

    $("#nameFieldId").on("input", function(){
           var regexp = /[^a-zA-Z ]*$/;
          if($(this).val().match(regexp)){
          $(this).val( $(this).val().replace(regexp,'') );
          }
       });


 $(document).ready(function () {

    $('#template_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            subject: {
                required: true,
                maxlength: 255,
            }, 
            title: {
                required: true,
                maxlength: 255,
            }, 
            type: {
               required: true, 
            },    
        messages: {
        name: { required :"Enter Subject",                 
              },
        type: { required :"Enter Email",                 
                },
    },
  
});


   });
</script>

 <link href="{{ asset('/css/optimized/create_new_branch_manager.css?ver=5') }}" rel="stylesheet" type="text/css" />



    
@stop

@section('styles')
  <link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />

 <link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />

@endsection
