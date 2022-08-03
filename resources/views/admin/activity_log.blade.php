@extends('layouts.admin.admin_lte')

@section('content')

 <div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Activity Log</div>     
      </a>
      
  </div>
  
  <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
            @include('layouts.admin.partials.lte_alerts')

           
            <div class="box-body">


                <div class="form-box-styled">
                                           
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>
                                {{Form::select('user_id',$users,'',['class'=>'form-control','id'=>'user_id','placeholder'=>'Filter User'])}}
                               
                            </div>
                            <span class="help-block">Users</span>
                        </div>

                          <div class="col-sm-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                                </div>
                                {{Form::select('action_filter',['Payment Creation'=>'Payment Creation','Merchant Creation'=>'Merchant Creation','Merchant Edit'=>'Merchant Edit','Payment Delete'=>'Payment Delete','Login'=>'Login','Investor Creation'=>'Investor Creation','Investor Edit'=>'Investor Edit'],'',['class'=>'form-control','id'=>'action_filter','placeholder'=>'Action Filter'])}}
                               
                            </div>
                            <span class="help-block">Action Filter</span>
                        </div>
                         <div class="col-sm-12">

                          <div class="btn-wrap btn-right">
                            <div class="btn-box">
                              <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter">

                            </div>
                          </div>

                       </div>



                     </div>                                   
                    </div>

               



                 <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
             
                    <div class="table-responsive">
                     {!! $tableBuilder->table(['class' => 'table table-bordered '],true) !!}
                    </div>
              
            </div>
                

               

             </div>
      </div>
         
 </div>    

@stop

@section('scripts')

   

    {!! $tableBuilder->scripts() !!}


    <script type="text/javascript">

         var table = window.LaravelDataTables["dataTableBuilder"];
      
       $(document).ready(function(){


  $(".js-user-placeholder").select2({
        placeholder: "Select a Users"
});

   $('#date_filter').click(function (e) {
            e.preventDefault();
            table.draw();

          });

 });



    </script>
        
@stop

@section('styles')
     <link href="{{ asset('/css/optimized/admin_user.css?ver=5') }}" rel="stylesheet" type="text/css" />
     <link href="{{ asset('/css/optimized/Payment_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop