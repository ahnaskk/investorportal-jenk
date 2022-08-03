@extends('layouts.admin.admin_lte')
@section('content')

<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{$page_title}} </h3>
      <a href="#" class="help-link">
        <i class="fa fa-question-circle" aria-hidden="true"></i>
        <div class="tool-tip">{{ $page_title }}</div>     
      </a>
      
</div>
 {{ Breadcrumbs::render('admin::payments::ach-payment.index') }}
<div class="col-md-12">
    <div class="box">
        <div class="box-head ">
            @include('layouts.admin.partials.lte_alerts')
        </div>
        <div class="box-body"> 
            <div class="form-box-styled" >
                <div class="serch-bar">
                    <form action="{{ route('admin::payments::ach-payment.data') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    
                                    <input class="form-control datepicker" type="text" id="date1" name="date1" multiple placeholder="Choose Date" value="{{ $date }}" min="{{ $tomorrow }}"  autocomplete="off">
                                    <input type="hidden" name="date" id="date" value="{{ $date }}" class="date_parse">
                                    <label >Date</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    
                                    <select class="form-control" id="sub_status_id" name="sub_status_id[]" multiple placeholder="Select Status">
                                        @foreach($statuses as $key=>$status)
                                        <option value="{{ $key }}" @if(old('sub_status_id') && in_array($key, old('sub_status_id'))) selected @endif>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label">Status</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    
                                    <select class="form-control" id="merchants_id" name="merchants_id[]" multiple placeholder="Select Merchant">
                                        @foreach($merchants_id as $key=>$merchant_name)
                                        <option value="{{ $key }}" @if(old('merchants_id') && in_array($key, old('merchants_id'))) selected @endif>{{ $merchant_name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label">Merchant</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                {{-- <div class="form-group">
                                    <label for="manual_payment">Manual Payment</label>
                                    <div class="input-group">
                                        <input class="" id="manual_payment" name="manual_payment" type="checkbox" value="1" @if(old('manual_payment')) checked @endif/>
                                    </div>
                                </div> --}}
                                <div class="btn-box " style="margin-bottom: 25px;">
                                    <div class="input-group">
                                        <input type="submit" value="Apply Filter" class="btn btn-primary" id="date_filter">
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </form>                             
                </div>
            </div>
                    
           
            <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
            <div class="loadering" style="display:none;">
                    <div class="loader"></div><br>
                </div>
                <div class="row">
                    <div class="col-sm-12 grid table-responsive">
                        <form method="POST" id="formId">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        {{-- <th align="center">
                                            <input type="checkbox" id="select-all">
                                        </th> --}}
                                        <th>#</th>
                                        <th>Merchant</th>
                                        <th>Auto ACH</th>
                                        <th>Payment Amount ($)</th>
                                        <th>Fees ($)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $key => $payment)
                                    <tr>    
                                        {{-- <td>
                                            <label class="chc">
                                                <input type='checkbox' class='confirmed-payment' name='confirmed[]' data-id="{{$payment->merchant_id}}" value='{{$payment->merchant_id}}'>
                                                <span class="checkmark"></span>
                                             
                                            </label>
                                        </td> --}}
                                        <td>{{ ++$key }}</td>
                                        <td>
                                            <a href="{{ route('admin::merchants::payment-terms',['mid'=>$payment->merchant_id]) }}">
                                                {{ $payment->merchant_name }}
                                            </a>
                                        </td>
                                        <td>
                                            <a merchant_id="{{$payment->merchant_id}}" class="changeAutoACHStatus">
                                                @if($payment->ach_pull == 1)
                                                <label class="label label-success">ON</label>
                                                @else
                                                <label class="label label-danger">OFF</label>
                                                @endif
                                            </a> 
                                        </td>
                                        <td>
                                            <input class="payments form-control" type="number" step="0.01" class="form-control" name="ach[{{ $payment->merchant_id}}][amount]" value="{{ $payment->payment_amount }}" {{ $payment->disabled ? 'disabled' : ''}} min="0.01" required>
                                        </td>
                                        <td>
                                            <div class="outer-wrapper">
                                                <div class="add-remove-wrapper">
                                                    <div class="select-inner-wrapper">
                                                        <select class="add-remove-selector">
                                                            <option value="1">ACH Rejection</option>
                                                            <option value="2">NFS</option>
                                                            <option value="3">Bank Change</option>
                                                            <option value="4">Blocked Account</option>
                                                            <option value="6">Default fee</option>
                                                        </select>
                                                    </div>
                                                    <button type="button" class="add-remove-action btn btn-primary">Add  or remove fields</button>
                                                </div>
                                                {{-- @if($payment->first_month_payment)
                                                <label>First payment of the month:</label> 
                                                <input class="fees form-control" type="number" step="0.01" class="form-control" name="ach[{{ $payment->merchant_id}}][fees][monthly_first_payment_fee]" value="{{ $payment->first_month_payment_fee }}">
                                                @endif --}}
                                                @foreach($fee_types as $key=> $fee_type)
                                                @if($key != 'ach_fee')  
                                                <input class="fees form-control" type="number" step="0.01" class="form-control" name="ach[{{ $payment->merchant_id}}][fees][{{$key}}]" value="{{ isset($payment->$key) ? $payment->$key : ''}}"  placeholder="{{ $fee_type }}" {{ $payment->disabled ? 'disabled' : ''}}  title="{{ $fee_type }}" min="0" >
                                                @endif
                                                @if($key == 'ach_fee')  
                                                <input class="ach-fee form-control" type="number" step="0.01" name="ach[{{ $payment->merchant_id}}][fees][{{$key}}]" value="{{ isset($payment->$key) ? $payment->$key : ''}}"  placeholder="{{ $fee_type }}" {{ $payment->disabled ? 'disabled' : ''}}  title="{{ $fee_type }}" min="0" readonly {{ isset($payment->$key) ? '' : 'hidden'}}>
                                                @endif
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if(empty($payments))
                                    <tr>
                                        <td colspan="5" class="text-center">No Data</td>
                                    </tr>
                                    @else
                                    <tr>
                                        {{-- <th></th> --}}
                                        <th></th>
                                        <th></th>
                                        <th>Total</th>
                                        <th><input class="total form-control" value="{{ $total_payment_amount }}" type="number" step="0.01" disabled></th>
                                        <th><input class="totalFee form-control" value="" type="number" step="0.01" disabled></th>
                                    </tr>
                                    <tr>
                                        <td colspan="5" align="center">
                                            <div class="ml-auto">
                                                <input type="button" onclick="submitForm(this.id);" formaction="{{ route('admin::payments::ach-payment.update') }}" value="Save" class="btn btn-primary" id="achSave" name="achSave" {{ $payment->disabled ? 'disabled' : ''}}>
                                                <input type="button" onclick="submitForm(this.id);" formaction="{{ route('admin::payments::ach-payment.store') }}"  value="Send Debit ACH" class="btn btn-success" name="achSubmit" id="achSubmit" >
                                                {{-- <input type="submit" value="Send Same Day Debit ACH" class="btn btn-primary"> --}}
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
    </div>
