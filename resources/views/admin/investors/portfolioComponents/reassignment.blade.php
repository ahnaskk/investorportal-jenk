<div class="grid table-responsive">
	<table class="table table-list-search table-bordered" id="ReAssignmentTableDataTable" width="100%">
		<thead>
			<tr role="row">
				<th>#</th>
				<th>Merchant</th>
				<th>Amount</th>
				<th>Balance</th>
				<th>Liquidity Change</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="2"></th>
				<th>0</th>
				<th></th>
				<th>0</th>
			</tr>
		</tfoot>
	</table>
</div>
@section('scripts')
@parent
<script type="text/javascript">
var ReAssignmentTableDataTable = $('#ReAssignmentTableDataTable').DataTable({
	"processing" : true,
	"serverSide" : true,
	"fixedHeader": true,
	"searching"  : true,
	"lengthMenu" : [[50, 100, 200, 1000], [50, 100, 200, 1000, ] ],
	"ajax": {
		"url": "<?= route('admin::investors::portfolio::reassignment') ?>",
		"dataType": "json",
		"type": "POST",
		data: function(d) {
			d._token      = "<?= csrf_token() ?>";
			d.investor_id = "{{$investor_id}}";
		},
	},
	"columns": [
		{ "data": "merchant_id",'className':"text-right details-control", 'visible': true },
		{ "data":"merchant"},
		{ "data":"amount",'className':'text-right'},
		{ "data":"merchant_balance",'className':'text-right'},
		{ "data":"liquidity_change",'className':'text-right'},
	],
	"footerCallback":function(t,o,a,l,m){
		var n=this.api(),o=ReAssignmentTableDataTable.ajax.json();
		$(n.column(2).footer()).html(o.amount);
		// $(n.column(3).footer()).html(o.liquidity_change);
		$(n.column(4).footer()).html(o.liquidity_change);
	},
});
function Single_format ( d ) {
	return d.single;
}
$("#ReAssignmentTableDataTable tbody").on("click", "td.details-control", function () {
	let selected_tr  = $(this).closest("tr");
	let row = ReAssignmentTableDataTable.row(selected_tr);
	if(row.child.isShown()){
		row.child.hide();
		selected_tr.removeClass("shown");
	} else {
		row.child(Single_format(row.data())).show();
		selected_tr.addClass("shown");
	}
	$("#ReAssignmentTableDataTable tbody tr").each(function () {
		let tr = $(this).closest("tr");
		if(selected_tr[0]["_DT_RowIndex"]!=tr[0]["_DT_RowIndex"]){
			let row = ReAssignmentTableDataTable.row( tr );
			if ( row.child.isShown() ) {
				row.child.hide();
				tr.removeClass("shown");
			}
		}
	});
});
</script>
@stop
