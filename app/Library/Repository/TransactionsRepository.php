<?php
/**
* Created by Rahees.
* User: raheesiocod
* Date: 05/10/21
*/
namespace App\Library\Repository;
use App\InvestorTransaction;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\ITransactionsRepository;
use App\Library\Repository\Interfaces\IInvestorTransactionRepository;
use App\Models\Views\ParticipentPaymentView;
use App\Models\Views\TransactionView;
use App\Models\Transaction;
use App\Settings;
use App\User;
use FFM;
use GPH;
use InvestorHelper;
use Carbon\Carbon;
use App\ParticipentPayment;
use Yajra\DataTables\Html\Builder;
use Illuminate\Support\Facades\DB;
class TransactionsRepository implements ITransactionsRepository
{
    public function __construct(IRoleRepository $role,Builder $tableBuilder)
    {
        $this->role         = $role;
        $this->Transaction  = new ParticipentPaymentView;
        $this->tableBuilder = $tableBuilder;
    }
    public function getTransactionList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['width' => '5%', 'data' => 'checkbox', 'type' => 'checkbox', 'name' => 'checkbox', 'title' => '<label class="chc" title=""><input type="checkbox" id="checkAllButtont"><span class="checkmark checkk"></span></label>', 'orderable' => false, 'searchable' => false, 'className' => 'checkbox11'],
                ['orderable' => true, 'visible' => true, 'searchable' => false, 'title' => '#', 'data' => 'id', 'name' => 'id', 'className' => ''],
                ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'AccountHead', 'data' => 'AccountHead', 'name' => 'AccountHead'],
                ['orderable' => true, 'visible' => true, 'title' => 'Date', 'data' => 'date', 'name' => 'date'],
                ['orderable' => true, 'visible' => true, 'title' => 'Method', 'data' => 'mode_of_payment', 'name' => 'mode_of_payment'],
                ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'Type', 'data' => 'type', 'name' => 'model'],
                ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'Credit', 'data' => 'credit', 'name' => 'credit', 'className' => 'text-right'],
                ['orderable' => true, 'visible' => true, 'searchable' => true, 'title' => 'Debit', 'data' => 'debit', 'name' => 'debit', 'className' => 'text-right'],
                ['orderable' => true, 'visible' => false, 'title' => 'Status', 'data' => 'status', 'name' => 'status'],
                ['orderable' => false, 'visible' => true, 'searchable' => false, 'title' => 'Action', 'data' => 'action', 'name' => 'action'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) { $requestData['merchant_id'] = $data['merchant_id']; }
        if (isset($data['investor_id'])) { $requestData['investor_id'] = $data['investor_id']; }
        if (isset($data['status'])) { $requestData['status'] = $data['status']; }
        if (isset($data['from_date'])) { $requestData['from_date'] = $data['from_date']; }
        if (isset($data['to_date'])) { $requestData['to_date'] = $data['to_date']; }
        $data   = $this->IgetTransactionData($requestData);
        $count  = $data['count'];
        $datas  = clone $data['data'];
        $debit  = $data['data']->sum('debit');
        $credit = $data['data']->sum('credit');
        $sendButton = '';
        if ($count) {
            $sendButton = '<button type="button" id="updateButton" class="btn btn-info">Update All</button>';
        }
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        return \DataTables::of($datas)
        ->setTotalRecords($count)
        ->editColumn('AccountHead', function ($row) {
            if ($row->merchant_id) {
                $url = \URL::to('/admin/merchants/view', $row->merchant_id);
                return "<a target='blank' href='".$url."'>".strtoupper($row->AccountHead).'</a>';
            }
            if ($row->investor_id) {
                $url = \URL::to('/admin/investors/portfolio', $row->investor_id);
                return "<a target='blank' href='".$url."'>".strtoupper($row->AccountHead).'</a>';
            }
        })
        ->editColumn('Merchant', function ($row) {
            $url = \URL::to('/admin/merchants/view', $row->merchant_id);
            return "<a target='blank' href='".$url."' class='pull-right'>".strtoupper($row->Merchant).'</a>';
        })
        ->editColumn('date', function ($row) {
            $created_date = 'Created On '.FFM::datetime($row->created_at).' by '.get_user_name_with_session($row->creator_id);
            return "<a title='$created_date'>".FFM::date($row->date).'</a>';
        })
        ->editColumn('credit', function ($row) {
            return FFM::dollar($row->credit);
        })
        ->editColumn('debit', function ($row) {
            return FFM::dollar($row->debit);
        })
        ->editColumn('mode_of_payment', function ($row) {
            return $row->PaymentMethodName;
        })
        ->editColumn('status', function ($row) {
            return $row->statusName;
        })
        ->addColumn('type', function ($row) {
            $return = '';
            switch ($row->model) {
                case \App\ParticipentPayment::class:
                $return = 'Payment';
                break;
                case \App\MerchantUser::class:
                $return = 'Investment';
                break;
                case \App\InvestorTransaction::class:
                $return = 'InvestorTransaction';
                break;
            }
            return $return;
        })
        ->editColumn('action', function ($row) {
            return '<i table_id="'.$row->id.'" modal_name="'.$row->Merchant.'" class="glyphicon glyphicon-send singleSend pointer_cursor" title="Approve Transaction and Update"></i>';
        })
        ->addColumn('checkbox', function ($row) {
            return "<label class='chc'><input type='checkbox' name='".$row->AccountHead."' class='single_check' value='".$row->id."' onclick='uncheckMain();'><span class='checkmark checkk0'></span></label>";
        })
        ->rawColumns(['Merchant', 'action', 'checkbox', 'date', 'AccountHead'])
        ->with('credit', FFM::dollar($credit))
        ->with('debit', FFM::dollar($debit))
        ->with('sendButton', $sendButton)
        ->addIndexColumn()
        ->make(true);
    }
    public function IgetTransactionData($data = [])
    {
        $totalCount = $this->Transaction;
        if (isset($data['merchant_id'])) {
            $totalCount = $totalCount->wheremerchant_id($data['merchant_id']);
        }
        $totalCount=$totalCount->count();
        $tableData = $this->Transaction;
        if (isset($data['status'])) {
            if (is_array($data['status'])) {
                $tableData = $tableData->whereIn('status', $data['status']);
            } else {
                $tableData = $tableData->wherestatus($data['status']);
            }
        }
        if (isset($data['merchant_id'])) {
            $tableData = $tableData->wheremerchant_id($data['merchant_id']);
        }
        if (isset($data['investor_id'])) {
            $tableData = $tableData->whereinvestor_id($data['investor_id']);
        }
        if (isset($data['from_date'])) {
            $tableData = $tableData->where('date', '>=', $data['from_date']);
        }
        if (isset($data['to_date'])) {
            $tableData = $tableData->where('date', '<=', $data['to_date']);
        }
        $totalCountfilterd = $tableData->count();
        $tableData = $tableData->select(
            'id',
            'merchant_id',
            'Merchant',
            'investor_id',
            'AccountHead',
            'Investor',
            'model_id',
            'mode_of_payment',
            'model',
            'date',
            'amount',
            'debit',
            'credit',
            DB::raw('SUM(credit-debit) OVER (ORDER BY date ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) as balance'),
            'status',
            'created_at',
            'creator_id'
        );
        $return['count'] = $tableData->count();
        $return['data'] = $tableData;
        return $return;
    }
    public function IgetTransactionDataTable($request)
    {
        $data = [];
        if ($request['status']) { $data['status'] = $request['status']; }
        if ($request['merchant_id']) { $data['merchant_id'] = $request['merchant_id']; }
        $TableData = $this->IgetTransactionData($data);
        $datas   = clone $TableData['data'];
        $credit  = $TableData['data']->where('status', ParticipentPayment::StatusCompleted)->sum('credit');
        $debit   = $TableData['data']->where('status', ParticipentPayment::StatusCompleted)->sum('debit');
        $balance = $credit - $debit;
        session_set('all_users', $users = User::withTrashed()->select('id', 'name')->get()->getDictionary());
        return \DataTables::of($datas)
        ->setTotalRecords($TableData['count'])
        ->addColumn('Merchant', function ($row) {
            $url = \URL::to('/admin/merchants/view', $row->merchant_id);
            return "<a target='blank' href='".$url."'>".$row->Merchant.'</a>';
            return '';
        })
        ->editColumn('date', function ($row) {
            $created_date = 'Created On '.FFM::datetime($row->created_at).' by '.get_user_name_with_session($row->creator_id);
            return "<a title='$created_date'>".FFM::date($row->date).'</a>';
        })
        ->addColumn('balance', function ($row) {
            if ($row->status != Transaction::Deleted) {
                return FFM::dollar($row->balance);
            } else {
                return FFM::dollar(0);
            }
        })
        ->editColumn('credit', function ($row) {
            return FFM::dollar($row->credit);
        })
        ->editColumn('debit', function ($row) {
            return FFM::dollar($row->debit);
        })
        ->addColumn('type', function ($row) {
            $return = '';
            switch ($row->model) {
                case \App\ParticipentPayment::class:
                $return = 'Payment';
                break;
                case \App\MerchantUser::class:
                $return = 'Investment';
                break;
            }
            return $return;
        })
        ->editColumn('mode_of_payment', function ($row) {
            return $row->PaymentMethodName;
        })
        ->editColumn('status', function ($row) {
            return $row->StatusName;
        })
        ->rawColumns(['Merchant', 'date'])
        ->with('credit', FFM::dollar($credit))->with('debit', FFM::dollar($debit))
        ->with('balance', FFM::dollar($balance))
        ->addIndexColumn()
        ->make(true);
    }
    public function IPendingTransactions($request)
    {
        $requestData = [];
        if ($request->ajax() || $request->wantsJson()) {
            if ($request->status) { $requestData['status']           = $request->status; }
            if ($request->merchant_id) { $requestData['merchant_id'] = $request->merchant_id; }
            if ($request->investor_id) { $requestData['investor_id'] = $request->investor_id; }
            if ($request->from_date) { $requestData['from_date']     = $request->from_date; }
            if ($request->to_date) { $requestData['to_date']         = $request->to_date; }
            return $this->getTransactionList($requestData);
        } else {
            $Merchant = ParticipentPaymentView::where('status', ParticipentPayment::StatusPending)->pluck('Merchant', 'merchant_id')->toArray();
            $Investor = ParticipentPaymentView::where('status', ParticipentPayment::StatusPending)->pluck('Investor', 'investor_id')->toArray();
            $this->tableBuilder->ajax([
                'url' => route('admin::payments::pending-transactions-data'),
                'type' => 'post',
                'data' => 'function(data){
                    data._token      = "'.csrf_token().'";
                    data.from_date   = $("#from_date").val();
                    data.to_date     = $("#to_date").val();
                    data.status      = $("#status").val();
                    data.merchant_id = $("#merchant_id").val();
                    data.investor_id = $("#investor_id").val();
                }',
            ]);
            $this->tableBuilder->parameters([
                'fnCreatedRow' => "function (nRow, aData, iDataIndex) {
                    var info   = this.dataTable().api().page.info(); 
                    var page   = info.page; 
                    var length = info.length; 
                    var index  = (page * length + (iDataIndex + 1)); 
                    $('td:eq(1)', nRow).html(index).addClass('txt-center');
                }",
                'footerCallback' => 'function(t,o,a,l,m){
                    if(typeof table !== "undefined") { 
                        var n=this.api(),o=table.ajax.json();
                        $(n.column(6).footer()).html(o.credit);
                        $(n.column(7).footer()).html(o.debit);
                        $(n.column(9).footer()).html(o.sendButton);
                    }
                }',
                'pagingType' => 'input',
                'serverSide' => false,
                'pageLength' => 100,
                'order' => [
                    1,  'asc',
                ],
            ]);
            $this->tableBuilder->parameters(['order' => [[3, 'desc']], 'pagingType' => 'input']);
            $requestData['columRequest'] = true;
            $this->tableBuilder->columns($this->getTransactionList($requestData));
            $investors = $this->role->allInvestors()->pluck('name', 'id');
            $return['tableBuilder'] = $this->tableBuilder;
            $return['investors']    = $investors;
            $return['Investor']     = $Investor;
            $return['Merchant']     = $Merchant;
        }
        return $return;
    }
    public function IApproveTransactions($request,$id=null)
    {
        DB::beginTransaction();
        $return['success_response'] = '';
        $return['success_count']    = 0;
        $return['failed_response']  = '';
        $return['failed_count']     = 0;
        if ($id) {
            $Self = ParticipentPaymentView::where('id', $id);
        } else {
            $data = $request->all();
            if (empty($data['selectedId'])) throw new \Exception('Empty Selection', 1);
            $Self = ParticipentPaymentView::where('status', ParticipentPayment::StatusPending);
            if ($data['from_date']) { $Self->where('date', '>=', $data['from_date']); }
            if ($data['to_date']) { $Self->where('date', '<=', $data['to_date']); }
            $Self->whereIn('id', $data['selectedId']);
        }
        $Self = $Self->get();
        if ($Self->count()) {
            foreach ($Self as $key => $value) {
                try {
                    $Model = [];
                    $amount = FFM::dollar($value->debit);
                    $name = $value->Merchant;
                    if ($value->Investor) {
                        $name = $value->Investor;
                    }
                    if ($value->credit) {
                        $amount = FFM::dollar($value->credit);
                    }
                    switch ($value->model) {
                        case \App\ParticipentPayment::class:
                        $Model = ParticipentPayment::find($value->id);
                        $Model->status = ParticipentPayment::StatusCompleted;
                        $return_function = GPH::ApprovePaymentFunction($Model->id);
                        if ($return_function['result'] != 'success') throw new \Exception($return_function['result'], 1);
                        $Model->save();
                        break;
                        case \App\InvestorTransaction::class:
                        $Model = ParticipentPayment::find($value->id);
                        break;
                        default:
                        dd('next Stage');
                        break;
                    }
                    if ($Model) {
                        $Model->status = ParticipentPayment::StatusCompleted;
                        $Model->approved_by = $request->user()->name;
                        $Model->approved_at = Carbon::now();
                        $Model->created_at  = Carbon::now();
                        $Model->save();
                        switch ($value->model) {
                            case \App\InvestorTransaction::class:
                            $description = \ITran::getLabel($Model->Model->transaction_category);
                            InvestorHelper::update_liquidity($value->investor_id, $description,$merchant_id = '', $liquidity_adjuster = '');
                            break;
                        }
                    }
                    $return['result'] = 'success';
                    $return['success_count']++;
                    $return['success_response'] .= 'Successfully Approved For '.$name.' on '.$amount.'<br>';
                } catch (\Exception $e) {
                    $return['result'] = $e->getMessage();
                    $return['failed_count']++;
                    $return['failed_response'] .= 'Failed For '.$name.' - '.$amount.'- Reason : '.$e->getMessage().'<br>';
                    DB::rollback();
                }
                $value->save();
                DB::commit();
            }
        } else {
            $return['failed_response'] = 'No Pending Transactions';
            $return['failed_count'] = '';
        }
        return $return;
    }
}
