@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{ $page_title }}</div>     
      </a>
      
</div>
{{ Breadcrumbs::render('admin::merchants-statements') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <div class="box-body"> 
            <div class="form-box-styled" >
                <div class="serch-bar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input class="form-control from_date1 datepicker" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off" value=""/>
                                <input type="hidden" name="date_start" id="date_start" class="date_parse">
                            </div>
                            <span class="help-block">From Date</span>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input class="form-control to_date1 datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" autocomplete="off" value=""/>
                                <input type="hidden" name="date_end" id="date_end" class="date_parse">
                            </div>
                            <span class="help-block">To Date</span>
                        </div>
                    </div>                        
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                </div>
                                {!! Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','multiple','id'=>'merchants']) !!}
                            </div>
                            <span class="help-block">Merchants</span>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-box " style="margin-bottom: 25px;">
                                <div class="input-group">
                                    <input type="button" value="Apply Filter" class="btn btn-success" id="date_filter"
                                        name="student_dob">
                                </div>
                            </div> 
                        </div>
                    </div>                             
                </div>
            </div>
                    
           
                @if(@Permissions::isAllow('Merchants','Delete')) 

                <div class="btn-box " style="margin-bottom: 25px;">
                    <div class="input-group">
                        <a href="#" class="btn  btn-danger delete_multi delete-mul2" id="delete_multi_statment" ><i class="glyphicon glyphicon-trash"></i> Delete Selected</a>
                    </div>
                </div>
                
                @endif

                {{-- <div class="btn-box " style="margin-bottom: 25px;">
                <div class="input-group">
                        <a href="#" class="btn  btn-success multi_mail_send" id="multi_mail_send" ><i class="fa fa-send-o"></i> Mail Send</a>
                    </div>
                </div>     --}}
                    
               
            <div class="clearfix"></div>
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
            <div class="loadering" style="display:none;">
                    <div class="loader"></div><br>
                    <h5 class="alert alert-warning"><b>Selected records are being deleted. Please wait until the page refreshes automatically.</b></h5>
                </div>
                <div class="row">
                    <div class="col-sm-12 grid table-responsive">
                        {!! $tableBuilder->table(['class' => 'table table-bordered'], true); $tableBuilder->parameters([
              'drawCallback' => 'function(){recheck()}',]) !!}
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

<script type="text/javascript">

    function uncheckMain(){
var uncheck = 0;
 $('input:checkbox.checked_data').each(function () {
       if(!this.checked){
        uncheck = 1;
 $('#checked_statement').prop('checked', false);
       }
  });
 if(uncheck==0){
  $('#checked_statement').prop('checked', true);
 }
}
function recheck(){
  if($("#checked_statement").prop('checked')){
    $(".checked_data").prop('checked', true);
  }
}

var table = window.LaravelDataTables["dataTableBuilder"];
var URL_statementDelete = "{{ URL::to('admin/delete_statements_merchants') }}";
// var URL_sendMail= "{{ URL::to('admin/send_mail_to_investors') }}";
var delay = 100;
$(document).ready(function(){
  
    $('#date_filter').click(function (e) {
        e.preventDefault();
        table.draw();
    });

    $('#checked_statement').on('click',function()
    { 
        if($(this).is(':checked',true))  
            { 
                $(".checked_data").prop('checked', true); 
            } else {
                $('.checked_data').prop('checked', false);
            }
    }); 

    $('#multi_mail_send').on('click',function()
    {
        var el = this;
        var id_arr=[];

            if(confirm('Do you really want to Send mail to selected Investors'))
            {
                $('.checked_data:checked').each(function() {
                    id_arr.push($(this).val()); 
                });

                    if(id_arr.length >0)
                    { 
                        $.ajax({
                            type:'POST',
                            data: {'multi_id': id_arr, '_token': _token},
                            url:URL_sendMail,
                            success:function(data)
                            {
                                    if (data.status == 1) {

                                            $('.box-head').html('<div class="alert alert-info col-ssm-12" >' + data.msg + '</div>');


                                    }
                                    else
                                    {
                                    } 
                            }
                        });

                    }
                    else
                    {
                            alert('Please select rows');
                    }


            }
        

    });

    $('#delete_multi_statment').on('click',function() {
    // alert('clicked');
        var el = this;
        var id_arr=[];
        if(confirm('Do you really want to delete selected items?'))
        {
        $('.checked_data:checked').each(function() {
            id_arr.push($(this).val()); 
        });
        //console.log(id);
        if(id_arr.length >0)
        {
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
</script>
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>


@stop
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
<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('/css/libs-font-awesome.min.css') }}">

@stop
