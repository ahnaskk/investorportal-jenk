<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Builder;

class ReportController extends Controller
{
    public function general(Request $request, Builder $tableBuilder)
    {
        $userId = $request->user()->id;
        $page_title = 'General Report';
        if ($request->ajax() || $request->wantsJson()) {
            return \MTB::generalReport($request->start_date, $request->end_date, $request->merchant_id);
        }
        $tableBuilder->ajax(['url' => route('investor::report::general'), 'data' => 'function(d){ d.start_date = $("#date_start").val(); d.end_date = $("#date_end").val();d.merchant_id = $("#merchant_id").val();}']);
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(4).footer()).html(o.total_debited),$(n.column(5).footer()).html(o.total_company),$(n.column(7).footer()).html(o.total_syndicate),$(n.column(6).footer()).html(o.total_mgmnt),$(n.column(8).footer()).html(o.total_pricipal),$(n.column(9).footer()).html(o.total_profit),$(n.column(13).footer()).html(o.total_participant_rtr),$(n.column(14).footer()).html(o.total_particaipant_rtr_balance)}', 'order' => [[2, 'desc']]]);
        $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'data' => null, 'defaultContent' => '', 'title' => ''], ['data' => 'name', 'name' => 'name', 'title' => 'Merchant'], ['data' => 'date_funded', 'name' => 'date_funded', 'defaultContent' => '', 'title' => 'Funded Date '], ['orderable' => false, 'data' => 'id', 'name' => 'id', 'defaultContent' => '', 'title' => 'Merchant Id'], ['orderable' => true, 'data' => 'TOTAL_DEBITED', 'name' => 'TOTAL_DEBITED', 'defaultContent' => '', 'title' => 'Debited'], ['orderable' => false, 'data' => 'TOTAL_COMPANY', 'name' => 'TOTAL_COMPANY', 'defaultContent' => '', 'title' => 'Total Payments'], ['orderable' => false, 'data' => 'TOTAL_MGMNT_FEE', 'name' => 'TOTAL_MGMNT_FEE', 'defaultContent' => '', 'title' => 'Management Fee'], ['orderable' => false, 'data' => 'TOTAL_SYNDICATE', 'name' => 'TOTAL_SYNDICATE', 'defaultContent' => '', 'title' => 'Net amount'], ['orderable' => false, 'data' => 'principal', 'name' => 'principal', 'defaultContent' => '', 'title' => 'Principal'], ['orderable' => false, 'data' => 'profit', 'name' => 'profit', 'defaultContent' => '', 'title' => 'Profit'], ['orderable' => false, 'data' => 'last_rcode', 'name' => 'last_rcode', 'defaultContent' => '', 'title' => 'Last Rcode'], ['orderable' => false, 'data' => 'last_payment_date', 'name' => 'last_payment_date', 'defaultContent' => '', 'title' => 'Last Payment Date'], ['orderable' => false, 'data' => 'last_payment_amount', 'name' => 'last_payment_amount', 'defaultContent' => '', 'title' => 'Last Payment Amount'], ['orderable' => false, 'data' => 'participant_rtr', 'name' => 'participant_rtr', 'defaultContent' => '', 'title' => 'Participant RTR'], ['orderable' => false, 'data' => 'participant_rtr_balance', 'name' => 'participant_rtr_balance', 'defaultContent' => '', 'title' => 'Participant RTR Balance']]);
        $merchants = Merchant::whereHas('investments', function ($q) use ($userId) {
            $q->where('merchant_user.user_id', '=', $userId);
        })->where('active_status', 1)->pluck('name', 'id');

        return view('investor.report.index', compact('tableBuilder', 'page_title', 'merchants'));
    }
}
