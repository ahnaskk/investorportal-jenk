<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Library\Repository\Interfaces\IInvestorTransactionRepository;
use FFM;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use DB;
class InvestorTransactionController extends Controller
{
    public function __construct( IInvestorTransactionRepository $transaction) {
        $this->transaction = $transaction;
    }
    
    public function index($investorId, Request $request)
    {
        try {
            $page_title = 'Investor Transactions';
            $ReturnData=$this->transaction->iIndexData($investorId,$request);
            if ($request->wantsJson() || $request->ajax()) {
                return $ReturnData;
            }
            if($ReturnData['result']!='success') throw new \Exception($ReturnData['result'], 1);
            return view('admin.investors.transaction.index')
            ->with('page_title',$page_title)
            ->with('investorId',$investorId)
            ->with('sdate',$ReturnData['sdate'])
            ->with('edate',$ReturnData['edate'])
            ->with('tableBuilder',$ReturnData['tableBuilder'])
            ->with('invest_count',$ReturnData['invest_count'])
            ->with('this_investor',$ReturnData['this_investor'])
            ->with('invested_amount',$ReturnData['invested_amount'])
            ->with('liquidity',$ReturnData['liquidity'])
            ->with('categories',$ReturnData['categories'])
            ;
        } catch (\Exception $e) {
            $result=$e->getMessage();
            $request->session()->flash('error', $result);
            if($result=="Permission Denied"){
                return view('admin.permission_denied');
            }
            return redirect(route("admin::investors::index"));
        }
    }
    
    public function edit($investorId, $tid)
    {
        try {
            $page_title = 'Edit Transaction';
            $action     = 'edit';
            $ReturnData=$this->transaction->iEdit($investorId, $tid);
            if($ReturnData['result'] != 'success') throw new \Exception($ReturnData['result']);
            return view('admin.investors.transaction.create')
            ->with('page_title',$page_title)
            ->with('action',$action)
            ->with('investorId',$investorId)
            ->with('transaction_categories',$ReturnData['transaction_categories'])
            ->with('investors',$ReturnData['investors'])
            ->with('transaction',$ReturnData['transaction'])
            ->with('liquidity',$ReturnData['liquidity'])
            ->with('Investor',$ReturnData['Investor'])
            ;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    
    public function create($investorId, $tid = 0)
    {
        if (!$investorId) return redirect()->back();
        $page_title = 'Add Transaction';
        $action     = 'create';
        $ReturnData = $this->transaction->iCreate($investorId);
        return view('admin.investors.transaction.create')
        ->with('action',$action)
        ->with('page_title',$page_title)
        ->with('investorId',$investorId)
        ->with('investors',$ReturnData['investors'])
        ->with('transaction_categories',$ReturnData['transaction_categories'])
        ->with('investor_type',$ReturnData['investor_type'])
        ->with('liquidity',$ReturnData['liquidity'])
        ->with('Investor',$ReturnData['Investor'])
        ;
    }
    
    public function update(Request $request, $id, $tid)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->transaction->iUpdate($request,$id,$tid);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result']);
            $request->session()->flash('message', 'Transaction updated!');
            DB::commit();
            return redirect()->route('admin::investors::transaction::index',['id' => $id]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    
    public function store(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $return_result=$this->transaction->iStore($request,$id);
            if($return_result['result'] != 'success') throw new \Exception($return_result['result'], 1);
            $request->session()->flash('message', 'Transaction Created!');
            DB::commit();
            return redirect()->route('admin::investors::transaction::index', ['id' => $id]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    
    public function delete(Request $request, $id, $tid)
    {
        try {
            DB::beginTransaction();
            if (!$this->transaction->deleteTransaction($tid)) throw new \Exception("Something Went Wrong", 1);
            $request->session()->flash('message', 'Transaction Deleted!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    
    public function status_change(Request $request, $id, $tid)
    {
        try {
            DB::beginTransaction();
            if (!$this->transaction->changeStatus($tid, $request)) {
                throw new \Exception("Something went wrong", 1);
            }
            $request->session()->flash('message', 'Transaction Added to Deposits!');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
    
    public function export($id, Request $request)
    {
        $export = $this->transaction->iexportData($request,$id);
        $fileName = 'Transaction Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        return Excel::download($export, $fileName);
    }
    public function update_multiple_transaction(Request $request){
        $trans_ids = $request->tran_ids;
        $tran_ids_arr = explode(',',$trans_ids);
         try {
            DB::beginTransaction();
            $return_result=$this->transaction->iUpdateMultipleTrans($request,$tran_ids_arr);
            $request->session()->flash('message', 'Transaction updated!');
            DB::commit();
            return redirect()->route('admin::investors::transactionreport');
         } catch (\Exception $e) {
             DB::rollback();
             return redirect()->back()->withErrors($e->getMessage());
         }
    }
}
