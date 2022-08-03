<?php

namespace App\Library\Helpers;

use App\Library\Repository\Interfaces\IReportRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Library\Repository\ReportRepository;
use App\Merchant;
use App\PaymentInvestors;
use App\Settings;
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

class ReportTableBuilder extends ReportRepository
{
    protected $ReportTable;

    public function __construct()
    {
        $this->loggedUser = Auth::user();
    }

    public function getFeeReportList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible' => true, 'title' => 'Date', 'data' => 'date', 'name' => 'date', 'className' => 'details-control'],
                ['orderable' => true, 'visible' => true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'width' => '70%'],
                ['orderable' => true, 'visible' => true, 'title' => 'Fee', 'data' => 'fee', 'name' => 'fee', 'className' => 'text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['from_date'])) {
            $requestData['from_date'] = $data['from_date'];
        }
        if (isset($data['to_date'])) {
            $requestData['to_date'] = $data['to_date'];
        }
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        $data = $this->getFeesReport($requestData);
        $count = $data['count'];
        $datas = $data['data'];
        $totalFee = $data['data']->sum('fee');

        return \DataTables::of($datas)
                          ->setTotalRecords($count)
                          ->editColumn('Merchant', function ($row) {
                              return $row->Merchant;
                          })
                          ->editColumn('fee', function ($row) {
                              return $row->fee;
                          })
                          ->editColumn('date', function ($row) {
                              $user = User::where('id', $row->creator_id)->value('name');
                              $user = ($user) ? $user : '--';
                              $created_date = 'Created On '.FFM::datetime($row->created_at).' by '.$user;

                              return "<a title='$created_date'>".FFM::date($row->date).'</a>';
                          })
                          ->editColumn('Details', function ($row) {
                              $table = DB::table('payment_investors')->join('participent_payments', 'payment_investors.participent_payment_id', 'participent_payments.id');
                              $table = $table->join('users', 'payment_investors.user_id', 'users.id');
                              $table = $table->where('participent_payments.merchant_id', $row->merchant_id);
                              $table = $table->where('participent_payments.payment_date', $row->date);
                              $table = $table->where('payment_investors.mgmnt_fee', '!=', 0);
                              $table = $table->select([
                                                          DB::raw('round(payment_investors.mgmnt_fee,2) as mgmnt_fee'),
                                                          'users.name as User',
                                                          'users.id',
                                                      ]);
                              $table = $table->get();
                              $table = $table->toArray();

                              return $table;
                          })
                          ->filterColumn('Merchant', function ($query, $keyword) {
                              $sql = 'Merchant  like ?';
                              $query->whereRaw($sql, ["%{$keyword}%"]);
                          })
                          ->filterColumn('date', function ($query, $keyword) {
                              $query->whereRaw("DATE_FORMAT(date,'%m-%d-%Y') like ?", ["%$keyword%"]);
                          })
                          ->rawColumns(['Details', 'date'])
                          ->with('totalFee', FFM::dollar($totalFee))
                          ->addIndexColumn()
                          ->make(true);
    }

    public function getInvestorLiquidityLogList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible' => true, 'title' => 'Date', 'data' => 'date', 'name' => 'date'],
                ['orderable' => true, 'visible' => true, 'title' => 'Company', 'data' => 'Company', 'name' => 'Company', 'width' => '30%'],
                ['orderable' => true, 'visible' => true, 'title' => 'Investor', 'data' => 'Investor', 'name' => 'Investor', 'width' => '30%'],
                ['orderable' => true, 'visible' => true, 'title' => 'Liquidity', 'data' => 'liquidity', 'name' => 'liquidity', 'className' => 'text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['from_date'])) {
            $requestData['from_date'] = $data['from_date'];
        }
        if (isset($data['to_date'])) {
            $requestData['to_date'] = $data['to_date'];
        }
        if (isset($data['company_id'])) {
            $requestData['company_id'] = $data['company_id'];
        }
        if (isset($data['investor_id'])) {
            $requestData['investor_id'] = $data['investor_id'];
        }
        $data = $this->getInvestorLiquidityLog($requestData);
        $count = $data['count'];
        $datas = $data['data'];

        return \DataTables::of($datas)
                          ->setTotalRecords($count)
                          ->editColumn('date', function ($row) {
                              $user = ($row->owner) ? $row->owner->name : '--';
                              $created_date = 'Created On '.FFM::datetime($row->created_at).' by '.$user;

                              return "<a title='$created_date'>".FFM::date($row->date).'</a>';
                          })
                          ->editColumn('Investor', function ($row) {
                              return "<a href='/admin/investors/portfolio/$row->investor_id'>$row->Investor</a>";
                          })
                          ->editColumn('liquidity', function ($row) {
                              return FFM::dollar($row->liquidity);
                          })
                          ->rawColumns(['Investor', 'date'])
                          ->addIndexColumn()
                          ->make(true);
    }

    public function getInvestorRTRBalanceLogList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible' => true, 'title' => 'Date', 'data' => 'date', 'name' => 'date'],
                ['orderable' => true, 'visible' => true, 'title' => 'Company', 'data' => 'Company', 'name' => 'Company', 'width' => '30%'],
                ['orderable' => true, 'visible' => true, 'title' => 'Investor', 'data' => 'Investor', 'name' => 'Investor', 'width' => '30%'],
                ['orderable' => true, 'visible' => true, 'title' => 'RTRBalance', 'data' => 'rtr_balance', 'name' => 'rtr_balance', 'className' => 'text-right'],
                ['orderable' => true, 'visible' => false, 'title' => 'Default Value', 'data' => 'rtr_balance_default', 'name' => 'rtr_balance_default', 'className' => 'text-right'],
                ['orderable' => true, 'visible' => false, 'title' => 'Total', 'data' => 'total', 'name' => 'total', 'className' => 'text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['from_date'])) {
            $requestData['from_date'] = $data['from_date'];
        }
        if (isset($data['to_date'])) {
            $requestData['to_date'] = $data['to_date'];
        }
        if (isset($data['company_id'])) {
            $requestData['company_id'] = $data['company_id'];
        }
        if (isset($data['investor_id'])) {
            $requestData['investor_id'] = $data['investor_id'];
        }
        $data = $this->getInvestorRTRBalanceLog($requestData);
        $count = $data['count'];
        $datas = $data['data'];

        return \DataTables::of($datas)
                          ->setTotalRecords($count)
                          ->editColumn('date', function ($row) {
                              $user = ($row->owner) ? $row->owner->name : '--';
                              $created_date = 'Created On '.FFM::datetime($row->created_at).' by '.$user;

                              return "<a title='$created_date'>".FFM::date($row->date).'</a>';
                          })
                          ->editColumn('total', function ($row) {
                              return FFM::dollar($row->rtr_balance - $row->rtr_balance_default);
                          })
                          ->editColumn('Investor', function ($row) {
                              return "<a href='/admin/investors/portfolio/$row->investor_id'>$row->Investor</a>";
                          })
                          ->editColumn('rtr_balance', function ($row) {
                              return "<span title='".$row->details."'>".FFM::dollar($row->rtr_balance).'</span>';
                          })
                          ->editColumn('rtr_balance_default', function ($row) {
                              return FFM::dollar($row->rtr_balance_default);
                          })
                          ->rawColumns(['Investor', 'rtr_balance', 'date'])
                          ->addIndexColumn()
                          ->make(true);
    }
}
