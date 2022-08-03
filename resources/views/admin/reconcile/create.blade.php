@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Reconcile Merchant</div>     
      </a>
      
  </div>

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap ">

              @include('layouts.admin.partials.lte_alerts')
            <!-- form start -->
                  <div class="box-body box-body-sm">

                    
                    <div class="form-group">
                        <label for="exampleInputEmail1">Date <span class="validate_star">*</span></label>
                       <input id="datepicker1" class="form-control input-sm date datepicker" placeholder="Date" autocomplete="off" name="dates1" type="text">
                       <input type="hidden" name="dates" id="datepicker" class="date_parse">
                    </div> 
 


                    <div class="reconcil_create">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Lender <span class="validate_star">*</span></label>
                        {!! Form::select('lender',$lenders,isset($admin)? $admin->lender : old('lender'),['class'=>'form-control' , 'id'=>'lender','placeholder'=>'Select Lender','onchange'=>'javascript:location.href = location.href+"/"+this.value+"?days="+document.getElementById("datepicker").value ;']) !!}
                    </div> 
                </div>
 
 </div>     




        </div>
        <!-- /.box -->


    </div>


@stop

@section('styles')
     <link href="{{ asset('/css/optimized/create_new_user_admin.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop