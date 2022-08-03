@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_title}}</div>     
      </a>
  </div>
{{ Breadcrumbs::render('admin::merchants-statements-create') }}
<div class="col-md-12">
<div class="box">
  <div class="box-head">
    @include('layouts.admin.partials.lte_alerts')
  </div>
    <div class="box-body"> 
    	 <div class="loadering text-center" style="display:none;">
         <div class="loader"></div><br>
         <h5 class="alert alert-warning"><b> Statement generating now......</b></h5>
      </div>
      <div class="pay-rep">
            <div class="filter-group-wrap" >
                <div class="serch-bar">
                        {{Form::open(['route'=>'admin::pdf_for_investors','id'=>'statement_generation'])}}
                    <div class="row">
                        <div class="col-md-4">
                            <span class="help-block">To Date</span>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input class="form-control to_date1 datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="type" autocomplete="off" value=""/>
                                <input type="hidden" name="date_end" id="date_end" class="date_parse">
                            </div>
                            </div>
                        
                        <div class="col-md-4">
                            <span class="help-block">Merchants<span class="validate_star">*</span></span>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                                </div>                             
                                {!! Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','multiple','id'=>'merchants']) !!}
                            </div>
                        </div>
                    </div>
            <div class="row">
               <div class="col-md-12 btn-wrap btn-right">
                  <div class="btn-box">
                  <input type="button" value="Generate PDF" class="btn btn-primary" id="data_filter" name="data_filter">
                  </div>
                </div>
            </div>
           {{Form::close()}}
            </div>
        </div>
          <div class="row">
          	 <div class="col-md-12">
               <span id="preview"> 
             </span>
          </div> 
      </div>	
 </div>
    <!-- /.box-body -->
</div>
</div>
@stop
@section('scripts')
<script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>
 <script>
    $(document).ready(function () {
        $('#statement_generation').validate({
                errorClass: 'errors_msg1',
                rules: {
                    'merchants[]': {
                    required: true
                    },
                }
            });
    	var URL_pdfGenerationAction = "{{ URL::to('admin/generate_pdf_for_merchants') }}";
        $('#data_filter').on('click',function() {
            if ($('#statement_generation').valid()) {
            var merchants=$('#merchants').val();
            if(merchants)
            { 
                $(".loadering").css("display", "block");
            }
            var endDate=$('#date_end').val();
            $.ajax({
                type:'POST',
                data: {'merchants':merchants, 'endDate':endDate, '_token': _token},
                url:URL_pdfGenerationAction,
                success:function(data)
                {  
                    console.log(data);
                        if (data.status == 1) {
                        $(".loadering").css("display", "none");
                        $('.box-head').html('<div class="alert alert-info col-ssm-12" >' + data.msg + '</div>');

                        }
                        else
                        {
                            $('.box-head').html('<div class="alert alert-danger col-ssm-12" >' + data.msg + '</div>');
                        }
                }

                });
            } else {
                return false;
             }
             
    
        });

    });

    // function getMerchant(status)
    // {
    //     var URL_selectMerchant = "{{ URL::to('admin/merchants/selectMerchants') }}";

    //     $('#merchants').empty();

    //     var merchants = [];

    //     if(status)
    //     {
    //         $.ajax({
    //             type: "POST",
    //             url: URL_selectMerchant,
    //             data:{'_token': _token,'status':status},
    //             beforeSend: function() {
    //                 $("#merchants").addClass("loader");
    //             },
    //             success: function(data){

    //                 if(data.status==1)
    //                 {
    //                     var result=data.merchants;
    //                     $('#merchants').append('<option value=""></option>');
    //                     $.each(data.merchants, function(i, d) {
    //                         $('#merchants').append('<option value="' + i + '" selected="selected">'+ d + '</option>');
                            
    //                     });

    //                 }
            
    //             }
    //             });
    //     }

    // }     //


 </script>
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
@stop
@section('styles')
<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />
<style type="text/css">
.loader{border:16px solid #f3f3f3;border-radius:50%;border-top:16px solid #3498db;width:50px;height:50px;-webkit-animation:spin 2s linear infinite;animation:spin 2s linear infinite;}
</style>
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
@stop