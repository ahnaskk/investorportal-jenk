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
    <div class="box box-primary box-sm-wrap">
          
         <div class="box-body-sm">
            @include('layouts.admin.partials.lte_alerts')

            <a href="{{route('admin::merchants::payment-terms',['mid'=>$term->merchant_id])}}">
                <button type="button" class="btn btn-xs btn-primary">
                    Back
                </button>
            </a>
            <!-- form start -->
            <form method="POST" action="{{route('admin::merchants::payment-terms-update',['mid'=>$term->merchant_id])}}" id="terms-form">

                <h4 class="text-center">Update Term</h4>
                
                @csrf
                @method('PUT')
                <input type="hidden" name="term_id" value="{{ $term->id }}" id="editTermId">
                
                <div class="form-group current-balance">
                    <label >Current Balance: {{ \FFM::dollar($merchant['balance']) }}</label>
                    <label >Anticipated Balance: {{ \FFM::dollar($merchant['anticipated_balance']) }}</label>
                    <label >ACH Paid Payments: {{ $term->pmnts - $term->payment_left }}</label>
                    <label >ACH Payments Left: {{ $term->actual_payment_left }}</label>
                </div>

                <div class="form-group">
                    <label for="advance_type">Advance Type: @if(!$term->payment_started)<span class="validate_star">*</span>@endif</label>
                    <select class="form-control" name="advance_type" id="editAdvanceType" class="editAdvanceType" {{ ($term->payment_started) ? 'disabled' : '' }} required>
                        @foreach($advance_types as $value => $type)
                            <option value="{{ $value }}" {{ ($term->advance_type == $value) ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @if($term->payment_started)
                    <input type="hidden" name="advance_type" value="{{ $term->advance_type }}" > 
                    @endif
                </div>

                <div class="form-group">
                    <label for="terms">Terms: <span class="validate_star">*</span></label>
                    <input type="number" class="form-control" name="terms" id="editTerms" value="{{ $term->pmnts }}" {{ ($term->payment_started) ? 'min='.$term->paid_payments : '' }} required>
                </div> 

                <div class="form-group">
                    <label for="start-date">Start Date: @if(!$term->payment_started)<span class="validate_star">*</span>@endif</label>
                    <input type="text" class="form-control datepicker" autocomplete="off" name="start_date1" id="editStartDate1" value="{{ $term->start_at }}" {{ ($term->payment_started) ? 'readonly' : '' }}  required>
                    <input type="hidden" name="start_date" class="date_parse" id="editStartDate"  value="{{ $term->start_at }}">
                    @if($term->payment_started)
                    <label class="errors_msg">Start Date cannot be modified since Payment started.</label>
                    @else
                    <label id="startDate-min" class="errors_msg" for="start-date" style="display: none">Please select date greater than or equal to {{ \FFM::date($tomorrow)}}.</label>
                    @endif
                </div>

                <div class="form-group">
                    <label for="end-date">End Date: @if($term->payment_started)<span class="validate_star">*</span>@endif</label>
                    <input type="text" autocomplete="off" class="form-control datepicker" name="end_date1" id="editEndDate1" value="{{ $term->end_at }}" {{ (!$term->payment_started) ? 'readonly' : '' }}  required>
                    <input type="hidden" name="end_date" class="date_parse" id="editEndDate" value="{{ $term->end_at }}">
                    <label id="editEndDate-holiday" class="errors_msg" for="editEndDate" style="display: none">Selected date is a holiday, Please select another date.</label>
                    @if(!$term->payment_started)
                    <label class="errors_msg">End Date will be calculated based on other values.</label>
                    @else 
                    <label id="editEndDate-min" class="errors_msg" for="editEndDate" style="display: none">Please select date greater than or equal to {{ \FFM::date($term->editable_date)}}.</label>
                    @endif
                </div>

                <div class="form-group">
                    <label for="payment_amount">Payment Amount: @if(!$term->payment_started)<span class="validate_star">*</span>@endif</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" name="payment_amount" step="0.01" id="editPaymentAmount" value="{{ $term->payment_amount }}" {{ ($term->payment_started) ? 'readonly' : '' }} required>
                    </div>
                    @if($term->payment_started)
                    <label class="errors_msg">Payment Amount cannot be modified since Payment started.</label>
                    @endif
                </div>
              
                <div class="btn-wrap btn-right">
                    <div class="btn-box">
                        <input type="submit" value="Update" class="btn btn-primary creaate-merc">
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
    var balance = {{$merchant['anticipated_balance']}}
    var URL_CheckDate = "{{ URL::to('admin/merchants/terms/date') }}";
    var paymentStarted = "{{ $term->payment_started }}";
    var editableDate = "{{ $term->editable_date }}";
    var minStartDate = "{{ $tomorrow }}";
    var minTerms = {{ $term->paid_payments }};
    var dbEndDate = "{{ $term->end_at }}";
    var dbTerms = "{{ $term->pmnts }}";
    var futurePayments = {{ $term->actual_payment_left }};
    var currentPayment = {{ $term->payment_amount }};
    var paymentLeftTotal = {{ $term->payment_left_total }};
    var holidays = @json($holidays);
    var default_date_format = "{{ \FFM::defaultDateFormat('format') }}";
    var termID = "{{ $term->id }}";

    $(document).ready(function () {
        $('#editAdvanceType').change(function(){
            editStartDate()
        })

        $('#editStartDate1').change(function(){
            editStartDate()
        })
        function editStartDate(){
            var startDate = $('#editStartDate').val()
            var terms = $('#editTerms').val()
            var advanceType = $('#editAdvanceType').val()
            document.getElementById("startDate-min").style.display = 'none';
            if(startDate < minStartDate){
                $('#editStartDate,#editStartDate1').val('');
                $('#editEndDate,#editEndDate1').val('');
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
                        
                        $('#editStartDate').val(data.data.startDate);
                        $('#editEndDate').val(data.data.endDate);
                        $('#editStartDate1').val(moment(data.data.startDate, 'YYYY-MM-DD').format(default_date_format));
                        $('#editEndDate1').val(moment(data.data.endDate, 'YYYY-MM-DD').format(default_date_format));

                    }
                });
            }
        }

        $('#editEndDate1').change(function(){
            if(paymentStarted){
                var startDate = $('#editStartDate').val()
                var endDate = $('#editEndDate').val()
                // var terms = $('#editTerms').val()
                var advanceType = $('#editAdvanceType').val()
    
                if(startDate && advanceType && endDate) {
                    $.ajax({
                        type: 'POST',
                        data: {
                            'term_id' : termID,
                            'start_date' : startDate,
                            'end_date' : endDate,
                            'terms' : minTerms,
                            'advance_type' : advanceType,
                            '_token': _token
                        },
                        url: URL_CheckDate,
                        success: function (data) {
                            if(data.status){
                                $('#editEndDate').val(dbEndDate); 
                                $('#editTerms').val(dbTerms);
                            }else{
                                $('#editTerms').val(data.data.pmnts);
                                $('#editEndDate').val(data.data.endDate);
                            }
                        }
                    });
                }

            }
        })

        $('#editTerms').change(function(){
            if(paymentStarted){
                var startDate = $('#editStartDate').val()
                // var endDate = $('#editEndDate').val()
                var terms = $('#editTerms').val()
                var advanceType = $('#editAdvanceType').val()

                if(terms >= minTerms )
                    // console.log(minTerms)
    
                if(terms >= minTerms && advanceType && startDate) {
                    $.ajax({
                        type: 'POST',
                        data: {
                            'term_id' : termID,
                            'start_date' : startDate,
                            // 'end_date' : startDate,
                            'terms' : terms,
                            'advance_type' : advanceType,
                            '_token': _token
                        },
                        url: URL_CheckDate,
                        success: function (data) {
                            if(data.status){
                                $('#editEndDate').val(dbEndDate); 
                                $('#editTerms').val(dbTerms);
                                $('#editEndDate1').val(moment(dbEndDate, 'YYYY-MM-DD').format(default_date_format));
                            }else{
                                $('#editEndDate').val(data.data.endDate);
                                $('#editEndDate1').val(moment(data.data.endDate, 'YYYY-MM-DD').format(default_date_format));
                                // $('#editTerms').val(data.data.pmnts);
                            }
                        }
                    });
                }

            }
            else{
                var terms = $('#editTerms').val()
                paymentAmount = (balance + paymentLeftTotal) / terms
                if(paymentAmount < 0)
                    paymentAmount = 0
                $("#editPaymentAmount").val(paymentAmount.toFixed(2));
                editStartDate()
                // $('#editStartDate').val(null)
            }
        })
        
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

    // Everything except weekend days and holidays
    const validate = dateString => {
        const day = (new Date(dateString)).getDay();
        var today = new Date().toJSON().slice(0,10);
        if(dateString == today)
            return true
        else{
            if (day==0 || day==6) {
                return false;
            }
            if(holidays.includes(dateString))
                return false;
            return true;
        }
    }


    // Sets the value to '' in case of an invalid date
    document.querySelector('#editEndDate1').onchange = evt => {
        document.getElementById("editEndDate-holiday").style.display = 'none';
        document.getElementById("editEndDate-min").style.display = 'none';
        var value = document.getElementById('editEndDate').value;
        if (!validate(value)) {
            evt.target.value = '';
            document.getElementById('editEndDate').value = '';
            document.getElementById("editEndDate-holiday").style.display = 'block';
        }
        else if(value < editableDate){
            evt.target.value = '';
            document.getElementById('editEndDate').value = '';
            document.getElementById("editEndDate-min").style.display = 'block';
        }
    }
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