@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Lender Action</div>     
      </a>
      
  </div>
{{ Breadcrumbs::render('lenderSettings') }}
  <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

            @include('layouts.admin.partials.lte_alerts')
              

                {!! Form::open(['route'=>'admin::lender-activation', 'method'=>'POST','id'=>'create_status_form']) !!}
          
            <div class="box-body col-md-12">
                @if(count($lenders)>0) 
                 

                <?php
                    $i=0;$no=1;
                    ?>
                     <table class="leander-act" style="width: 100%; margin:0 auto;">

                         <thead>
                         <tr style="border-bottom: 2px solid #0605053d;">
                         <th style="width: 11%;height:100%;">#</th>
                         <th>Lender</th>
                         <th>Action</th>
                         </tr>
                        </thead>

                     @foreach($lenders as $len)
                        
                           {!! Form::hidden($i.'_lender_id',$len['id'],['class'=>'form-control','required','id'=>$i.'_lender_id']) !!}

                       
                        <tbody>
                        <tr>
                        <td>{{$no}}</td>
                        <td class="leand-name">{!! Form::text($i.'_lender_name',$len['name'],['class'=>'form-control name-inv','readonly'=>  true,'required']) !!} </td>
                        
                        <td style="width: 17%;text-align: center;">
                            @if($len['active_status']==1)
                            <input checked data-toggle="toggle"  type="checkbox" data-on="Enable" data-off="Disable" data-onstyle="success" data-offstyle="danger" name='{{$i}}_row_status' id='{{$i}}_row_status' class="enable_disable">
                            @else
                            <input data-toggle="toggle"  type="checkbox" data-on="Enable" data-off="Disable" data-onstyle="success" data-offstyle="danger" name='{{$i}}_row_status' id='{{$i}}_row_status' class="enable_disable">
                            @endif
                            
                        </td>
                        
                        
                        </tr>
                      </tbody>



                       
 <?php $i++;$no++;?>
 @endforeach
  </table>
   </div>
            
        
                <!--<div class="box-footer" style="margin-left: 150px;">
                    {!! Form::submit('Submit',['class'=>'btn btn-primary cre-cub-btn sub-intr']) !!}
                </div>-->
           
@else
<h3>No Investors Found!</h3>
@endif
            {!! Form::close() !!}
        </div>
        <!-- /.box -->


    </div>


@stop
@section('scripts')
 
 <script src="{{ asset ("js/bootstrap-toggle.min.js") }}"></script>
 <script type="text/javascript">
     $(document).ready(function () {
     $(".enable_disable").change(function (evt) {
         var row_id = $(this).attr('id');
         var filed_arr = row_id.split("_row_status");
         var filed_no = filed_arr[0];
         var lender_id = $('#'+filed_no+'_lender_id').val();
         var status = $(this).is(':checked'); 
        $.ajax({
                     headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                
                
               
                    url: '/admin/enableDisableLender',
                    type: 'POST',
                    /* send the csrf-token and the input to the controller */
                    data: {lender_id:lender_id,status:status},
                   
                    /* remind that 'data' is the response of the AjaxController */
                    success: function (data) {                       
                      
                    }
                }); 
     });
      });
 </script>
 @stop
 @section('styles')
<link href="{{ asset('/css/optimized/lender_settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset("css/bootstrap-toggle.min.css") }}" rel="stylesheet"/>
<link href="{{ asset('/css/bower_components/breadcrumbs/breadcrumb1.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
