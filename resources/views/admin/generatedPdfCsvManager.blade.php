@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
  <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
  <a href="#" class="help-link">
    <i class="fa fa-question-circle" aria-hidden="true"></i>
    <div class="tool-tip">Generated PDF/CSV Manager</div>
  </a>
</div>
{{ Breadcrumbs::render('admin::generated-pdf-csv') }}
<div class="col-md-12">
  <div class="box">
    <div class="box-head ">
      @include('layouts.admin.partials.lte_alerts')
    </div>
    <div class="box-body">
    <form action="" id="csvPdfFilterForm">
      <div class="form-filter" >
        <div class="form-filter-wrap">
          <div class="row">
            <div class="col-md-3">
              <div class="input-group">
                <div class="input-group-text">
                  <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                </div>
                <input class="form-control from_date1 datepicker" id="date_start1" name="date_start1" autocomplete="off" placeholder="{{ \FFM::defaultDateFormat('format') }}" type="text" value=""/>
                <input type="hidden" class="date_parse" name="date_start" id="date_start">
              </div>
              <span class="help-block">From Date</span>
            </div>
            <div class="col-md-3">
              <div class="input-group">
                <div class="input-group-text">
                  <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                </div>
                <input class="form-control to_date1 datepicker" autocomplete="off" id="date_end1" name="date_end1" placeholder="{{ \FFM::defaultDateFormat('format') }}" type="text" value=""/>
                <input type="hidden" class="date_parse" name="date_end" id="date_end">
              </div>
              <span class="help-block">To Date</span>
              <div class="errorTxt"></div>
            </div>
            <div class="col-md-6">
              <div class="input-group">
                <div class="input-group-text">
                  <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                </div>
                {{Form::select('investors[]',$investors,'',['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}
              </div>
              <span class="help-block">Investors</span>
            </div>
            
          </div>
        </div>
        <div class="btn-box " style="margin-bottom: 25px;">
              <div class="input-group">
                <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                name="student_dob">
              </div>
            </div>
      </div>
      </form>




      @if (!Auth::user()->hasRole('editor'))
      @if(@Permissions::isAllow('Generate PDF','Delete'))
      <div class="btn-box " style="margin-bottom: 25px;">
        <div class="input-group">
          <a href="#" class="btn  btn-danger delete_multi delete-mul2" id="delete_multi_statment" ><i class="glyphicon glyphicon-trash"></i> Delete Selected</a>
        </div>
      </div>
      @endif
      <div class="btn-box " style="margin-bottom: 25px;">
        <div class="input-group">
          <a href="#" class="btn  btn-success multi_mail_send" id="multi_mail_send" ><i class="fa fa-send-o"></i> Send Mail </a>
        </div>
      </div>
      @endif
      <div class="clearfix"></div>
      <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
        <div class="loadering" style="display:none;">
          <div class="loader"></div><br>
          <h5 class="alert alert-warning"><b>Selected records are being deleted. Please wait until the page refreshes automatically.</b></h5>
        </div>
        <div class="row">
          <div class="col-sm-12 grid table-responsive">
            {!! $tableBuilder->table(['class' => 'table table-bordered'], true); !!}
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
var URL_statementDelete = "{{ URL::to('admin/delete_statements') }}";
var URL_sendMail= "{{ URL::to('admin/send_mail_to_investors') }}";
var delay = 100;
var uncheck_flag_one = false;
var uncheck_flag_two = false;


$(document).on('click','.checked_data',function(){
  let checked = 0
  let total = 0
  $('.checked_data').each(function(){
    total ++ 
    if($(this).is(':checked')){
      checked ++
    }
  })
  if(checked < total ){
    $('#checked_statement').prop("checked",false)
  }else{
    $('#checked_statement').prop("checked",true)
  }
})
$(document).on('change','#investors',function(e){
  uncheck_flag_one = true
})
$(document).ready(function(){

  $('#date_filter').click(function (e) {
    e.preventDefault();
    uncheck_flag_two = true
    if($("#csvPdfFilterForm").valid())table.draw();
    uncheck_flag_one = uncheck_flag_two = false;
  });
  $('#checked_statement').on('click',function() {
    if($(this).is(':checked',true)) {
      $(".checked_data").prop('checked', true);
    } else {
      $('.checked_data').prop('checked', false);
    }
  });
  $('#multi_mail_send').on('click',function() {
    var el = this;
    var id_arr=[];
    if(confirm('Do you really want to Send mail to selected Investors')) {
      $('.checked_data:checked').each(function() {
        id_arr.push($(this).val());
      });
      if(id_arr.length >0) {
        $.ajax({
          type:'POST',
          data: {'multi_id': id_arr, '_token': _token},
          url:URL_sendMail,
          success:function(data) {
            if (data.status == 1) { $('.box-head').html('<div class="alert alert-info col-ssm-12" >' + data.msg + '</div>');
          }
          
        }
      });
    } else {
      alert('Please select rows');
    }
  }
});
$('#delete_multi_statment').on('click',function() {
  var el = this;
  var id_arr=[];
  if(confirm('Do you really want to delete selected items?')) {
    $('.checked_data:checked').each(function() {
      id_arr.push($(this).val());
    });
    if(id_arr.length >0) {
      $(".loadering").css("display", "block");
      $.ajax({
        type:'POST',
        data: {'multi_id': id_arr, '_token': _token},
        url:URL_statementDelete,
        success:function(data)
        {
          if (data.status == 1) {
            //  $.notify("Assigned Investor delete successfully", "success");
            setTimeout(function () {
              // window.location = redirectUrl;
              location.reload();
            }, delay);
          }
          else
          {
          }
        }
      });
    }
    else{
      alert('Please select rows');
    }
  }
});
  
});


jQuery.validator.addMethod("dateValidation",function(value, element, params) {
  if($('#date_start1').val() && value) {
    var startDate = moment($('#date_start1').val(), '{{\FFM::defaultDateFormat("format")}}').format("YYYY-MM-DD");
    var value = moment(value, "{{\FFM::defaultDateFormat('format')}}").format('YYYY-MM-DD');
    return Date.parse(startDate) <= Date.parse(value) || value == "" && startDate=="";
  }else {
    return true;
  }
},'To Date must be greater than from date');


$("#csvPdfFilterForm").validate({
  rules:{
    date_end1: { dateValidation: "#date_start1" }
  },
  errorElement : 'div',
  errorLabelContainer: '.errorTxt'
});


$(".js-investor-placeholder-multiple").select2({
    placeholder: "Select Investors"
});
</script>
@stop
@section('styles')
<style type="text/css">
      li.breadcrumb-item.active{
        color: #2b1871!important;
      }
      li.breadcrumb-item a{
        color: #6B778C;
      } 
      .form-filter .input-group {
        justify-content: flex-end;
      }
</style>
<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('/css/libs-font-awesome.min.css') }}">
@stop
