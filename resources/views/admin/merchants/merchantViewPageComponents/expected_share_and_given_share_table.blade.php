<div class="row">
	<div class="col-md-12">
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped table-bordered" id="ExpectationVsGivenDataTable">
						<thead>
							<tr>
								<th>id</th>
								<th>Date</th>
								<th>Payment</th>
								<th>Expected</th>
								<th>Given</th>
								<th>Difference</th>
								<th>Single</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th colspan="2">Total</th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@section('scripts')
@parent
<script type="text/javascript">
var ExpectationVsGivenDataTable = $('#ExpectationVsGivenDataTable').DataTable({
	"processing" : true,
	"serverSide" : true,
	"fixedHeader": true,
	"searching"  : false,
	"lengthMenu" : [[50, 100, 200, 1000], [50, 100, 200, 1000, ] ],
	"order": [[ 1, "asc" ]],
	"ajax": {
		"url": "<?= route('Merchant::Payment::ExpectationVsGivenData::TableData',[$merchant->id]) ?>",
		"dataType": "json",
		"type": "POST",
		data: function(d) {
			d._token      = "<?= csrf_token() ?>";
			d.merchant_id = "{{$merchant->id}}";
		},
	},
	"columns": [
		{"data": "id",'className':"details-control"},
		{"data": "Date"},
		{"data": "Payment"   ,'className':"text-right"},
		{"data": "Expected"  ,'className':"text-right"},
		{"data": "Given"     ,'className':"text-right"},
		{"data": "Diffrence" ,'className':"text-right"},
		{"data": "single"    ,'visible':false},
	],
	"footerCallback":function(t,o,a,l,m){
		var n=this.api(),o=ExpectationVsGivenDataTable.ajax.json();
		$(n.column(1).footer()).html('Total');
		$(n.column(2).footer()).html(o.Total_Payment);
		$(n.column(3).footer()).html(o.Total_expected_participant_share);
		$(n.column(4).footer()).html(o.Total_participant_share);
		$(n.column(5).footer()).html(o.Total_Diffrence);
	},
});
function Single_format ( d ) {
	return d.single;
}
$("#ExpectationVsGivenDataTable tbody").on("click", "td.details-control", function () {
	let selected_tr  = $(this).closest("tr");
	let row = ExpectationVsGivenDataTable.row(selected_tr);
	if(row.child.isShown()){
		row.child.hide();
		selected_tr.removeClass("shown");
	} else {
		row.child(Single_format(row.data())).show();
		selected_tr.addClass("shown");
	}
	$("#ExpectationVsGivenDataTable tbody tr").each(function () {
		let tr = $(this).closest("tr");
		if(selected_tr[0]["_DT_RowIndex"]!=tr[0]["_DT_RowIndex"]){
			let row = ExpectationVsGivenDataTable.row( tr );
			if ( row.child.isShown() ) {
				row.child.hide();
				tr.removeClass("shown");
			}
		}
	});
});
</script>
@stop
