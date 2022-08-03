@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Duplicate DB</h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Duplicate DB</div>     
      </a>
      
  </div>
<div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
            <div class="box-body">
             @include('layouts.admin.partials.lte_alerts')

        	  <div class="statusloadering" style="display:none;">
       
             <div class="loader"></div><br>
             <h5 class="alert alert-warning"><b>Generating duplicate database. Please wait until the page refreshes automatically.</b></h5>
      
            </div>


          <div class="form-box-styled">
            {{Form::open(['route'=>'admin::duplicate-db','method'=>'GET'])}}
              <div class="col-md-6">
                
                <?php $date=isset($_GET['date_start'])?$_GET['date_start']:''; ?>
                    <div class="input-group" data-date-format="{{\FFM::defaultDateFormat('format')}}">
                        <div class="input-group-text">
                            <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                        </div>
                        <input class="form-control datepicker" id="date_start1" autocomplete="off" name="date_start1" value="{{ $date }}" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                        <input type="hidden" name="date_start" class="date_parse" id="date_start" value="{{ $date }}">
                    </div>
                    <span class="help-block">date</span>

              </div>
              <div class="col-md-6">   
                {{Form::submit('filter',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                <a href="#" class="btn btn-danger pull-right" id="duplicate_db">Duplicate Database</a>

                <?php  $db=session('DB_DATABASE');
                $old_db=config('app.database'); 
                ?>

                @if($db)
                <a href="#" class="btn btn-success pull-right" id="reset_db">Reset Database</a>   

                @endif
              </div>
              {{Form::close()}} 
   </div>

  <table class="table table-striped">
  <thead class="thead-dark">
    
      <th scope="col">#</th>
      <th scope="col">DB</th>
      <th scope="col">Action</th>
    
   
  </thead>
  <tbody>
  <?php $i=1; ?>

  	@if($array)
  	@foreach($array as $data)


    <tr class="row1">
      <th scope="row">{{ $i++ }}</th>
      <td><a href="">{{ $data }} </a></td><td><a href="#" class="btn btn-primary change_db" id="change_db"  data-db='{{ $data }}'>Change Database</a>
        &nbsp;&nbsp;&nbsp;

        @if($data==$db)

        <img src="{{ asset('/images/greencheck.png') }}" width='25px' height='25px'>

        @endif

        @if(!$db)

        @if($data==$old_db)
       
        <img src="{{ asset('/images/greencheck.png') }}" width='25px' height='25px'>

        @endif

        @endif
    </td>
    </tr>

    @endforeach

    @endif
   
  </tbody>
</table>
</div>

</div>
</div>
@stop

@section('scripts')

<script type="text/javascript">

	 var URL_duplicateDb = "{{ URL::to('admin/duplicate-db/') }}";
	 var redirectUrl="{{ URL::to('admin/duplicate-db/') }}";
	 var URL_changeDB = "{{ URL::to('admin/change-db/') }}";
   var URL_resetDB="{{ URL::to('admin/reset-db/') }}";

	 $(document).ready(function () {

	 	  $('#duplicate_db').on('click',function()
           {
                $(".statusloadering").css("display", "block");
             	      $.ajax({
  			             type:'POST',
  			             data: {'_token': _token},
  			             url:URL_duplicateDb,
  			             success:function(data)
  			             {
  			             	  if(data.status==1)
  			             	   { 
                            $(".statusloadering").css("display", "none");
                             window.location = redirectUrl;
                         }
  			             }
  			             
  			          }); 	
           	   
           });

    $('#reset_db').on('click',function()
       {
               $.ajax({
                   type:'GET',
                   data: {'_token': _token},
                   url:URL_resetDB,
                   success:function(data)
                   {
                     if(data.status==1)
                     {
                          window.location = redirectUrl;


                     }

                           

                   }
                   
                }); 

      });


          $('.change_db').on('click',function()
          {
                var row = $(this).closest('.row1');
                var database=row.find('.change_db').data('db');

                    $.ajax({
			             type:'POST',
			             data: {'_token': _token,'db':database},
			             url:URL_changeDB,
			             success:function(data)
			             {
                       if(data.status==1)
                       {
                           
                              window.location = redirectUrl; 


                       }
			             	

                           

			             }
			             
			          }); 

               
          });





	}); 	
	
</script>

@stop

@section('styles')
<link href="{{ asset('/css/optimized/Change_Merchant_Status.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop
