<div class="row">
	<div class="col-md-3">
		<span class="help-block">
			<div class="col-md-8">
				Zero Payment
			</div>
			<div class="col-md-4">
				Exclude {{ Form::checkbox('exlcude_zero_payment',1,false,['id'=>'exlcude_zero_payment','class'=>'']) }}
			</div>
		</span>
	</div>
</div>
<div class="grid table-responsive">
	<table class="table table-list-search table-bordered" id="PaymentTableDataTable" width="100%">
		<thead>
			<tr role="row">
				<th>#</th>
				<th>Merchant</th>
				<th>Participant Share</th>
				<th>Management Fee</th>
				<th>Net Amount</th>
				<th>Overpayment</th>
				<th>Principal</th>
				<th>Profit</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="2">Total</th>
				<th>0</th>
				<th>0</th>
				<th>0</th>
				<th>0</th>
				<th>0</th>
				<th>0</th>
			</tr>
		</tfoot>
	</table>
</div>
@section('scripts')
@parent
<script type="text/javascript">
var PaymentTableDataTable = $('#PaymentTableDataTable').DataTable({
	"processing" : true,
	"serverSide" : true,
	"fixedHeader": true,
	"searching"  : true,
	"lengthMenu" : [[50, 100, 200, 1000], [50, 100, 200, 1000, ] ],
	"ajax": {
		"url": "<?= route('admin::investors::portfolio::payments') ?>",
		"dataType": "json",
		"type": "POST",
		data: function(d) {
			d._token               = "{{csrf_token()}}";
			d.investor_id          = "{{$investor_id}}";
			d.exlcude_zero_payment = $('#exlcude_zero_payment').is(":checked");
		},
	},
	"columns": [
		{"data": "id",'className':"text-right details-control", 'visible': true },
		{ "data":"name"},
		{ "data":"participant_share",'className':'text-right'},
		{ "data":"mgmnt_fee",'className':'text-right'},
		{ "data":"net_amount",'className':'text-right'},
		{ "data":"overpayment",'className':'text-right'},
		{ "data":"principal",'className':'text-right'},
		{ "data":"profit",'className':'text-right'},
	],
	"footerCallback":function(t,o,a,l,m){
		var n=this.api(),o=PaymentTableDataTable.ajax.json();
		$(n.column(2).footer()).html(o.participant_share);
		$(n.column(3).footer()).html(o.mgmnt_fee);
		$(n.column(4).footer()).html(o.net_amount);
		$(n.column(5).footer()).html(o.overpayment);
		$(n.column(6).footer()).html(o.principal);
		$(n.column(7).footer()).html(o.profit);
	},
});
function Single_format ( d ) {
	return d.single;
}
$("#PaymentTableDataTable tbody").on("click", "td.details-control", function () {
	let selected_tr  = $(this).closest("tr");
	let row = PaymentTableDataTable.row(selected_tr);
	if(row.child.isShown()){
		row.child.hide();
		selected_tr.removeClass("shown");
	} else {
		row.child(Single_format(row.data())).show();
		selected_tr.addClass("shown");
	}
	$("#PaymentTableDataTable tbody tr").each(function () {
		let tr = $(this).closest("tr");
		if(selected_tr[0]["_DT_RowIndex"]!=tr[0]["_DT_RowIndex"]){
			let row = PaymentTableDataTable.row( tr );
			if ( row.child.isShown() ) {
				row.child.hide();
				tr.removeClass("shown");
			}
		}
	});
});
</script>
@stop
