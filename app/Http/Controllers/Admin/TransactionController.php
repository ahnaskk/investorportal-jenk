<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Library\Repository\Interfaces\ITransactionsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TransactionController extends Controller
{
    public function __construct(ITransactionsRepository $transaction)
    {
        $this->Transaction = $transaction;
    }
    public function TransactionData(Request $request)
    {
        return $this->Transaction->IgetTransactionDataTable($request);
    }
    public function PendingTransactions(Request $request)
    {
        $page_title       = 'Pending Transactions';
        $page_description = 'Pending Transactions';
        $ReturnData=$this->Transaction->IPendingTransactions($request);
        if ($request->ajax() || $request->wantsJson()) {
            return $ReturnData;
        }
        $tableBuilder = $ReturnData['tableBuilder'];
        $investors    = $ReturnData['investors'];
        $Investor     = $ReturnData['Investor'];
        $Merchant     = $ReturnData['Merchant'];
        return view('admin.payments.pending_transactions', compact('tableBuilder', 'page_title', 'investors', 'page_description', 'Merchant', 'Investor'));
    }
    public function ApproveTransactions(Request $request, $id = null)
    {
        $return = $this->Transaction->IApproveTransactions($request,$id);
        return response()->json($return);
    }
}
