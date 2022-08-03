@extends('layouts.investor.admin_lte')

@section('content')
<div class="box">
        <div class="box-body">
<div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12">

                        <div class="table-container" > 
                            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
                        </div>
                    
                    </div>
                </div>
            </div>
             </div>
            </div>
@stop
@section('scripts')
<script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}"
            type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}"
            type="text/javascript"></script>
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];

</script> 
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">
    
@stop