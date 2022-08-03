<div class="row">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped table-bordered dataTable" id='ExpectedRTRAndGivenRTRDataTable'>
						<thead>
							<tr>
								<th>#</th>
								<th>Investor</th>
								<th class="text-right">Amount</th>
								<th class="text-right">RTR(Saved)</th>
								<th class="text-right">RTR(2)</th>
								<th class="text-right">Difference</th>
								<th class="text-right">RTR(4)</th>
								<th class="text-right">Difference</th>
							</tr>
						</thead>
						<tbody>
							<?php $expected_funded_amount=0; ?>
							<?php $expected_actual_rtr=0; ?>
							<?php $expected_rtr_2=0; ?>
							<?php $expected_rtr_4=0; ?>
							<?php $expected_diffrence1=0; ?>
							<?php $expected_diffrence2=0; ?>
							<?php foreach ($investor_data as $key => $investor): ?>
								<?php if($investor->amount) : ?>
									<?php $twortr  =round($investor->amount*$merchant->factor_rate,2); ?>
									<?php $fourRtr =round($investor->amount*$merchant->factor_rate,4); ?>
									<?php $diffrence1=$investor->invest_rtr-$twortr; ?>
									<?php $diffrence2=$fourRtr-$twortr; ?>
									<?php $expected_actual_rtr    +=$investor->invest_rtr; ?>
									<?php $expected_funded_amount +=$investor->amount; ?>
									<?php $expected_rtr_2         +=$twortr; ?>
									<?php $expected_rtr_4         +=$fourRtr; ?>
									<?php $expected_diffrence1    +=$diffrence1; ?>
									<?php $expected_diffrence2    +=$diffrence2; ?>
									<tr>
										<td>{{++$key}}</td>
										<td>
											<a target="_blank" href="{{URL::to('admin/investors/edit',$investor->user_id)}}">{{$investor->name}}</a>
										</td>
										<td class="text-right" for="Amount">{{FFM::dollar($investor->amount)}}</td>
										<td class="text-right" for="Amount">{{FFM::dollar($investor->invest_rtr)}}</td>
										<td class="text-right" for="RTR(2)">${{number_format($twortr,2)}} </td>
										<td class="text-right" for="Diffrence">${{number_format($diffrence1,2)}} </td>
										<td class="text-right" for="RTR(4)">${{number_format($fourRtr,4)}} </td>
										<td class="text-right" for="Diffrence">${{number_format($diffrence2,4)}} </td>
									</tr>
								<?php endif; ?>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<td class="text-right" colspan="2">Total</td>
								<td class="text-right" for="Amount">{{FFM::dollar($expected_funded_amount)}}</td>
								<td class="text-right" for="Amount">{{FFM::dollar($expected_actual_rtr)}}</td>
								<td class="text-right" for="RTR(2)">${{number_format($expected_rtr_2,2)}} </td>
								<td class="text-right" for="Diffrence">${{number_format($expected_diffrence1,2)}} </td>
								<td class="text-right" for="RTR(4)">${{number_format($expected_rtr_4,4)}} </td>
								<td class="text-right" for="Diffrence">${{number_format($expected_diffrence2,4)}} </td>
							</tr>
						</tfoot>
					</table>
					<div class="row">
						<div class="col-md-2">
						</div>
						<div class="col-md-4">
							<table class="table table-striped table-bordered dataTable">
								<thead>
									<tr>
										<th>Merchant RTR</th>
										<?php $merchant_rtr=$merchant->rtr*$syndication_percent/100; ?>
										<th>{{number_format($merchant_rtr,2)}}</th>
									</tr>
									<tr>
										<th>Investor RTR(2)</th>
										<th>{{number_format($expected_rtr_2,2)}}</th>
									</tr>
									<tr>
										<th>Difference</th>
										<?php $diffrence=$merchant_rtr-$expected_rtr_2; ?>
										<th>{{number_format($diffrence,2)}}</th>
									</tr>
								</thead>
							</table>
						</div>
						<div class="col-md-4">
							<table class="table table-striped table-bordered dataTable">
								<thead>
									<tr>
										<th>Merchant RTR</th>
										<th>{{number_format($merchant_rtr,2)}}</th>
									</tr>
									<tr>
										<th>Investor RTR(Saved)</th>
										<th>{{number_format($expected_actual_rtr,2)}}</th>
									</tr>
									<tr>
										<th>Difference</th>
										<?php $diffrence=$merchant_rtr-$expected_actual_rtr; ?>
										<th>{{number_format($diffrence,2)}}</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2">
						</div>
						<div class="col-md-4"> <br>
							<div class="input-group">
								<a href="{{route('PennyAdjustment::AdjustInvestorRtrId',['id'=>$merchant->id])}}" class="btn btn-info">Adjust highest Investor RTR</a>
							</div>
							<span class="help-block">Adjust highest Investor RTR</span>
						</div>
						<div class="col-md-4"> <br>
							<div class="input-group">
		                        <a href="{{route('PennyAdjustment::UpdateInvestorRtrId',['id'=>$merchant->id])}}" class="btn btn-success">Set Actual Investor RTR</a>
		                    </div>
		                    <span class="help-block">Set Investor RTR =Funded Amount * Factor Rate </span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@section('scripts')
@parent
<script type="text/javascript">
var ExpectedRTRAndGivenRTRDataTable = $('#ExpectedRTRAndGivenRTRDataTable').DataTable({
	"searching" : false,
	"paging"    : false,
	"bInfo"     : false,
});
</script>
@stop
