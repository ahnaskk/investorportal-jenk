@extends('layouts.admin.admin_lte')

@section('content')

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

            
                    <!-- form start -->

                {!! Form::open(['route'=>'admin::merchant_batches::update', 'method'=>'POST']) !!}
                @include('layouts.admin.partials.lte_alerts')
            <div class="box-body col-md-12">
                
                <div class="col-sm-12">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Name</label>
                    {!! Form::text('name',isset($batch)? $batch->name : old('name'),['class'=>'form-control']) !!}
                  
                    {!! Form::hidden('id',$batch->id) !!}
                </div>
                 </div>
                   <div class="col-sm-12">
                    <div class="form-group">
                    <label for="exampleInputEmail1">Merchant</label>
                     {!! Form::select('merchants[]', $merchants, $slected_merchant,['class'=>'form-control march-selec js-investor-placeholder-multiple', 'placeholder'=>'select   merchant','form-control00', 'multiple'=>'multiple']) !!}
                   </div>
                </div>
            </div>
            
            <!-- /.box-body -->

                <div class="box-footer" style="display: inline-block;">
                    {!! Form::submit('Update',['class'=>'btn btn-primary merc-bt']) !!}
                    <div class="btn btn-primary"> <a href="{{URL::to('admin/merchant_batches')}}" style="color: #fff">Back to lists</a></div>
                </div>
            {!! Form::close() !!}
        </div>
        <!-- /.box -->
    </div>

@stop

@section('scripts')
<script type="text/javascript">
        $(document).ready(function(){
  $(".js-investor-placeholder-multiple").select2({
        placeholder: "Select Investor"
}); 
});
</script>
@stop
@section('styles')
<!--     <link rel="stylesheet" href="{{asset('bower_components/bootstrap-daterangepicker/daterangepicker.css')}}"> -->
    <!-- bootstrap datepicker -->
    <!-- <link rel="stylesheet"
          href="{{asset('bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')}}"> -->

@stop