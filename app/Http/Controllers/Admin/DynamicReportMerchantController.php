<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicReportRequest;
use App\Merchant;
use App\MerchantCRMDetails;
use App\Models\DynamicReport;
use App\Models\DynamicReportMerchant;
use App\Models\Logs;
use App\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder;
use FFM;

class DynamicReportMerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Builder $tableBuilder)
    {
        
        $page_title = 'Merchant Dynamic Reports';
        $tableBuilder->ajax(route('admin::dynamic-report-investor.data'));
        $tableBuilder->parameters([
            'responsive' => true,
            'autoWidth' => false,
            'processing' => true,
            'aaSorting' => [],
            'pagingType' => 'input',
            'serverSide' => false,
            'stateSave' => true,
            "pageLength" =>100,
        ]);
        $tableBuilder = $tableBuilder->columns([
            [
                'data' => 'id',
                'name' => 'id',
                'title' => '#',
                'searchable' => false,
                'orderable' => false,
            ],
            ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
            ['data' => 'description', 'name' => 'description', 'title' => 'Description'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created at'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ]);
       
        $all_fields= DynamicReport::merchant_fields();
        $investors = User::where('id','!=',1)->pluck('name', 'id');
        $merchant_fields = DynamicReport::investor_fields();

        return view('admin.dynamic-reports.merchant', compact('page_title', 'tableBuilder', 'all_fields','investors'));
    }

    public function rowData()
    {
        $data = DynamicReport::all();
        $data = $data->toArray();
        session_set('all_users', User::select('id', 'name')->get()->getDictionary());

        return \DataTables::collection($data)->editColumn('name', function ($data) {
            return '<a href="'.route('admin::dynamic-report-investor.show', $data['id']).'">'.$data['name'].'</a>';
        })->addColumn('action', function ($data) {
            $return = '';
            $return .= '<a class="btn btn-xs btn-primary" href="'.route('admin::dynamic-report-investor.show', $data['id']).'"> <i class="glyphicon glyphicon-list-alt"></i>  </a>';

            //$return .= '<a href="'.route('admin::template::edit', ['id' => $data['id']]).'" class="btn btn-xs btn-secondary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
            $return .= '<a data-bs-toggle="modal" data-bs-target="#editReport" data-id="'.$data['id'].'" class="btn btn-xs btn-secondary edit_report"><i class="glyphicon glyphicon-edit"></i> </a>';

            /*$return .= '<form method="POST" action="'.route('admin::dynamic-report.destroy',$data['id']).'" accept-charset="UTF-8" style="display:inline">
	                                         '.method_field('DELETE').csrf_field().'<button type="submit" class="btn btn-xs btn-danger" title="Delete" onclick="return confirm(&quot;Are you sure want to delete ?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
											</form>';*/
            $return .= '<a data-id="'.$data['id'].'" class="btn btn-xs btn-danger delete_report"><i class="glyphicon glyphicon-trash"></i> </a>';

            return $return;
        })->editColumn('created_at', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data['created_at']).' by '.get_user_name_with_session($data['created_by']);

            return "<a title='$created_date'>".FFM::datetime($data['created_at']).'</a>';
        })->rawColumns(['name', 'action', 'created_at'])->make(true);
    }

    public function index2()
    {

        //return DynamicReportMerchant::orderBy('payment_date', 'desc')->limit(10)->get();

        $unit = 50;
        $datetime = 'day';
        $start_date = date('Y-m-d', strtotime("-$unit $datetime"));
        $count = (round((time() - strtotime($start_date)) / (60 * 60 * 24))) - 2;

        for ($x = $count; $x >= 0; $x--) {
            $date = date('Y-m-d', strtotime("-$x day"));

            //$date = '2020-12-31';
            $data1 = DB::select("select `users`.`id`, `users`.`name`, (SELECT liquidity FROM user_details WHERE users.id = user_details.user_id) liquidity, (SELECT liquidity_adjuster FROM user_details WHERE users.id = user_details.user_id) liquidity_adjuster, (select SUM(investor_transactions.amount) as credit from `investor_transactions` where `users`.`id` = `investor_transactions`.`investor_id` and `investor_transactions`.`status` = 1 and `investor_transactions`.`date` = '$date' ) as `credit_amount`, (select SUM(merchant_user.amount) as total_funded from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `total_funded`, (select SUM(merchant_user.commission_amount) as commission_amount from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `commission_amount`, (select SUM(merchant_user.under_writing_fee) as under_writing_fee from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `under_writing_fee`, (select SUM(merchant_user.pre_paid) as pre_paid from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `pre_paid`, (select SUM(merchant_user.invest_rtr- (merchant_user.mgmnt_fee/100)*merchant_user.invest_rtr) as invest_rtr from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `rtr`, (select sum(merchant_user.actual_paid_participant_ishare-merchant_user.paid_mgmnt_fee) as ctd from `merchant_user` where `users`.`id` = `merchant_user`.`user_id` and `merchant_user`.`status` in (1, 3) and exists (select * from `merchants` where `merchant_user`.`merchant_id` = `merchants`.`id` and `active_status` = 1 and `merchants`.`date_funded` = '$date' and `merchants`.`deleted_at` is null)) as `ctd` from `users` inner join `user_has_roles` on `users`.`id` = `user_has_roles`.`model_id` and `user_has_roles`.`role_id` in (2, 13) where `users`.`deleted_at` is null");
            $data2 = DB::select(" select merchant_user.user_id as id,sum(IF(merchants.date_funded = '$date' = 1, merchant_user.pre_paid, IF(merchants.date_funded = '$date', merchant_user.pre_paid, 0))) as syndication_fee, sum(IF(merchants.date_funded = '$date' = 1, merchant_user.under_writing_fee, IF(merchants.date_funded = '$date', merchant_user.under_writing_fee, 0))) as underwriting_fee_earned, IF(merchants.date_funded = '$date' = 1,(merchants.origination_fee * merchants.funded) / 100, IF(merchants.date_funded = '$date', (merchants.origination_fee * merchants.funded) / 100, 0)) as origination_fee, IF(merchants.date_funded = '$date' = 1, merchants.up_sell_commission, IF(merchants.date_funded = '$date', merchants.up_sell_commission, 0)) as up_sell_commission, IF(merchants.date_funded = '$date' = 1, 350, IF(merchants.date_funded = '$date', 350, 0)) as underwriting_fee_flat, IF(merchants.date_funded = '$date' = 1, 'yes', IF(merchants.date_funded = '$date', 'yes', 'no')) as within_funded_date from `merchants` left join `merchant_user` on `merchants`.`id` = `merchant_user`.`merchant_id` inner join `user_has_roles`  on `merchant_user`.`user_id` = `user_has_roles`.`model_id` and `user_has_roles`.`role_id` in (2, 13) group by `merchant_user`.`user_id` order by `merchants`.`date_funded` desc");

            $data3 = DB::select("select payment_date, sum(profit), sum(principal), sum(`payment_investors`.mgmnt_fee) as management_fee_earned, `payment_investors`.`user_id` as id from `payment_investors` LEFT JOIN `merchant_user` ON `merchant_user`.`user_id` = `payment_investors`.`user_id` inner join `participent_payments` on `payment_investors`.`participent_payment_id` = `participent_payments`.`id` where `payment_type` = 1 and `payment_date` = '$date' group by `payment_investors`.`user_id`");

            $kk = array_values(dynamic_report_array(dynamic_report_array($data1, $data2), $data3));
            DynamicReportMerchant::insert($kk);
        }

        return 'done';

        foreach ($kk as $key => $val) {
            echo $val['name'].'<br>';
        }

        //DynamicReportMerchant::create($kk);
        //return $kk;

        die;

        return $data3;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $start_date = Carbon::createFromFormat('Y-m-d', '2019-12-01');
        $end_date = Carbon::createFromFormat('Y-m-d', '2021-01-31');

        $period = new CarbonPeriod($start_date, '1 day', $end_date);

        foreach ($period as $dt) {
            echo $dt->format("Y-m-d").'<br>';
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(DynamicReportRequest $request)
    {
        $dr = new DynamicReport();
        $dr->name = $request->name;
        $dr->description = $request->description;
        $dr->field_keys = json_encode($request->field_keys, true);
        $dr->save();
        $request->session()->flash('message', 'Merchant Report created!');

        return redirect()->to(route('admin::dynamic-report-investor.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Builder $tableBuilder, $id)
    {
        session_set('all_merchants', Merchant::select('id', 'name')->get()->getDictionary());

        //return DynamicReport::actual_val('merchant_id',$merchants);
        $dr = DynamicReport::find($id);
        session_set('dr',$dr);

        $all_fields = DynamicReport::investor_fields();

        $table_columns = [//['data' => 'id', 'name' => 'id', 'title' => '#', 'searchable' => false, 'orderable' => false,],
        ];
        $table_parameter = ''; $i=0;
        foreach (json_decode($dr->field_keys) as $key) {
            //foreach (DynamicReport::all_fields() as $key => $val) {
            $table_columns[] = [
                'data' => $key,
                'name' => $key,
                'title' => changeCase($all_fields[$key]),
            ];
            $tp_val = 'total_'.$key;
            $table_parameter .= " $(n.column($i).footer()).html(o.$tp_val);";
            $i++;
        }
        session_set('table_parameter',$table_parameter);

        $page_title = $dr->name;
        //$tableBuilder->ajax(route('admin::dynamic-report-investor.report-data'));


        /*if ($request->ajax() || $request->wantsJson()) {
            return \MTB::investorInterestAccuredReport($request->investors, $request->date_start, $request->date_end);
        }*/
	    $field_keys=array_diff(json_decode($dr->field_keys),['payment_date','name']);

	    $ajx = '';
	    foreach ($field_keys as $key) {
		    $ajx .= 'data.start_'.$key.' = $("#start_'.$key.'").val();';
		    $ajx .= 'data.end_'.$key.' = $("#end_'.$key.'").val();';
	    }
        $tableBuilder->ajax(['url' => route('admin::dynamic-report-investor.report-data'), 'type' => 'post', 'data' => 'function(data){
		        data._token = "'.csrf_token().'";
		        data.investors = $("#investors").val();
		        data.date_end = $("#date_end").val();
		        data.date_start = $("#date_start").val();'
	        .$ajx.'
	        
	        }']);


        $tableBuilder->parameters([
            'responsive' => true,
            'autoWidth' => false,
            'processing' => true,
            'aaSorting' => [],
            'pagingType' => 'input',
            "pageLength" =>100,
        ]);
        $tableBuilder = $tableBuilder->columns($table_columns);
        $tableBuilder->parameters(['serverSide' => false,
             'footerCallback' => 'function(t,o,a,l,m){
            if(typeof table !== "undefined") {
                var n=this.api(),o=table.ajax.json();'.
               session('table_parameter') .' }
            }']);
        $all_fields = DynamicReport::investor_fields();
        $investors = User::where('id','!=',1)->pluck('name', 'id');
        $dynamic_data = true;

	    return view('admin.dynamic-reports.investor', compact('page_title', 'tableBuilder', 'all_fields','investors','dynamic_data','field_keys'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
   //

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if (DynamicReport::destroy($id)) {
            return true;
        }
        return false;
    }
    public function edit($id)
    {
        return DynamicReport::find($id);
    }
    public function update(DynamicReportRequest $request, $id)
    {
        $dr = DynamicReport::findOrFail($id);
        $dr->name = $request->name;
        $dr->description = $request->description;
        $dr->field_keys = json_encode($request->field_keys, true);
        $dr->save();
        $request->session()->flash('message', 'Report updated!');
        return redirect()->to(route('admin::dynamic-report-investor.index'));

    }

    public function report_data(Request  $request)
    {
        session_set('accessor',true);
        $pageSize = ($request->length) ? (int)$request->length : 10;
        $start = ($request->start) ? (int)$request->start : 0;
        $data = DynamicReportMerchant::where('payment_date','!=',null)->where('name','!=',null)->orderBy('payment_date', 'desc');//->where('total_funded', '>=',  500)->where('total_funded','<=', 1000);

        if ($request->date_start) {
            $data = $data->where('payment_date', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $data = $data->where('payment_date', '<=', $request->date_end);
        }

	    $field_keys=array_diff(json_decode(session('dr')->field_keys),['payment_date','name']);
	    foreach ($field_keys as  $key) {
		    $start = 'start_'.$key;
		    $end = 'end_'.$key;
	    	if($request->$start){
			    $data = $data->where("$key", '>=', (int)$request->$start);
		    }
		    if($request->$end){
			    $data = $data->where($key, '<=', (int) $request->$end);
		    }
	    }


        if ($request->investors && is_array($request->investors)) {
            $a=[];
            foreach ($request->investors as $key => $val){
                array_push($a,(int)$val);
            }
            $data = $data->whereIn('id', $a);
        }
        $DynamicReportMerchant = $data = $data->get();

        $data = $data->toArray();


        $dt =  \DataTables::collection($data)->addIndexColumn();
        session_set('accessor',false);
        foreach (json_decode(session('dr')->field_keys) as $key) {
            if($key == 'name'|| $key == 'payment_date'){
                $total = ' ';
            } else {
                $total = \FFM::dollar($DynamicReportMerchant->sum($key));
            }
            $dt =    $dt->with('total_'.$key, $total);
        }

        return $dt->make(true);


    }
}
