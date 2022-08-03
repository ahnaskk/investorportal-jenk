@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Generate Statement for Investors</div>     
      </a>

  </div>
{{ Breadcrumbs::render('admin::pdf_for_investors') }}
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

                    	    {{Form::open(['route'=>'admin::pdf_for_investors'])}}
                        <div class="row">

                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden=" true"></span>
                                </div>
                                <input autocomplete="off" class="form-control from_date1 datepicker" id="date_start1" name="date_start1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                <input type="hidden" class="date_parse" name="date_start" id="date_start">
                            </div>
                            <span class="help-block">From Date (Filter Based On Payment Added Date)</span>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                </div>
                                <input autocomplete="off" class="form-control to_date1 datepicker" id="date_end1" name="date_end1" placeholder="{{\FFM::defaultDateFormat('format')}}" type="text" value=""/>
                                <input type="hidden" class="date_parse" name="date_end" id="date_end">
                            </div>
                            <span class="help-block">To Date</span>
                           </div>
                      </div>   

                   <?php $investor=isset($_GET['id'])?[$_GET['id']]:''; ?>


                    <div class="row">



                      <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>

                             {!! Form::select('investor_type',$investor_types, old('investor_type'),['class'=>'form-control','placeholder'=>'Select Investor Type','id'=> 'inputInvestorType','onChange'=>"getInvestor(this.value);"]) !!}

                        </div>
                        <span class="help-block">Investor Type</span>
                    </div>


                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>

                             {{Form::select('investors[]',$investors,$investor,['class'=>'form-control js-investor-placeholder-multiple','id'=>'investors','multiple'=>'multiple'])}}

                        </div>
                        <span class="help-block">Investors <pre class="error">*</pre></span>
                    </div>

                     <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>

                              {!! Form::select('sub_status_id',$statuses,old('sub_status_id'),['class'=>'form-control js-status-placeholder-multiple','placeholder'=>'Select Status','onChange'=>"getMerchant(this.value);",'id'=>'sub_status_id']) !!}  

                        </div>
                        <span class="help-block">Merchant Status</span>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4">
                        <div class="input-group">
                            <div class="input-group-text">
                                <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            </div>                             
                            {!! Form::select('merchants[]',[],'',['class'=>'form-control js-merchant-placeholder-multiple','multiple','id'=>'merchants']) !!}
                        </div>
                        <span class="help-block">Merchants</span>
                    </div>

                    <!--  <div class="col-md-4">                    
                    <div class="input-group">
                      	{!! Form::select('groupby',$groupBy,'',['class'=>'form-control','placeholder'=>'Select Group By','id'=> 'groupby']) !!}
                        <span id="invalid-groupby_recurence" />

                    </div>
                      <span class="help-block">Group By</span>
                   </div> -->

                   	<div class="col-md-4">
	                   <div class="input-group">
	                      {!! Form::select('recurrence',$recurrence,'',['class'=>'form-control recurrence','id'=> 'recurrence','placeholder'=>'Select Recurrence']) !!}
	                         <span id="invalid-recurence" />
	                   </div>
	                   <span class="help-block">Recurrence</span>
                   	</div>

                    <div class="col-md-4">
                 		<div class="form-group">

	                        <div class="input-group check-box-wrap">
	                            <div class="input-group-text">
	                                <label class="chc">
	                                    <input title="Please go to 'Generated PDF/CSV' section from the menu to send email." type="checkbox" data-toggle="toggle" name="send_mail" id="send_mail" value="" data-onstyle="success">
	                                    <span class="checkmark chek-m"></span>
	                                    <span class="chc-value">Check this</span>
	                                </label>
	                            </div>
	                         </div>
	                         <span class="help-block">Mail Send </span>
			             </div>

                    </div>                  
                </div>
            </div>

            <div class="row">
               <div class="col-md-12 btn-wrap btn-right">
                  <div class="btn-box">
                  <input type="button" value="Generate Statement" class="btn btn-primary" id="data_filter" name="data_filter">

                  </div>
                </div>
            </div>

           {{Form::close()}}

            </div>
        </div>


         <!--  <span id="preview"></span> -->

          <div class="row">

          	 <div class="col-md-12">

               <span id="preview"> 

           <!--  <iframe src="http://investor-portal.s3.amazonaws.com/weekly_reports/2019-01-26/19/weekly_1548659957.pdf" width="900px" height="600px" /></iframe> -->
               </span>




               <!--  <span id="sendMailtoInvestorSpan"> </span> -->

                <!-- <span id="sendtoInvestorPortalSpan"> </span> -->

          </div> 

      </div>	

 </div>
    <!-- /.box-body -->
