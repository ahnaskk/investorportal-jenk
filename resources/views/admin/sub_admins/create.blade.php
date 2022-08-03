@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

<h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?($page_title):''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?($page_title):''}}</div>     
      </a>
      
  </div>
  @if($action=="create")
     {{ Breadcrumbs::render('admin::sub_admins::create') }}
  @else
     {{ Breadcrumbs::render('admin::collection_users::edit') }}
  @endif
    <div class="col-md-12">

         @php  $validation=isset($sub_admin->id)?'':'<span class="validate_star">*</span>';  @endphp

        <!-- general form elements -->
        <div class="box box-primary sub_adm box-sm-wrap">

            
            <!-- form start -->
            @if($action=="create")
              {!! Form::open(['route'=>'admin::sub_admins::storeCreate', 'method'=>'POST','id'=>'create_subadmin','files'=>true]) !!}
            @else
              {!! Form::open(['route'=>['admin::sub_admins::update','id'=>$sub_admin->id], 'method'=>'POST','id'=>'edit_subadmin','files'=>true]) !!}
            @endif
               
                <div class="box-body adminSelect box-body-sm">
                     @include('layouts.admin.partials.lte_alerts')
                    <div class="form-group">
                        <label for="exampleInputEmail1">Name <span class="validate_star">*</span></label>
                        {!! Form::text('name',isset($sub_admin)? $sub_admin->name : old('name'),['class'=>'form-control','id'=>'nameId','placeholder'=>'Enter Name']) !!}
                    </div>  
                    
                     <?php $userId=Auth::user()->id;?>
                      {!! Form::hidden('creator_id',$userId) !!}

                    <div class="form-group">
                        <label for="exampleInputEmail1">Email Address <span class="validate_star">*</span></label>
                        {!! Form::email('email',isset($sub_admin)? $sub_admin->email : old('email'),['class'=>'form-control','placeholder'=>'Enter Email Id']) !!}
                    </div>
                    
                
                <div class="form-group">
                    <label for="logo">Logo <span class="validate_star">*</span></label>

                    @if(isset($sub_admin->logo))
                    <img src="{{ asset($sub_admin->logo) }}" width="100" height="100" id="previewImg" alt="" /> 
                    @else

                    
                    <img src="" width="100" height="100" id="previewImg" alt=""/>
                    @endif
                    {!! Form::file('logo',['class'=>'form-control','id'=>'imageUpload']) !!}
          
                </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Brokerage (%)<span class="validate_star">*</span></label>
                        {!! Form::number('brokerage',isset($sub_admin)? $sub_admin->brokerage : 0,['class'=>'form-control','id'=>'brokerageId','placeholder'=>'Enter Brokerage','min'=>'0','step' => '.01']) !!}
                    </div>
                      
                    <div class="form-group">
                        <label for="exampleInputPassword1">Password {!! $validation !!}</label>
                        {!! Form::password('password',['class'=>'form-control','placeholder'=>'Enter Password ','id'=>'inputPassword','minlength'=>6]) !!}
                    </div>

                    <div class="form-group">
                        <label for="exampleInputPassword1">Confirm Password {!! $validation !!}</label>
                        {!! Form::password('password_confirmation',['class'=>'form-control','placeholder'=>'Enter Password ','minlength'=>6]) !!}
                    </div>

                <?php 
                       $merchant_permission=isset($sub_admin->merchant_permission)?$sub_admin->merchant_permission:0;
                       $syndicate_permission=isset($sub_admin->syndicate)?$sub_admin->syndicate:0;
                  ?>

                     <div class="form-group">
                            <label for="exampleInputPassword1">Merchant Permission</label>
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                {{ Form::checkbox('merchant_permission',1,$merchant_permission, array('id'=>'merchant_permission')) }}
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check This</span>
                                    </label>
                                </div>
                             </div>
                    </div>
                     <div class="form-group">
                            <label for="exampleInputPassword1">Syndicate</label>
                            <div class="input-group check-box-wrap">
                                <div class="input-group-text">
                                    <label class="chc">
                                {{ Form::checkbox('syndicate_company',1,$syndicate_permission, array('id'=>'merchant_permission')) }}
                                        <span class="checkmark chek-m"></span>
                                        <span class="chc-value">Check This</span>
                                    </label>
                                </div>
                             </div>
                    </div>
                    <?php 
                        $company_status=isset($sub_admin->company_status)?$sub_admin->company_status:1;
                    ?>
                    <div class="form-group">
                        <label class="form-check-label" for="flexSwitchCheckDefault">Status</label>
                        <input data-onstyle="success" data-toggle="toggle" type="checkbox"  name="company_status" id="company_status" @if($company_status==1) checked @endif  class="badgebox">
                      </div>


                        <div class="btn-wrap btn-right">
                            <div class="btn-box">     @if(@Permissions::isAllow('Companies','View')) 
                                <a class="btn btn-success" href="{{URL::to('admin/sub_admins')}}">View Compaines</a>
                                @endif
                                @if($action=="create")
                                    {!! Form::submit('Create',['class'=>'btn btn-primary bran-mng-bt']) !!}
                                        @else
                                    {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                                @endif        
                            </div>                   
                        </div>
                        {!! Form::close() !!}


                     </div>


                <!-- /.box-body -->

                
        </div>
        <!-- /.box -->


    </div>

    



<div class="guid-outer">
    <button class="guid-btn">?</button>
    <div class="guid-wrap">
        <div class="header">User Guidline</div>
        <div class="guid-content">
            <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.  
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.  
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.  
            </p>
        </div>
    </div>
</div>


@stop
@section('scripts')

   <script>



    //$("#nameId").on("input", function(){
          // var regexp = /[^a-zA-Z ]*$/;
        //  if($(this).val().match(regexp)){
         // $(this).val( $(this).val().replace(regexp,'') );
         // }
     //  });

    $(document).ready(function () {
    $("#imageUpload").change(function(e){
        if(this.files && this.files[0]){
            let preview = document.getElementById('previewImg')
            preview.src = URL.createObjectURL(e.target.files[0])
            preview.onload = function() {
                URL.revokeObjectURL(preview.src) 
            }
        }
    })
    $('#create_subadmin').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            name: {
                required: true,
                maxlength: 255,
                
            },
            brokerage: {
                required:true,
                range:[0,100],
                
                
            },
            email: {
                required: true,
                email: true,
                maxlength: 255,
            },
             logo: {
                required: true,
                maxlength: 255,
            },
            
            company: {
                required: true,
                maxlength: 255,
            },
           password: {
                required: true,
                 maxlength: 255,
                 minlength: 6
                
            },
            password_confirmation: {
                required: true,
                equalTo: "#inputPassword",
                maxlength: 255,
                minlength: 6,
               
            },
            
        },
        messages: {
        name: "Enter Name",
        email: { required :"Enter Email Id",                 
                },

             
        password:"Enter Password",
        brokerage:"Enter Valid Brokerage",
        logo:"Upload Logo",
        password_confirmation: {
            required: "You must confirm your password.",
            minlength: "Your password must contain more than 6 characters.",
            equalTo: "Your Passwords Must Match." // custom message for mismatched passwords
        },
        
        
    },
  
});

 $('#edit_subadmin').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            name: {
                required: true,
                maxlength: 255,
            },
            brokerage: {
                required: true,
                range:[0,100],

                
            },
            email: {
                required: true,
                email: true,
                 maxlength: 255,
            },
           
            
        },
        messages: {
        name: "Enter Name",
        email: { required :"Enter Email Id",                 
                },        
        brokerage:"Enter Brokerage",
        
    },
  
});

    });



    $(document).ready(function(){
        $(".guid-btn").click(function(){
            $(".guid-wrap").slideToggle();
        });
    });


</script>
<script src="{{ asset ("js/bootstrap-toggle.min.js") }}"></script>
@stop

@section('styles')

  <style type="text/css">
    .adminSelect .select2-hidden-accessible {
    display: none;
    }
    .breadcrumb {
        padding: 8px 15px;
        margin-bottom: 20px;
        list-style: none;
        background-color: #f5f5f5;
        border-radius: 4px;
    }
    .breadcrumb > li {
        display: inline-block;
    }
   li.breadcrumb-item a{
        color: #6B778C;
    }
    .breadcrumb > li + li::before {
        padding: 0 5px;
        color: #ccc;
        content: "/\00a0";
    }
    li.breadcrumb-item.active{
        color: #2b1871!important;
    }
    .form-switch .form-check-input{
        width: 3em; height: 1.5em;
    }
    .form-switch .form-check-label {
        padding-top: .3em; 
        padding-left: 1em;
    }

 </style>

  <link href="{{ asset('/css/optimized/create_new_investor_admin.css?ver=5') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('css/bootstrap-toggle.min.css') }}" rel='stylesheet'/>
@stop

    

