<?php

namespace App\Http\Controllers\Admin;

use App\BankDetails;
use App\Bills;
use App\Exports\Data_arrExport;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\InvestorTransaction;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\LiquidityLog;
use App\Merchant;
use App\User;
use App\UserDetails;
use Form;
use InvestorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Permissions;
use Yajra\DataTables\Html\Builder;

class BillsController extends Controller
{
    public function __construct(IRoleRepository $role)
    {
        $this->mBilll = new InvestorTransaction();
        $this->mBilll = $this->mBilll->where('status', InvestorTransaction::StatusCompleted);
        $this->role = $role;
    }

    public function index(Builder $tableBuilder, IRoleRepository $role)
    {
        $page_title = 'All Transactions';
        $tableBuilder->ajax(['url' => route('admin::bills::data'), 'data' => 'function(d){ d.start_date = $("#date_start").val(); d.end_date = $("#date_end").val();d.investors = $("#investors").val();d.investor_type = $("#investor_type").val();d.categories = $("#categories").val();d.companies = $("#companies").val();d.account_no_f=$("#account_no_f").val(); d.batch = $("#batch").is(\':checked\') ? true : false ;   }']);
        $tableBuilder->parameters(['footerCallback' => 'function(t,o,a,l,m){var n=this.api(),o=table.ajax.json();$(n.column(3).footer()).html(o.total)}', 'aaSorting' => [], 'bSortable' => false]);
        $tableBuilder->parameters(['fnCreatedRow' => "function (nRow, aData, iDataIndex) {\n               var info = this.dataTable().api().page.info();\n               var page = info.page;\n               var length = info.length;\n               var index = (page * length + (iDataIndex + 1));\n               $('td:eq(1)', nRow).html(index).addClass('txt-center');\n           }", 'pagingType' => 'input']);
        $tableBuilder = $tableBuilder->columns([['className' => 'details-control', 'orderable' => false, 'data' => 'null', 'name' => 'investor', 'defaultContent' => '', 'title' => '', 'searchable' => false], ['data' => 'id', 'name' => 'id', 'title' => '#', 'orderable' => false, 'searchable' => false], ['data' => 'category_notes', 'name' => 'category_notes', 'title' => 'Category'], ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'], ['data' => 'date', 'name' => 'date', 'title' => 'Date'], ['data' => 'account_no', 'name' => 'account_no', 'title' => 'Account No'], ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false]]);
        $investors = $role->allInvestors()->pluck('name', 'id');
        $companies = $role->allCompanies()->pluck('name', 'id')->toArray();
        $categories = \ITran::getAllOptions();
        $bank_accounts = BankDetails::where('status', 1)->get();
        $investor_types = User::getInvestorType();

        return view('admin.bills.index', compact('page_title', 'tableBuilder', 'investors', 'categories', 'bank_accounts', 'companies', 'investor_types'));
    }

    public function update_s($merchant_id = '')
    {
        try {
            $action = 'create';
            $mBilll = mBilll::where('merchant_id', $merchant_id)->first();
            if ($mBilll) {
                $action = 'edit';
            }

            return view('admin.bills.create', compact('mBilll', 'action', 'merchant_id', 'action'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $page_title = 'Create Bill';
        $action = 'create';
        $investors = $this->role->allInvestorsLiquidity1()->whereNOTIn('users.investor_type', [5])->get();
        $bank_accounts = BankDetails::where('status', 1)->select(DB::raw("CONCAT(bank_name,' ',account_no) as bank_name"), 'account_no')->pluck('bank_name', 'account_no')->toArray();
        $merchants = Merchant::select('name', 'id')->get();
        $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        $categories = \ITran::getAllOptions();

        return view('admin.bills.create', compact('action', 'investors', 'bank_accounts', 'page_title', 'merchants', 'companies', 'categories'));
    }

    public function import_bill()
    {
        $page_title = 'CSV Mapper';
        $investors = $this->role->allInvestorsLiquidity();

        return view('admin.bills.import_bill', compact('page_title', 'investors'));
    }

    public function uploadBillCsv(Request $request)
    {
        $page_title = 'CSV Mapper';
        $companies = $this->role->allCompanies()->pluck('name', 'id')->toArray();
        $companies[0] = 'All';
        $companies = array_reverse($companies, true);
        $investor_type = [0 => 'All', 1 => 'Dept', 2 => 'Equity'];
        $accounts = BankDetails::where('status', 1)->get();
        $investors = $this->role->allInvestorsLiquidity();
        if ($request->has('csv')) {
            $csv_import = $request->file('csv');
            if ($csv_import) {
                $extension = 'csv';
                if ($extension != $csv_import->getClientOriginalExtension()) {
                    $validator = Validator::make($request->all(), ['csv' => 'max:8000|required|file|mimes:csv|size:8000']);
                    if ($validator->fails()) {
                        return redirect()->back()->withErrors($validator)->withInput();
                    }
                }
                $tmpFilename = 'bill_import_'.time().'.csv';
                $path = $request->file('csv')->getRealPath();
                if (filesize($path)) {
                    $data = array_map('str_getcsv', file($path));
                    $csv_data = array_slice($data, 1);

                    return view('admin.bills.csv_mapper', compact('csv_data', 'page_title', 'companies', 'accounts', 'investors', 'investor_type'));
                } else {
                    $request->session()->flash('error', 'CSV file is empty!');
                }
            }
        }

        return redirect()->back()->with('error', 'Please select CSV file to upload');
    }

    public function csvProcess(Request $request)
    {
        $note = $request->note;
        $date = $request->bill_date;
        $debit = $request->debit;
        $account = $request->account_no;
        $investorT = [];
        $creator_id = $request->user()->id;
        $validator = Validator::make($request->all(), ['investor_id' => 'required', 'account_no' => 'required']);
        if ($validator->fails()) {
            echo 'Validation error';
            exit();

            return redirect()->back()->withErrors($validator)->withInput();
        }
        $UserDetails = UserDetails::select('user_id', 'liquidity');
        if ($request->investor_id) {
            $UserDetails = $UserDetails->whereIn('user_id', $request->investor_id);
        }
        $UserDetails = $UserDetails->orderByDesc('liquidity');
        $total_liquidity = $UserDetails->sum('liquidity');
        $liquidities = $UserDetails->get();
        $testCount = count($liquidities->toArray());
        $count = 0;
        if (! empty($debit)) {
            foreach ($debit as $key => $value) {
                $batch = $key.time();
                $amount = str_replace([',', '$'], '', $debit[$key]);
                if (is_numeric(($amount))) {
                    $amount = -1 * ($amount);
                    foreach ($liquidities as $data) {
                        $amount2 = $data->liquidity / $total_liquidity * $amount;
                        $investorT[$count] = ['date' => $date[$key], 'transaction_type' => 1, 'category_notes' => $note[$key], 'transaction_category' => 10, 'status' => 1, 'creator_id' => $creator_id, 'account_no' => $account, 'investor_id' => $data->user_id, 'batch' => $batch, 'amount' => $amount2];
                        $count++;
                    }
                } else {
                    echo floatval($debit[$key]);
                }
            }
        }
        foreach ($investorT as $transInput) {
            $create = InvestorTransaction::create($transInput);
        }
        InvestorHelper::update_liquidity($request->investor_id, 'Bill Creation');
        $request->session()->flash('message', 'Bill Generated Successfully!');

        return redirect()->to('admin/bills/import_bill');
    }

    public function storeCreate(Requests\AdminBills $request)
    {
        {
            $request_var = $request->all();
            $investorT = new InvestorTransaction();
            $request->amount = str_replace(',', '', $request->amount);
            $total_investor = count($request->investor_id);
            $amount = -1 * ($request->amount);
            $UserDetails = UserDetails::select('user_id', 'liquidity')->whereIn('user_id', $request->investor_id)->orderByDesc('liquidity');
            $total_liquidity = $UserDetails->sum('liquidity');
            if ($total_liquidity < 0) {
                return redirect()->back()->with('error', 'Insufficient amount')->withInput();
            }
            if ($request->amount > $total_liquidity) {
                return redirect()->back()->with('error', 'Insufficient amount')->withInput();
            }
            $liquidities = $UserDetails->get();
            $batch = time();
            $testCount = count($liquidities->toArray());
            $count = 0;
            foreach ($liquidities as $data) {
                $amount2 = $data->liquidity / $total_liquidity * $amount;
                $investorT = ['date' => $request->date, 'transaction_type' => 1, 'category_notes' => $request->category_notes, 'transaction_category' => 10, 'status' => 1, 'creator_id' => $request->creator_id, 'account_no' => $request->account_no, 'investor_id' => $data->user_id, 'batch' => $batch];
                if ($count == $testCount) {
                    $investorT['amount'] = round($amount2);
                } else {
                    $investorT['amount'] = $amount2;
                }
                $create = InvestorTransaction::create($investorT);
                $count++;
            }
            InvestorHelper::update_liquidity($request->investor_id, 'Bill Creation');
            $request->session()->flash('message', 'New Bill created.');

            return redirect()->back();
        }
        {
    }
    }

    public function update(Requests\AdminBills $request, $merchant_id)
    {
        try {
            $request->amount = str_replace(',', '', $request->amount);
            $request->amount = -1 * abs($request->amount);
            $total_investor = count($request->investor_id);
            $amount = $request->amount;
            $UserDetails = UserDetails::select('user_id', 'liquidity')->whereIn('user_id', $request->investor_id)->orderByDesc('liquidity');
            $total_liquidity = $UserDetails->sum('liquidity');
            $liquidities = $UserDetails->get();
            $testCount = count($liquidities->toArray());
            $count = 0;
            $delete = InvestorTransaction::where('batch', $request->id)->delete();
            foreach ($liquidities as $data) {
                $amount = $data->liquidity / $total_liquidity * $request->amount;
                $investorT = ['date' => $request->date, 'transaction_type' => 1, 'category_notes' => $request->category_notes, 'status' => 1, 'creator_id' => $request->creator_id, 'account_no' => $request->account_no, 'investor_id' => $data->user_id, 'batch' => $request->id];
                if ($count == $testCount) {
                    $investorT['amount'] = round($amount);
                } else {
                    $investorT['amount'] = $amount;
                }
                $create = InvestorTransaction::create($investorT);
                $count++;
            }
            $liquidity_old = UserDetails::sum('liquidity');
            InvestorHelper::update_liquidity($request->investor_id, 'Bill Updation');
            $liquidity_new = UserDetails::sum('liquidity');
            $liquidity_change = $liquidity_new - $liquidity_old;
            $final_liquidity = 0;
            $aggregated_liquidity = UserDetails::sum('liquidity');
            $creator_id = ($request->user()) ? $request->user()->id : null;
            $input_array = ['aggregated_liquidity' => $aggregated_liquidity, 'name_of_deal' => 'Bill Updation', 'final_liquidity' => $final_liquidity, 'member_id' => '', 'liquidity_change' => $liquidity_change, 'member_type' => 'merchant', 'description' => 'Bill Updation', 'creator_id' => $creator_id];
            if ($liquidity_change != 0) {
                // $insert = LiquidityLog::insert($input_array);
            }
            $request->session()->flash('message', 'Bill Updated');

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function accountSelect(Request $request)
    {
        $search = $request->get('q');
        $investorId = $request->get('investorId');
        $bank = BankDetails::select('id', 'bank_details.name as account_name')->where(function ($query) use ($investorId) {
            if ($investorId != null) {
                $query->where('investor_id', $investorId);
            }
        })->where(function ($query) use ($search) {
            $query->orWhere('name', 'like', '%'.$search.'%');
        })->orderBy('name')->get()->toArray();
        $data = ['total_count' => BankDetails::count(), 'incomplete_results' => true, 'items' => $bank];

        return response()->json($data);
    }

    public function edit($bill_id)
    {
        try {
            if ($mBills = $this->mBilll->select('investor_transactions.transaction_type', DB::raw('sum(amount) as amount'), 'investor_transactions.date', 'investor_transactions.transaction_category', 'investor_transactions.maturity_date', 'investor_transactions.category_notes', 'bank_details.name as account_name', 'investor_transactions.account_no', 'investor_transactions.investor_id')->leftJoin('bank_details', 'bank_details.id', 'investor_transactions.account_no')->where('batch', $bill_id)->groupBy('batch')->first()) {
                $selected_investors = $this->mBilll->select('users.id', 'users.name', 'users.investor_type', 'management_fee', 'global_syndication', 'liquidity')->where('batch', $bill_id)->join('users', 'users.id', 'investor_transactions.investor_id')->join('user_details', 'user_details.user_id', 'users.id')->get();
                $investors = $this->role->allInvestorsLiquidity();
                $action = 'edit';
                $bank_accounts = BankDetails::where('status', 1)->get();
                $companies = [58 => 'VP Funding', 1 => 'Velocity'];

                return view('admin.bills.create', compact('mBills', 'action', 'bill_id', 'investors', 'action', 'bank_accounts', 'companies', 'selected_investors'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try {
            $id = ($_GET['id']);
            $investor_id = $this->mBilll->where('batch', $id)->pluck('investor_id')->toArray();
            if ($this->mBilll->where('batch', $id)->delete()) {
                $liquidity_old = UserDetails::sum('liquidity');
                InvestorHelper::update_liquidity($investor_id, 'Bill Deletion');
                $liquidity_new = UserDetails::sum('liquidity');
                $liquidity_change = $liquidity_new - $liquidity_old;
                $final_liquidity = 0;
                $aggregated_liquidity = UserDetails::sum('liquidity');
                $input_array = ['aggregated_liquidity' => $aggregated_liquidity, 'name_of_deal' => 'Bill Deletion', 'final_liquidity' => $final_liquidity, 'member_id' => '', 'liquidity_change' => $liquidity_change, 'member_type' => 'merchant', 'description' => 'Bill Deletion'];
                if ($liquidity_change != 0) {
                    // $insert = LiquidityLog::insert($input_array);
                }
                $request->session()->flash('message', 'Bill Deleted!');
            }

            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function export(Request $request)
    {
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $data = $this->mBilll->join('users', 'users.id', 'investor_transactions.investor_id')->select(['investor_transactions.batch', 'investor_transactions.id', DB::raw('sum(amount) as amount'), 'investor_transactions.investor_id', 'category_notes', 'date', 'transaction_category', 'account_no'])->orderByDesc('id');
        if ($request->date_start) {
            $data = $data->where('date', '>=', $request->date_start);
        }
        if ($request->date_end) {
            $data = $data->where('date', '<=', $request->date_end);
        }
        if (empty($permission)) {
            $data = $data->where('company', $userId);
        }
        if ($request->investor_type) {
            $data = $data->whereIn('users.investor_type', $request->investor_type);
        }
        if ($request->account_no_f) {
            $data = $data->where('account_no', $request->account_no_f);
        }
        if ($request->investors) {
            $data = $data->whereIn('investor_id', $request->investors);
        }
        if ($request->companies) {
            $data = $data->where('users.company', $request->companies);
        }
        if ($request->categories) {
            $data = $data->where('transaction_category', '=', $request->categories);
        }
        $fileName = 'investor_transaction_'.date(\FFM::defaultDateFormat('db').' H-i-s').'.csv';
        $total = $data->sum('amount');
        $data = $data->groupBy('batch');
        $data = $data->get()->toArray();
        $i = 1;
        $excel_array[0] = ['No', 'Category', 'Amount', 'Date', 'Account No', 'Notes'];
        if (! empty($data)) {
            foreach ($data as $key => $total) {
                $excel_array[$i]['No'] = $i;
                $excel_array[$i][] = $total['category_notes'];
                $excel_array[$i][] = $total['amount'];
                $excel_array[$i][] = \FFM::date($total['date']);
                $excel_array[$i][] = $total['account_no'];
                $excel_array[$i][] = $total['category_notes'];
                $i++;
            }
        }
        $export = new Data_arrExport($excel_array);

        return Excel::download($export, $fileName);
    }

    public function rowData(Request $request)
    {
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $data = $this->mBilll->join('users', 'users.id', 'investor_transactions.investor_id');
        $total = 0;
        $data->select(['investor_transactions.batch', 'investor_transactions.id', DB::raw('sum(amount) as amount'), 'investor_transactions.investor_id', 'category_notes', 'date', 'transaction_category', 'account_no']);
        if (empty($permission)) {
            $data->where('company', $userId);
        }
        if ($request->start_date) {
            $data = $data->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $data = $data->where('date', '<=', $request->end_date);
        }
        if ($request->companies) {
            $data = $data->where('users.company', $request->companies);
        }
        if ($request->investor_type) {
            $data = $data->whereIn('users.investor_type', $request->investor_type);
        }
        if ($request->categories) {
            $data = $data->whereIn('transaction_category', $request->categories);
        }
        if ($request->investors) {
            $data = $data->whereIn('investor_id', $request->investors);
        }
        if ($request->account_no_f) {
            $data = $data->where('account_no', $request->account_no_f);
        }
        $total = $data->sum('amount');
        $data = $data->groupBy('batch');
        $categories = \ITran::getAllOptions();

        return \DataTables::of($data)->editColumn('investor', function ($data) {
            $array = [];
            $investors = $this->mBilll->select('users.id', 'users.name', 'users.investor_type', 'liquidity', 'investor_transactions.amount', 'investor_transactions.investor_id')->where('investor_transactions.batch', $data->batch)->join('users', 'users.id', 'investor_transactions.investor_id')->join('user_details', 'user_details.user_id', 'users.id')->get();
            foreach ($investors as $investor) {
                $array[] = ['investor_name' => $investor->name, 'amount' => $investor->amount, 'investor_id' => $investor->investor_id];
            }

            return $array;
        })->addColumn('action', function ($data) use ($total) {
            $action_column = '';
            if (Permissions::isAllow('Transactions', 'Delete')) {
                if ($data['transaction_category'] == 10) {
                    $action_column .= Form::open(['route' => ['admin::bills::delete', 'id' => $data->batch], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete Batch', ['class' => 'btn btn-xs btn-danger']).Form::close();
                } else {
                    $action_column .= Form::open(['route' => ['admin::bills::delete', 'id' => $data->batch], 'method' => 'POST', 'onsubmit' => 'return confirm("Are you sure want to delete ?")']).Form::submit('Delete', ['class' => 'btn btn-xs btn-danger']).Form::close();
                }
            }

            return $action_column;
        })->editColumn('investo_id', function ($data) {
            return $data->investor_id;
        })->editColumn('amount', function ($data) {
            return \FFM::dollar($data->amount);
        })->editColumn('category_notes', function ($data) use ($categories) {
            return $categories[$data->transaction_category];
        })->editColumn('date', function ($data) {
            return \FFM::date($data->date);
        })->editColumn('account_no', function ($data) {
            return $data->account_no;
        })->with('total', \FFM::dollar($total))->make(true);
    }
}
