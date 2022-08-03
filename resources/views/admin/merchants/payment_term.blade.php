@extends('layouts.admin.admin_lte')

@section('content')
<div class="inner admin-dsh header-tp">

    <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ $page_title }}</h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">{{ $page_title }}</div>     
        </a>
      
</div>

{{ Breadcrumbs::render('Payment_term',$merchants) }}

<div class="col-md-12">
    <!-- general form elements -->
    <div class="box box-primary box-wrap">
        
		<div class="box-head ">
			@include('layouts.admin.partials.lte_alerts')
		</div>
         <div class="box-body">

            <div class="heading">
                		<div class="left">
		            		<h4>Merchant Details</h4>
		            	</div>
	            		<div class="right">
		            		<a class="btn btn-xs btn-primary" href="{{route('admin::merchants::view',['id'=>$merchant['merchant_id']])}}">View</a>
		                </div>
	            	</div>
            
            <div class="row">
                

                <div class="col-md-12 merchant-dash">
                	<div class="row">
                		<div class="col-md-3 dash-wrap">
	                		<div class="dash-card">
		                        <div class="title">Merchant:</div>
		                        <div class="value" title="{{ $merchant['name'] }}">{{ $merchant['name'] }}</div>
		                    </div>
	                    </div>
	                    <div class="col-md-3 dash-wrap">
	                		<div class="dash-card">
		                        <div class="title">First Payment:</div>
		                        <div class="value">{{ $merchant['first_payment'] }}</div>
		                    </div>
	                    </div>
	                    <div class="col-md-3 dash-wrap">
	                		<div class="dash-card">
		                        <div class="title">Last Payment:</div>
		                        <div class="value">{{ $merchant['last_payment_date'] }}</div>
		                    </div>
	                    </div>
	                    <div class="col-md-3 dash-wrap">
	                		<div class="dash-card">
		                        <div class="title">Payment End Date:</div>
		                        <div class="value">{{ $merchant['payment_end_date'] }}</div>
		                    </div>
	                    </div>
	                    <div class="col-md-3 dash-wrap">
	                		<div class="dash-card">
		                        <div class="title">Balance:</div>
		                        <div class="value">{{ \FFM::dollar($merchant['balance']) }}</div>
		                    </div>
	                    </div>
						
	                    <div class="col-md-3 dash-wrap">
                		<div class="dash-card">
	                        <div class="title">Future ACH payments:</div>		                        
	                        <div class="value">
								{{ \FFM::dollar($merchant['future_payment_total']) }}
			                    </div>
	                        </div>
						</div>
						
	                    <div class="col-md-3 dash-wrap">
                		<div class="dash-card">
	                        <div class="title">Processing ACH payments:</div>		                        
	                        <div class="value">
								{{ \FFM::dollar($merchant['processing_payment_total']) }}
			                    </div>
	                        </div>
	                    </div>

	                    <div class="col-md-3 dash-wrap">
                		<div class="dash-card">
	                        <div class="title">Anticipated ACH balance:</div>		                        
	                        <div class="value">
								{{ \FFM::dollar($merchant['anticipated_balance']) }}
			                    </div>
	                        </div>
	                    </div>

	                    <div class="col-md-3 dash-wrap">
                		<div class="dash-card">
	                        <div class="title">Payment amount:</div>		                        
	                        <div class="value">
								{{ \FFM::dollar($merchant['payment_amount']) }}
			                    </div>
	                        </div>
	                    </div>

	                    <div class="col-md-3 dash-wrap">
                		<div class="dash-card">
	                        <div class="title">Advance type:</div>		                        
	                        <div class="value">
								{{ $merchant['advance_type'] }}
			                    </div>
	                        </div>
	                    </div>

	                    <div class="col-md-3 dash-wrap">
                		<div class="dash-card">
	                        <div class="title">Merchant Status:</div>		                        
	                        <div class="value">
								{{ $merchant['sub_status'] }}
			                    </div>
	                        </div>
						</div>

	                    <div class="col-md-3 dash-wrap">
                		<div class="dash-card">
	                        <div class="title">ACH Status:</div>		                        
	                        <div class="value">
								{{ $merchant['status'] }}
			                    </div>
	                        </div>
						</div>
	               
	                    @if($merchant['payment_pause'])
	                    <div class="col-md-3 dash-wrap">
	                		<div class="dash-card">
		                        <div class="title">Paused at:</div>
		                        <div class="value">{{\FFM::datetime($merchant['payment_pause']->paused_at)}}</div>				                    
	                		</div>
	                	</div>

	                	<div class="col-md-3 dash-wrap">
	                		<div class="dash-card">
		                        <div class="title">Paused By:</div>
		                        <div class="value">{{$merchant['payment_pause']->paused_by}}</div>
	                		</div>
	                	</div>
	                	@endif

                    </div>	
                </div>


            </div>
			<label class="errors_msg error-msg-style" title="ACH status will be active only if merchant's status is Active Advance, Collections, Merchant in collections, Others, Partial Payment and Payment Modified and automatic ACH will be sent up to zero balance considering processing ACH payments.">
				ACH Payment sent only if ACH status is Active.
				@if($merchant['makeup_payments'] && $active)
				<br>
				Add {{$merchant['makeup_payments']}} more payments to achieve 0 balance.
				@endif
			</label>
            <hr>
            
            <div class="row">            	

                <div class="col-md-12">

                	<div class="heading">
                		<div class="left">
		            		<h4>Terms</h4>
		            	</div>
						@if($active)
	            		<div class="right">
							@if(count($old_terms))
							@if(!$merchant['payment_paused'])
							<button id="pause-payment" class="btn btn-warning up-load" click> Pause ACH</button>
							@endif
							@if($merchant['payment_paused'])
							<button id="resume-payment" class="btn btn-success up-load"> Resume ACH</button>
							@endif
							@endif
		            		<a class="btn btn-xs btn-success" href="{{route('admin::merchants::payment-terms-create',['mid'=>$merchant['merchant_id']])}}">
		                        Add Term
		                    </a>
		                </div>
						@endif
	            	</div>
	            	<div class="table-wrap-desgin">  
	                    <table class="table" id="paymentTable">
	                        <thead>
	                            <tr>
	                                <th>#</th>
	                                <th>Payment Amount</th>
	                                <th>Payments</th>
	                                <th>Advance Type</th>
	                                <th>ACH Paid Payments</th>
	                                {{-- <th>Payments Left</th> --}}
	                                <th>ACH Payments Left</th>
	                                <th>Start Date</th>
	                                <th>End Date</th>
	                                <th><i class="fa fa-edit"></i></th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                            @foreach($old_terms as $key => $term)
	                            <tr
	                                style="{{ ($term->current_term) ? (($merchant['payment_paused']) ? 'background-color: #ff3434; color: #000;' : 'background-color: #c3e6cb; color: #000;') : '' }}"
	                            >
	                                <td valign="middle">{{ ++$key }}</td>
	                                <td valign="middle">{{ \FFM::dollar($term->payment_amount) }}</td>
	                                <td valign="middle">{{ $term->pmnts }}</td>
	                                <td valign="middle">{{ $advance_types[$term->advance_type] }}</td>
	                                <td valign="middle">{{ $term->pmnts - $term->payment_left }}</td>
	                                {{-- <td valign="middle">{{ $term->payment_left }}</td> --}}
	                                <td valign="middle">{{ $term->actual_payment_left }}</td>
	                                <td valign="middle">{{ \FFM::date($term->start_at) }}</td>
	                                <td valign="middle">{{ \FFM::date($term->end_at) }}</td>
	                                <td>
	                                    <a href="#" class="help-link">
	                                        <i class="fa fa-question-circle" aria-hidden="true"></i>
											<div class="tool-tip">
												Created at: {{ \FFM::datetime($term->created_at) }}{{$term->created_by ? (' By: '.$term->created_by) : '' }}
												@if($term->updated_by)
												<br>
												Updated at: {{ \FFM::datetime($term->updated_at) }}{{$term->updated_by ? (' By: '.$term->updated_by) : '' }}
												@endif
											</div>
	                                    </a>
										@if($active && $term->is_active)
	                                    @if($term->payment_started)
	                                    {{-- <a href="{{route('admin::merchants::payment-terms-edit',['mid'=>$merchant['merchant_id'], 'id'=>$term->id])}}">
	                                        <button type="" class="btn btn-xs btn-primary">
	                                            <i class="glyphicon glyphicon-edit"></i>
	                                        </button>
	                                    </a> --}}
	                                    @else
	                                        {{-- <a href="{{route('admin::merchants::payment-terms-edit',['mid'=>$merchant['merchant_id'], 'id'=>$term->id])}}">
	                                            <button type="" class="btn btn-xs btn-primary">
	                                                <i class="glyphicon glyphicon-edit"></i>
	                                            </button>
	                                        </a> --}}
	                                        <form action="{{route('admin::merchants::payment-terms-delete',['mid'=>$merchant['merchant_id'], 'id'=>$term->id])}}" method="POST" style="display:inline">
	                                            @csrf
	                                            <button type="submit" class="btn btn-xs btn-danger" onclick='return confirm("Are you sure that you want to delete this term?")'>
	                                                <i class="glyphicon glyphicon-trash"></i>
	                                            </button>
	                                        </form>
	                                    @endif
	                                    @endif
	                                </td>
	                            </tr>
	                            @endforeach
	                        </tbody>
	                    </table>
	                </div>
                </div>
            </div>

            <hr>
            
            <div class="row">                
                
                <div class="col-md-12">

                	<div class="heading">
                		<div class="left">
		            		<h4>ACH Schedule Of Payments</h4>
		            	</div>
						<div class="right">
							@if($merchant['makeup_payments'] && $active)
							<a data-bs-toggle="modal" data-bs-target="#AddPaymentModal">
								<button type="" class="btn btn-xs btn-success"  title="Add New Payment">
									<i class="glyphicon glyphicon-plus"></i>
								</button>
							</a>
							@endif
						</div>
		            </div>

                	<div class="table-wrap-desgin">  
	                    <table class="table">
	                        <thead>
	                            <tr>
									<th>
										@if($active)
										
										<label class="chc">
											<input type="checkbox" id="select-all-ach">
											<span class="checkmark"></span>
										</label>
										@endif
									</th>
	                                <th>#</th>
	                                <th>Payment Date</th>
	                                <th>Amount</th>
	                                <th>Status</th>
	                                <th>Payment Recieved</th>
	                                <th>Advance Type</th>
	                                <th></th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                            @foreach($term_payments as $key => $payment)
	                            <tr style="{{$payment->ach_style}}">
									<td>
										@if($payment->editable)
										<label class="chc">
											<input type='checkbox' class='delete-payment' name='ach_ids[]' data-tid="{{$payment->term_id}}" value='{{$payment->id}}'>
											<span class="checkmark"></span>
										</label>
										@endif
									</td>
	                                <td>{{ ++$key }}</td>
	                                <td>{{ $payment->payment_date }}</td>
	                                <td>
										{{ $payment->payment_amount }}
									</td>
	                                <td>{{ $payment->status_type }}</td>
	                                <td>{{ $payment->total_payments }}</td>
	                                <td>{{ $payment->advance_type }}</td>
	                                <td>
										@if($payment->editable)
											<a data-bs-toggle="modal" data-bs-target="#paymentModal" data-date={{ $payment->payment_date }} data-amount="{{ $payment->payment_amount_actual }}" data-payment-id="{{ $payment->id }}" data-editable="{{ $payment->editable }}">
												<button type="" class="btn btn-xs btn-primary">
													<i class="glyphicon glyphicon-edit"></i>
												</button>
											</a>
											<form action="{{route('admin::merchants::payment-terms-delete-payment',['mid'=>$merchant['merchant_id'], 'tid'=>$payment->term_id, 'id'=>$payment->id])}}" method="POST" style="display:inline">
	                                            @csrf
	                                            <button type="submit" class="btn btn-xs btn-danger" title="Delete Payment" onclick='return confirm("Are you sure that you want to delete this Payment?")'>
	                                                <i class="glyphicon glyphicon-trash"></i>
	                                            </button>
	                                        </form>
										@endif
									</td>
	                            </tr>
	                            @endforeach

								<tr>
									@if($active)
									<td colspan="2">
										<button type="button" class="btn btn-xs btn-danger"  title="Delete Selected ACH Payment" id="DeleteMultipleACH">
											Delete Selected
										</button>
									</td>
									<td colspan="6"></td>
									@endif
								</tr>
								<!-- 	<td colspan="6"></td>
									<td>
										<button class="btn btn-xs btn-success" id="MakeupButton" title="Add term to make up balance to zero">
											<i class="glyphicon glyphicon-plus"></i>
										</button>
									</td> -->
	                        </tbody>
	                    </table>
	                </div>
                </div>
			</div>
			<hr>
        <!-- /.box-body -->
        </div>
    <!-- /.box -->
    </div>  
<!-- /.col -->
</div>


@if($active)   
<div class="modal fade bd-example-modal-xl" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
	<div class="modal-dialog modal-xl">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title text-capitalize" id="paymentModalLabel">Update term Payment</h5>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		  </button>
		</div>
		<div class="modal-body">
			<form action="{{route('admin::merchants::payment-terms-update-payment',['mid'=>$merchant['merchant_id']])}}" method="POST" id="paymentModalForm">
				@csrf
				<input hidden name="payment_id" id="paymentModalId">
				<input type="number" step="0.01" min="0" class="form-control" value="" name="payment_amount" id="paymentModalValue" required>
				<button  class="btn btn-primary" type="submit" >Update</button>
			</form>
		</div>
	  </div>
	</div>
</div>
@if($merchant['makeup_payments'])
<div class="modal fade bd-example-modal-xl" id="AddPaymentModal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
	<div class="modal-dialog modal-xl">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title text-capitalize" id="paymentModalLabel">Add New Payment</h5>
		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
		  </button>
		</div>
		<div class="modal-body">
			<div id="AddPaymentError"></div>

			<form id="AddPaymentModalForm">
                <div class="form-group">
                    <label for="AddPaymentModalPaymentAmount">Payment Amount: <span class="validate_star">*</span></label>
					<input type="number" class="form-control" step="0.01" value="{{ $merchant['payment_amount'] }}" name="payment_amount" id="AddPaymentModalPaymentAmount" required>
				</div>
                <div class="form-group">
                    <label for="AddPaymentModalPaymentDate1">Payment Date: <span class="validate_star">*</span></label>
					<input type="text" autocomplete="off" class="form-control datepicker" name="payment_date1" id="AddPaymentModalPaymentDate1" required min="{{ $next_working_day }}">
                    <input type="hidden" name="payment_date" class="date_parse" id="AddPaymentModalPaymentDate" required>
                    <label id="Date-holiday" class="errors_msg" for="AddPaymentModalPaymentDate1" style="display: none">Selected date is a holiday, Please select another date.</label>
                    
				</div>
				<button  class="btn btn-primary" type="button" id="AddPaymentButton">Add</button>
			</form>
		</div>
	  </div>
	</div>
</div>
@endif
@endif

@stop

@section('scripts')
<script src="{{asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{ asset('/js/jquery-mask.min.js') }}"></script>

<script>
    var _token = '{{csrf_token()}}';
    var balance = '{{$merchant['balance']}}'
    var URL_CheckDate = "{{ URL::to('admin/merchants/terms/date') }}";
	var merchant_id="{{ $merchant['merchant_id'] }}";
	var URL_resumePayment="{{ route('admin::merchants::payments.resume') }}";
	var URL_pausePayment="{{ route('admin::merchants::payments.pause') }}";
	var paused="{{ $merchant['payment_pause'] }}";
	var makeupPayments = "{{ $merchant['makeup_payments'] }}";
	var URL_makeupTerm="{{ route('admin::merchants::payment-terms-makeup') }}";
	var URL_AddPayment="{{route('admin::merchants::payment-terms-add-payment',['mid'=>$merchant['merchant_id']])}}";
	var URL_DeletePayments="{{route('admin::merchants::payment-terms-delete-payments',['mid'=>$merchant['merchant_id']])}}";
    
	var holidays = @json($holidays);
	var default_date_format = "{{FFM::defaultDateFormat('format')}}";
	var holidays_array = holidays.map(function(date){
		return moment(date, 'YYYY-MM-DD').format(default_date_format);
	});

    $(document).ready(function () {

		$("#paymentTable").find(".help-link").each(function(){
			let thisDiv = $(this).parent("td")
			let thisHeight = $(this).find(".tool-tip").outerHeight(true)
			if( thisHeight > thisDiv.outerHeight(true) ){
			$(this).parents("tr").children("td").each(function(){
				$(this).css("height",(thisHeight+20)+"px")
			})
			}
		})

		$('#pause-payment').on('click', function() {
			if(confirm('Do you really want to Pause payments?'))
			{
			if(!paused){
				console.log('hi')
			$.ajax({
				type:'POST',
				data: {'_token': _token,'merchant_id':merchant_id},
				url:URL_pausePayment,
				success:function(data)
				{
					if (data.status == 1) {
					$('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
					setTimeout(function() {
							location.reload();
					}, 2500);
					}else
					{
						$('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
					}
				}
			})

			}
			
			}
		})
		$('#resume-payment').on('click', function() {
			if(confirm('Do you really want to Resume payments?'))
			{
			if(paused){
			$.ajax({
				type:'POST',
				data: {'_token': _token,'merchant_id':merchant_id},
				url:URL_resumePayment,
				success:function(data)
				{
					if (data.status == 1) {
					$('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
					setTimeout(function() {
							location.reload();
					}, 2500);
					}else
					{
						$('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
					}
				}
			})

			}
			}
		})

		$('#paymentModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget) // Button that triggered the modal
			var payment = button.data('amount') // Extract info from data-* attributes
			var payment_date = button.data('date') // Extract info from data-* attributes
			var payment_id = button.data('payment-id') // Extract info from data-* attributes
			var editable = button.data('editable') // Extract info from data-* attributes
			$('#paymentModalValue').val(payment)
			$('input[name=payment_id]').val(payment_id);
			var modal = $(this)
			modal.find('.modal-title').text('Update payment of ' + payment_date)

		})

		$('#MakeupButton').on('click', function() {
			if (makeupPayments) {
				if (confirm('Do you really want add new term to makeup balance?')) {
					$.ajax({
						type:'POST',
						data: {'_token': _token,'merchant_id':merchant_id},
						url:URL_makeupTerm,
						success:function(data)
						{
							if (data.status == 1) {
								$('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
								alert(data.msg)
								location.reload();
							}else {
								$('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
							}
						},
						error: function(xhr, statusText) {
							$('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + statusText + '</div>');
						}
					})
				}
			}
		})


		$('#AddPaymentButton').on('click', function(e) {
			e.preventDefault()
			$("#AddPaymentModalPaymentAmount").valid();
			$("#AddPaymentModalPaymentDate1").valid();
			paymentAmount = $('#AddPaymentModalPaymentAmount').val()
			paymentDate = $('#AddPaymentModalPaymentDate').val()
			if(paymentDate && paymentAmount) {
				$.ajax({
					type:'POST',
					data: {
						'_token': _token,
						'merchant_id':merchant_id,
						'payment_date':paymentDate,
						'payment_amount':paymentAmount
						},
					url:URL_AddPayment,
					success:function(data)
					{
						if (data.status == 1) {
							$('#AddPaymentError').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
							setTimeout(function() {
								location.reload();
							}, 2500);

						}else{
							$('#AddPaymentError').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
						}
					}
				})
			}
		})
		$('#AddPaymentModalPaymentDate1').datepicker("setDatesDisabled", holidays_array).datepicker('setDaysOfWeekDisabled', [0, 6]);
        $('#AddPaymentModalPaymentDate1').datepicker('setStartDate', new Date($('#AddPaymentModalPaymentDate1').attr('min')))
		$("#select-all-ach").click(function () {
        	$(".delete-payment").prop('checked', $(this).prop('checked'));
    	});
		$("#DeleteMultipleACH").click(function () {
			if (confirm('Do you really want delete selected ACH Schedules?')) {
				var toDelete = []
				$(".delete-payment:checked").each(function(a,index){
					element = {
						'id':index.value,
						'tid':index.getAttribute('data-tid'),
					}
					toDelete.push(element)
				});
				if (toDelete.length > 0) {
					$.ajax({
						type:'POST',
						data: {'_token': _token,'ach_ids':toDelete},
						url:URL_DeletePayments,
						success:function(data)
						{
							if (data.status == 1) {
								$('.box-head').html('<div class="alert alert-info alert-dismissable col-ssm-12" >' + data.msg + '</div>');
								alert(data.msg)
								location.reload();
							}else {
								$('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + data.msg + '</div>');
								alert(data.msg)
							}
						},
						error: function(xhr, statusText) {
							$('.box-head').html('<div class="alert alert-danger alert-dismissable col-ssm-12" >' + statusText + '</div>');
						}
					})
				} else {
					alert('No ACH Schedule selected to delete.')
				}

			}
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
   li.breadcrumb-item.active{
      color: #2b1871!important;
   }
   li.breadcrumb-item a{
      color: #6B778C;
   }
</style>
@stop