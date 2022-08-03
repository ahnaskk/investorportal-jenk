@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        @if($action=="create")
        <div class="tool-tip">Create Module</div>
        @else
        <div class="tool-tip">Edit Module</div>
        @endif
    </a>
</div>
@if($action=="create")
{{ Breadcrumbs::render('admin::roles::create-module') }}
@else
{{ Breadcrumbs::render('Edit_module') }}
@endif
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary box-sm-wrap">
        <!-- form start -->
        @if($action=="create")
        {!! Form::open(['route'=>'admin::admins::save_module_data', 'method'=>'POST','id'=>'crete_module_form']) !!}
        @else
        {!! Form::open(['route'=>['admin::admins::update_module_data','id'=>$module->id], 'method'=>'POST']) !!}
        @endif
        <div class="box-body box-body-sm">
            @include('layouts.admin.partials.lte_alerts')
            <div class="form-group">
                <label for="exampleInputEmail1">Name <font color="#FF0000"> * </font></label>
                  {!! Form::text('name',isset($module)?$module->name:old('name'), ['class' => 'form-control', 'id' => 'module_name', 'data-placeholder'=>'Select Module']) !!}  


                <!-- {{ Form::select('name',[''=>'Select Modules','Payment Report' =>'Payment Report','Default Rate Report' =>'Default Rate Report','Default Rate Merchant Report' =>'Default Rate Merchant Report','Delinquent Report' => 'Delinquent Report','Payment Left Report'=> 'Payment Left Report','Lender Delinquent' => 'Lender Delinquent','Profitability(65/20/15)' => 'Profitability(65/20/15)', 'Profitability(50/30/20)' => 'Profitability(50/30/20)', 'Profitability(50/50)' =>'Profitability(50/50)','Investment Report'=>'Investment Report','Investor Assignment Report' => 'Investor Assignment Report','Investor Reassignment Report' => 'Investor Reassignment Report','Revenue Recognition Report'=>'Revenue Recognition Report', 'Transaction Report'=>'Transaction Report','Accrued Pre Return Report'=>'Accrued Pre Return Report','Debt Investor Report' => 'Debt Investor Report','Equity Investor Report'=>'Equity Investor Report','Total Portfolio Earnings' => 'Total Portfolio Earnings','OverPayment Report'=>'OverPayment Report','Merchants Per Diff Report' => 'Merchants Per Diff Report','Liquidity Report' =>'Liquidity Report','Investors'=> 'Investors','Generate PDF' => 'Generate PDF','Branch Manager'=>'Branch Manager', 'Merchants' => 'Merchants','Transactions' => 'Transactions','Velocity Distributions'=>'Velocity Distributions','Merchant Batches'=>'Merchant Batches','Payments'=>'Payments','Marketplace'=>'Marketplace','Liquidity Log'=>'Liquidity Log','Merchant Liquidity Log'=>'Merchant Liquidity Log','Merchant Status Log'=>'Merchant Status Log','Activity Log' =>'Activity Log','Bank Details'=>'Bank Details','Reconcile'=>'Reconcile','Template Management'=>'Template Management','Merchant Graph'=>'Merchant Graph','Marketing Offers'=>'Marketing Offers','Settings Advanced'=>'Settings Advanced','Settings Re-assign'=>'Settings Re-assign','Settings Sub Status'=>'Settings Sub Status','Settings Duplicate DB' =>'Settings Duplicate DB','Collection Users' =>'Collection Users','Companies'=>'Companies','Admins'=>'Admins','Editors'=>'Editors','Lenders'=>'Lenders','Viewers'=>'Viewers','Roles'=>'Roles','Permissions'=>'Permissions','Modules'=>'Modules','Firewall'=>'Firewall','Users'=>'Users'],'',['class'=>'form-control','id'=>'payment_type','data-placeholder'=>'Select Module']) }} -->
            </div>
            <?php $userId=Auth::user()->id;?>
            {!! Form::hidden('creator_id',$userId) !!}
            <!-- /.box-body -->
            <div class="btn-wrap btn-right">
                <div class="btn-box">
                    <a href="{{URL::to('admin/role/show-modules')}}" class="btn btn-success">View Modules</a>
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
</div>
<!-- /.box -->
</div>


@stop
@section('scripts')

<script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>
<script>
    $("#viewerNameId").on("input", function(){
   var regexp = /[^a-zA-Z ]*$/;
   if($(this).val().match(regexp)){
    $(this).val( $(this).val().replace(regexp,'') );
  }
});


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
          required: true,
          
          
        },
        password_confirmation: {
          required: true,
          equalTo: "#password"
        },
        
      },
      messages: {
        name: "Enter Name",
        email: { required :"Enter Email Id",                 
      },
      password:"Enter Password",
      password_confirmation:{ required : "Please Confirm Password",equalTo:"Passwords Do Not Match"},
      
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
          
          
        },
        messages: {
          name: "Enter Name",
          email: { required :"Enter Email Id",                 
        },
        
        
      }
      
    });


      
    });



</script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/create_new_investor.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/create_new_branch_manager.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/summernote-lite.min.css') }}" rel="stylesheet">
@stop