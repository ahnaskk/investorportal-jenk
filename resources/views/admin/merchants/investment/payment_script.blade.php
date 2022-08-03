<script>
var URL_getInvestor = "{{ URL::to('admin/getCompanyWiseInvestors') }}";
var table = window.LaravelDataTables["dataTableBuilder"];
$(document).ready(function () {
	$(document).on('submit', 'form', function() {
		$(this).find('button:submit, input:submit').attr('disabled', 'disabled');
	});
	$("#unselect").click(function(e){
		$('#investors').val('').change();
		$('#company').val('').trigger("change.select2");
	});
	$('#company').change(function(e) {
		var company=$('#company').val();
		var investors = [];
		if(company) {
			$.ajax({
				type: 'POST',
				data: {
					'company'  : company,
					'_token'   : _token
				},
				url: URL_getInvestor,
				success: function (data) {
					var result=data.items;
					for(var i in result) {
						investors.push(result[i].id);
					}
					$('#investors').attr('selected','selected').val(investors).change();
				},
				error: function (data) {
				}
			});
		} else {
			$('#investors').attr('selected','selected').val('').change();
		}
	});
	$('#select_all').click(function() {
		$('#company').val(0).change();
	});
	$('#investors').change(function(e) {
		table.draw();
		CompanyShareDataTable.draw();
	});
	$('.from_date1,.to_date1').change(function(e) {
		table.draw();
		CompanyShareDataTable.draw();
	});
});
var CompanyShareDataTable = $('#CompanyShareDataTable').DataTable({
	"processing" : true,
	"serverSide" : true,
	"fixedHeader": true,
	"searching"  : false,
	"bInfo"      : false,
	"paging"     : false,
	"ordering"   : true,
	"lengthMenu" : [[50, 100, 200, 1000], [50, 100, 200, 1000, ] ],
	"ajax": {
		"url": "<?= route('admin::merchants::Investment::PaymentBased::CompanyShare') ?>",
		"dataType": "json",
		"type": "POST",
		data: function(d) {
			d._token      = "{{csrf_token()}}";
			d.investors   = $("#investors").val();
			d.merchant_id = $("#merchant_id").val();
			d.from_date   = $("#date_start").val();
			d.end_date    = $("#date_end").val();
		},
	},
	"columns": [
		{ "data":"company",'width':"50%"},
		{ "data":"max_participant_percentage",'className':'text-right'},
		{ "data":"max_participant",'className':'text-right'},
	],
	"footerCallback":function(t,o,a,l,m){
		var n=this.api(),o=CompanyShareDataTable.ajax.json();
		$(n.column(2).footer()).html(o.max_participant);
	},
});
</script>
