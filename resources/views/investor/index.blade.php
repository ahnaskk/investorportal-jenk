@extends('layouts.branchmanager.admin_lte')

@section('content')
    <div class="box">
        <div class="box-head ">
            @include('layouts.branchmanager.partials.lte_alerts')

        </div>
        <div class="box-body">
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                <div class="row">
                    <div class="col-sm-10"></div>
                    <div class="col-sm-2" style="padding-bottom:15px">

                        <a href="{{route('branch::marketplace::create')}}" class="btn btn-primary">DASHBOARD</a>

                    </div>
                </div>
               <div class="container">
    <div class="row">
     <h2></h2>
    <ul class="ds-btn">
        

        <li>
             <a class="btn btn-lg btn-danger" href="{{URL::to('investor/marketplace')}}">
         <i class="glyphicon glyphicon-shop pull-left"></i><span>Marketplace<br><small>List</small></span></a> 
            
        </li>


    </ul>
    
    </div>
</div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
@stop

@section('scripts')

    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}" type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}" type="text/javascript"></script>


@stop

@section('styles')
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">

    <style type="text/css">
        
.ds-btn li{ list-style:none; float:left; padding:10px; }
.ds-btn li a span{padding-left:15px;padding-right:5px;width:100%;display:inline-block; text-align:left;}
.ds-btn li a span small{width:100%; display:inline-block; text-align:left;}


    </style>
@stop