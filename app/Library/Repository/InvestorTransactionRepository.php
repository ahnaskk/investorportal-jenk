<?php
/**
* Created by SREEJ32H.
* User: iocod
* Date: 11/01/18
* Time: 4:50 PM.
*/
namespace App\Library\Repository;
use App\InvestorTransaction;
use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IInvestorTransactionRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\LiquidityLog;
use App\Merchant;
use App\MerchantUser;
use App\Settings;
use App\Template;
use App\User;
use App\UserDetails;
use App\Models\Views\InvestorAchTransactionView;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Html\Builder;
use App\Exports\Data_arrExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use FFM;
use ITran;
use Permissions;
use Exception;
use InvestorHelper;

class InvestorTransactionRepository implements IInvestorTransactionRepository
{
    public function __construct(Builder $tableBuilder,IRoleRepository $role)
    {
        $this->tableBuilder = $tableBuilder;
        $this->role         = $role;
        $this->table        = new InvestorTransaction();
    }
    
    public function iIndexData($investorId,$request)
    {
        try {
            $Investor=User::find($investorId);
            if(!$Investor) throw new \Exception("Invalid User Id", 1);
            if (Auth::user()->hasRole(['company'])) {
                $id1 = Auth::user()->id;
                $subinvestors = [];
                $inv = $this->role->allInvestors();
                $subadmininvestor = $inv->where('company', $id1);
                foreach ($subadmininvestor as $key1 => $value) {
                    $subinvestors[] = $value->id;
                }
                if (! in_array($investorId, $subinvestors)) {
                    throw new \Exception("This Investor not a company based", 1);
                }
            }
            $sdate = ($request->sdate) ? $request->sdate : '';
            $edate = ($request->edate) ? $request->edate : '';
            (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
            $userId = Auth::user()->id;
            if (!$permission) {
                $investorAccess = User::where('id', $investorId)->where('company', $userId)->first();
                if (empty($investorAccess)) {
                    throw new \Exception("Permission Denied", 1);
                }
            }
            if ($request->wantsJson() || $request->ajax()) {
                return $this->rowData($investorId, $request);
            }
            $this_investor = User::find($investorId);
            $invest_count  = MerchantUser::leftjoin('merchants', 'merchants.id', 'merchant_user.merchant_id')->where('merchants.active_status', 1);
            $invest_count  = $invest_count->where('merchant_user.user_id', $investorId);
            $invest_count  = $invest_count->whereIn('status', [ 1, 3, ]);
            $invest_count  = $invest_count->count();
            $invested_amount = MerchantUser::where('user_id', $investorId)->sum('amount');
            $liquidity = isset($this_investor->userDetails->liquidity) ? $this_investor->userDetails->liquidity : 0;
            $this->tableBuilder = $this->tableBuilder->columns([
                [ 'data' => 'DT_RowIndex', 'title'         => '#', 'orderable'                => false, 'searchable'                 => false, ],
                [ 'data' => 'transaction_category', 'name' => 'transaction_category', 'title' => 'Transaction Category', 'orderable' => false, ],
                [ 'data' => 'transaction_type', 'name'     => 'transaction_type', 'title'     => 'Transaction Type'],
                [ 'data' => 'transaction_method', 'name'   => 'transaction_method', 'title'   => 'Transaction Method'],
                [ 'data' => 'amount', 'name'               => 'amount', 'title'               => 'Amount','className' => 'text-right'],
                [ 'data' => 'status', 'name'               => 'status', 'title'               => 'Status'],
                [ 'data' => 'date', 'name'                 => 'date', 'title'                 => 'Investment Date '],
                [ 'data' => 'updated_at', 'name'           => 'updated_at', 'title'           => 'Last Updated At'],
                [ 'data' => 'action', 'name'               => 'action', 'title'               => 'Action', 'orderable'               => false, 'searchable' => false,'width'=>"5%"],
            ]);
            $this->tableBuilder->ajax([
                'url'  => route('admin::investors::transaction::index', ['id' => $investorId]),
                'data' => 'function(d){
                    d.start_date         = $("#date_start").val(); 
                    d.end_date           = $("#date_end").val();
                    d.transaction_type   = $("#transaction_type").val();
                    d.status             = $("#status").val();
                    d.transaction_method = $("#transaction_method").val();
                    d.categories         = $("#categories").val();
                    d.date_type          = $("#date_type").is(\':checked\') ? true : false ;
                }',
            ]);
            $this->tableBuilder->parameters([
                'order'          => [[5, 'desc']],
                'footerCallback' => 'function(t,o,a,l,m){
                    var n=this.api(),o=table.ajax.json();
                    $(n.column(0).footer()).html(o.Total);
                    $(n.column(4).footer()).html(o.total);
                }',
                'oSearch'        => [['regex', false]],
                'order'          => [[7, 'desc']]
            ]);
            $categories = InvestorAchTransactionView::transactionCategoryOptions();
            $return['result']          = 'success';
            $return['tableBuilder']    = $this->tableBuilder;
            $return['categories']      = $categories;
            $return['this_investor']   = $this_investor;
            $return['invest_count']    = $invest_count;
            $return['invested_amount'] = $invested_amount;
            $return['liquidity']       = $liquidity;
            $return['sdate']           = $sdate;
            $return['edate']           = $edate;
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    
    private function rowData($investorId,$request)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $data['investorId']=$investorId;
        $data1 = $this->getInvestorTransactionData($data);
        if ($request->start_date) {
            if ($request->date_type == 'true') {
                $data1->where('maturity_date', '>=', $request->start_date);
            } else {
                $data1->where('date', '>=', $request->start_date);
            }
        }
        if ($request->end_date) {
            if ($request->date_type == 'true') {
                $data1->where('maturity_date', '<=', $request->end_date);
            } else {
                $data1->where('date', '<=', $request->end_date);
            }
        }
        if ($request->transaction_type != 0) {
            $data1->where('transaction_type', '=', $request->transaction_type);
        }
        if ($request->transaction_method) {
            $data1->where('transaction_method', $request->transaction_method);
        }
        if ($request->status) {
            $data1->where('status', $request->status);
        }
        if ($request->categories) {
            $data1->where('transaction_category', '=', $request->categories);
        }
        $data = $data1;
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        
        return \DataTables::of($data)
        ->editColumn('transaction_category', function ($data) {
            $category = $data->transaction_category ? $data->TransactionCategoryName : '';
            
            return $category;
        })
        ->editColumn('amount', function ($data) {
            return \FFM::dollar($data->amount);
        })
        ->addColumn('merchant', function ($data) {
            if ($data->merchant) {
                return $data->merchant->name;
            } else {
                return '-';
            }
        })
        ->editColumn('updated_at', function ($data) {
            return $data->updated_at ? \FFM::datetime($data->updated_at) : '';
        })
        ->editColumn('date', function ($data) {
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.get_user_name_with_session($data->creator_id);
            
            return "<a title='$created_date'>".\FFM::date($data->date).'</a>';
        })
        ->editColumn('maturity_date', function ($data) {
            $user = User::where('id', $data->creator_id)->value('name');
            $user = ($user) ? $user : '--';
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.$user;
            
            return $data->maturity_date ? "<a title='$created_date'>".date(\FFM::defaultDateFormat('db'), strtotime($data->maturity_date)).'</a>' : '';
        })
        ->addColumn('transaction_type', function ($data) {
            return $data->TransactionTypeName;
        })
        ->editColumn('status', function ($data) {
            return $data->StatusName;
        })
        ->editColumn('transaction_method', function ($data) {
            $user = User::where('id', $data->creator_id)->value('name');
            $user = ($user) ? $user : '--';
            $created_date = 'Created On '.FFM::datetime($data->created_at).' by '.$user;
            
            return "<a title='$created_date' style='text-decoration: none;'>".$data->TransactionMethodName.'</a>';
        })
        ->addColumn('action', function ($data) use ($investorId) {
            $status_button = '';
            $return        = '';
            if ($data->table_name == 'it') {
                if (Permissions::isAllow('Investors', 'Edit')) {
                    $href=route('admin::investors::transaction::status_change', [ 'id' => $investorId, 'tid' => $data->id, 'status' => 1]);
                    $status_button  = $data->status == 2 ? "<a href='".$href."' class='btn btn-xs btn-danger' >Reinvest <i class='fa fa-repeat' aria-hidden='true'></i>s</a>" : '';
                    $status_button .= $data->status == 3 && $data->transaction_type == 2 ? "<a href='".$href."' class='btn btn-xs btn-danger' >Maturity Renew <i class='fa fa-repeat' aria-hidden='true'></i></a>" : '';
                    $delete_confirm = ''.'return confirm("Are you sure want to delete ?")'.'"';
                    $href=route('admin::investors::transaction::edit', [ 'id' => $investorId, 'tid' => $data->id]);
                    $return = "<a href='".$href."' class='btn btn-xs btn-primary invtr-bt'>Edit</a><br/>";
                }
                if (Permissions::isAllow('Investors', 'Delete')) {
                    $href=route('admin::investors::transaction::delete', [ 'id' => $investorId, 'tid' => $data->id]);
                    $return .= "<a Onclick='return confirmDelete();' href='".$href."' class='btn btn-xs btn-danger invtr-bt1' >Delete</a>";
                }
            }
            $return .= $status_button;
            return $return;
        })
        ->rawColumns([ 'transaction_method', 'action', 'maturity_date', 'date', 'updated_at', ])
        ->filterColumn('amount', function ($query, $keyword) {
            $sql = 'amount like ?';
            $query->orWhere('amount', 'like', '%'.$keyword.'%');
        })
        ->filterColumn('transaction_type', function ($query, $keyword) {
            if ($keyword == 'Credit') {
                $keyword = 2;
            } elseif ($keyword == 'Debit') {
                $keyword = 1;
            }
            $sql = 'transaction_type like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('transaction_method', function ($query, $TextKeyword) {
            switch ($TextKeyword) {
                case 'Admin Credit':
                $keyword = 1;
                break;
                case 'Admin Debit':
                $keyword = 2;
                break;
                case 'Automatic Debit':
                $keyword = 3;
                break;
                case 'Marketplace Credit':
                $keyword = 4;
                break;
                case 'Participant Credit':
                $keyword = 5;
                break;
                case 'Participant Credit':
                $keyword = 6;
                break;
            }
            $sql = 'transaction_method like ?';
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->filterColumn('updated_at', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(updated_at,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })
        ->filterColumn('maturity_date', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(maturity_date,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })
        ->filterColumn('date', function ($query, $keyword) {
            $query->whereRaw("DATE_FORMAT(date,'%m-%d-%Y') like ?", ["%$keyword%"]);
        })
        ->with('Total', 'Total:')
        ->with('total', \FFM::dollar($data->sum('amount')))
        ->addIndexColumn()
        ->make(true);
    }
    
    public function iCreate($investorId)
    {
        $investors     = $this->role->allInvestors();
        $Investor      = User::where('id', $investorId)->first();
        $inv_type      = User::select('investor_type')->find($investorId);
        $investor_type = $inv_type->investor_type;
        $transaction_categories = \ITran::getAllOptions();
        $liquidity = UserDetails::where('user_id', $investorId)->value('liquidity');
        $return['investors']              =$investors;
        $return['Investor']               =$Investor;
        $return['investor_type']          =$investor_type;
        $return['transaction_categories'] =$transaction_categories;
        $return['liquidity']              =$liquidity;
        return $return;
    }
    
    public function iStore($request,$id)
    {
        try {
            if($request->tran_type == 2) { $request->merge(['transaction_method' => 1]); }
            if($request->tran_type == 1) { $request->merge(['transaction_method' => 2]); }
            $request->transaction_type = $request->tran_type;
            if (!$this->insertTransaction($request)) throw new \Exception("Something Went Wrong", 1);
            $return['result']='success';
        } catch (\Exception $e) {
            $return['result']=$e->getMessage();
        }
        return $return;
    }
    
    public function iEdit($investorId, $tid)
    {
        try {
            $transaction = $this->findTransaction($tid);
            if (!$transaction) throw new Exception("Invalid Id", 1);
            $investors              = $this->role->allInvestors();
            $Investor               = User::where('id', $investorId)->first();
            $transaction_categories = ITran::getAllOptions();
            $liquidity              = UserDetails::where('user_id', $investorId)->value('liquidity');
            $return['transaction']            = $transaction;
            $return['investors']              = $investors;
            $return['Investor']               = $Investor;
            $return['transaction_categories'] = $transaction_categories;
            $return['liquidity']              = $liquidity;
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    
    public function iUpdate($request,$id,$tid)
    {
        try {
            if($request->tran_type == 2) { $request->merge(['transaction_method' => 1]); }
            if($request->tran_type == 1) { $request->merge(['transaction_method' => 2]); }
            $request->transaction_type = $request->tran_type;
            if(!$this->updateTransaction($tid, $request)) throw new \Exception("Something Went Wrong", 1);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }
        return $return;
    }
    public function iUpdateMultipleTrans($request,$tran_ids_arr){
        foreach($tran_ids_arr as $id){
        if ($transaction = $this->findTransaction($id)) {
            $amount = trim(str_replace(',', '', $request->new_amount));
            if (is_numeric($amount)) {
                $request->amount = $amount;
            }
            $request->transaction_category = $request->new_transaction_category;
            $description = "Updated the category from '".\ITran::getLabel($transaction->transaction_category)."' to '". \ITran::getLabel($request->transaction_category)."'";
            $request->transaction_type = $request->new_transaction_type;
            if($request->tran_type == 2) { $request->merge(['transaction_method' => 1]); }
            if($request->tran_type == 1) { $request->merge(['transaction_method' => 2]); }
            $request->transaction_type = $request->tran_type;
            $transaction->transaction_category = ($request->transaction_category==0) ? $transaction->transaction_category: $request->transaction_category;
            $transaction->transaction_type     = ($request->tran_type!='') ? $request->transaction_type : $transaction->transaction_type;
            if($request->tran_type!=''){
            $transaction->transaction_method   = $request->transaction_method;
            $transaction->amount               = $request->transaction_type == 1 ? (-1 * abs($transaction->amount)) : abs($transaction->amount);
            }
            $transaction->date     = (!empty($request->new_inv_date)) ? $request->new_inv_date : $transaction->date;
            $transaction->save();
            $user_id = $this->table->where('id', $id)->first()->investor_id;
            InvestorHelper::update_liquidity($user_id, $description); 
           
        }
       }
        return true;

    }
    
    public function getInvestorTransactionData($data)
    {
        $return = InvestorAchTransactionView::select(
            'id',
            'transaction_type',
            'transaction_method',
            'transaction_category',
            'amount',
            'date',
            'maturity_date',
            'updated_at',
            'status',
            'created_at',
            'category_notes',
            'creator_id',
            'table_name'
        );
        if(isset($data['investorId'])){
            if($data['investorId']){
                $return = $return->whereInvestorId($data['investorId']);
            }
        }
        if(isset($data['date_start'])){
            if($data['date_start']){
                $return->where('date', '>=', $data['date_start']);
            }
        }
        if(isset($data['date_end'])){
            if($data['date_end']){
                $return->where('date', '<=', $data['date_end']);
            }
        }
        if(isset($data['company'])){
            if($data['company']){
                $return->where('company', $data['company']);
            }
        }
        if(isset($data['transaction_type'])){
            if($data['transaction_type']){
                $return->where('transaction_type', $data['transaction_type']);
            }
        }
        if(isset($data['transaction_category'])){
            if($data['transaction_category']){
                $return->where('transaction_category', $data['transaction_category']);
            }
        }
        if(isset($data['status'])){
            if($data['status']){
                $return->where('status', $data['status']);
            }
        }
        return $return;
    }
    
    public function iexportData($request,$id)
    {
        ($request->user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = $request->user()->id;
        $data=[];
        if ($id) {
            $data['investorId']=$id;
        }
        if ($request->date_start) {
            $data['date_start']=$request->date_start;
        }
        if ($request->date_end) {
            $data['date_end']=$request->date_end;
        }
        if ($request->date_end) {
            $data['date_end']=$request->date_end;
        }
        if (empty($permission)) {
            $data['company']=$userId;
        }
        if ($request->transaction_type != 0) {
            $data['transaction_type']=$request->transaction_type;
        }
        if ($request->categories) {
            $data['transaction_category']=$request->categories;
        }
        if ($request->status) {
            $data['status']=$request->status;
        }
        $DataList = $this->getInvestorTransactionData($data);
        $DataList = $DataList->orderByDesc('date');
        $excel_array[0] = [
            'No',
            'Transaction Category',
            'Transaction Type',
            'Transaction Method',
            'Amount',
            'Status',
            'Investment Date',
            'Maturity Date',
            'Last Updated At',
            'Notes',
        ];
        $total = $DataList->sum('amount');
        $data1 = $DataList->get();
        $total_amount = 0;
        $i = 1;
        if (! empty($data1)) {
            foreach ($data1 as $key => $value) {
                $total_amount = $total_amount + $value->amount;
                $excel_array[$i]['No']                   = $i;
                $excel_array[$i]['Transaction Category'] = $value->transaction_category ? $value->TransactionCategoryName : '';
                $excel_array[$i]['Transaction Type']     = $value->transaction_type ? $value->TransactionTypeName : '';
                $excel_array[$i]['Transaction Method']   = $value->transaction_method ? $value->TransactionMethodName : '';
                $excel_array[$i]['Amount']               = \FFM::dollar($value->amount);
                $excel_array[$i]['Status']               = $value->StatusName ?? '';
                $excel_array[$i]['Investment Date']      = ($value->date) ? \FFM::date($value->date) : '';
                $excel_array[$i]['Maturity Date']        = ($value->maturity_date) ? \FFM::date($value->maturity_date) : '';
                $excel_array[$i]['Last Date']            = ($value->updated_at) ? \FFM::datetime($value->updated_at) : '';
                $excel_array[$i]['Notes']                = $value->category_notes;
                $i++;
            }
        }
        $count = count($excel_array) + 1;
        $excel_array[$i]['No']                   = null;
        $excel_array[$i]['Transaction Category'] = null;
        $excel_array[$i]['Transaction Type']     = null;
        $excel_array[$i]['Transaction Method']   = null;
        $excel_array[$i]['Amount']               = \FFM::dollar($total_amount);
        return new Data_arrExport($excel_array);
    }
    
    public function deleteTransaction($id)
    {
        $user_id = $this->table->where('id', $id)->first()->investor_id;
        if ($transaction = $this->findTransaction($id)) {
            if ($status = $transaction->delete()) {
                InvestorHelper::update_liquidity($user_id, 'Delete Investor Transaction'); //Pass User Id
                return $status;
            }
        }
        
        return false;
    }
    
    public function findTransaction($id)
    {
        return $this->table->find($id);
    }
    
    public function updateTransaction($id, Request $request)
    {
        if ($transaction = $this->findTransaction($id)) {
            $amount = trim(str_replace(',', '', $request->amount));
            if (is_numeric($amount)) {
                $request->amount = $amount;
            }
            $transaction->amount               = $request->transaction_type == 1 ? (-1 * abs($request->amount)) : abs($request->amount);
            $transaction->date                 = $request->date;
            $transaction->category_notes       = $request->category_notes;
            $transaction->maturity_date        = $request->maturity_date;
            $transaction->transaction_category = $request->transaction_category;
            $transaction->transaction_type     = $request->transaction_type;
            $transaction->transaction_method   = $request->transaction_method;
            $transaction->status               = 1;
            $transaction->save();
            $user_id = $this->table->where('id', $id)->first()->investor_id;
            $description = \ITran::getLabel($request->transaction_category);
            InvestorHelper::update_liquidity($user_id, $description); //Pass User Id
            return true;
        }
        return false;
    }
    
    public function insertTransaction(Request $request)
    {
        $data = $request->all();
        if (isset($request->transaction_type)) {
            if (! isset($data['transaction_type'])) {
                $data['transaction_type'] = $request->transaction_type;
            }
        }
        $return_function = InvestorHelper::insertTransactionFunction($data);
        if ($return_function['result'] != 'success') {
            return false;
        }
        return true;
    }
    
    public function changeStatus($id, Request $request)
    {
        if ($transaction = $this->findTransaction($id)) {
            {
                $transaction->status        = $request->status; // $request->transaction_type;
                $transaction->date          = date('Y-m-d'); // new date (today)
                $transaction->maturity_date = date('Y-m-d', strtotime('+1 year')); // new date (today)
                $transaction->save();
                $user_id = $this->table->where('id', $id)->first()->investor_id;
                $description = \ITran::getLabel($transaction->transaction_category);
                InvestorHelper::update_liquidity($user_id, $description); //Pass User Id
                
                return true;
            }
            
            return false;
        }
    }
    
}
