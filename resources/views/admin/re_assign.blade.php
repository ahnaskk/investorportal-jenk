@extends('layouts.admin.admin_lte')
@section('content')
<div class="inner admin-dsh header-tp">
    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{isset($page_title)?$page_title:''}} </h3>
    <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{isset($page_title)?$page_title:''}}</div>
    </a>
</div>
{{ Breadcrumbs::render('re-assign') }}
<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary">
        @include('layouts.admin.partials.lte_alerts')
        {!! Form::open(['route'=>'admin::re-assign', 'method'=>'GET','id'=>'create_status_form']) !!}
        <div class="box-body">
            <pre class="pre">
                Transfer Funded Amount (Without Commission)
            </pre>
            @php $test=isset($_GET['investor_id'])?$_GET['investor_id']:old('investor_id'); @endphp
            <div class="form-box-styled">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="exampleInputEmail1">Withdraw Money From Investor <font color="#FF0000"> * </font></label>
                        <select name="investor_id" id="investor_id" class="form-control" required="required">
                            <option>Select An Investor To Withdraw Money.</option>
                            @foreach($investors as $inv_id => $investor2)
                            <option <?PHP if($test == $investor2->id){
                                echo " Selected ";
                            }  ?> value="{{$investor2->id}}">   {{$investor2->name}} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6" style="padding-top: 25px;"><label></label> {!! Form::submit('Assign to all other investors',['class'=>'btn btn-primary']) !!}</div>
                </div>
            </div>
            {!! Form::close() !!}
            <div class="form-box-styled">
                <div class="row">
                    <div class="col-md-6 form-group">
                        {!! Form::open(['route'=>'admin::re-assign', 'method'=>'POST','id'=>'create_status_form']) !!}
                        <label><span title="Gross Investment Amount">Invested Amount To Transfer</span> </label>
                        @php
                        $invested_amount=($investor_details->total_invested_amount>0)?$investor_details->total_invested_amount:0;
                        @endphp
                         <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-dollar"></i></span>
                                <input type="text"  class="form-control" name="balance_amount" value="{{ round($invested_amount,2) }}" id="balance_amount" readonly="readonly">
                          </div>
                    </div>
                    <div class="col-md-6 form-group" style="padding-top: 25px;">
                        <a href="#" id="clear_all" class="btn btn-danger">Clear All</a>
                        <input type="hidden" name="balance_amount1" value="{{ round($invested_amount,2) }}" id="balance_amount">
                        <input type="hidden" value="{{$investor_id}}" name="investor_id" id="investor_id">
                        @foreach($investors as $inv_id => $investor2)
                    </div>
                </div>
            </div>

           


            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="exampleInputEmail1"></label>
                        <input type='hidden' class='investor' name="amount[{{$investor2->id}}][investor]" value="{{$investor2->id}}">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">{{$investor2->name}} <font color="#FF0000"> * </font></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-dollar"></i></span>
                            {{Form::text("amount[$investor2->id][amount_per]",0,['class'=>'form-control re-assign-per perntages_class_main accept_digit_only ','placeholder'=>'Re-assign Amount','id'=>$investor2->id])}}
                        </div>
                        @endforeach
                    </div>
                    @if(@Permissions::isAllow('Settings Re-assign','Create'))
                    <div class="btn-wrap btn-right" >
                        <div class="btn-box" >
                            {!! Form::submit('Transfer',['class'=>'btn btn-primary']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('scripts')
<script src="{{ asset ('bower_components/bootstrap-tagsinput-latest/dist/bootstrap-tagsinput.min.js') }}" type="text/javascript"></script>
<script>
$(document).ready(function () {

    $('#investor_id').on('change',function()
    {
        $v=$('#investor_id').val();
        window.location = "{{ url('admin/re-assign?investor_id=')}}" + $v;
    });

    $('.re-assign-per').keyup(function(event) {
        // skip for arrow keys
        if(event.which >= 37 && event.which <= 40){
            event.preventDefault();
        }
        $(this).val(function(index, value) {
            value = value.replace(/,/g,'');
            return value;
            // return numberWithCommas(value);
        });
    });

    $(".re-assign-per").on("input", function() {
  if (/^0/.test(this.value)) {
    this.value = this.value.replace(/^0/, "")
  }
});

   $('.re-assign-per').keypress(function(event) {
     if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
        ((event.which < 48 || event.which > 57) &&
         (event.which != 0 && event.which != 8))) {
        event.preventDefault();
      }
      var text = $(this).val();
     if ((text.indexOf('.') != -1) &&
        (text.substring(text.indexOf('.')).length > 8) &&
        (event.which != 0 && event.which != 8) &&
        ($(this)[0].selectionStart >= text.length - 8)) {
        event.preventDefault();
      }
     });

    //  $('.re-assign-per').focus(()=>{
    //     $('.re-assign-per').mask("0.00");
    // });

   $(".accept_digit_only").keypress(function (event) {
        var theEvent = event || window.event;
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
        if (key.length == 0) return;
        var regex = /^[0-9.,\b]+$/;
        if (!regex.test(key)) {
          theEvent.returnValue = false;
          if (theEvent.preventDefault) theEvent.preventDefault();
        }

         var str=this.value;

         if((str.length==0) && event.which == 46) {
        event.preventDefault();

      } // prevent if already dot

      if ((str.indexOf('.')>=0) && (event.keyCode==46)) return false;


      if(event.which == 44
        && $(this).val().indexOf(',') != -1) {
          event.preventDefault();
        } // prevent if already comma
      });

    // $('.accept_digit_only').on('paste', function (event) {
    //   if (event.originalEvent.clipboardData.getData('Text').match(/[^\d]/)) {
    //   event.preventDefault();
    //   }
    // });
    

    $('#clear_all').on('click',function()
    {
        $('.re-assign-per').val(0);
    });
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
<link href="{{ asset('/css/optimized/Default_Settings.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/optimized/create_merchant.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
