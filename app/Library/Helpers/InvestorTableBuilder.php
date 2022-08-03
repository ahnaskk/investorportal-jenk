<?php

namespace App\Library\Helpers;

use App\Library\Repository\Interfaces\IInvestorRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Models\InvestorAchRequest;
use App\User;
use Carbon\Carbon;
use DataTables;
use FFM;
use Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Permissions;

class InvestorTableBuilder
{
    public function __construct(IInvestorRepository $investor)
    {
        $this->investor = $investor;
        $this->loggedUser = Auth::user();
    }

    public function getInvestorAchRequestAll($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'  => false, 'searchable' => true, 'title'  => 'id', 'data'                   => 'id', 'name'                   => 'id'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'date', 'data'                 => 'date', 'name'                 => 'date'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'Investor', 'data'             => 'Investor', 'name'             => 'Investor'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'order id', 'data'             => 'order_id', 'name'             => 'order_id'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'transaction type', 'data'     => 'transaction_type', 'name'     => 'transaction_type'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'transaction method', 'data'   => 'transaction_method', 'name'   => 'transaction_method'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'transaction category', 'data' => 'transaction_category', 'name' => 'transaction_category'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'amount', 'data'               => 'amount', 'name'               => 'amount', 'className' => 'text-right'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'Request Status', 'data'       => 'ach_status', 'name'           => 'ach_status'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'Settlement Status', 'data'    => 'ach_request_status', 'name'   => 'ach_request_status'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'auth code', 'data'            => 'auth_code', 'name'            => 'auth_code'],
                ['orderable' => true, 'visible'  => false, 'searchable' => true, 'title'  => 'reason', 'data'               => 'reason', 'name'               => 'reason'],
                ['orderable' => true, 'visible'  => true, 'searchable'  => true, 'title'  => 'status response', 'data'      => 'status_response', 'name'      => 'status_response'],
                ['orderable' => true, 'visible'  => false, 'searchable' => true, 'title'  => 'request ip address', 'data'   => 'request_ip_address', 'name'   => 'request_ip_address'],
                ['orderable' => true, 'visible'  => false, 'searchable' => true, 'title'  => 'Updated At', 'data'           => 'updated_at', 'name'           => 'updated_at'],
                ['orderable' => false, 'visible' => true, 'searchable'  => false, 'title' => 'action', 'data'               => 'action', 'name'               => 'action'], ];
        }
        $requestData = [];
        if (isset($data['investor_id'])) {
            $requestData['investor_id'] = $data['investor_id'];
        }
        if (isset($data['transaction_type'])) {
            $requestData['transaction_type'] = $data['transaction_type'];
        }
        if (isset($data['transaction_method'])) {
            $requestData['transaction_method'] = $data['transaction_method'];
        }
        if (isset($data['order_id'])) {
            $requestData['order_id'] = $data['order_id'];
        }
        if (isset($data['ach_request_status'])) {
            $requestData['ach_request_status'] = $data['ach_request_status'];
        }
        if (isset($data['ach_status'])) {
            $requestData['ach_status'] = $data['ach_status'];
        }
        if (isset($data['from_date'])) {
            $requestData['from_date'] = $data['from_date'];
        }
        if (isset($data['to_date'])) {
            $requestData['to_date'] = $data['to_date'];
        }
        $data = $this->investor->getInvestorAchRequestAll($requestData);
        $count = $data['count'];
        $datas = $data['data'];

        return DataTables::of($datas)->setTotalRecords($count)->addIndexColumn()->editColumn('date', function ($row) {
            $user = User::where('id', $row->creator_id)->value('name');
            $user = ($user) ? $user : '--';
            $created_date = InvestorAchRequest::find($row->id);
            $created_date = ($created_date) ? $created_date->created_at : null;
            $created_date = 'Created On '.FFM::datetime($created_date).' by '.$user;

            return "<a title='$created_date'>".$row->date.'</a>';
        })->editColumn('Investor', function ($row) {
            return "<a href='".url('admin/investors/portfolio/'.$row->investor_id)."'>$row->Investor</a>";
        })->editColumn('ach_status', function ($row) {
            return $row->AchStatusName;
        })->editColumn('transaction_method', function ($row) {
            return $row->TransactionMethodName;
        })->editColumn('transaction_category', function ($row) {
            return $row->TransactionCategoryName;
        })->editColumn('transaction_type', function ($row) {
            return '<span title="ACH '.$row->TransactionTypeName.' Request">'.$row->InvertedTransactionTypeName.'</span>';
        })->editColumn('auth_code', function ($row) {
            return "<div title='".$row->reason."'>$row->auth_code</div>";
        })->editColumn('ach_request_status', function ($row) {
            return "<div title='".$row->reason."'>$row->AchRequestStatusName</div>";
        })->editColumn('amount', function ($row) {
            $amount_without_dollar = str_replace('$', '', $row->amount);
            $amount = str_replace(',', '', $amount_without_dollar);

            return $row->amount.'<span style="display:none;">'.round($amount, 2).'</span>';
        })->addColumn('action', function ($row) {
            $return = '<div class="row">';
            if ($row->order_id) {
                $return .= '<div class="col-md-6"><i table_id="'.$row->id.'" class="glyphicon glyphicon-send check_status"></i></div>';
            } elseif ($row->ach_status == InvestorAchRequest::AchStatusPending) {
                $return .= '<div class="col-md-6"><i table_id="'.$row->id.'" class="glyphicon glyphicon-send check_status"></i></div>';
            } elseif ($row->ach_status == InvestorAchRequest::AchStatusDeclined) {
                $return .= '<div class="col-md-6"><i table_id="'.$row->id.'" class="glyphicon glyphicon-send check_status"></i></div>';
            }
            $return .= '<div class="col-md-6" hidden><i table_id="'.$row->id.'" order_id="'.$row->order_id.'" class="glyphicon glyphicon-edit edit"></i></div>';
            $return .= '</div>';

            return $return;
        })->rawColumns(['auth_code', 'action', 'ach_request_status', 'Investor', 'transaction_type', 'amount', 'date'])->make(true);
    }
}