</div>
@stop


@section('scripts')
<script src="{{ url('/vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="text/javascript">
    var ach_fee_amounts = <?php echo json_encode($fee_amounts) ?>;
</script>
<script src="{{ url('/js/custom/addOrRemoveACHFields.js') }}"></script>
<script type="text/javascript">
    token = "<?= csrf_token() ?>"
    var url_changeAutoACHStatus = '{{route("admin::payments::ach-auto-status")}}';
    $(document).ready(function(){
        $('.datepicker').datepicker('setStartDate', new Date($('#date1').attr('min')))
    });
    // $("#select-all").click(function () {
    //     $(".confirmed-payment").prop('checked', $(this).prop('checked'));
    // });
    function submitForm(id) {
        //validate first
        if($("#formId").valid()){
            if(id=="achSave"){
                if(confirm('Do you really want to save ACH Payments?')){
                    document.getElementById('achSubmit').disabled = true;
                    var form =  document.getElementById('formId');
                    form.action = document.getElementById(id).getAttribute('formaction');
                    form.submit();
                    // document.getElementById('formId').submit();
                }
            }else if(id == "achSubmit") {
                if(confirm("Do you really want to send ACH Payments?")) {
                    document.getElementById('achSubmit').disabled = true;
                    var form =  document.getElementById('formId');
                    form.action = document.getElementById(id).getAttribute('formaction');
                    form.submit();
                }
            }
        }
        // if(confirm('Do you really want to continue?')) {
        //     document.getElementById('achSubmit').disabled = true;
        //     return true
        // }
        // return false
    }
    $(document).on('click', '.changeAutoACHStatus', function() {
        if(confirm('Are you sure you want to change Auto ACH Payment Status?'))
        {
            var merchant_id = $(this).attr('merchant_id');
            var data = {
                _token: token,
                merchant_id: merchant_id,
            };
            $.post(url_changeAutoACHStatus, data, function(response) {
                if (response.status == 0) {
                    Swal.fire('Error!', response.message, 'error');
                    return false;
                }
                Swal.fire('Success', response.message, 'success').then((value) => {
                    $('#date_filter').click()
                });
            }, "json");
        }
    });
    jQuery.extend(jQuery.validator.messages, {
        step: "Not more than 2 decimals are accepted.",
    })


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
    .select2-selection__rendered {
      display: inline !important;
    }
    .select2-search--inline {
      float: none !important;
    }
    input{
        border: solid 1px;
    }
    #formId tr td{
        position: relative;
    }
    #formId tr td > label.error{
        left: 6px;
        padding: 0 10px;
    }
    label.error{
        padding: 0 10px;
    }
</style>
<link href="{{ asset('/css/optimized/genarated_csv_pdf.css?ver=5') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/css/ach-payment.css?ver=1') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{ asset('/css/libs-font-awesome.min.css') }}">

@stop
