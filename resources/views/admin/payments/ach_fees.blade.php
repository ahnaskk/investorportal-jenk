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
@stop
@section('content')
<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">{{ $page_title }}</div>
  </a>
</div>
{{ Breadcrumbs::render('admin::payments::ach-fees.index') }}
<div class="col-md-12">
  <div class="box">
    <div class="box-head ">
      @include('layouts.admin.partials.lte_alerts')
    </div>
    <div class="box-body">
      <div class="form-box-styled" >
        <div class="serch-bar">
          <form action="" method="POST" id="ach_fees">
            @csrf
            <div class="row text-capitalize">
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                  </div>
                  {{Form::text('from_date1',date('Y-m-01'),['class'=>'form-control datepicker table_change','id'=>'from_date1','placeholder'=>\FFM::defaultDateFormat('format'), 'autocomplete'=>'off'])}}
                  <input type="hidden" name="from_date" value="{{ date('Y-m-01') }}" id="from_date" class="date_parse">
                  <span id="invalid-date_start"></span>
                </div>
                <span class="help-block">{{Form::label('from_date','From Date')}}</span>
              </div>
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                  </div>
                  {{Form::text('to_date1',date('Y-m-d',strtotime('+1 day')),['class'=>'form-control datepicker table_change','id'=>'to_date1','placeholder'=>\FFM::defaultDateFormat('format'), 'autocomplete'=>'off'])}}
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
                  {{Form::select('status',$statuses,'',['class'=>'form-control table_change select2_class','id'=>'status', 'placeholder'=>'Select Status'])}}
                </div>
                <span class="help-block">{{Form::label('status','status')}}</span>
              </div>
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <div class="input-group-text">
                    <span class="glyphicon glyphicon-user" aria-hidden=" true"></span>
                  </div>
                  {{Form::select('type',$fee_types,'',['class'=>'form-control table_change select2_class','placeholder'=>'Select Type','id'=>'type'])}}
                </div>
                <span class="help-block">{{Form::label('type','type')}}</span>
              </div>
              <div class="col-md-4 col-sm-12">
                <div class="input-group">
                  <input type="submit"  value="Download" formaction="{{ route('admin::payments::ach-fees.export') }}" class="btn btn-primary" >
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
            {!! $tableBuilder->table(['class' => 'table table-bordered'], true);$tableBuilder->parameters(['stateSave' => true]) !!}
          </div>
      </div>
    </div>
  </div>
</div>
@stop
@section('scripts')
{!! $tableBuilder->scripts() !!}
<script type="text/javascript">
var form_fields = "#from_date1,#to_date1,#status,#merchants_id,#type"
function PreserveFormVals(fields){
  this.fields = fields
  this.state = {}
  this.saveState = function(){
    localStorage.setItem('form_state',JSON.stringify(this.state))
  }
  this.parseState = function(){
    this.state = JSON.parse(localStorage.getItem('form_state'))
    let state = this.state
    for(const field in state){
      $("#"+field).val(this.state[field]).trigger("change")
    }
  } 
  $(fields).change(function(e){
    this.state[e.target.id] = $(e.target).val()
    this.saveState();
  }.bind(this))
}
var preserve_form = new PreserveFormVals(form_fields);
var table = window.LaravelDataTables["dataTableBuilder"];
if(window.performance.navigation.type == 2){
  $(document).ready(function(){
    preserve_form.parseState();
    table.draw();
  })
  
}
$(document).ready(function(){
    $('.table_change').change(function() {
      table.draw();
    });
    
})
</script>
@stop
