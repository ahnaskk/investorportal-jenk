@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">{{isset($page_title)?$page_title:''}} </div>     
  </a>
  
</div>

<div class="col-md-12">
  <!-- general form elements -->
  <div class="box box-primary box-sm-wrap">


    <!-- form start -->
   
    {!! Form::open(['route'=>['admin::admins::update_user_role','id'=>$merchant->id], 'method'=>'POST','id'=>'edit_editor_form']) !!}
    

    <div class="box-body box-body-sm">
      @include('layouts.admin.partials.lte_alerts')
      <div class="form-group">
        <label for="exampleInputEmail1">Name <font color="#FF0000"> * </font></label>
        {!! Form::text('name',isset($merchant)? $merchant->name : old('name'),['class'=>'form-control','id'=>'viewerNameId','placeholder'=>'Enter Name','minlength'=>'1','maxlength'=>'255']) !!}
      </div>  
      <?php $userId=Auth::user()->id;?>


      {!! Form::hidden('creator_id',$userId) !!}

      <div class="form-group">

        <label for="exampleInputEmail1">Email Id <font color="#FF0000"> * </font></label>
        {!! Form::email('email',isset($merchant)? $merchant->email : old('email'),['class'=>'form-control','placeholder'=>'Enter Email Id']) !!}
      </div>

    

       <div class="form-group synd-march reset_password">
                            <label for="email_notification">Reset password and send notification</label>
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                    <input type="checkbox" name="email_notification" value="1" id="email_notification"/>
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check This</span>
                                    </label>
                                </div>
                             </div>

                        </div>


    
      <div class="form-group">
        <label for="exampleInputPassword1">Password </label>
        {!! Form::password('password',['class'=>'form-control','placeholder'=>'Enter Password','id'=>'password']) !!}
      </div>

      <div class="form-group">
        <label for="exampleInputPassword1">Confirm Password </label>
        {!! Form::password('password_confirmation',['class'=>'form-control','placeholder'=>'Enter Password ']) !!}
      </div>

      <div style="display: none;">

         {{ Form::checkbox('roles[]',$role_id,true,['class'=>'checkRoles']) }} 
      </div>

      <!-- /.box-body -->

      <div class="btn-wrap btn-right">
        <div class="btn-box">
           @if(@Permissions::isAllow('Users','View')) 
          <a href="{{URL::to('admin/merchants/show-merchant-users')}}"  class="btn btn-success">View Users</a>
          @endif
          

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

//   $("#viewerNameId").on("input", function(){
//    var regexp = /[^a-zA-Z ]*$/;
//    if($(this).val().match(regexp)){
//     $(this).val( $(this).val().replace(regexp,'') );
//   }
// });


  $(document).ready(function () {
    $('#crete_admin_form').validate({ // initialize the plugin
      errorClass: 'errors',
      rules: {
        name: {
          required: true
        },
        email: {
          required: true,
          email: true
        },
        password: {
            maxlength: 255,
            minlength: 6 
        },
        password_confirmation: {
         
          equalTo: "#password",
          maxlength: 255,
          minlength: 6,
        },
        
      },
      messages: {
        name: "Enter Name",
        email: { required :"Enter Email Id",                 
      },
     password:{ 
        required :"Enter Password", 
        minlength: "Your password must contain more than 6 characters.",
        maxlength: "Password can be max 255 characters long."
     },
      password_confirmation:{ 
      required : "Please Confirm Password",
      equalTo:"Passwords Do Not Match",
      minlength: "Your password must contain more than 6 characters.",
      maxlength: "Password can be max 255 characters long."
    },
      
    }
    
  });
      $('#edit_editor_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
          name: {
            required: true
          },
          email: {
            required: true,
            email: true
          },
          password: {
            maxlength: 255,
            minlength: 6 
        },
        password_confirmation: {
         
          equalTo: "#password",
          maxlength: 255,
          minlength: 6,
        },
          
          
        },
        messages: {
          name: "Enter Name",
          email: { required :"Enter Email Id",                 
        },

      password:{ 
        minlength: "Your password must contain more than 6 characters.",
        maxlength: "Password can be max 255 characters long."
     },
      password_confirmation:{ 
       equalTo:"Passwords Do Not Match",
       minlength: "Your password must contain more than 6 characters.",
       maxlength: "Password can be max 255 characters long."
    },  
        
        
      }
      
    });

      $('.checkRoles').click(function() {
        $('.checkRoles').not(this).prop('checked', false);
    });
    });
  </script>
  @stop
  @section('styles')
  <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css" />
  @stop
