@extends('layouts.admin.admin_lte')

@section('content')
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')

        </div>
        <div class="box-body">
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
             <div class="row">
                    <div class="col-sm-12">
                        <div class="grid">
                            <div class="">
                                {{Form::open(['route'=>'admin::merchants::export-deals'])}}

                              <div class="col-md-4">
                                     <div class="input-group">
                                {{Form::text('effectiveDate1',$default_date,['class'=>'dat-exp datepicker', 'placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete' => 'off'])}}
                                <input type="hidden" name="effectiveDate" value="{{$default_date}}" class="date_parse">
                              </div>

                              </div>

                            <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                </div>

                        {{Form::select('merchant',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants'])}}

                            

                            </div>
                            <span class="help-block">Merchants</span>
                        </div>

                           <div class="col-md-4">
                                  {{Form::submit("export",['class'=>'btn btn-success export'])}}
                           </div>

                          



                               
                             
                                {{Form::close()}}
                            </div>
                         </div>
                      </div>
                   </div>
                </div>
           </div>
        <!-- /.box-body -->
    </div>
    <link href="{{ asset('/css/optimized/amort_sched.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop

@section('scripts')
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
@stop

@section('styles')
<link href="{{ asset('/css/optimized/Payment_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop

