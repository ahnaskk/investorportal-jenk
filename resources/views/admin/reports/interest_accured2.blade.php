@extends('layouts.admin.admin_lte')

@section('content')

    <div class="box">
        


            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">

                <div class="row">
                    <div class="col-sm-12 grid table-responsive">

                            {!! $tableBuilder->table(['class' => 'table table-bordered investorReport'], true) !!}
                        
                        <div class="blockCust pull-right" style="padding-bottom: 15px">

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
  <script src="{{ asset ('/js/updated/moment.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset ('/js/updated/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>          

    {!! $tableBuilder->scripts() !!}

    
    
     




@stop
@section('styles')
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
