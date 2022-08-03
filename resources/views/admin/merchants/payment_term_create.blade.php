@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ $page_title }}</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ $page_title }}</div>     
        </a>
      
</div>


<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary box-sm-wrap ">
          
         <div class="box-body-sm no-over-flow">
            @include('layouts.admin.partials.lte_alerts')
            
            <div class="heading">
                <h4 class="left">New Term</h4> 
                <div class="right">
                    <a class="btn btn-xs btn-primary" href="{{route('admin::merchants::payment-terms',['mid'=>$merchant['merchant_id']])}}">
                        Back
                    </a>
                </div>
            </div>

            
            <!-- form start -->
            <form method="POST" action="{{route('admin::merchants::payment-terms-store',['mid'=>$merchant['merchant_id']])}}" id="terms-form">

                
                @csrf
                <div class="form-group current-balance">
                    Current Balance: {{ \FFM::dollar($merchant['balance']) }}<br>
                    Anticipated Balance: {{ \FFM::dollar($merchant['anticipated_balance']) }}
                </div>
                <div class="form-group">
                    <label for="advance_type">Advance Type: <span class="validate_star">*</span></label>
                    <select class="form-control" name="advance_type" id="advance_type" required>
                        <option selected disabled value="">Select Type</option>
                        @foreach($advance_types as $value => $type)
                            <option value="{{ $value }}" {{ (($preset_values['advance_type'] ?? old('advance_type')) == $value) ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="terms">Terms: <span class="validate_star">*</span></label>
                    <input type="number" class="form-control" name="terms" id="terms" value="{{ $preset_values['terms'] ?? old('terms') }}" step="1" required>
                </div>

                <div class="form-group">
                    <label for="start-date">Start Date: <span class="validate_star">*</span></label>
                    <input type="text" autocomplete="off" class="form-control datepicker" name="start_date1" value="{{ $preset_values['start_date'] ?? old('start_date')  }}" id="start-date1" required min="{{ $min_start_date }}">
                    <input type="hidden" class="date_parse" name="start_date" value="{{ $preset_values['start_date'] ?? old('start_date')  }}" id="start-date">
                    <label id="startDate-min" class="errors_msg" for="start-date" style="display: none">Please select date greater than or equal to {{ \FFM::date($min_start_date)}}.</label>
                </div>

                <div class="form-group">
                    <label for="end-date">End Date:</label>
                    <input type="text" autocomplete="off" class="form-control datepicker" name="end_date1" value="{{ $preset_values['end_date'] ?? old('end_date') }}" id="end-date1" readonly>
                    <input type="hidden" class="date_parse" name="end_date" value="{{ $preset_values['end_date'] ?? old('end_date') }}" id="end-date" required>
                    <label class="errors_msg">End Date will be calculated based on other values.</label>
                </div>

                <div class="form-group">
                    <label for="payment_amount">Payment Amount: <span class="validate_star">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" name="payment_amount" value="{{ $preset_values['payment_amount'] ?? old('payment_amount') }}" min="0.01" step="0.01" id="payment_amount">
                    </div>
                </div>
              
                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        <input type="submit" value="Submit" class="btn btn-primary creaate-merc">
                    </div>
                </div>

            </form>
        <!-- /.box-body -->
        </div>
    <!-- /.box -->
    </div>  
<!-- /.col -->
</div>


@stop

@section('scripts')
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>

<script>
    var _token = '{{csrf_token()}}';
    var balance = '{{$merchant['anticipated_balance']}}'
    var URL_CheckDate = "{{ URL::to('admin/merchants/terms/date') }}";
    var minStartDate = "{{ $min_start_date }}";
    var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";

    $(document).ready(function () {
        $('#start-date1').datepicker('setStartDate', new Date($('#start-date1').attr('min')))
        $('#terms').change(function(){
            var terms = $('#terms').val()
            paymentAmount = balance / terms
            if(paymentAmount < 0)
                paymentAmount = 0
            $("#payment_amount").val(paymentAmount.toFixed(2));
            changeStartDate()
        })
        $('#advance_type').change(function(){
            // $('#start-date,#start-date1').val(null)
            // $('#end-date,#end-date1').val(null)
            changeStartDate()
        })

        $('#start-date1').change(function(){
            changeStartDate()
        })
        function changeStartDate(){
            var startDate = $('#start-date').val()
            var terms = $('#terms').val()
            var advanceType = $('#advance_type').val()
            document.getElementById("startDate-min").style.display = 'none';
            if(startDate < minStartDate){
                $('#start-date,#start-date1').val('');
                $('#end-date,#end-date1').val('');
                document.getElementById("startDate-min").style.display = 'block';
            }else if(terms && advanceType && startDate) {
                $.ajax({
                    type: 'POST',
                    data: {
                        'start_date' : startDate,
                        'terms' : terms,
                        'advance_type' : advanceType,
                        '_token': _token
                    },
                    url: URL_CheckDate,
                    success: function (data) {
                        
                        $('#start-date').val(data.data.startDate);
                        $('#end-date').val(data.data.endDate);
                        $('#start-date1').val(moment(data.data.startDate, 'YYYY-MM-DD').format(default_date_format));
                        $('#end-date1').val(moment(data.data.endDate, 'YYYY-MM-DD').format(default_date_format));
                    }
                });
            }

        }
        
        $("#terms-form").click(function(){
            $('#terms-form').validate({ // initialize the plugin
                errorClass: 'errors_msg',
                rules: {
                    advance_type: {
                        required: true,
                    },
                    terms:{
                        required: true,
                        number:true,
                        range: [1,999]
                    },
                    // payment_amount:{
                        // required: true,
                        // checkNumeric:true 
                    // }
                },
                messages: {
                    advance_type: {required : "Select Type"},
                    terms:{required : "Enter Valid Term",range: "Enter a term between 1 and 999" },      
                },
             }); 
        });
    })
</script>
@stop

@section('styles')
<link href="{{ asset('/css/optimized/merchant_payment.css?ver=5') }}" rel="stylesheet" type="text/css" />
<style>
    .errors_msg {
        color: red;      
    }
</style>
@stop