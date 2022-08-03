@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Edit Transactions </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Edit Transactions</div>     
      </a>

  </div>
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-pad">

            @include('layouts.admin.partials.lte_alerts')
            <!-- form start -->
            {!! Form::open(['route'=>['admin::investors::transaction::update','id'=>$investorId , 'tid' => $transaction->id], 'method'=>'POST','id'=>'transaction_form']) !!}
                <div class="box-body">

           <?php 
//                 $amount=\FFM::dollar($transaction->amount);
//                 $amount = trim(str_replace( '$', '', $amount));
           ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Amount</label>
                            {!! Form::text('amount',$transaction->amount,['class'=>'form-control accept_digit_only','placeholder'=>'Enter Investor Name']) !!}
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Investment Date</label>
                            {!! Form::text('date1',$transaction->date,['class'=>'form-control datepicker','placeholder' =>\FFM::defaultDateFormat('format'), 'autocomplete'=>'off']) !!}
                            <input type="hidden" name="date" class="date_parse" value="{{$transaction->date}}">
                        </div>
                    </div> 
                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Maturity Date</label>
                            {!! Form::text('maturity_date1',$transaction->maturity_date,['class'=>'form-control datepicker','placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete'=>'off']) !!}
                            <input type="hidden" name="maturity_date" value="{{$transaction->maturity_date}}" class="date_parse">
                        </div>
                    </div>

<!--   <div class="col-md-6">
                    <label for="exampleInputEmail1">Investor</label>

                    <select id="investor_id" name="investor_id" class="form-control">
                        @foreach($investors as $investor)

                            <option  {{$transaction->investor_id==$investor->id?'selected':''}} value="{{$investor->id}}">{{$investor->name}}</option>
                        @endforeach
                    </select>
                </div>  -->        

                <div class="col-md-4">
                    <label for="exampleInputEmail1">Transaction Category</label>

                    <select id="transaction_category" name="transaction_category" class="form-control">
                        @foreach($transaction_categories as $key => $transaction_category)

                            <option  {{$transaction->transaction_category==$key?'selected':''}} value="{{$key}}">{{$transaction_category}}</option>
                        @endforeach
                    </select>
                </div>



                        <div class="col-md-4">
                    <label for="exampleInputEmail1">Transaction Type</label>
                 <?PHP
                 $transaction_types=[1=>'Debit',2=>'Credit'];
                 ?>
{{Form::hidden('tran_type','',['class'=>'form-control','id'=>'tran_type'] )}}
                 {{Form::select('transaction_type',$transaction_types,$transaction->transaction_type,['class'=>'form-control','placeholder'=>'Enter The Amount','disabled'=>'disable','id'=>'transaction_type'] )}}
                <!--     <select id="transaction_category" name="transaction_category" class="form-control">

                    </select> -->
                </div>



                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                   <div class="col-md-12 col-sm-12">
                       <div class="block pull-right">
                           {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                            <div class="btn btn-primary"> <a href="{{URL::to('admin/investors/transactions',$investorId)}}" style="color: #fff">View lists</a></div>
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

  $(document).ready(function () {
    var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
    $.validator.addMethod("dateRange", function(value, element, params) {
        var date = moment(value, default_date_format).format('YYYY-MM-DD');
        var from = moment(params.from, 'YYYY-MM-DD').format('YYYY-MM-DD');
        var to = moment(params.to, 'YYYY-MM-DD').format('YYYY-MM-DD');
        if (date >= from && date <= to) {
            return true;
        } 
        return false;
    }, 'Enter Date between 01/01/2016 to 31/12/2026');
    $('#transaction_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            amount: {
              range: [1.0, 99999999999],
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
                dateRange: {
                    from: '2016-01-01',
                    to: '2026-12-31'
                  }
            }
        },
        messages: {
        amount: {required : "Enter Amount",range:"Enter Amount Between 1 and 99999999999"},
        transaction_category: { required :"Select Transaction Category"},

      },

});
 $('#transaction_category').change(function() {
    var category = $(this).val();
    if(category==1){
    $('#transaction_type').val(2).change(); 
    $('#tran_type').val(2).change(); 

    }
    else{
    $('#transaction_type').val(1).change();  
    $('#tran_type').val(1).change(); 
    }


});   

$('#inputAmount').keypress(function(event) {

     if(event.which == 46 && $(this).val().indexOf('.') != -1) {
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
      return (value);
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

@stop