</div>
</div>
@stop

@section('scripts')
<script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>  

 <script>
    $(document).ready(function () {

    	var URL_pdfGenerationAction = "{{ URL::to('admin/generate_pdf_preview') }}";
    	var URL_sendMailToInvestor="{{ URL::to('admin/send_mail_to_investor') }}";
    	var URL_sendInvestorPortal="{{ URL::to('admin/send_investor_portal') }}";


      $('#recurrence').on('change',function()
         {

         	  $recurrence=$('#recurrence').val();
         	  var date_start='';
         	  var date_end='';



         	  if($recurrence==1)
         	  {
         	  	   <?php

                       $date = date('Y-m-d');
                       $last_day = date('Y-m-d', strtotime($date .' -1 day'));

                        $date_end = date('Y-m-d', strtotime($date .' -1 day'));
                        $date_start = date('Y-m-d', strtotime($date .' -1 day'));


         	  	    ?>

                    $('#date_start').val('<?php echo $date_start ?>');
                    $('#date_start1').val(moment($('#date_start').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));
                    $('#date_end').val('<?php echo $date_end ?>');
                    $('#date_end1').val(moment($('#date_end').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));






         	  }
         	  else if($recurrence==2)
         	  {
         	  	  <?php

                        $date_end = date('Y-m-d', strtotime('last saturday')); // -$days2 days
                        $date_start = date('Y-m-d', strtotime('last saturday', strtotime('last saturday'))); // -$days2 days


         	  	   ?>

         	  	    $('#date_start').val('<?php echo $date_start ?>');
                    $('#date_start1').val(moment($('#date_start').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));
                    $('#date_end').val('<?php echo $date_end ?>');
                    $('#date_end1').val(moment($('#date_end').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));






         	  }
              else if($recurrence==4)
         	    { 
         	    	 <?php


                       $date_end = date('Y-m-d', strtotime('last saturday'));
                       $date_start = date('Y-m-d', strtotime('-1 months', strtotime($date_end)));


         	    	  ?>

                    $('#date_start').val('<?php echo $date_start ?>');
                    $('#date_end').val('<?php echo $date_end ?>');
                    $('#date_start1').val(moment($('#date_start').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));
                    $('#date_end1').val(moment($('#date_end').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));





         	    }
         	    else if($recurrence==5)
         	      {
                       <?php

                          $$date_end = date('Y-m-d', strtotime('last saturday'));
                          $date_start = date('Y-m-d', strtotime('-1 year', strtotime($date_end)));   


                        ?>

                    $('#date_start').val('<?php echo $date_start ?>');
                    $('#date_end').val('<?php echo $date_end ?>');
                    $('#date_start1').val(moment($('#date_start').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));
                    $('#date_end1').val(moment($('#date_end').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));



         	      }
         	      else if($recurrence==3)
         	      	 {

         	      	 	<?php

                         $date_end = date('Y-m-d', strtotime('last saturday')); 
                         $date_start = date('Y-m-d', strtotime('last saturday',  strtotime("-2 week")));

                         ?>

                                $('#date_start').val('<?php echo $date_start ?>');
                                $('#date_end').val('<?php echo $date_end ?>');
                                $('#date_start1').val(moment($('#date_start').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));
                                $('#date_end1').val(moment($('#date_end').val(), "YYYY-MM-DD").format("{{\FFM::defaultDateFormat('format')}}"));


         	      	 }
                        $('.datepicker').datepicker('update');
     });

   $('#data_filter').on('click',function() {

         var investor=$('#investors').val();

           if(investor)
           { 
                $(".loadering").css("display", "block");

           }

           var merchants=$('#merchants').val();
           var startDate=$('#date_start').val();
           var endDate=$('#date_end').val();
           var groupBy= $('#groupby').val();
           var recurrence= $('#recurrence').val();
           var send_mail= $('#send_mail').prop("checked");
           var whole_portfolio=$("input[name=whole_portfolio]:checked").val();

           $.ajax({
              type:'POST',
              data: {'investor': investor,'merchants':merchants,'send_mail':send_mail,'startDate':startDate,'endDate':endDate,'groupBy':groupBy,'recurrence':recurrence, '_token': _token, 'whole_portfolio': whole_portfolio},
              url:URL_pdfGenerationAction,
              success:function(data)
              {  
                    if (data.status == 1) {
                       $(".loadering").css("display", "none");
                       $('.box-head').html('<div class="alert alert-info col-ssm-12" ><button type="button" class="close"  data-bs-dismiss="alert" aria-hidden="true" id="close">&times;</button>' + data.msg + '</div>');

                    }
                    else
                    {
                          $('.box-head').html('<div class="alert alert-danger col-ssm-12" ><button type="button" class="close"  data-bs-dismiss="alert" aria-hidden="true" id="close">&times;</button>' + data.msg + '</div>');
                           $(".loadering").css("display", "none");
                    }
              }

            });

   });

$(".js-investor-placeholder-multiple").select2({
    placeholder: "Select Investors"
});

$(".js-status-placeholder-multiple").select2({
    placeholder: "Select Status"
});

$("#recurrence").select2({
    placeholder: "Select Recurrence"
});






 // generate pdf for multple investors 

     // $('#data_filter').on('click',function() {
     //         $(".loadering").css("display", "block");

     //        var investor=$('#investors').val();
     //        var startDate=$('#date_start').val();
     //        var endDate=$('#date_end').val();
     //        var groupBy= $('#groupby').val();
     //        var recurrence= $('#recurrence').val();
     //     	 $.ajax({
     //         type:'POST',
     //         data: {'investor': investor,'startDate':startDate,'endDate':endDate,'groupBy':groupBy,'recurrence':recurrence, '_token': _token},
     //         url:URL_pdfGenerationAction,
     //         success:function(data)
     //         {   
     //         	 //width="800px" height="600px"

     //         	     var html='';
     //                 if (data.status == 1) {

     //                     html+='<iframe src="'+data.fileUrl+'" width="1000px" height="600px" /><br><br><br><input type="checkbox" name="sendMailtoInvestor" id="sendMailtoInvestor">Send Mail <br><br><input type="hidden" id="statmentId" name="statmentId" value="'+data.last_id+'"><input type="checkbox" name="sendtoInvestorPortal" id="sendtoInvestorPortal">Send Investor Portal';

     //                      // html+='<input type="checkbox" name="sendMailtoInvestor" id="sendMailtoInvestor">Send Mail';

     //                      // html+='<input type="hidden" name="statmentId" value="'+data.last_id+'"><input type="checkbox" name="sendtoInvestorPortal" id="sendtoInvestorPortal">Send Investor Portal';

     //                       $('#preview').html(html);

     //                        $(".loadering").css("display", "none");

     //                     // $('#sendMailtoInvestorSpan').html(output);

     //                     // $('#sendtoInvestorPortalSpan').html(portal);

     //                       $('#sendMailtoInvestor').on('click',function()
     //                          {
     //                                var msg=data.message;

     //                          	    if($(this).is(':checked',true))  
     //                                   {
     //                                        $("#sendMailtoInvestor").prop('checked', true);  
     //                                   }  
     //                                  else  
     //                                  {  
     //                                     $("#sendMailtoInvestor").prop('checked',false);  
     //                                   } 


     //                                  $.ajax({
     //                                           type:'POST',
     //                                           data: {'msg':msg, '_token': _token},
     //                                           url:URL_sendMailToInvestor,
     //                                           success:function(data)
     //                                            {
     //                                                 if (data.status == 1) {

     //                                                 	   if (data.status == 1) {

     //                                                     $('.box-head').html('<div class="alert alert-info col-ssm-12" >' + data.msg + '</div>')



     //                                                   }
     //                                             else
     //                                              {
     //                                                    $('.box-head').html('<div class="alert alert-danger col-ssm-12" >' + data.msg + '</div>')

     //                                              } 





     //                                                   }
     //                                             else
     //                                              {


     //                                              } 
     //                               }
     //                          });




     //                     });


     //                       $('#sendtoInvestorPortal').on('click',function()
     //                          {
     //                          	   var last_id=data.last_id;


     //                          	    if($(this).is(':checked',true))  
     //                                   {
     //                                        $("#sendtoInvestorPortal").prop('checked', true);  
     //                                   }  
     //                                  else  
     //                                  {  
     //                                     $("#sendtoInvestorPortal").prop('checked',false);  
     //                                   } 


     //                                       $.ajax({
     //                                           type:'POST',
     //                                           data: {'last_id':last_id, '_token': _token},
     //                                           url:URL_sendInvestorPortal,
     //                                           success:function(data)
     //                                            {
     //                                                 if (data.status == 1) {

     //                                                     $('.box-head').html('<div class="alert alert-info col-ssm-12" >' + data.msg + '</div>')



     //                                                   }
     //                                             else
     //                                              {
     //                                                    $('.box-head').html('<div class="alert alert-danger col-ssm-12" >' + data.msg + '</div>')

     //                                              } 
     //                               }
     //                          });
     //                     });







     //                 }
     //                 else
     //                 {
     //                 	  $('.box-head').html('<div class="alert alert-danger col-ssm-12" >' + data.msg + '</div>')

     //                 } 


     //         }
     //       });


     //      });
        // let startDt = $('#date_start').val() && new Date($('#date_start').val());
        // if(startDt){
        //     $('#date_end1').datepicker('setStartDate', startDt);
        // }
        // $('#date_start1').on('changeDate', function(selected){
        //     let endDateSelected = $('#date_end').val() && new Date($('#date_end').val());
        //     if($('#date_start').val() && new Date($('#date_start').val())){
        //     let minDate = new Date(selected.date.valueOf());          
        //     if(endDateSelected && endDateSelected < minDate){
        //         $("#date_end1").datepicker('update', "");
        //     }
        //     $('#date_end1').datepicker('setStartDate', minDate);
        //     }else{
        //     $('#date_end1').datepicker('setStartDate', '');
        //     }
        // })

    });

