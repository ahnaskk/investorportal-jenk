@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Create Distribution </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Create Distribution</div>     
      </a>
      
  </div>
{{ Breadcrumbs::render('admin::vdistribution::createVdistribution') }}
     <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

            
            <!-- form start -->
            {!! Form::open(['route'=>['admin::vdistribution::storeVdistribution','method'=>'POST','data-parsley-validate'=>true],'id'=>'velocity_distribution_form']) !!}
            @include('layouts.admin.partials.lte_alerts')
                <div class="box-body" id="valid-req">
                  <div class="row">
                    <div class="col-md-6 velocity-log">
                         <label for="exampleInputEmail1">Amount <span class="validate_star">*</span></label>
                        {!! Form::text('amount',old('amount'),['class'=>'form-control accept_digit_only','required','id'=> 'inputAmount','data-parsley-required-message' => 'Amount Is Required','placeholder'=>'Enter the amount']) !!}
                    </div>
                     <div class="col-md-6 velocity-log">
                        <label for="exampleInputEmail1">Investment Date</label>
                        {!! Form::text('date1',old('date'),['class'=>'form-control datepicker','id'=>'date1','placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete'=>'off']) !!}
                        <input type="hidden" name="date" value="{{old('date')}}" id="date" class="date_parse">
                    </div>   <div class="col-md-6 velocity-log">
                        <label for="exampleInputEmail1">Maturity Date</label>
                        {!! Form::text('maturity_date1',old('maturity_date'),['class'=>'form-control datepicker','id'=>'maturity_date1', 'placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete'=>'off']) !!}
                        <input type="hidden" name="maturity_date" value="{{old('maturity_date')}}" id="maturity_date" class="date_parse">
                    </div>
                    
                       <?php $userId=Auth::user()->id;?>
                      {!! Form::hidden('creator_id',$userId) !!}

                   <div class="col-md-6 velocity-log">
                    <label for="exampleInputEmail1">Investor <span class="validate_star">*</span></label>
                 
                    <select id="investor_id" name="investor_id" class="form-control">
                        @foreach($investors as $investor)

                            <option  {{

                            old('investor_id')?


                            (old('investor_id')==$investor->id?'selected':''):

                            ($investorId==$investor->id?'selected':'') 


                          }} value="{{$investor->id}}">{{$investor->name}}</option>
                        @endforeach
                    </select>
                   </div>         

                <div class="col-md-6 velocity-log">
                    <label for="exampleInputEmail1">Transaction Category <span class="validate_star">*</span></label>
                 
                    <select id="transaction_category" name="transaction_category" class="form-control">
                        @foreach($transaction_categories as $key => $transaction_category)

                            <option  {{old('transaction_category')==$key?'selected':''}} value="{{$key}}">{{$transaction_category}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 velocity-log">
                    <label for="exampleInputEmail1">Transaction Type <span class="validate_star">*</span></label>
                 <?PHP
                 $transaction_types=[1=>'Debit',2=>'Credit'];
                 ?>

                 {{Form::select('transaction_type',$transaction_types,1,['class'=>'form-control','placeholder'=>'Enter the amount'] )}}
                <!--     <select id="transaction_category" name="transaction_category" class="form-control">
                 
                    </select> -->
                </div>
                <div class="col-md-12">
                  <div class="btn-wrap btn-right">
                      <div class="btn-box">
                            @if(@Permissions::isAllow('Velocity Distributions','View'))
                          <a href="{{URL::to('admin/vdistribution')}}" class="btn btn-success">View lists</a>
                          @endif
                              @if(@Permissions::isAllow('Velocity Distributions','Create'))
                          {!! Form::submit('Create',['class'=>'btn btn-primary']) !!}   
                          @endif                                                   
                      </div>                   
                  </div>
                </div>

                </div>
                <!-- /.box-body -->
                

                
            {!! Form::close() !!}
        </div>
        <!-- /.box -->

    </div>


@stop

@section('scripts')

 <script>
     
 $(document).ready(function () {
  jQuery.validator.addMethod("date",function(value, element, params) {
    return moment(params).isValid();
  });
    $('#velocity_distribution_form').validate({ // initialize the plugin
        errorClass: 'error',
        rules: {
            amount: {
              required: {
              depends:function(){
              $(this).val($.trim($(this).val()));
               return true;
            }
          },
                 numbersWithComma:true
            },
            transaction_category: {
                required: true,
            },
            date1:{
              date:function(){
                return $('#date').val();
              },
            },
            maturity_date1:{
              date:function(){
                return $('#maturity_date').val();
              },
            }
      },
        messages: {
        amount: "Enter Amount",
        transaction_category: { required :"Select Transaction Category"},
        date1:{date:"Enter Valid Date"},
        maturity_date1:{date:"Enter Valid Date"},

        
      },
  
});

$('#inputAmount').keypress(function(event) {
    
     if(event.which == 46 && $.trim($(this).val()).indexOf('.') != -1) {
        event.preventDefault();
     } // prevent if already dot
     if(event.which == 44
     && $(this).val().indexOf(',') != -1) {
        event.preventDefault();
      } // prevent if already comma
});


$('#inputAmount').keyup(function(event) {

  // skip for arrow keys
  if(event.which >= 37 && event.which <= 40){
   event.preventDefault();
  }

  $(this).val(function(index, value) {
      value = value.replace(/,/g,'');
      return value;
     // return numberWithCommas(value);

  });
});

function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

 $(".accept_digit_only").keypress(function (evt) {
     
     var theEvent = evt || window.event;
          var key = theEvent.keyCode || theEvent.which;
          key = String.fromCharCode(key);
          if (key.length == 0) return;
          var regex = /^[0-9.,\b]+$/;
          if (!regex.test(key)) {
              theEvent.returnValue = false;
              if (theEvent.preventDefault) theEvent.preventDefault();
          } 
     
});


});
</script>

<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>

<!-- <script type="text/javascript">

$(document).ready(function(){
        
         $('#date').datepicker({
            autoclose: true,
            format : "mm/dd/yyyy",
         });
         $('#maturity_date').datepicker({
            autoclose: true,
            format : "mm/dd/yyyy",
         });

});

</script> -->

@stop


@section('styles')
<style type="text/css">
#valid-req label#inputAmount-error {
  display: inline-block;
    left: 83%;
    width: 100px;
    position: absolute;
    left: 100%;
    margin-left: -109px;
        color: #ff0101;
}
.velocity-log {
    margin-bottom: 16px!important;
}
.validate_star {
    color: red;
}  

</style>
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/velocity_edit.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
