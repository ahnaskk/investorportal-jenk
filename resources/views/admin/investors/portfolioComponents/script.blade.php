<script type="text/javascript">
(function($){"use strict";var fullHeight=function(){$('.js-fullheight').css('height',$(window).height());$(window).resize(function(){$('.js-fullheight').css('height',$(window).height());});};fullHeight();$('#sidebarCollapse').on('click',function(){$('#sidebar').toggleClass('active');});})(jQuery);
</script>
<script type="text/javascript">
$('#rtr-Area').click(function (){ $('.rtr-sub_area').toggle(); });
$('#OVERPAYMENT-Area').click(function (){ $('.OVERPAYMENT-sub_area').toggle(); });
$('#INVESTED-Area').click(function (){ $('.INVESTED-sub_area').toggle(); });
$('#DefaultRate-Area').click(function (){ $('.DefaultRate-sub_area').toggle(); });
$('#CTD-Area').click(function (){ $('.CTD-sub_area').toggle(); });
$('#PROJECTEDPORTFOLIOVALUE-Area').click(function (){ $('.PROJECTEDPORTFOLIOVALUE-sub_area').toggle(); });
$('#PRINCIPALINVESTMENT-Area').click(function (){ $('.PRINCIPALINVESTMENT-sub_area').toggle(); });
$('#CURRENTINVESTED-Area').click(function (){ $('.CURRENTINVESTED-sub_area').toggle(); });
$('#Profit-Area').click(function (){ $('.Profit-sub_area').toggle(); });
$('#PaidToDate-Area').click(function (){ $('.PaidToDate-sub_area').toggle(); });
$('#ANTICIPATEDRTR-Area').click(function (){ $('.ANTICIPATEDRTR-sub_area').toggle(); });
$('#ROI-Area').click(function (){ $('.ROI-sub_area').toggle(); });
</script>
<script type="text/javascript">
var MerchantDataTable = $('#MerchantDataTable').DataTable({
	"processing" : true,
	"serverSide" : true,
	"fixedHeader": true,
	"searching"  : true,
	"lengthMenu" : [[50, 100, 200, 1000], [50, 100, 200, 1000, ] ],
	"ajax": {
		"url": "<?= route('admin::investors::portfolio::merchants') ?>",
		"dataType": "json",
		"type": "POST",
		data: function(d) {
			d._token                      = "{{csrf_token()}}";
			d.investor_id                 = "{{$investor_id}}";
			d.sub_status_id               = $('#sub_status_id').val();
			d.label                       = $('#label').val();
			d.overpayment_status          = $('#overpayment_status').val();
			d.completed_percentage_value  = $('#completed_percentage_value').val();
			d.completed_percentage_option = $('#completed_percentage_option').val();
			d.exlcude_sub_status_id       = $('#exlcude_sub_status_id').is(":checked");
		},
	},
	"columns": [
		{"data": "id",'className':"text-right", 'visible': true },
		{ "data":"name"},
		{ "data":"date_funded"},
		{ "data":"merchant_balance",'className':'text-right','orderable': false},
		{ "data":"paid_count",'className':'text-right'},
		{ "data":"amount",'className':'text-right'},
		{ "data":"commission",'className':'text-right'},
		{ "data":"up_sell_commission_per",'className':'text-right'},
		{ "data":"factor_rate",'className':'text-right'},
		{ "data":"invest_rtr",'className':'text-right'},
		{ "data":"paid_participant_ishare",'className':'text-right'},
		{ "data":"balance",'className':'text-right'},
		{ "data":"annualized_rate",'className':'text-right'},
		{ "data":"complete_per",'className':'text-right'},
		{ "data":"sub_status_name"},
	],
	"footerCallback":function(t,o,a,l,m){
		var n=this.api(),o=MerchantDataTable.ajax.json();
		$(n.column(5).footer()).html(o.funded_total);
		$(n.column(6).footer()).html(o.commission_total);
		$(n.column(9).footer()).html(o.rtr_total);
		$(n.column(10).footer()).html(o.ctd_total);
		$(n.column(11).footer()).html(o.balance);
	},
});
$('#Merchant_apply_button').click(function(){
	MerchantDataTable.draw();
});
</script>
