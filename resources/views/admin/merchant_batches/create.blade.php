@extends('layouts.admin.admin_lte')

@section('content')

   <div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>     
      </a>
      
  </div>
  <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">


           

            
                    <!-- form start -->
            @if($action=="create")
                {!! Form::open(['route'=>'admin::merchant_batches::storeCreate', 'method'=>'POST' ,'id'=>'batch_form']) !!}
            @else
                {!! Form::open(['route'=>'admin::merchant_batches::update', 'method'=>'POST']) !!}
            @endif
            @include('layouts.admin.partials.lte_alerts')
            <div class="box-body box-body-sm">
                
                <div class="form-group">
                    <label for="exampleInputEmail1">Name <span class="validate_star">*</span></label>
                    {!! Form::text('name',isset($batch)? $batch->name : old('name'),['class'=>'form-control','id'=>'inputName']) !!}
                    @if(isset($batch)){!! Form::hidden('id',$batch->id) !!}@endif
                     <span id="invalid-inputName" />
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Merchant <span class="validate_star">*</span></label>
                    <select name="merchants[]" class="form-control js-merchant-placeholder-multiple" multiple id="inputMerchant">
                        @if(isset($selected_merchants))
                        @foreach ($selected_merchants as $selected_merchant)
                        <option value="{{ $selected_merchant->id }}" selected="selected">{{ $selected_merchant->name }}<option>
                        @endforeach
                        @endif
                    </select>
                     <span id="invalid-inputMerchant" />
                </div>
                <!-- /.box-body -->

                    <div class="btn-wrap btn-right">
                        <div class="btn-box"> 
                              @if(@Permissions::isAllow('Merchant Batches','View'))  
                            <a class="btn btn-success" href="{{URL::to('admin/merchant_batches')}}">Back to lists</a>
                            @endif
                             @if($action=="create")
                             @if(@Permissions::isAllow('Merchant Batches','Create'))  
                            {!! Form::submit('Create',['class'=>'btn btn-primary']) !!}
                            @endif
                            @else
                            @if(@Permissions::isAllow('Merchant Batches','Edit'))  
                              {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                              @endif
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
    <script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
    <script type="text/javascript">
        //Date picker
        $('#datepicker').datepicker({
            autoclose: true,
            format : "yyyy-mm-dd",
            clearBtn: true,
            todayBtn: "linked"
        });

        $("#inputName").on("input", function(){
           var regexp = /[^a-zA-Z ]*$/;
          if($(this).val().match(regexp)){
          $(this).val( $(this).val().replace(regexp,'') );
          }
       });


        
    $(document).ready(function () {
    $('#batch_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            name: {
                required: true,
                maxlength: 255,
            },
            "merchants[]":{
                 required: true,  
            }
         
         
            
        },
        messages: {
        name: "Enter Name",
        "merchants[]": { required :"Enter Merchant",                 
      },      
        
        
    },
      errorPlacement: function(error, element) {
        error.appendTo('#invalid-' + element.attr('id'));
        }
  
});

    });

</script>
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>  
@stop

@section('styles')
  <link href="{{ asset('/css/optimized/merchant_batches_create.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop