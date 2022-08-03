@extends('layouts.admin.admin_lte')
@section('content')

 <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
             @include('layouts.admin.partials.lte_alerts')
             
                {!! Form::open(['route'=>'admin::rate-config', 'method'=>'POST','id'=>'create_status_form']) !!}
                 <div class="box-body">

                <div class="form-group">
                <label for="exampleInputEmail1">Email <font color="#FF0000"> * </font></label>
                <?php $testMails=implode(", ",$emails); ?>
                
                <input type="text" value="{{ $testMails }}" data-role="tagsinput" name="email" style="display: none;" required="required">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Rate <font color="#FF0000"> * </font></label>
                    {!! Form::text('rate',isset($default)? $default['rate'] : old('rate'),['class'=>'form-control','id'=> 'inputRate','data-parsley-required-message' => 'Rate is required']) !!}
                </div>

                 <div class="form-group">
                    <label for="exampleInputEmail1">Default Payments <font color="#FF0000"> * </font></label>
                {!! Form::select('payments',$default_payment,$default['default_payment'],['class'=>'form-control','placeholder'=>'Select default payment','required','id'=> 'default_payment']) !!}

                </div>

                <div class="form-group">
                     <label for="exampleInputEmail1">Start Date <font color="#FF0000"> * </font></label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control from_date1 datepicker" autocomplete="off" id="date_start1" name="date_start1" placeholder="{{ \FFM::defaultDateFormat('format') }}" type="text" value="{{$default['portfolio_start_date']}}"/>
                                <input type="hidden" class="date_parse" name="date_start" id="date_start" value="{{$default['portfolio_start_date']}}">
                            </div>

                </div>
                </div>
              <div class="box-footer">
               {!! Form::submit('Update',['class'=>'btn btn-primary']) !!}
              </div>
             {!! Form::close() !!}


        </div>
      </div>

@stop

@section('scripts')
 <script src="{{ asset ('bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.min.js') }}" type="text/javascript"></script>

@stop

@section('styles')
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop
