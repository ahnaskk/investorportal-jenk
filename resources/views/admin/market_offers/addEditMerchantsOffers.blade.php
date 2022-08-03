@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>


      </a>
      
  </div>
  

  <?php $offer_id=isset($_GET['id'])?$_GET['id']:''; ?>

        @if($offer_id)
         {{ Breadcrumbs::render('merchant_marketing_offers_edit') }}
       @else
         {{ Breadcrumbs::render('merchant_marketing_offers_create') }}
       @endif
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary box-sm-wrap">

               {!! Form::open(['route'=>'admin::addUpdateMerchantMarketOffer', 'method'=>'POST','id'=>'crete_offer_form']) !!}

               {!! Form::hidden('offer_id',$offer_id,['class'=>'form-control','id'=> 'offer_id']) !!}
            
                <div class="box-body box-body-sm">
                   @include('layouts.admin.partials.lte_alerts')
               
                <div class="form-group">
                        <label for="exampleInputEmail1">Title<span class="validate_star">*</span></label>
                    {!! Form::text('title',isset($offers) ? $offers['title'] : old('title'),['class'=>'form-control','required'=>'required','id'=>'title']) !!}

               </div>

                 <div class="form-group">
                    <label for="exampleInputEmail1">Status</label>
                    {!! Form::select('sub_status_id',$statuses,old('sub_status_id'),['class'=>'form-control js-status-placeholder-multiple','placeholder'=>'Select Status','onChange'=>"getMerchant(this.value);",'id'=>'sub_status_id']) !!}
                </div>

               <div class="form-group">
                    <label for="exampleInputEmail1">Merchant <span class="validate_star">*</span></label>
                    <select name="merchants[]" class="form-control js-merchant-placeholder-multiple" multiple id="merchants">
                      @if(isset($selected_merchant))
                      @foreach ($selected_merchant as $selected)
                      <option value="{{ $selected->id }}" selected="selected">{{ $selected->name }}<option>
                      @endforeach
                      @endif
                    </select>
                     <span id="invalid-inputMerchant"/>
                </div>

                <div class="form-group">
                    <label for="type">Template Type<span class="validate_star">*</span></label>
                         {!! Form::select('type',$template_types,isset($offers)? $offers['type']: old('type'),['class'=>'form-control','placeholder'=>'Select Type','required','id'=> 'type','onChange'=>"getType(this.value);"]) !!}
                </div>

                <div class="form-group">
                    <label for="exampleInputEmail1">Template<span class="validate_star">*</span></label>
                {!! Form::select('template',$template,isset($offers)?$offers['template_id']:old('template'),['class'=>'form-control js-template-placeholder-multiple','required','id'=>'template','placeholder'=>'Select Template']) !!}
                     <span id="invalid-inputMerchant" />
                </div>

                <div class="form-group">
                        <label for="exampleInputEmail1">Offer<span class="validate_star">*</span></label>
                    {!! Form::textarea('offer',isset($offers)? $offers['offers'] : old('offer'), ['class'=>'form-control','id'=>'template_content']) !!}
                </div>
                   
                  <div class="btn-wrap btn-right">
                  <div class="btn-box">
                    <a href="{{URL::to('admin/merchantMarketOfferList')}}"  class="btn btn-success">View Offers</a>
                  @if($offer_id)

                  <?php $button='Update'; ?>

                  @else

                     <?php $button='Create'; ?>

                  @endif
                  {!! Form::submit($button,['class'=>'btn btn-primary bran-mng-bt']) !!}
                  
                 </div>

                </div>
                <!-- /.box-body -->


                </div>
            {!! Form::close() !!}
        </div>
        <!-- /.box -->
    </div>

@stop
 @section('scripts')
       
   <script src="{{ asset('/js/custom/merchantSelect2.js') }}"></script>
   <script src='{{ asset("js/jquery_validate_min.js")}}' type="text/javascript"></script>
   <script>
    $(document).ready(function () {

    var URL_selectTemplate = "{{ URL::to('admin/template/selectTemplate') }}";
       
    $('#template').on('change',function()
       {
          var template_id=$('#template').val();

          if(template_id)
          {
                $.ajax({
                       type:'POST',
                       data: {'_token': _token,'template_id':template_id},
                       url:URL_selectTemplate,
                       success:function(data)
                       {
                             $('#template_content').val(data.content);
                             

                       }
                });
          }

       });


  $('#crete_offer_form').validate({ // initialize the plugin
        errorClass: 'errors',
        rules: {
            title:{
                required: true
            },
            offer: {
                required: true
            },

            type:{
               
               required: true
            },
            template:{
              required: true
            },

            // 'investors[]':{
            //         required: function(element) {
            //                     if($('#inputInvestorType').val()!=0)
            //                       return true;
            //                   else
            //                       return false;
            //             },
                   
            //     },

            'merchants[]':{
                    required: function(element) {
                              //  if($('#sub_status_id').val()!=0)
                                  return true;
                              //else
                                //  return false;
                        },
                   
                },

        },
        messages: {
        offer: { required :"Enter Offer",
              },

        // 'investors[]':{ required :"Enter Investors",
        //       },

        'merchants[]':{ required :"Enter Merchants",
              },
         type :{
             required :"Enter Type",
         },
         template :{
             required :"Enter Template",
         },
         title:{
             required :"Enter Title",
         }

        },
        submitHandler: function(form) { 
            $(":submit").attr("disabled", true);
            form.submit();
        }
       
        
    });

$(".js-investor-placeholder-multiple").select2({
    placeholder: "Select Investor"
});

$(".js-template-placeholder-multiple").select2({
    placeholder: "Select Template"
});

$(".js-status-placeholder-multiple").select2({
    placeholder: "Select Status"
});

});


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


function getType(template_type)
{
    var URL_selectType = "{{ URL::to('admin/template/selectType') }}";
    $('#template').empty();
    if(template_type)
    {
        $.ajax({
              type: "POST",
              url: URL_selectType,
              data:{'_token': _token,'template_type':template_type},
              beforeSend: function() {
                $("#template").addClass("loader");
              },
              success: function(data){

                if(data.status==1)
                {
                     $('#template').append('<option value="">Select Template</option>');

                     $.each(data.template, function(i, d) {

                         $('#template').append('<option value="' + i + '">'+ d + '</option>');
                           //alert(d);
                          // alert(i);
                     });


                }


              

               //$("#template").html(data);
               
              }
            });

    }
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
      <link href="{{ asset('/css/optimized/merchant_batches_create.css?ver=5') }}" rel="stylesheet" type="text/css" />

@stop
