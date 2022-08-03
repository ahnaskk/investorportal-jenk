@extends('layouts.branchmanager.admin_lte')

@section('content')

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

            @include('layouts.branchmanager.partials.lte_alerts')
                    <!-- form start -->
            @if($action=="create")
                {!! Form::open(['route'=>'branch::marketplace::storeCreate', 'method'=>'POST']) !!}
            @else
                {!! Form::open(['route'=>['branch::marketplace::update','id'=>$marketplace->id], 'method'=>'POST']) !!}
            @endif
            





            <div class="box-body col-md-6">
                <div class="form-group">
                    <label for="exampleInputEmail1">Name</label>
                    {!! Form::text('name',isset($merchant)? $merchant->name : old('name'),['class'=>'form-control']) !!}
                    {!! Form::hidden('id',isset($merchant)? $merchant->id : '' ) !!}
                </div>






                <div class="form-group">
                    <label for="exampleInputEmail1">Business Entity Name</label>
                    {!! Form::text('business_en_name',isset($merchant)? $merchant->business_en_name : old('business_en_name'),['class'=>'form-control']) !!}
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">MID</label>
                    {!! Form::text('id',isset($merchant)? $merchant->id : old('id'),['class'=>'form-control']) !!}
                </div>
                     <div class="form-group">
                    <label for="exampleInputEmail1">Funded</label>

                    <div class="input-group">
                        <span class="input-group-addon">$</span>
                        {!! Form::text('funded',isset($merchant)? $merchant->funded : old('funded'),['class'=>'form-control']) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">RTR</label>

                    <div class="input-group">
                        <span class="input-group-addon">$</span>
                        {!! Form::text('rtr',isset($merchant)? $merchant->rtr : old('rtr'),['class'=>'form-control','disabled']) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">CTD</label>

                    <div class="input-group">
                        <span class="input-group-addon">$</span>
                        {!! Form::text('ctd',isset($merchant)? $merchant->ctd : old('ctd'),['class'=>'form-control','disabled']) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Balance</label>

                    <div class="input-group">
                        <span class="input-group-addon">$</span>
                        {!! Form::text('balance',isset($merchant)? $merchant->balance : old('balance'),['class'=>'form-control','disabled']) !!}
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Factor Rate</label>

                    <div class="input-group">
                        {!! Form::text('factor_rate',isset($merchant)? $merchant->factor_rate : old('factor_rate'),['class'=>'form-control']) !!}
                        <span class="input-group-addon">%</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Date Funded</label>
                    {!! Form::text('date_funded',isset($merchant)? $merchant->date_funded : old('date_funded'),['class'=>'form-control','id'=>'datepicker']) !!}
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Commission</label>

                    <div class="input-group">
                        {!! Form::text('commission',isset($merchant)? $merchant->commission : old('commission'),['class'=>'form-control']) !!}
                        <span class="input-group-addon">%</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">PMNTS</label>
                    {!! Form::text('pmnts',isset($merchant)? $merchant->pmnts : old('pmnts'),['class'=>'form-control']) !!}
                </div>  
<hr>

 <div class="form-group">
                    <label for="exampleInputEmail1">Open Status</label>
                    {!! Form::select('open_item',[0=>"No",1=>"Yes"] ,isset($merchant)? $merchant->open_item : old('business_en_name'),['class'=>'form-control']) !!}
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Status</label>
                    {!! Form::select('sub_status_id',$statuses,isset($merchant)? $merchant->sub_status_id : old('sub_status_id'),['class'=>'form-control']) !!}
                </div>
          

          
                <div class="form-group">
                    <label for="exampleInputEmail1">Marketplace?</label>
                    {!! Form::select('marketplace_status',[0=>'No',1=>'Yes'],isset($merchant)? $merchant->marketplace_status : old('marketplace_status'),['class'=>'form-control']) !!}
                </div>
            </div>

            <div class="box-body col-md-6"></div>
            <!-- /.box-body -->
            @if($action=="create")
                <div class="box-footer">
                    {!! Form::submit('Create',['class'=>'btn btn-primary']) !!}
                </div>
            @else
                <div class="box-footer">
                    {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
                </div>
            @endif

            {!! Form::close() !!}
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

    management_fee=$(this).find(':selected').data('management-fee');
    participant_name=$(this).find(':selected').data('name');
   // alert(management_fee);
    $('#mgmnt_fee').val(management_fee);
    $('#participant_name').val(participant_name);
});

    </script>
@stop

@section('styles')
    <link rel="stylesheet" href="{{asset('bower_components/bootstrap-daterangepicker/daterangepicker.css')}}">
    <!-- bootstrap datepicker -->
    <link rel="stylesheet"
          href="{{asset('bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}">

@stop