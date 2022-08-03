<?php use App\SubStatus; ?>
<?php $SubStatus=SubStatus::whereIn('id',[1,5])->pluck('name','id')->toArray(); ?>
@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">Change Merchant Status</div>     
    </a>
</div>
{{ Breadcrumbs::render('admin::change_merchant_status') }}
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
        @include('layouts.admin.partials.lte_alerts')
        <div class="box-body">
            <div class="changeStatusloadering" style="display:none;">
                <!-- <div class="loader"></div><br> -->
                <!-- <h5 class="alert alert-warning"><b>Merchant status changed to default. Please wait until the page refreshes automatically.</b></h5> -->
            </div>
            <div class="form-box-styled text-center">
                <p class="lg">If a deal is not complete and it has not received a payment in one month, move it to default status.</p>
            </div>
            <div class="btn-wrap btn-right mt-15">
                <div class="form-group col-md-3">
                    <label for="sub_status_id">Status</label>
                    {!! Form::select('sub_status_id',$SubStatus,'',['id'=>'sub_status_id','class'=>'form-control']) !!}
                </div>
                <div class="form-group col-md-2">
                    <label for="sub_status_id">No Of Days</label>
                    {!! Form::number('no_of_days',30,['id'=>'no_of_days','class'=>'form-control']) !!}
                </div>
                <div class="form-group col-md-2">
                    <div class="btn-box"> <br>
                        <a href="#" id="change_merchant_status" class="btn btn-primary btn-sm">Change Merchant Status</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    
<div class="modal fade" id="confirmDeal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Change Status</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-header">
                {!! Form::open(['method'=>'POST','id'=>'change_status']) !!}
                <span id="dealBox"></span>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" class="btn btn-default" data-bs-dismiss="modal">Cancel</a>
                <a href="javascript:void(0)" class="btn btn-success" id="submitMerchant" data-bs-dismiss="modal">Yes</a>
                {!! Form::close() !!}    
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script type="text/javascript">
$(document).ready(function(){
    var URL_changeMerchantStatus = "{{ URL::to('admin/change_merchant_status/') }}";
    var redirectUrl="{{ URL::to('admin/change_merchant_status/') }}";
    var changeStatus="{{ URL::to('admin/merchant_status_check/') }}";
    $(document).on('click','.bulk_merchant', function(){
        var total_merchants = $('.bulk_merchant:not([type=hidden])').length
        var selected_merchants  = $('.bulk_merchant:not([type=hidden]):checked').length
        if(selected_merchants < total_merchants ){
            $("#select_merchant").prop('checked',false)
        } else {
            $("#select_merchant").prop('checked',true)
        }
    })
    $('#change_merchant_status').on('click',function() {
        $.ajax({
            type: 'POST',
            url : URL_changeMerchantStatus,
            data: {
                '_token'       : _token,
                'sub_status_id': $('#sub_status_id').val(),
                'no_of_days'   : $('#no_of_days').val(),
            },
            success:function(data) {
                var html='';
                if (data.status == 1) {
                    html+='<table class="table table-bordered table-hover">\<thead>\
                    <tr>\
                    <th class="slNoTd"># <input type="checkbox"  class="select_merchant" id="select_merchant"></th>\
                    <th>Merchant</th>\
                    <th>Status</th>\
                    <th>Last Payment Date</th>\
                    <th>Complete %</th>\
                    </tr>\
                    </thead><tbody>';
                    $.each(data.result, function (i, val) {
                        html+='<tr>\
                        <td><input type="checkbox" name="merchants[]" value="'+val.merchant_id+'" class="bulk_merchant">\
                        <input type="hidden" value="'+val.days+'" name="diff_in_months['+val.merchant_id+']" class="bulk_merchant">\
                        <input type="hidden" value="'+val.merchant_name+'" name="merchant_name[]" class="bulk_merchant"></td>\
                        <td><a target="blank" href="/admin/merchants/view/'+val.merchant_id+'" >'+val.merchant_name+'</a></td>\
                        <td>'+val.status+'</td>\
                        <td>'+val.last_payment_date+ ' - '+ val.days +'  days</td>\
                        <td>'+val.complete_per+'</td>\
                        </tr>';
                    });
                    html+='</tbody></table>';
                    $('#dealBox').html(html);
                    $('#confirmDeal').modal('show');
                    $('#select_merchant').on('click',function() {
                        if($(this).is(':checked',true)) {
                            $(".bulk_merchant").prop('checked', true);  
                        } else {  
                            $(".bulk_merchant").prop('checked',false);  
                        } 
                    });
                } else {
                } 
            }
        });  
    });
    $('#submitMerchant').on('click',function() { 
        $(".changeStatusloadering").css("display", "block");
        if($("#change_status").serialize()) {
            $.ajax({
                type: 'POST',
                data: $("#change_status").serialize(),
                url : changeStatus,
                success: function (data) {
                    if (data.status == 1) {
                        window.location = redirectUrl;
                    } else {
                        $(".changeStatusloadering").css("display", "none");
                        window.location = redirectUrl;
                    }
                }
            });
        }
    });
});
$('#dealBox').checkboxes('range', true);
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
#change_status {width:100%;}
</style>
<link href="{{ asset('/css/optimized/Change_Merchant_Status.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
