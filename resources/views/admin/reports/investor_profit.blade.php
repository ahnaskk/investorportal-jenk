@extends('layouts.admin.admin_lte')

@section('content')


    <div class="box">
        <div class="box-body">
        <div class="form-group pay-rep">
                <div class="filter-group-wrap" >
                    <div class="filter-group" >
                    {{Form::open(['route'=>''])}}

<div class="serch-bar">
<div  class="row">
<div class="col-sm-12">

            <div class="col-md-4">
                <div class="input-group inp-grp">
                    <div class="input-group-text">
                       <label class="chc"><input  id="date_type" name="date_type" type="checkbox" value="true" @if($checked_status) checked @endif/><span class="checkmark checkk00"></span>
                        </label>
                      </div>         
                   </div>
                <span class="grid inputInfoLg small">Filter based on Payment Added Date (Payment Date by default)</span>
            </div>

<div class="date-star" id="date-star">
            <div class="col-md-4">
                <div class="input-group">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                        </div>
                         <input class="form-control from_date1 datepicker" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off" value="{{$sdate}}"/>
                         <input type="hidden" name="date_start" id="date_start" value="{{$sdate}}" class="date_parse">
                      </div>
                    <span class="help-block">From Date</span>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <div class="input-group-text">
                           <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                           </div>
                           <input class="form-control to_date1 datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off" value="{{$edate}}"/>
                           <input type="hidden" name="date_end" id="date_end" value="{{$edate}}" class="date_parse">
                        </div>
                     <span class="help-block">To Date</span>
    </div>
</div>


   <div id="time_filter" class="check-time" style="display:none;">
       <div class="col-sm-12">
         <div class="row">

            <div class="col-md-3 serch-timeer-one">
                <div class="input-group serch-two">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                        </div>
                         <input class="form-control from_date2 datepicker" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off" value="{{$sdate}}"/>
                         <input type="hidden" name="date_start" id="date_start" value="{{$sdate}}" class="date_parse">
                      </div>
                    <span class="help-block">From Date</span>
                </div>


                <div class="col-lg-3 serch-timeer">
                 <div class="input-group">
                    <div class="input-group-text">
                        <span class="glyphicon glyphicon-time" aria-hidden=" true"></span>
                        </div>
                          <input class="timepicker form-control from_time" type="text" id="time_start" name="time_start">
                      </div>
                    <span class="help-block">From Time</span>
                    </div>

           
                  <div class="col-md-3 serch-timeer-one">
                    <div class="input-group serch-two">
                        <div class="input-group-text">
                           <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                           </div>
                           <input class="form-control to_date2 datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off" value="{{$edate}}"/>
                           <input type="hidden" name="date_end" id="date_end" value="{{$edate}}" class="date_parse">
                        </div>
                     <span class="help-block">To Date</span>
                </div>      
                      <div class="col-lg-3 serch-timeer">
                    <div class="input-group">
                        <div class="input-group-text">
                           <span class="glyphicon glyphicon-time" aria-hidden="true"></span>
                           </div>
                           <input class="timepicker form-control to_time" type="text" id="time_end" name="time_end">
                        </div>
                     <span class="help-block">To Time</span>
                 </div> 
               </div>
            </div>        
          </div>
        </div>
    </div>
</div>
                  
                        
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                </div>

                                {{Form::select('investors[]',$investors,$selected_investor,['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                            </div>
                            <span class="help-block">Investors</span>
                        </div>
                     
                    </div>
          
                    <div class="btn-box " style="margin-bottom: 25px;">
                        <div class="input-group">
                            <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                                           name="student_dob">
                         
                            {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}

                        </div>
                    </div>
                </div>
                    
                    {{Form::close()}}
            </div>


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12">

                        <div class="table-container grid table-responsive" > 
                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>

@stop

@section('scripts')

  <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"
            type="text/javascript"></script>

  <script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"
            type="text/javascript"></script>

  {!! $tableBuilder->scripts() !!}

   <script src="{{ asset ('/js/updated/moment.min.js') }}" type="text/javascript"></script>
        <script src="{{ asset ('/js/updated/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>

  <script type="text/javascript">
  	
     $(document).ready(function () {

     	  $('#date_filter').click(function (e) {
                e.preventDefault();
                table.draw();

            });
        var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
        $('.from_date1,.from_date2').on('change changeDate', function(){
            var val = $(this).val();
            if(val &&  moment(val, default_date_format).isValid())
            {
                let year = moment(val, default_date_format).year();
                if(year.toString().length == 1 || year.toString().length == 2) {
                    year = moment(year, 'YY').format('YYYY');
                }
                var newDate = moment(val, default_date_format).set('year', year).format(default_date_format);
                $('.from_date1,.from_date2').val(newDate);
                $('.from_date1,.from_date2').datepicker('update');
                $('.from_date1,.from_date2').siblings('.date_parse').val(moment(val, default_date_format).set('year', year).format('YYYY-MM-DD'));
            }else {
                $('.from_date1,.from_date2').siblings('.date_parse').val('');
            }
        });
        $('.to_date1,.to_date2').on('change changeDate', function(){
            var val = $(this).val();
            if(val && moment(val, default_date_format).isValid())
            {
                let year = moment(val, default_date_format).year();
                if(year.toString().length == 1 || year.toString().length == 2) {
                    year = moment(year, 'YY').format('YYYY');
                }
                var newDate = moment(val, default_date_format).set('year', year).format(default_date_format);
                $('.to_date1,.to_date2').val(newDate);
                $('.to_date1,.to_date2').datepicker('update');
                $('.to_date1,.to_date2').siblings('.date_parse').val(moment(val, default_date_format).set('year', year).format('YYYY-MM-DD'));
            }else {
                $('.to_date1,.to_date2').siblings('.date_parse').val('');
            }
        });
        
    });

 </script> 


@stop

@section('styles')

<link href="{{ asset('/css/bootstrap-datetimepicker4.17.37.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
<style type="text/css">
  
        td.details-control {
            background: url('{{asset("img/icons/details_open.png")}}') no-repeat center center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            background: url('{{asset("img/icons/details_close.png")}}') no-repeat center center;
        }
</style>

@stop