@extends('layouts.admin.admin_lte')

@section('content')

 <div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{$page_title}}</div>     
      </a>
      
  </div>
  {{ Breadcrumbs::render('admin::change_advanced_status') }}
  <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">

            <div class="box-head">

            @include('layouts.admin.partials.lte_alerts')

           </div>

            
            <div class="box-body">
                <div class="changeStatusloadering" style="display:none;">
           
                 <div class="loader"></div><br>
                 <h5 class="alert alert-warning"><b>Merchant status changed to Advance Completed. Please wait until the page refreshes automatically.</b></h5>
          
                </div>
                <div class="form-box-styled text-center">
                <p class="lg">To change status to "Advance completed" if 100% completed. </p>
                </div>
                <div class="btn-wrap btn-right mt-15">
                    <div class="btn-box">
                        <a href="#" id="change_advanced_status" class="btn btn-primary">Change to Advance Completed Status</a>
                 </div>
             </div>

             </div>
      </div>
         
 </div>    

 <div class="modal fade" id="confirmDeal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="padding-left:10px">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Change Status</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['method'=>'POST','id'=>'change_to_advanced_status']) !!}

            <span id="dealBox"></span>
            <div class="modal-footer">
                <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                <a href="javascript:void(0)" class="btn btn-success" id="submitStatusChange" data-bs-dismiss="modal">Yes</a>

             {!! Form::close() !!}    

            </div>
        </div>
    </div>
</div>
     	
@stop

@section('scripts')

<script type="text/javascript">
 
  $(document).ready(function(){

     var URL_changeMerchantStatus = "{{ URL::to('admin/change_advanced_status/') }}";
     var redirectUrl="{{ URL::to('admin/change_advanced_status/') }}";
     var changeStatus="{{ URL::to('admin/advanced_status_check/') }}";

      $('#change_advanced_status').on('click',function() {

       $.ajax({
             type:'POST',
             data: {'_token': _token},
             url:URL_changeMerchantStatus,
             success:function(data)
             {
                    if (data.status == 1) {
                       var html='';

                       html+='<table class="table table-bordered table-hover">\<thead>\
                                <tr>\
                                    <th class="slNoTd"># <input type="checkbox"  class="select_merchant" id="select_merchant"></th>\
                                    <th>Merchant</th>\
                                    <th>Complete %</th>\
                                  </tr>\
                            </thead><tbody>';
                       
                       $.each(data.result, function (i, val) {
                          html+='<tr>\
                                 <td><input type="checkbox" name="merchants[]" value="'+val.merchant_id+'" class="bulk_merchant chkbx_bulk">\
                                 <input type="hidden" value="'+val.merchant_name+'" name="merchant_name[]" class="bulk_merchant"></td>\
                                 <td><a target="blank" href="/admin/merchants/view/'+val.merchant_id+'">'+val.merchant_name+'</a></td>\
                                 <td>'+val.complete_percentage+'</td>\
                                 </tr>';
                        });

                        html+='</tbody></table>';
                        $('#dealBox').html(html);
                        $('#confirmDeal').modal('show');

                        $('#select_merchant').on('click',function()
                             {
                                if($(this).is(':checked',true))  
                                {
                                  $(".bulk_merchant").prop('checked', true);  
                                }  
                                else  
                                {  
                                  $(".bulk_merchant").prop('checked',false);  
                                } 

      
                            });
                    }
                    else
                    {
                         
                        
                        
                    } 
             }
           });  
    });


$('#submitStatusChange').on('click',function()
{ 
     $(".changeStatusloadering").css("display", "block");

       $.ajax({
            type: 'POST',
            data: $("#change_to_advanced_status").serialize(),
            url: changeStatus,
            success: function (data) {
                if (data.status == 1) {
                     window.location = redirectUrl;
                } else {

                    $(".changeStatusloadering").css("display", "none"); 
                    $('.box-head').html('<div class="alert alert-danger col-ssm-12" >' + data.msg + '</div>');
                    
                }
            }
        });

     
   
});
$(document).on("click",".chkbx_bulk",function(){
  let total_chk_boxes = $(".chkbx_bulk").length
  let checked  = $(".chkbx_bulk:checked").length
  if(total_chk_boxes == checked){
    $("#select_merchant").prop("checked",true)
  }
  else{
    $("#select_merchant").prop("checked",false)
  }
})

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
</style>
<link href="{{ asset('/css/optimized/Change_Merchant_Status.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop
