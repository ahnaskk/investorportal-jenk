@extends('layouts.admin.admin_lte')

@section('content')
<?PHP

$i2=0;

while($i2<=6) 
{

    // $i+=0.25;
    $fee["$i2"]=$i2;
    $i2=$i2+0.25;
    # code...
}



$merchant_amount=$merchant->funded;

                //$select_val=[5,10,15,20,25,30,35,40,45,50];

for($i=5;$i<=50;$i++) 
{

    $select_val[$i]=$i;
    # code...
}

$final_val=[];

$percent=$max_participant_fund/$merchant_amount*100;
               // $final_val[$max_participant_fund]=FFM::dollar($max_participant_fund)." - ". FFM::percent($percent);
foreach ($select_val as $key => $value) 
{
    $per_value = $value*$merchant_amount/100;


    if($per_value<$max_participant_fund)
    {

        $final_val[$per_value]=FFM::dollar($per_value)." - ". FFM::percent($value);
    }else
    {

        $final_val[$per_value]=FFM::dollar($per_value)." - ". FFM::percent($value);
    }

}

?>


<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">

        <div class="row">
            <div class="col-md-12">
                @include('layouts.admin.partials.lte_alerts')
                <!-- form start -->

                {!! Form::open(['route'=>'admin::merchant_investor::create', 'method'=>'POST']) !!}
                <div class="box-body col-md-12">
                    <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Participant Amount </label>
                            {!! Form::select('amount',$final_val,isset($merchant)? $merchant->amount : old('amount'),['class'=>'form-control','id'=>'amount']) !!}
                        </div>

                    </div>
                        </div>
                  <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="input-group">
                                {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}

                                <a class="btn btn-success" href="{{URL::to('admin/merchants/edit',$merchant->id)}}">Edit Merchant</a>
                                <a class="btn btn-danger pull-right" href="{{URL::to('admin/merchants/view',$merchant->id)}}">View Merchant</a>
                            </div>
                        </div>
                    </div>
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
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript">
    //Date picker
    $('#datepicker').datepicker({
        autoclose: true,
        format : "yyyy-mm-dd",
        clearBtn: true,
        todayBtn: "linked"
    });


    $('#user_id').change(function() {

liquiditycheck();
    mgmnt_fee_percentage=$(this).find(':selected').data('management-fee');
    syndication_fee_percentage=$(this).find(':selected').data('synd-fee');
    participant_name=$(this).find(':selected').data('name');
   // alert(management_fee);
   $('#mgmnt_fee_percentage').val(mgmnt_fee_percentage).trigger('change');
   $('#syndication_fee_percentage').val(syndication_fee_percentage).trigger('change');;
   $('#participant_name').val(participant_name);
});



        $('#merchant_id').change(function() {

            funded_date=$(this).find(':selected').data('funded-date');
            funded_amount=$(this).find(':selected').data('funded-amount');


            $('#date_funded').val(funded_date);

            $("#amount").empty();


            var option = $('<option></option>').attr("value", 5*funded_amount/100).text(5*funded_amount/100);
            $("#amount").append(option);
            var option = $('<option></option>').attr("value", 10*funded_amount/100).text(10*funded_amount/100);
            $("#amount").append(option);
            var option = $('<option></option>').attr("value", 15*funded_amount/100).text(15*funded_amount/100);
            $("#amount").append(option);


            var option = $('<option></option>').attr("value", 20*funded_amount/100).text(20*funded_amount/100);
            $("#amount").append(option);
            var option = $('<option></option>').attr("value", 25*funded_amount/100).text(25*funded_amount/100);
            $("#amount").append(option);
            var option = $('<option></option>').attr("value", 30*funded_amount/100).text(30*funded_amount/100);
            $("#amount").append(option);
            var option = $('<option></option>').attr("value", 35*funded_amount/100).text(35*funded_amount/100);
            $("#amount").append(option);
            var option = $('<option></option>').attr("value", 40*funded_amount/100).text(40*funded_amount/100);
            $("#amount").append(option);
            var option = $('<option></option>').attr("value", 45*funded_amount/100).text(45*funded_amount/100);
            var option = $('<option></option>').attr("value", 50*funded_amount/100).text(50*funded_amount/100);
            $("#amount").append(option);

  //  $('#amount').option('hi');
});
        $('#amount').change(function() {

         
  liquiditycheck();
});
function liquiditycheck()
{
      liquidity=$('#user_id').find(':selected').data('liquidity');
    funded_amount=$('#amount').find(':selected').val();
   

    if(funded_amount>liquidity)
    {
        if(!liquidity)
        {
            liquidity=0;
        }
        alert('Cash in hand is only $'+liquidity);
    }
  //  $('#amount').option('hi');
}



</script>
@stop

@section('styles')
<link rel="stylesheet" href="{{asset('bower_components/bootstrap-daterangepicker/daterangepicker.css')}}">
<!-- bootstrap datepicker -->
<link rel="stylesheet"
href="{{asset('bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">

@stop
