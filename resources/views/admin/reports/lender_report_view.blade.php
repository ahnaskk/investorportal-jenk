@extends('layouts.admin.admin_lte')
@section('styles')
<link href="{{ asset('/css/optimized/Lender_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/Default_Rate_Report.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
@section('content')
<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>Lender Report</h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Lender Report</div>
  </a>
</div>
<div class="col-md-12">
  <div class="box">
    <div class="box-body">
      <div class="form-box-styled" >
        <div class="row">
          <div class="col-md-6 report_rate">
            <div class="input-group">
              <div class="input-group-text">
                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
              </div>
              {{Form::select('industry[]',$industries,'',['class'=>'form-control js-industry-placeholder-multiple','id'=>'industry','multiple'=>'multiple'])}}
            </div>
            <span class="help-block">Industry</span>
          </div>
          <div class="col-md-6 report_rate">
            <div class="input-group">
              <div class="input-group-text">
                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
              </div>
              {{Form::select('lenders[]',$lenders,'',['class'=>'form-control js-lender-placeholder-multiple','id'=>'lenders','multiple'=>'multiple'])}}
            </div>
            <span class="help-block">Lenders</span>
          </div>
          <div class="col-md-4 report_rate">
            <div class="input-group">
              <div class="input-group-text">
                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
              </div>
              {{Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','id'=>'merchants','multiple'=>'multiple'])}}
            </div>
            <span class="help-block">Merchants</span>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4 report_rate">
            <div class="input-group">
              <div class="input-group-text">
                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
              </div>
              {{Form::text('from_date1',null,['class'=>'form-control datepicker','id'=>'from_date1', 'autocomplete'=>'off'])}}
              <input type="hidden" name="from_date" id="from_date" class="date_parse">
            </div>
            <span class="help-block">From date </span>
          </div>
          <div class="col-md-4 report_rate">
            <div class="input-group">
              <div class="input-group-text">
                <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
              </div>
              {{Form::text('to_date1',null,['class'=>'form-control datepicker','id'=>'to_date1', 'autocomplete'=>'off'])}}
              <input type="hidden" name="to_date" id="to_date" class="date_parse">
            </div>
            <span class="help-block">To date </span>
          </div>
          <div class="col-sm-12">
            <div class="btn-wrap btn-right">
              <div class="btn-box ">
                <input type="button" value="Apply Filter" class="btn btn-success" id="apply"
                name="student_dob">
                @if(@Permissions::isAllow('Lender Delinquent','Download'))
                {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
        <div class="row">
          <div class="col-sm-12 grid table-responsive">
            <table  class="table table-bordered" id="DataTable">
              <thead>
                <tr>
                  <th  title="No">No</th>
                  <th  title="Lender">Lender</th>
                  <th  title="Invested Amount">Invested Amount</th>
                  <th  title="Share %">Share %</th>
                  <th  title="Default Invested">Default Invested</th>
                  <th  title="CTD profit">CTD profit</th>
                  <th  title="Default (%)">Default (%)</th>
                </tr>
              </thead>
              <tfoot>
                <tr>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop
@section('scripts')
<script src="{{ asset('/js/custom/report.js') }}"></script>
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
<script type="text/javascript">
var DataTable = $('#DataTable').DataTable({
  "processing": true,
  "serverSide": true,
  "fixedHeader": true,
  "lengthMenu": [ [50, 100, 200, 1000], [50, 100, 200, 1000, ] ],
  "ajax": {
    "url": "<?= url('lenderReportView') ?>",
    "dataType": "json",
    "type": "POST",
    data: function(d) {
      d._token    = "<?= csrf_token() ?>";
      d.industry  = $('#industry').val();
      d.lenders   = $('#lenders').val();
      d.merchants = $('#merchants').val();
      d.from_date = $('#from_date').val();
      d.to_date   = $('#to_date').val();
    },
  },
  dom: 'Bfrtip',
  buttons: [
    'colvis',
    'pageLength',
  ],
  "columns": [
    { "data": "no"},
    { "data": "Lender"},
    { "data": "invested_amount"},
    { "data": "share_percentage"},
    { "data": "default_invested"},
    { "data": "ctd_profit"},
    { "data": "default_percentage"},
  ],
});
$('#form_filter').click(function(){
  DataTable.draw();
});
</script>
@stop
