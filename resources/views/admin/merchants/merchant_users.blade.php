@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>     
      </a>
      
  </div>

<div class="col-md-12">
<div class="box">
    <div class="box-head ">
        @include('layouts.admin.partials.lte_alerts')

    </div>
    <div class="box-body">
        <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

       

            <div class="row">
                <div class="col-sm-10"></div>
                @if(!Auth::user()->hasRole('viewer'))
                @if($page_title=='All Modules')
                 <div class="col-sm-2" style="padding-bottom:15px">
                   @if(@Permissions::isAllow('Modules','Create')) 
                    <a href="{{route('admin::roles::create-module')}}" class="btn btn-primary admin-btn">Create Module </a>
                    @endif
                </div>
                @else
                <div class="col-sm-2" style="padding-bottom:15px">
                    @if(@Permissions::isAllow('Users','Create')) 
                    <a href="{{route('admin::roles::create-user')}}" class="btn btn-primary admin-btn">Create User </a>
                      @endif
                </div>
                 @endif
                @endif
            </div>

              


            <div class="row">
                <div class="col-sm-12">
                    {!! $tableBuilder->table(['class' => 'table table-bordered','id'=>'editor'],true) !!}
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
</div>

@stop

@section('scripts')



{!! $tableBuilder->scripts() !!}

      <script>


        
           $(document).ready(function() {

             var table = $('#editor').DataTable();
                
               window.onpopstate = function() {
                 // alert("clicked back button");
                 
                   var info = table.page.info(); 
                   var pageNo=info.page;
                    if(pageNo==0)
                    {
                       pageNo=0; 
                    }
                    else
                    {
                       pageNo=pageNo-1;
                    }
                  
                // alert(pageNo);
                   table.page(pageNo).draw(false);
                 
                  // location.reload();
                 }; history.pushState({}, '');


                  $('#date_filter').click(function (e) {
            e.preventDefault();
            table.draw();

          });
                 
         });   

       </script>

@stop

@section('styles')
  <link href="{{ asset('/css/optimized/editor.css?ver=5') }}" rel="stylesheet" type="text/css" />

  <link href="{{ asset('/css/optimized/merchants.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop