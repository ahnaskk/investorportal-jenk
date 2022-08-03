@extends('layouts.admin.admin_lte')

@section('content')
    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
          <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{$page_title}}</div>     
           </a>   
    </div>
@if($action=="create")
  {{ Breadcrumbs::render('add_sub_status_flag') }}
@else
  {{ Breadcrumbs::render('edit_sub_status_flag') }}
@endif

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

            
                    <!-- form start -->
            @if($action=="create")
                {!! Form::open(['route'=>'admin::sub_status_flag::storeCreate', 'method'=>'POST','id'=>'create_sub_status_flag_form']) !!}
            @else
                {!! Form::open(['route'=>'admin::sub_status_flag::update', 'method'=>'POST']) !!}
            @endif
            @include('layouts.admin.partials.lte_alerts')
            <div class="box-body col-md-12">

                
                <div class="form-group">
                    <label for="exampleInputEmail1">Name <font color="#FF0000"> * </font></label>
                    {!! Form::text('name',isset($sub_status_flag)? $sub_status_flag->name : old('name'),['class'=>'form-control','required','id'=> 'inputName','data-parsley-required-message' => 'Name is required']) !!}
                   @if($action!="create") {!! Form::hidden('id',$sub_status_flag->id) !!} @endif
                </div>

               

                <!-- /.box-body -->
                
                    <div class="btn-wrap btn-right">
                        <div class="btn-box">
                            <a href="{{URL::to('admin/sub_status_flag')}}" class="btn btn-success"> View all </a>
                            @if($action=="create")
                            {!! Form::submit('Create',['class'=>'btn btn-primary']) !!} 

                            @else
                             {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}     

                            @endif
                        </div>
                    </div>
               
                   
                

                {!! Form::close() !!}


            </div>
            
           
        </div>
        <!-- /.box -->


    </div>


@stop


@section('scripts')
<script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>
<script>
$(document).ready(function () {

$(document).on('submit', 'form', function() {
  $(this).find('button:submit, input:submit').attr('disabled', 'disabled');
});



    $('#create_sub_status_flag_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            name: {
                required: true
            },            
            
        },
        messages: {
        name: "Enter Name",
        
    }
    
        
    });
});
 </script>
 <style>
    .errors {
      color: red;      
   }
</style>

    <script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
    
    <script src='{{ asset("js/parsley.js")}}' type="text/javascript"></script>
       
       <script type="text/javascript">
        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<span class="error-text"></span>',
            classHandler: function (el) {
                return el.$element.closest('input');
            },
            successClass: 'valid',
            errorClass: 'invalid'
        };
    </script>

@stop

@section('styles')
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/add_status.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop