@extends('layouts.admin.admin_lte')
@section('styles')
<style type="text/css">
    li.breadcrumb-item.active{
    color: #2b1871!important;
    }
    li.breadcrumb-item a{
    color: #6B778C;
    }
    .select2-selection__rendered {
      display: inline !important;
    }
    .select2-search--inline {
      float: none !important;
    }
</style>
<link href="{{ asset('/css/optimized/Transaction_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ url('/vendor/sweetalert2/sweetalert2.min.css') }}">
@stop
@section('content')
<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">{{ $page_title }}</div>
  </a>
</div>
{{ Breadcrumbs::render('admin::payments::ach-requests.index') }}
<div class="col-md-12">
  <div class="box">
    <div class="box-head ">
      @include('layouts.admin.partials.lte_alerts')
    </div>
    <div class="box-body">
      <div class="form-box-styled" >
        <div class="serch-bar">
          <form action="{{ route('admin::payments::ach-requests.export') }}" method="POST">
            @csrf
            <div class="row text-capitalize px-2">
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                  </div>
                  {{Form::text('from_date1',date('Y-m-01'),['class'=>'form-control datepicker table_change','id'=>'from_date1','placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete'=>'off'])}}
                  <input type="hidden" name="from_date" value="{{ date('Y-m-01') }}" id="from_date" class="date_parse">
                  <span id="invalid-date_start"/>
                </div>
                <span class="help-block">{{Form::label('from_date','From Date')}}</span>
              </div>
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                  </div>
                  {{Form::text('to_date1',date('Y-m-d',strtotime('+1 day')),['class'=>'form-control table_change datepicker','id'=>'to_date1','placeholder' => \FFM::defaultDateFormat('format'), 'autocomplete'=>'off'])}}
                  <input type="hidden" name="to_date" value="{{ date('Y-m-d',strtotime('+1 day')) }}" id="to_date" class="date_parse">
                </div>
                <span class="help-block">{{Form::label('to_date','To Date')}}</span>
              </div>
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                  </div>
                  <select class="form-control table_change" id="merchants_id" name="merchants_id[]" multiple >
                    @foreach($merchants_id as $key=>$merchant_name)
                    <option value="{{ $key }}" >{{ $merchant_name }}</option>
                    @endforeach
                </select>
                </div>
                <span class="help-block">{{Form::label('merchants_id','Merchants')}}</span>
              </div>
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                  </div>
                  {{Form::select('ach_status',$statuses,'0',['class'=>'form-control table_change select2_class','id'=>'ach_status', 'placeholder'=>'Select Status'])}}
                </div>
                <span class="help-block">{{Form::label('ach_status','ACH status')}}</span>
              </div>
              <div class="col-md-4 col-sm-12">
                <div class="form-group">
                  <div class="input-group check-box-wrap">
                          <div class="input-group-text">
                              <label class="chc">
                                  {{ Form::checkbox('order_id',1,false,['id'=>'order_id','class'=>'table_change', 'checked']) }}
                                  <span class="checkmark chek-m"></span>
                                  <span class="chc-value">Check this</span>
                              </label>
                          </div>
                      </div>
                      <span class="help-block">{{ Form::label('order_id', ucfirst('Show only items with Order ID')) }}</span>
                  </div>

              </div>
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <input type="submit"  value="Download" class="btn btn-primary" >
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="dataTables_wrapper form-inline dt-bootstrap">
        <div class="loadering" style="display:none;">
          <div class="loader"></div><br>
        </div>
          <div class="table-responsive grid text-capitalize">
            {!! $tableBuilder->table(['class' => 'table table-bordered'], true) !!}
          </div>
        @if(config('app.env')=='local')
         <div class="row" style="text-align: center;">
          <form action="{{ route('admin::payments::ach-requests.status') }}" method="POST"  onsubmit="return checkAll(this)">
            @csrf
            <div class="input-group justify-content-center">
              <input type="submit" value="Check Status of All Processing ACH" class="btn btn-success" id="checkAllButton">
            </div>
          </form>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@stop
@section('scripts')
{!! $tableBuilder->scripts() !!}
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="text/javascript">
var table = window.LaravelDataTables["dataTableBuilder"];
$('.table_change').change(function() {
  table.draw();
});
$(document).on('click','.check_status',function() {
  if(confirm('Do you really want to check single ACH status?'))
  {
    var ach_id = $(this).attr('ach_id');
    var url_singe_ach = "{{ url('admin/payment/ach-requests') }}/" + ach_id;
    $.ajax({
      type: "GET",
      url: url_singe_ach,
      success: function(response){
        if(response.status == 'Success'){
          Swal.fire( response.status, 'Response - '+response.message, response.status.toLowerCase());
        }else{
          Swal.fire( response.status+'!', 'Response - '+response.message, 'warning' );
        }
        table.draw();
      },
      error: function(xhr, statusText) {
        Swal.fire("Error", statusText, 'error' ) }
    })
  }
});

function checkAll(form) {
  if(confirm('Do you really want to check all the pending ACH status?')) {
    document.getElementById('checkAllButton').disabled = true;
    return true
  }
  return false
}

</script>
@stop
