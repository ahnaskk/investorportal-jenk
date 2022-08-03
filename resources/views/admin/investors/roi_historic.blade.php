@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}} </div>
    </a>
</div>
{{ Breadcrumbs::render('Prefreturn',$investors) }}

<div class="col-md-12">
<div class="box-head">
        </div>
       
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">
        @if($action=="Create")
        {{Form::open(['route'=>['admin::investors::save-roi-rate','id'=>$user_id],'method'=>'POST','id'=>'investor_roi_rate_create_form'])}}
        @else
        {{Form::open(['route'=>['admin::investors::update-roi-rate','id'=>$id],'method'=>'POST','id'=>'investor_roi_rate_edit_form'])}}

        @endif
            
                <div class="box-body box-body-sm">
                   @include('layouts.admin.partials.lte_alerts')
                 <input type="hidden" name="user_id" id="user_id" value="{{$user_id}}">
                 <input type="hidden" name="table_id" id="table_id" value="{{isset($id) ? $id : ''}}">

                <div class="form-group">
                    <label for="exampleInputEmail1">From Date<span class="validate_star">*</span></label>
                    <div class="input-group">

                             <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input autocomplete="off" class="form-control datepicker" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" value="{{isset($roi_data)? $roi_data->from_date : old('date_start1')}}"/>
                                <input type="hidden" name="date_start" id="date_start" value="" class="date_parse">
                            </div>
                
                   </div>
                

                <div class="form-group">
                    <label for="type">To Date</label>
                    <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input autocomplete="off" class="form-control datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}"
                                       type="text" value="{{isset($roi_data)? $roi_data->to_date : old('date_end1')}}"/>
                                <input type="hidden" name="date_end" id="date_end" value="" class="date_parse">
                    </div>
                </div>

                <div class="form-group">
                    <label for="exampleInputEmail1">Pref Return<span class="validate_star">*</span></label>
                    {!! Form::select('roi_rate',$roi_rates,isset($roi_data)? number_format($roi_data->roi_rate,2) : old('roi_rate'),['class'=>'form-control','placeholder'=>'Select Pref Return','required','id'=> 'roi_rate']) !!}

                    <span id="invalid-inputMerchant" />
                </div>

                   
                  <div class="btn-wrap btn-right">
                  <div class="btn-box">


                  @if($action=="Create")
                  {!! Form::submit('Create',['class'=>'btn btn-success bran-mng-bt']) !!}
                  @else
                  {!! Form::submit('Update',['class'=>'btn btn-success bran-mng-bt']) !!}

                  @endif
                  <a href="{{URL::to('admin/investors/investor-pref-return')}}/{{$user_id}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-view"></i> View List</a>

                  
                 </div>

                </div>
                <!-- /.box-body -->


                </div>
            {!! Form::close() !!}
        </div>
        <!-- /.box -->
    </div>



@stop
@section('scripts')
<script type="text/javascript">
    $(document).ready( function () {
        let startDt = $('#date_start').val() && new Date($('#date_start').val());
            if(startDt){
                $('#date_end1').datepicker('setStartDate', startDt);
            }
            $('#date_start1').on('changeDate', function(selected){
                let endDateSelected = $('#date_end').val() && new Date($('#date_end').val());
                if($('#date_start').val() && new Date($('#date_start').val())){
                let minDate = new Date(selected.date.valueOf());
                if(endDateSelected && endDateSelected < minDate){
                $("#date_end1").datepicker('update', "");
                }
                $('#date_end1').datepicker('setStartDate', minDate);
                }else{
                $('#date_end1').datepicker('setStartDate', '');
                }
            });
    });

    $('#investor_roi_rate_create_form').validate({ // initialize the plugin
                // errorClass: 'errors',
                rules: {
                    date_start1: {
                        required: true
                    },
                    
                    roi_rate: {
                        required: true
                    },

                },
                messages: {
                    date_start1: {
                        required: "Enter Start Date"
                    },
                    date_end1: {
                        required: "Enter End Date"
                    },
                    roi_rate: {
                        required: "Enter Pref Return"
                    },
                    
                },
                submitHandler: function(form) {
                       var date_start = $('#date_start').val();
                       var date_end = $('#date_end').val();
                         $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: '/admin/investors/check-date-for-roi-rate',
                            type: 'POST',            
                            data: {user_id:$("#user_id").val(),date_start:date_start,date_end:date_end},
                            success: function (data) {  
                               if(data.status==0){
                               $('.box-head').html('<div class="alert alert-danger" >' + data.message + '<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>');
                               }else{
                                document.getElementById('investor_roi_rate_create_form').submit();

                               }
                            }
                        });
                }

            });


            $('#investor_roi_rate_edit_form').validate({ // initialize the plugin
                // errorClass: 'errors',
                rules: {
                    date_start1: {
                        required: true
                    },
                    roi_rate: {
                        required: true
                    },

                },
                messages: {
                    date_start1: {
                        required: "Enter Start Date"
                    },
                    date_end1: {
                        required: "Enter End Date"
                    },
                    roi_rate: {
                        required: "Enter Pref Return"
                    },
                    
                },
                submitHandler: function(form) {
                       var date_start = $('#date_start').val();
                       var date_end = $('#date_end').val();
                         $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: '/admin/investors/check-date-for-roi-rate',
                            type: 'POST',            
                            data: {user_id:$("#user_id").val(),date_start:date_start,date_end:date_end,tb_id:$('#table_id').val()},
                            success: function (data) {  
                               if(data.status==0){
                               $('.box-head').html('<div class="alert alert-danger" >' + data.message + '<button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button></div>');
                               }else{
                                document.getElementById('investor_roi_rate_edit_form').submit();

                               }
                            }
                        });
                }

            });

</script>
@stop
