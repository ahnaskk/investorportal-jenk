<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicReportRequest;
use App\Merchant;
use App\MerchantCRMDetails;
use App\Models\DynamicReport;
use App\Models\DynamicReportInvestor;
use App\Models\DynamicReportMerchant;
use App\Models\Logs;
use App\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder;
use FFM;
use App\Library\Repository\Interfaces\IRoleRepository;

class DynamicReportInvestorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	public function __construct(IRoleRepository $role)
	{
		$this->role = $role;
	}
    public function index(Builder $tableBuilder)
    {
        $page_title = 'Investor Dynamic Reports';
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
            /*[
                'data' => 'id',
                'name' => 'id',
                'title' => '#',
                'searchable' => false,
                'orderable' => false,
            ],*/
            ['data' => 'name', 'name' => 'name', 'title' => 'Report Name'],
            ['data' => 'user_type', 'name' => 'user_type', 'title' => 'Report Type'],
            ['data' => 'description', 'name' => 'description', 'title' => 'Description'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created at'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false],
        ]);
         $all_fields= DynamicReport::all_fields();
        $investors = User::where('id','!=',1)->pluck('name', 'id');
        $merchant_fields = DynamicReport::merchant_fields();
	    $report_type = false;
	    unset($merchant_fields['name']);unset($merchant_fields['payment_date']);
	    unset($all_fields['name']);unset($all_fields['payment_date']);
	    return view('admin.dynamic-reports.investor', compact('page_title', 'tableBuilder', 'all_fields','investors','merchant_fields','report_type'));
    }

    public function rowData()
    {
        $data = DynamicReport::get();
        $data = $data->toArray();
        session_set('all_users', User::select('id', 'name')->get()->getDictionary());

        return \DataTables::collection($data)->editColumn('name', function ($data) {
            return '<a href="'.route('admin::dynamic-report-investor.show', $data['id']).'">'.$data['name'].'</a>';
        })->addColumn('action', function ($data) {
            $return = '';
            $return .= '<a class="btn btn-xs btn-primary" href="'.route('admin::dynamic-report-investor.show', $data['_id']).'"> <i class="glyphicon glyphicon-list-alt"></i>  </a>';

            //$return .= '<a href="'.route('admin::template::edit', ['id' => $data['id']]).'" class="btn btn-xs btn-secondary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
	        //$return .= '<a data-bs-toggle="modal" data-bs-target="#editReport" data-id="'.$data['id'].'" class="btn btn-xs btn-secondary edit_report"><i class="glyphicon glyphicon-edit"></i> </a>';

            /*$return .= '<form method="POST" action="'.route('admin::dynamic-report.destroy',$data['id']).'" accept-charset="UTF-8" style="display:inline">
	                                         '.method_field('DELETE').csrf_field().'<button type="submit" class="btn btn-xs btn-danger" title="Delete" onclick="return confirm(&quot;Are you sure want to delete ?&quot;)"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
											</form>';*/
            $return .= '<a data-id="'.$data['id'].'" class="btn btn-xs btn-danger delete_report"><i class="glyphicon glyphicon-trash"></i> </a>';

            return $return;
        })->editColumn('user_type', function ($data) {
        	return $data['user_type'] == 1 ? 'Investor' : "Merchant";

        })->editColumn('created_at', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data['created_at']).' by '.get_user_name_with_session($data['created_by']);

            return "<a title='$created_date'>".FFM::datetime($data['created_at']).'</a>';
        })->rawColumns(['name', 'action', 'created_at'])->make(true);
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
	    $dr->user_type = 3;
        $dr->description = $request->description;
        $payment = ['name','payment_date'];
	    $field_keys = $request->report_type == 2 ? $request->merchant_fields : $request->field_keys;
	    if(!$field_keys){
		    return redirect()->back()->withErrors('Please select atleast one field');
	    }
	    $field_keys =  in_array("payment_date", $field_keys) ? $field_keys : array_merge( $payment,$field_keys,);
        $dr->field_keys = json_encode($field_keys, true);
        $dr->save();
        $request->session()->flash('message', 'Investor Report created!');

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

	    $company = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        session_set('all_merchants', Merchant::select('id', 'name')->get()->getDictionary());

        //return DynamicReport::actual_val('merchant_id',$merchants);
        $dr = DynamicReport::find($id);
        session_set('dr',$dr);
	    $table_columns = [];
        $table_parameter = ''; $i=0;

	    $all_fields = DynamicReport::all_fields();
       
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
	    $report_type = $dr->user_type;
	    $ajax_url = $report_type == 2 ? route('admin::dynamic-report-investor.report-data-merchant') : route('admin::dynamic-report-investor.report-data');

        $tableBuilder->ajax(['url' => $ajax_url, 'type' => 'post', 'data' => 'function(data){
		        data._token = "'.csrf_token().'";
		        data.merchants = $("#merchants").val();
		        data.investors = $("#investors").val();
		        data.company_id = $("#company_id").val();
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
	    $merchant_fields = DynamicReport::merchant_fields();
	    $co = ['0'=> 'Select Company'];
	    $company = $co+ $company;
	    $investors = User::where('id','!=',1)->pluck('name', 'id');
	    $merchants = Merchant::pluck('name', 'id');
        $dynamic_data = true;


	    return view('admin.dynamic-reports.investor', compact('page_title', 'tableBuilder', 'all_fields','investors','dynamic_data','field_keys','merchant_fields','company','report_type','merchants'));
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

	public function report_data_merchant(Request  $request)
	{
		session_set('accessor',true);
		$pageSize = ($request->length) ? (int)$request->length : 10;
		$start = ($request->start) ? (int)$request->start : 0;
		$data = DynamicReportMerchant::where('payment_date', '!=', null)->where('name', '!=', null)->orderBy('payment_date', 'desc');//->where('total_funded', '>=',  500)->where('total_funded','<=', 1000);

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
				$data = $data->where("$key", '>=', (int) $request->$start);
			}
			if($request->$end){
				$data = $data->where($key, '<=', (int)  $request->$end);
			}
		}

		/*if ($request->company_id) {
			$users =  User::where('id','!=',1)->where('company','=',$request->company_id)->pluck('name', 'id');
			$a=[];
			foreach ($users as $key => $val){
				array_push($a,(int)$key);
			}
			$data = $data->whereIn('id', $a);
		}*/


		if ($request->merchants && is_array($request->merchants)) {
			$a=[];
			foreach ($request->merchants as $key => $val){
				array_push($a,(int)$val);
			}
			$data = $data->whereIn('id', $a);
		}
		$DynamicReportInvestor = $data = $data->get();

		$data = $data->toArray();


		$dt =  \DataTables::collection($data)->addIndexColumn();
		session_set('accessor',false);
		foreach (json_decode(session('dr')->field_keys) as $key) {
			if($key == 'name'|| $key == 'payment_date'|| $key == 'date_funded'){
				$total = ' ';
			} else {
				//echo $key;
				$total = \FFM::dollar($DynamicReportInvestor->sum($key));
			}
			$dt =    $dt->with('total_'.$key, $total);
		}

		return $dt->make(true);


	}

    public function report_data(Request  $request)
    {
        session_set('accessor',true);
        $pageSize = ($request->length) ? (int)$request->length : 10;
        $start = ($request->start) ? (int)$request->start : 0;
	    $data = DynamicReportInvestor::where('payment_date', '!=', null)->where('name', '!=', null)->orderBy('payment_date', 'desc');//->where('total_funded', '>=',  500)->where('total_funded','<=', 1000);

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

	    if ($request->company_id) {
		    $users =  User::where('id','!=',1)->where('company','=',$request->company_id)->pluck('name', 'id');
		    $a=[];
		    foreach ($users as $key => $val){
			    array_push($a,(int)$key);
		    }
		    $data = $data->whereIn('id', $a);
	    }


        if ($request->investors && is_array($request->investors)) {
            $a=[];
            foreach ($request->investors as $key => $val){
                array_push($a,(int)$val);
            }
            $data = $data->whereIn('id', $a);
        }
        $DynamicReportInvestor = $data = $data->get();

        $data = $data->toArray();


        $dt =  \DataTables::collection($data)->addIndexColumn();
        session_set('accessor',false);
        foreach (json_decode(session('dr')->field_keys) as $key) {
            if($key == 'name'|| $key == 'payment_date'){
                $total = ' ';
            } else {
                $total = \FFM::dollar($DynamicReportInvestor->sum($key));
            }
            $dt =    $dt->with('total_'.$key, $total);
        }

        return $dt->make(true);


    }
}
