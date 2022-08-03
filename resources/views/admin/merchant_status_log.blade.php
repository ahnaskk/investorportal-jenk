@extends('layouts.admin.admin_lte')
@section('content')

         <?php
                 $date_end = date('Y-m-d');
                 $date_start = date('Y-m-d', strtotime('-1 days', strtotime($date_end)));
         ?>

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Merchant Status Log</div>     
      </a>
  </div>
      {{ Breadcrumbs::render('admin::merchant_status_log') }}
<div class="col-md-12">
<div class="box">
  <div class="box-head ">
    @include('layouts.admin.partials.lte_alerts')
  </div>
    <div class="box-body"> 
        <div class="form-box-styled">
        
                      <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control from_date1 datepicker" id="date_start1" autocomplete="off" value="{{ $date_start }}" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                <input type="hidden" class="date_parse" name="date_start" id="date_start" value="{{ $date_start }}">
                            </div>
                            <span class="help-block">From Date</span>
                        </div>

                               <div class="col-md-6">
                                 <div class="input-group">
                                    <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                  </div>
                                 <input class="form-control to_date1 datepicker" id="date_end1" autocomplete="off" value="{{ $date_end }}" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                 <input type="hidden" name="date_end" id="date_end" value="{{$date_end}}" class="date_parse">
                               </div>
                             <span class="help-block">To Date</span>

                           </div>
                       
                       </div>
                                            
                
                    <div class="row">
                      <div class="col-md-6">
                         <div class="input-group">                                  
                            <select class="form-control js-status-placeholder-multiple" name="status_id[]" id="status_id" multiple="multiple">
                              @foreach($sub_statuses as $sub_status)

                              <option  value='{{$sub_status->id}}'>{{$sub_status->name}} </option>
                              @endforeach
                            </select>
                            <span class="help-block">Status</span>                                     
                        </div>
                      </div>

                      <div class="col-md-6">
                          <div class="input-group">
                              <div class="input-group-text">
                                  <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                              </div>
                              {{Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}
                          </div>
                          <span class="help-block">Merchants</span>
                      </div>
                      <div class="col-md-12">
                        <div class="btn-wrap btn-right">
                          <div class="btn-box">
                            <input type="button" value="Apply Filter" class="btn btn-primary" id="date_filter"
                                       name="student_dob">
                            </div>
                        </div>
                      </div>

                    </div>                             
               
                
          </div>
     
     

    
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
              <div class="grid table-responsive">
                  {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                  <!--  <div class="blockCust pull-right" style="padding-bottom: 15px">
                        {{Form::submit('Download report',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                    </div>-->
              </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
</div>
@stop


@section('scripts')

{!! $tableBuilder->scripts() !!}
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>  
<script>var URL_getMerchants = "{{ URL::to('admin/getSelect2MerchantsWithDeleted') }}";</script>
<script type="text/javascript">
    $(document).ready(function(){
    	var table = window.LaravelDataTables["dataTableBuilder"];
        $('#date_filter').click(function (e) {
            e.preventDefault();
            table.draw();
        });

  $(".js-status-placeholder-multiple").select2({
        placeholder: "Select Status(es)"
});
  
 });
</script>
@stop
@section('styles')
  <link href="{{ asset('/css/optimized/Merchant_Status_Log.css?ver=5') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