function getMerchant(status)
{
    var URL_selectMerchant = "{{ URL::to('admin/merchants/selectMerchants') }}";

    $('#merchants').empty();

    var merchants = [];

    if(status)
    {
           $.ajax({
              type: "POST",
              url: URL_selectMerchant,
              data:{'_token': _token,'status':status},
              beforeSend: function() {
                $("#merchants").addClass("loader");
              },
              success: function(data){

                if(data.status==1)
                {
                     var result=data.merchants;
                     $.each(data.merchants, function(i, d) {

                        $('#merchants').append('<option value="' + i + '" selected="selected">'+ d + '</option>');

                     });

                }

              }
            });
    }

}

function getInvestor(type)
{
    var URL_selectType = "{{ URL::to('admin/investors/selectType') }}";
    $('#investors').empty();
    var investors = [];
    if(type)
    {
           $.ajax({
              type: "POST",
              url: URL_selectType,
              data:{'_token': _token,'type':type},
              beforeSend: function() {
                $("#investors").addClass("loader");
              },
              success: function(data){

                if(data.status==1)
                {
                     var result=data.investors;
                     $.each(data.investors, function(i, d) {

                        $('#investors').append('<option value="' + i + '" selected="selected">'+ d + '</option>');

                     });

                }

              }
            });

    }


}

 </script>


@stop

@section('styles')


<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=6') }}" rel="stylesheet" type="text/css" />

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
