@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">{{$page_title}}</div>
  </a>
</div>
{{ Breadcrumbs::render('admin::merchants::index') }}
<div class="box">
  <div class="box-body">
    @include('layouts.admin.partials.lte_alerts')
    <div class="grid">
      <div class="filter-group-wrap">
        <div class="filter-group grid">
          {{Form::open(['route'=>'admin::reports::merchant-list-download','autocomplete'=>'on'])}}
          <div class="grid">
            <div class="row">
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Lenders</label>
                  <select name="lender_id[]" id="lender_id" multiple="multiple" class="js-lender-placeholder-multiple redraw">
                    <option value="0">All</option>
                    @foreach($lenders as $lender)
                    <option {{$lender_id==$lender->id?'selected':''}} value='{{$lender->id}}'>{{$lender->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              @if(!Auth::user()->hasRole(['company']))
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Company</label>
                  {{Form::select('owner',$companies,'',['class'=>'form-control js-company-placeholder redraw','id'=>'owner','placeholder'=>'Select Company'])}}
                </div>
              </div>
              @endif
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Investors</label>
                  <select name="user_id[]" id="user_id" multiple="multiple" class="js-investor-placeholder-multiple redraw">
                    @foreach($users as $key=> $user)
                    <option {{$user_id==$key?'selected':''}} value='{{$key}}'>{{$user}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Status</label>
                  <select name="status_id[]" id="status_id" class="js-status-placeholder-multiple redraw" multiple="multiple">
                    <option value="0">All</option>
                    @foreach($sub_statuses as $sub_status)
                    <option {{$status_id==$sub_status->id?'selected':''}} value='{{$sub_status->id}}'>{{$sub_status->name}} </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Late Payment</label>
                  <select name="late_payment" id="late_payment">
                    <option value="">Select</option>
                    <option value="30">30 Days</option>
                    <option value="60">60 Days</option>
                    <option value="90">90 Days</option>
                  </select>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Advance Type</label>
                  {!! Form::select('advance_type',['daily_ach'=>'Daily ACH','weekly_ach'=>'Weekly ACH','credit_card_split'=>'Credit Card Split','variable_ach'=>'Variable ACH','lock_box'=>'Lock Box','hybrid'=>'Hybrid'],'',['class'=>'form-control','placeholder'=>'Select Advanced Type','id'=>'advance_type']) !!}
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label>From Date</label>
                  <input class="form-control from_date1 datepicker" id="date_start1" name="date_start1" placeholder="{{ \FFM::defaultDateFormat('format') }}" autocomplete="off" type="text" value=""/>
                  <input type="hidden" class="date_parse" name="date_start" id="date_start" value="">
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label>To Date</label>
                  <input class="form-control to_date1 datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" autocomplete="off" type="text" value=""/>
                  <input type="hidden" class="date_parse" name="date_end" id="date_end" value="">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Payoff/Money Request</label>
                  <select name="request_m" id="request_m">
                    <option value="">Select</option>
                    <option value="pay_off">Payoff</option>
                    <option value="money_request">Money Request</option>
                  </select>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Sub status Flag</label>
                  {!! Form::select('sub_status_flag_id[]', $substatus_flags, '',['class'=>'form-control js-substatus-flag-placeholder-multiple', 'id'=>'substatus_flag','multiple'=>'multiple']) !!}
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Label</label>
                  {!! Form::select('label[]',$label,'',['class'=>'form-control js-label-placeholder','id'=>'label','multiple']) !!}
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Bank Account</label>
                  <select name="bank_account" id="bank_account">
                    <option value="">Select</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Payment Paused</label>
                  <select name="payment_pause" id="payment_pause">
                    <option value="">Select</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                  </select>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label>Mode of Payment</label>
                    {!! Form::select('mode_of_payment',$payment_methods,'',['placeholder'=>'Select','class'=>'form-control js-payment-method-placeholder','id'=>'mode_of_payment']) !!}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row clearfix">
        <div class="col-sm-12">
          <div class="select-wrap">
            <div class="row">
              <div class="col-sm-12">
                <div class="filter_change">
                  <label class="customcheck">Check For Merchant With Marketplace
                    @if($marketplace_status=="true")
                    <input  id="marketplace" name="market_place" type="checkbox" value="true" checked/> <span class="checkmark chek-mm"></span>
                    @else
                    <input  id="market_place" name="market_place" type="checkbox" value="true" /> <span class="checkmark chek-mm"></span>
                    @endif
                  </label>
                </div>
              </div>
              <div class="col-sm-12  marchent-pay" >
                <div class="row">
                  <div class="filter_change col-sm-3">
                    <label class="customcheck">Not Started
                      <input {{(isset($not_started)) ? $not_started=="true"?"checked":""  : ""}}  id="not_started" type="checkbox" name="not_started" >
                      <span class="checkmark checkmark1"></span>
                    </label>
                  </div>
                  <div class="filter_change col-sm-3">
                    <label class="customcheck">Paid Off
                      <input {{(isset($paid_off)) ? $paid_off=="true"?"checked":""  : ""}}  id="paid_off" type="checkbox" name="paid_off">
                      <span class="checkmark checkmark2"></span>
                    </label>
                  </div>
                  <div class="filter_change col-sm-3">
                    <label class="customcheck">Stop Payment
                      <input {{(isset($stop_payment)) ? $stop_payment=="true"?'checked':'444'  : '666'}}  id="stop_payment" type="checkbox" name="stop_payment">
                      <span class="checkmark checkmark3"></span>
                    </label>
                  </div>
                  <div class="filter_change col-sm-3">
                    <label class="customcheck">Over Payment
                      <input {{(isset($over_payment)) ? $over_payment=="true"?"checked":""  : ""}}  id="over_payment" type="checkbox" name="over_payment">
                      <span class="checkmark checkmark4"></span>
                    </label>
                  </div>
                  <div class="filter_change col-sm-3">
                    <label class="customcheck">Not invested
                      <input {{(isset($not_invested)) ? $not_invested=="true"?"checked":""  : ""}}  id="not_invested" type="checkbox" name="not_invested" >
                      <span class="checkmark checkmark5"></span>
                    </label>
                  </div>
                  <div class="filter_change col-md-12">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- <div class="btn-box grid">
        <div class="input-group col-md-4">
        </div>
      </div> -->
      <div class="row">
        <div class="col-md-12 top-btn-wrap btn-wrap btn-right">
          <div class="btn-box">
            <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter" name="date_filter">
            @if(@Permissions::isAllow('Merchants','Download'))
            {{Form::submit('download',['class'=>'btn btn-primary','id'=>'form_filter'])}}
            @endif
            @if(@Permissions::isAllow('Merchants','Create'))
            <a href="{{route('admin::merchants::create')}}" class="btn btn-warning">Add Merchant</a>
            @endif
          </div>
        </div>
      </div>
      {{Form::close()}}
      <div class="modal right fade myModal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title merchant_name" id="myModalLabel"></h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              @if(@Permissions::isAllow('Notes','Create'))
              <textarea class="form-control note_enter" rows="2" cols="1" name="note"></textarea>
              @endif
              <div class="merchant_notes">
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--    modal for notes -->
      <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
        <div class="row">
          <div class="col-sm-12">
            <div class="table-container" >
              {!! 
                $tableBuilder->table(['class' => 'table table-bordered','id' => 'dataTableBuilder'], true);
                $tableBuilder->parameters(['stateSave' => true])
              !!}
            </div>
          </div>
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
var URL_noteAdd = "{{ URL::to('admin/merchants/addNotes') }}";
  function PreserveFormVals(fields,submit){
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
    $('#'+submit).click(function(submit){
        this.fields.forEach(function(field){
            this.state[field] = $('#'+field).val()
            this.saveState();
        }.bind(this))
    }.bind(this))
  }
  var form_fields = [
    "lender_id","owner","user_id","status_id","late_payment","advance_type","date_start",
    "date_end","request_m","substatus_flag","label","bank_account","payment_pause",
    "mode_of_payment", "market_place", "not_started", "paid_off", "stop_payment", "over_payment", "not_invested"];
  var preserve_form = new PreserveFormVals(form_fields,
      'date_filter'
  );
  window.addEventListener( "pageshow", function ( event ) {
    var historyTraversal = event.persisted || ( typeof window.performance != "undefined" && window.performance.navigation.type === 2 );
    if ( historyTraversal ) {
      // Handle page restore.
      $(document).ready(function(e){
         preserve_form.parseState();
         table.draw();
      })
    }else {
      preserve_form.saveState();
    }
});
$(document).ready(function() {
  // $(".redraw").change(function(e){
  //   var table = window.LaravelDataTables["dataTableBuilder"];
  //   // table.draw()
  // })
  var URL_getInvestor = "{{ URL::to('admin/getCompanyWiseInvestors') }}";
  $('#owner').change(function(e) {
    var company=$('#owner').val();
    var investors = [];
    if(company) {
      $.ajax({
        type: 'POST',
        data: {'company':company, '_token': _token},
        url: URL_getInvestor,
        success: function (data) {
          var result=data.items;
          for(var i in result) {
            investors.push(result[i].id);
          }
          $('#user_id').attr('selected','selected').val(investors).trigger('change.select2');
        },
        error: function (data) {
          //alert('hi');
        }
      });
    } else {
      $('#user_id').attr('selected','selected').val('').trigger('change.select2');
    }
  });
  $("#unselect").click(function(e){
    $('#user_id').val('').trigger("change.select2");
  });
  $('#select_all').click(function() {
    $('#user_id option').prop('selected',true).trigger("change.select2");
  });
  /*
  ON late payment days select, automatically select stop payment check.
  */
  $('#late_payment').change(function () {
    if($(this).val()) {
      $('#stop_payment').prop('checked', true);
    }else {
      $('#stop_payment').prop('checked', false);
    }
  });
  $(".js-lender-placeholder-multiple").select2({
    placeholder: "Select Lender(s)"
  });
  $(".js-investor-placeholder-multiple").select2({
    placeholder: "Select Investor(s)"
  });
  $(".js-status-placeholder-multiple").select2({
    placeholder: "Select Status(es)"
  });
  $(".js-substatus-flag-placeholder-multiple").select2({
    placeholder: "Select Sub status(es)"
  });
  $('.note_enter').keypress(function (e) {




    var html='';
    if (e.which == 13) {
      var row = $(this).closest('.modal-content');
      var merchant_id=row.find('.merchant_id').val();
      var note= row.find('.note_enter').val();
      if(merchant_id && note) {

         $('.note_enter').hide();

setTimeout(function(){
 $('.note_enter').show();
}, 1000);


        $.ajax({
          type:'POST',
          data: {'merchant_id': merchant_id,'note':note,'_token': _token},
          url:URL_noteAdd,
          success:function(data) {
            if(data.status==1) {
              html+='<div class="col-sm-10"><div class="row"><div class="card card-info"><div class="card-header"><strong></strong><span class="text-muted">'+data.note+'</div>\
              <div class="card-body"> by <b> '+data.added_by+' </b><br></span> <small> '+data.created_at+'</small> </div></div></div></div>';
              $('.merchant_notes').prepend(html);
              $('.note_enter').val('');
            }
          }
        });
      }
      return false;
    }
  });
  window.onpopstate = function() {
    var table = $('#merchant').DataTable(
    );
    var info = table.page.info();
    var pageNo=info.page;
    if(pageNo==0) {
      pageNo=0;
    } else {
      pageNo=pageNo-1;
    }
    table.page(pageNo).draw(false);
  };
  window.history.pushState({}, '');
  $('#date_filter').click(function (e) {
    e.preventDefault();
    table.draw();
  });
  
});
var URL_mercahantNote = "{{ URL::to('admin/merchants/merchantNotes') }}";
function note(merchant_id) {
  $('.note_enter').val('');
  if(merchant_id) {
    var html='';
    var merchant_name='';
    $.ajax({
      type:'POST',
      data: {'merchant_id': merchant_id,'_token': _token},
      url:URL_mercahantNote,
      success:function(data) {
        html+="<input type='hidden' class='merchant_id' name='merchant_id' value='"+data.merchant_id+"'>";
        if(data.status==1) {
          merchant_name+=data.merchant_name;
          $('.merchant_name').html(merchant_name);
          $.each(data.result, function (i, val) {
            var date=val.created_at;
            html+='<div class="col-sm-10"><div class="row"><div class="card card-info"><div class="card-header"><strong></strong><span class="text-muted" style="word-break: break-all;">'+val.note+'</div>\
              <div class="card-body"> by <b> '+val.added_by+' </b><br></span> <small> '+val.created_at+'</small> </div></div></div></div>';



          });
          $('.merchant_notes').html(html);
        } else {
          merchant_name+=data.merchant_name;
          $('.merchant_name').html(merchant_name);
          html+=data.msg;
          $('.merchant_notes').html(html);
        }
      }
    });
  }
  $('.myModal').modal('show');
}
</script>
@stop
@section('styles')
<style type="text/css">
    .adminSelect .select2-hidden-accessible {
    display: none;
    }
    .breadcrumb {
        padding: 8px 15px;
        margin-bottom: 20px;
        list-style: none;
        background-color: #f5f5f5;
        border-radius: 4px;
    }
    .breadcrumb > li {
        display: inline-block;
    }
   li.breadcrumb-item a{
        color: #6B778C;
    }
    .breadcrumb > li + li::before {
        padding: 0 5px;
        color: #ccc;
        content: "/\00a0";
    }
    li.breadcrumb-item.active{
        color: #2b1871!important;
    }
    .select2-selection__rendered {
      display: inline !important;
    }
    .select2-search--inline {
      float: none !important;
    }
</style>
<link href="{{ asset('/css/optimized/merchants.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
