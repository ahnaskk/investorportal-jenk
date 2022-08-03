@extends('layouts.admin.admin_lte')

@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($title)?$title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Investor Type</div>     
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
                    <div class="col-sm-12" style="padding-bottom:15px">
                        <div class="grid">
                            <div class="">
                                <div class="col-sm-4">
                                    

                                Lenders
                                <select name="lendors" id="lendors" onchange="filter_change()">
                                    <option value="0">All</option>
                                    @foreach($lenders as $lender)

                                    <option {{$lender_id==$lender->id?'selected':''}} value='{{$lender->id}}'>{{$lender->name}}</option>
                                    @endforeach
                                </select>
                                </div>

                                <div class="col-sm-4">
                                Status
                                <select name="status" id="status" onchange="filter_change()">
                                    <option value="0">All</option>
                                    @foreach($sub_statuses as $sub_status)

                                    <option {{$status_id==$sub_status->id?'selected':''}} value='{{$sub_status->id}}'>{{$sub_status->name}} </option>
                                    @endforeach
                                </select>
                                </div>
                                  @hasrole('admin|lender|editor')

                                <div class="paymntGnrte" style="margin-top: 0;">
                                    <a href="{{route('admin::merchants::create')}}" class="btn btn-primary">Add Merchant</a>
                                </div>
                                  @endhasrole

                            </div>
                            
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                       <div class="grid table-responsive"> 
                        {!! $tableBuilder->table(['class' => 'table table-bordered '],true) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop

@section('scripts')


    <script src="{{ asset ('bower_components/datatables.net/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>

    <script src="{{ asset ('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>


        {!! $tableBuilder->scripts() !!}
    <script type="text/javascript">
        var table = window.LaravelDataTables["dataTableBuilder"];

function filter_change() {

//alert($('#lendors').val());

    (window.location = '?lender_id='+$('#lendors').val()+'&'+'status_id='+$('#status').val());



    // body...
}



    </script>

    
@stop

@section('styles')
  <link href="{{ asset('/css/optimized/merchant_details.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop