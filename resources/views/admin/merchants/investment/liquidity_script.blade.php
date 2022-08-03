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
		RejectedList.draw();
	});
	RejectedList = $('#RejectedList').DataTable({
		"processing" : true,
		"serverSide" : true,
		"fixedHeader": true,
		"searching"  : false,
		"bInfo"      : false,
		"paging"     : false,
		"ordering"   : true,
		"lengthMenu" : [[50, 100, 200, 1000], [50, 100, 200, 1000, ] ],
		"ajax": {
			"url": "<?= route('admin::merchants::Investment::LiquidityBased::RejectedList') ?>",
			"dataType": "json",
			"type": "POST",
			data: function(d) {
				d._token      = "{{csrf_token()}}";
				d.investors   = $("#investors").val();
				d.merchant_id = $("#merchant_id").val();
			},
		},
		"columns": [
			{ "data":"DT_RowIndex",'className':"text-right"},
			{ "data":"company"},
			{ "data":"Investor"},
			{ "data":"liquidity",'className':"text-right"},
			{ "data":"available_liquidity",'className':"text-right"},
			{ "data":"eligible",'className':"text-right"},
		],
		"footerCallback":function(t,o,a,l,m){
			var n=this.api(),o=RejectedList.ajax.json();
			$(n.column(3).footer()).html(o.liquidity);
			$(n.column(4).footer()).html(o.available_liquidity);
		},
	});
	$(document).on('click','.remove_investor',function(){
		var investor_id=$(this).attr('investor_id');
		 $('#investors :selected').each(function(i, selected){
           if($(this).val() == investor_id){
             $("select option[value='"+investor_id+"']").prop("selected", false);
           }
        });
		$('#investors').change();
	});
});
</script>
