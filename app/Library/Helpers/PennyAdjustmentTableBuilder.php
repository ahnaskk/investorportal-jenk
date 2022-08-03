<?php
/**
* Created by Rahees.
* User: rahees_iocod
* Date: 13/11/20
* Time: 1:15 AM.
*/

namespace App\Library\Helpers;

use App\Library\Repository\Interfaces\IPennyAdjustmenRepository;
use App\Models\Views\MerchantUserView;
use Carbon\Carbon;
use DataTables;
use FFM;
use Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

trait PennyAdjustmentTableBuilder
{
    protected $table;

    public function __construct(IPennyAdjustmenRepository $PennyAdjustment)
    {
        $this->PennyAdjustment = $PennyAdjustment;
        $this->loggedUser = Auth::user();
    }

    public function getLiqidityChangeList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>false, 'searchable'=>true, 'title' => 'user_id', 'data' => 'user_id', 'name' => 'user_id', 'className'=>'text-left'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Investor', 'data' => 'Investor', 'name' => 'Investor', 'className'=>'text-center'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Management fee', 'data' => 'paid_mgmnt_fee', 'name' => 'paid_mgmnt_fee', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Paid participant share', 'data' => 'paid_participant_ishare', 'name' => 'paid_participant_ishare', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'ctd', 'data' => 'ctd', 'name' => 'ctd', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'total funded', 'data' => 'total_funded', 'name' => 'total_funded', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'commission_amount', 'data' => 'commission_amount', 'name' => 'commission_amount', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'under writing fee', 'data' => 'under_writing_fee', 'name' => 'under_writing_fee', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'pre paid', 'data' => 'pre_paid', 'name' => 'pre_paid', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'total credits', 'data' => 'total_credits', 'name' => 'total_credits', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'existing liquidity', 'data' => 'existing_liquidity', 'name' => 'existing_liquidity', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'actual liquidity', 'data' => 'actual_liquidity', 'name' => 'actual_liquidity', 'className'=>'text-right text-capitalize'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'diff', 'data' => 'diff', 'name' => 'diff', 'className'=>'text-right text-capitalize'],
                ['orderable' => false, 'visible'=>true, 'searchable'=>false, 'title' => 'Action', 'data' => 'Action', 'name' => 'Action', 'className'=>'text-right text-capitalize'],
            ];
        }
        $requestData = [];
        if (isset($data['user_id'])) {
            $requestData['user_id'] = $data['user_id'];
        }
        if (isset($data['diff'])) {
            $requestData['diff'] = $data['diff'];
        }
        $datas = $this->PennyAdjustment->getLiquidityDifference($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Investor', function ($row) {
            return '<a href="'.url('admin/investors/portfolio/'.$row->user_id).'">'.$row->Investor.'</a>';
        })
        ->editColumn('paid_mgmnt_fee', function ($row) {
            return number_format($row->paid_mgmnt_fee, 4);
        })
        ->editColumn('paid_participant_ishare', function ($row) {
            return number_format($row->paid_participant_ishare, 4);
        })
        ->editColumn('ctd', function ($row) {
            return number_format($row->ctd, 4);
        })
        ->editColumn('total_funded', function ($row) {
            return number_format($row->total_funded, 4);
        })
        ->editColumn('commission_amount', function ($row) {
            return number_format($row->commission_amount, 4);
        })
        ->editColumn('under_writing_fee', function ($row) {
            return number_format($row->under_writing_fee, 4);
        })
        ->editColumn('pre_paid', function ($row) {
            return number_format($row->pre_paid, 4);
        })
        ->editColumn('total_credits', function ($row) {
            return number_format($row->total_credits, 4);
        })
        ->editColumn('existing_liquidity', function ($row) {
            return number_format($row->existing_liquidity, 4);
        })
        ->editColumn('actual_liquidity', function ($row) {
            return number_format($row->actual_liquidity, 4);
        })
        ->editColumn('diff', function ($row) {
            return number_format($row->diff, 4);
        })
        ->editColumn('Action', function ($row) {
            if ($row->diff) {
                return '<a href="'.route('admin::investors::liquidity_update', ['id' => $row->user_id]).'"><i class="glyphicon glyphicon-send"></i></a>';
            }
        })
        ->rawColumns(['Investor', 'Action'])
        ->addIndexColumn()
        ->with('paid_mgmnt_fee', FFM::dollar($datas['paid_mgmnt_fee']))
        ->with('paid_participant_ishare', FFM::dollar($datas['paid_participant_ishare']))
        ->with('ctd', FFM::dollar($datas['ctd']))
        ->with('total_funded', FFM::dollar($datas['total_funded']))
        ->with('commission_amount', FFM::dollar($datas['commission_amount']))
        ->with('under_writing_fee', FFM::dollar($datas['under_writing_fee']))
        ->with('pre_paid', FFM::dollar($datas['pre_paid']))
        ->with('total_credits', FFM::dollar($datas['total_credits']))
        ->with('existing_liquidity', FFM::dollar($datas['existing_liquidity']))
        ->with('actual_liquidity', FFM::dollar($datas['actual_liquidity']))
        ->with('diff', FFM::dollar($datas['diff']))
        ->make(true);
    }

    public function getMerchantValueDifferenceList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'funded', 'data' => 'funded', 'name' => 'funded', 'className'=>'text-capitalize text-left'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'max participant fund', 'data' => 'max_participant_fund', 'name' => 'max_participant_fund', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Funding percentage', 'data' => 'percentage', 'name' => 'percentage', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'factor rate', 'data' => 'factor_rate', 'name' => 'factor_rate', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'existing rtr', 'data' => 'existing_rtr', 'name' => 'existing_rtr', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'actual rtr', 'data' => 'actual_rtr', 'name' => 'actual_rtr', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'rtr diff', 'data' => 'rtr_diff', 'name' => 'rtr_diff', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'payment', 'data' => 'payment', 'name' => 'payment', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'balance', 'data' => 'balance', 'name' => 'balance', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'complete percentage', 'data' => 'complete_percentage', 'name' => 'complete_percentage', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'existing final participant share', 'data' => 'existing_final_participant_share', 'name' => 'existing_final_participant_share', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'actual final participant share', 'data' => 'actual_final_participant_share', 'name' => 'actual_final_participant_share', 'className'=>'text-capitalize text-right'],
                ['orderable' => false, 'visible'=>true, 'searchable'=>false, 'title' => 'diff final participant share', 'data' => 'diff_final_participant_share', 'name' => 'diff_final_participant_share', 'className'=>'text-capitalize text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        if (isset($data['rtr_diff'])) {
            $requestData['rtr_diff'] = $data['rtr_diff'];
        }
        if (isset($data['diff_final_participant_share'])) {
            $requestData['diff_final_participant_share'] = $data['diff_final_participant_share'];
        }
        $datas = $this->PennyAdjustment->getMerchantValueDifference($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row->merchant_id).'">'.$row->Merchant.'</a>';
        })
        ->editColumn('funded', function ($row) {
            return number_format($row->funded, 4);
        })
        ->editColumn('max_participant_fund', function ($row) {
            return number_format($row->max_participant_fund, 4);
        })
        ->editColumn('percentage', function ($row) {
            return round($row->percentage, 4);
        })
        ->editColumn('factor_rate', function ($row) {
            return number_format($row->factor_rate, 4);
        })
        ->editColumn('existing_rtr', function ($row) {
            return number_format($row->existing_rtr, 4);
        })
        ->editColumn('actual_rtr', function ($row) {
            return number_format($row->actual_rtr, 4);
        })
        ->editColumn('rtr_diff', function ($row) {
            return number_format($row->rtr_diff, 4);
        })
        ->editColumn('payment', function ($row) {
            return number_format($row->payment, 4);
        })
        ->editColumn('complete_percentage', function ($row) {
            return number_format($row->complete_percentage, 4);
        })
        ->editColumn('existing_final_participant_share', function ($row) {
            return number_format($row->existing_final_participant_share, 4);
        })
        ->addColumn('actual_final_participant_share', function ($row) {
            $return = DB::select('CALL payment_investors_check_procedure(?)', [$row->merchant_id]);

            return number_format($return[0]->actual_final_participant_share, 4);
        })
        ->addColumn('diff_final_participant_share', function ($row) {
            $return = DB::select('CALL payment_investors_check_procedure(?)', [$row->merchant_id]);

            return number_format(($return[0]->actual_final_participant_share - $row->existing_final_participant_share), 4);
        })
        // ->editColumn('Action', function ($row) {
        //     if($row->diff) {
        //         return '<a href="'.route('admin::investors::liquidity_update', ['id' => $row->user_id]).'"><i class="glyphicon glyphicon-send"></i></a>';
        //     }
        // })
        ->rawColumns(['Merchant', 'Action'])
        ->addIndexColumn()
        ->with('funded', FFM::dollar($datas['funded']))
        ->with('max_participant_fund', FFM::dollar($datas['max_participant_fund']))
        ->with('percentage', FFM::dollar($datas['percentage']))
        ->with('factor_rate', FFM::dollar($datas['factor_rate']))
        ->with('existing_rtr', FFM::dollar($datas['existing_rtr']))
        ->with('actual_rtr', FFM::dollar($datas['actual_rtr']))
        ->with('rtr_diff', FFM::dollar($datas['rtr_diff']))
        ->with('payment', FFM::dollar($datas['payment']))
        ->with('balance', FFM::dollar($datas['balance']))
        ->with('existing_final_participant_share', FFM::dollar($datas['existing_final_participant_share']))
        // ->with('actual_final_participant_share',FFM::dollar($datas['actual_final_participant_share']))
        // ->with('diff_final_participant_share',FFM::dollar($datas['diff_final_participant_share']))
        ->make(true);
    }

    public function getCompanyAmountDifferenceList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left details-control'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'funded', 'data' => 'funded', 'name' => 'funded', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'factor_rate', 'data' => 'factor_rate', 'name' => 'factor_rate', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'rtr', 'data' => 'rtr', 'name' => 'rtr', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'percentage', 'data' => 'percentage', 'name' => 'percentage', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'complete percentage', 'data' => 'complete_percentage', 'name' => 'complete_percentage', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'existing max participant fund', 'data' => 'existing_max_participant_fund', 'name' => 'existing_max_participant_fund', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'total company amount', 'data' => 'total_company_amount', 'name' => 'total_company_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'merchant company diff', 'data' => 'merchant_company_diff', 'name' => 'merchant_company_diff', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'invsetor company diff', 'data' => 'invsetor_company_diff', 'name' => 'invsetor_company_diff', 'className'=>'text-capitalize text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        if (isset($data['merchant_company_diff'])) {
            $requestData['merchant_company_diff'] = $data['merchant_company_diff'];
        }
        if (isset($data['invsetor_company_diff'])) {
            $requestData['invsetor_company_diff'] = $data['invsetor_company_diff'];
        }
        if (isset($data['percentage'])) {
            $requestData['percentage'] = $data['percentage'];
        }
        $datas = $this->PennyAdjustment->getCompanyAmountDifference($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row->merchant_id).'">'.$row->Merchant.'</a>';
        })
        ->editColumn('funded', function ($row) {
            return number_format($row->funded, 4);
        })
        ->editColumn('factor_rate', function ($row) {
            return number_format($row->factor_rate, 4);
        })
        ->editColumn('rtr', function ($row) {
            return number_format($row->rtr, 4);
        })
        ->editColumn('percentage', function ($row) {
            return number_format($row->percentage, 4);
        })
        ->editColumn('complete_percentage', function ($row) {
            return number_format($row->complete_percentage, 4);
        })
        ->editColumn('Actual_Company89', function ($row) {
            return number_format($row->Actual_Company89, 4);
        })
        ->editColumn('Existing_Company89', function ($row) {
            return number_format($row->Existing_Company89, 4);
        })
        ->editColumn('Diff_Company89', function ($row) {
            return number_format($row->Diff_Company89, 4);
        })
        ->editColumn('Actual_Company284', function ($row) {
            return number_format($row->Actual_Company284, 4);
        })
        ->editColumn('Existing_Company284', function ($row) {
            return number_format($row->Existing_Company284, 4);
        })
        ->editColumn('Diff_Company284', function ($row) {
            return number_format($row->Diff_Company284, 4);
        })
        ->editColumn('Actual_Company58', function ($row) {
            return number_format($row->Actual_Company58, 4);
        })
        ->editColumn('Existing_Company58', function ($row) {
            return number_format($row->Existing_Company58, 4);
        })
        ->editColumn('Diff_Company58', function ($row) {
            return number_format($row->Diff_Company58, 4);
        })
        ->editColumn('existing_max_participant_fund', function ($row) {
            return number_format($row->existing_max_participant_fund, 4);
        })
        ->editColumn('total_company_amount', function ($row) {
            return number_format($row->total_company_amount, 4);
        })
        ->editColumn('merchant_company_diff', function ($row) {
            return number_format($row->merchant_company_diff, 4);
        })
        ->editColumn('invsetor_company_diff', function ($row) {
            return number_format($row->invsetor_company_diff, 4);
        })
        ->rawColumns(['Merchant'])
        ->with('merchant_company_diff', FFM::dollar($datas['merchant_company_diff']))
        ->with('invsetor_company_diff', FFM::dollar($datas['invsetor_company_diff']))
        ->make(true);
    }

    public function getZeroParticipantAmountList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left details-control'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'amount', 'data' => 'amount', 'name' => 'amount', 'className'=>'text-capitalize text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        $datas = $this->PennyAdjustment->getZeroParticipantAmount($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row->merchant_id).'">'.$row->Merchant.'</a>';
        })
        ->AddColumn('List', function ($row) {
            $List = DB::table('participent_payments')->where('merchant_id', $row->merchant_id)
            ->where('payment', '!=', 0)
            ->where('final_participant_share', '=', 0)
            ->get(['payment_date', 'payment', 'final_participant_share'])->toArray();
            $List = collect($List)->map(function ($record) {
                return [
                    'final_participant_share'=> \FFM::dollar($record->final_participant_share),
                    'payment'                => \FFM::dollar($record->payment),
                    'payment_date'           => \FFM::date($record->payment_date),
                ];
            })->toArray();

            return $List;
        })
        ->editColumn('amount', function ($row) {
            return number_format($row->amount, 4);
        })
        ->rawColumns(['Merchant'])
        ->with('amount', FFM::dollar($datas['amount']))
        ->make(true);
    }

    public function getFinalParticipantShareList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'percentage', 'data' => 'percentage', 'name' => 'percentage', 'className'=>'text-capitalize text-right'],
                // ['orderable' => true , 'visible'=>true, 'searchable'=>true , 'title' => 'payment date'                       , 'data' => 'payment_date'                        , 'name' => 'payment_date'                        ,'className'=>"text-capitalize"],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'payment', 'data' => 'payment', 'name' => 'payment', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'expected', 'data' => 'expected_final_participant_share', 'name' => 'expected_final_participant_share', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'existing', 'data' => 'existing_final_participant_share', 'name' => 'existing_final_participant_share', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'expected existing Diff', 'data' => 'expected_existing_participant_share', 'name' => 'expected_existing_participant_share', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'actual', 'data' => 'actual_final_participant_share', 'name' => 'actual_final_participant_share', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'diff', 'data' => 'diff', 'name' => 'diff', 'className'=>'text-capitalize text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        if (isset($data['expected_existing_participant_share'])) {
            $requestData['expected_existing_participant_share'] = $data['expected_existing_participant_share'];
        }
        if (isset($data['diff'])) {
            $requestData['diff'] = $data['diff'];
        }
        $datas = $this->PennyAdjustment->getFinalParticipantShare($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row->merchant_id).'">'.$row->Merchant.'</a>';
        })
        ->editColumn('percentage', function ($row) {
            return round($row->percentage, 3);
        })
        ->editColumn('payment', function ($row) {
            return '$'.number_format($row->payment, 4);
        })
        ->editColumn('expected_final_participant_share', function ($row) {
            return '$'.number_format($row->expected_final_participant_share, 4);
        })
        ->editColumn('expected_existing_participant_share', function ($row) {
            return '$'.number_format($row->expected_existing_participant_share, 4);
        })
        ->editColumn('existing_final_participant_share', function ($row) {
            return '$'.number_format($row->existing_final_participant_share, 4);
        })
        ->editColumn('expected_existing_participant_share', function ($row) {
            return '$'.number_format($row->expected_existing_participant_share, 4);
        })
        ->editColumn('actual_final_participant_share', function ($row) {
            return '$'.number_format($row->actual_final_participant_share, 4);
        })
        ->editColumn('diff', function ($row) {
            return '$'.number_format($row->diff, 4);
        })
        ->rawColumns(['Merchant'])
        ->with('expected_existing_participant_share', FFM::dollar($datas['expected_existing_participant_share']))
        ->with('diff', FFM::dollar($datas['diff']))
        ->make(true);
    }

    public function getMerchantInvestorShareDifferenceList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Investor', 'data' => 'Investor', 'name' => 'Investor', 'className'=>'text-capitalize text-left'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'complete per', 'data' => 'complete_per', 'name' => 'complete_per', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'actual completed percentage', 'data' => 'actual_completed_percentage', 'name' => 'actual_completed_percentage', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'diff', 'data' => 'diff', 'name' => 'diff', 'className'=>'text-capitalize text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        if (isset($data['diff'])) {
            $requestData['diff'] = $data['diff'];
        }
        $datas = $this->PennyAdjustment->getMerchantInvestorShareDifference($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row->merchant_id).'">'.$row->Merchant.'</a>';
        })
        ->editColumn('Investor', function ($row) {
            return '<a href="'.url('admin/investors/portfolio/'.$row->investor_id).'">'.$row->Investor.'</a>';
        })
        ->editColumn('complete_per', function ($row) {
            return '$'.number_format($row->complete_per, 4);
        })
        ->editColumn('actual_completed_percentage', function ($row) {
            return '$'.number_format($row->actual_completed_percentage, 4);
        })
        ->editColumn('diff', function ($row) {
            return '$'.number_format($row->diff, 4);
        })
        ->rawColumns(['Merchant', 'Investor'])
        ->with('diff', FFM::dollar($datas['diff']))
        ->make(true);
    }

    public function getMerchantsFundAmountCheckList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left details-control'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'merchant %', 'data' => 'merchant_completed_percentate', 'name' => 'merchant_completed_percentate', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'amount', 'data' => 'amount', 'name' => 'amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'invest rtr', 'data' => 'invest_rtr', 'name' => 'invest_rtr', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'pre paid', 'data' => 'pre_paid', 'name' => 'pre_paid', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'total investment', 'data' => 'total_investment', 'name' => 'total_investment', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'commission amount', 'data' => 'commission_amount', 'name' => 'commission_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'expected mgmnt fee amount', 'data' => 'expected_mgmnt_fee_amount', 'name' => 'expected_mgmnt_fee_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'paid mgmnt fee', 'data' => 'paid_mgmnt_fee', 'name' => 'paid_mgmnt_fee', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'mgmnt fee diff', 'data' => 'mgmnt_fee_diff', 'name' => 'mgmnt_fee_diff', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'paid participant ishare', 'data' => 'paid_participant_ishare', 'name' => 'paid_participant_ishare', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'user balance amount', 'data' => 'user_balance_amount', 'name' => 'user_balance_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => false, 'visible'=>true, 'searchable'=>false, 'title' => 'Action', 'data' => 'InvestmentFloorValue', 'name' => 'InvestmentFloorValue', 'className'=>'text-capitalize'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        if (isset($data['mgmnt_fee_diff'])) {
            $requestData['mgmnt_fee_diff'] = $data['mgmnt_fee_diff'];
        }
        if (isset($data['percentage'])) {
            $requestData['percentage'] = $data['percentage'];
        }
        $datas = $this->PennyAdjustment->getMerchantsFundAmountCheck($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row->merchant_id).'">'.$row->Merchant.'</a>';
        })
        ->addColumn('List', function ($row) {
            $List = MerchantUserView::where('merchant_id', $row->merchant_id)->get()->toArray();

            return $List;
        })
        ->addColumn('InvestmentFloorValue', function ($row) {
            return '<a target="_blank" href="'.url('PennyAdjustment/InvestmentAmountAdjuster/'.$row->merchant_id).'">Make Floor Value</a>';
        })
        ->editColumn('amount', function ($row) {
            return '$'.number_format($row->amount, 4);
        })
        ->editColumn('invest_rtr', function ($row) {
            return '$'.number_format($row->invest_rtr, 4);
        })
        ->editColumn('pre_paid', function ($row) {
            return '$'.number_format($row->pre_paid, 4);
        })
        ->editColumn('total_investment', function ($row) {
            return '$'.number_format($row->total_investment, 4);
        })
        ->editColumn('commission_amount', function ($row) {
            return '$'.number_format($row->commission_amount, 4);
        })
        ->editColumn('expected_mgmnt_fee_amount', function ($row) {
            return '$'.number_format($row->expected_mgmnt_fee_amount, 4);
        })
        ->editColumn('paid_mgmnt_fee', function ($row) {
            return '$'.number_format($row->paid_mgmnt_fee, 4);
        })
        ->editColumn('mgmnt_fee_diff', function ($row) {
            return '$'.number_format($row->mgmnt_fee_diff, 4);
        })
        ->editColumn('paid_participant_ishare', function ($row) {
            return '$'.number_format($row->paid_participant_ishare, 4);
        })
        ->editColumn('user_balance_amount', function ($row) {
            return '$'.number_format($row->user_balance_amount, 4);
        })
        ->rawColumns(['Merchant', 'Investor', 'InvestmentFloorValue'])
        ->with('mgmnt_fee_diff', FFM::dollar($datas['mgmnt_fee_diff']))
        ->with('user_balance_amount', FFM::dollar($datas['user_balance_amount']))
        ->make(true);
    }

    public function getInvestmentAmountCheckList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left details-control'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'merchant %', 'data' => 'merchant_completed_percentate', 'name' => 'merchant_completed_percentate', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'actual amount', 'data' => 'actual_amount', 'name' => 'actual_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'floor amount', 'data' => 'floor_amount', 'name' => 'floor_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'diff amount', 'data' => 'diff_amount', 'name' => 'diff_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'actual invest rtr', 'data' => 'actual_invest_rtr', 'name' => 'actual_invest_rtr', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'floor invest rtr', 'data' => 'floor_invest_rtr', 'name' => 'floor_invest_rtr', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'diff invest rtr', 'data' => 'diff_invest_rtr', 'name' => 'diff_invest_rtr', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Action', 'data' => 'Action', 'name' => 'Action', 'className'=>'text-capitalize'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        if (isset($data['diff_amount'])) {
            $requestData['diff_amount'] = $data['diff_amount'];
        }
        $datas = $this->PennyAdjustment->getInvestmentAmountCheck($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row->merchant_id).'">'.$row->Merchant.'</a>';
        })
        ->addColumn('List', function ($row) {
            $List = DB::table('investment_amount_check_view')->where('merchant_id', $row->merchant_id)->get()->toArray();

            return $List;
        })
        ->addColumn('Action', function ($row) {
            return '<a target="_blank" href="'.url('PennyAdjustment/InvestmentAmountAdjuster/'.$row->merchant_id).'">Make Floor Value</a>';
        })
        ->editColumn('actual_amount', function ($row) {
            return '$'.number_format($row->actual_amount, 4);
        })
        ->editColumn('floor_amount', function ($row) {
            return '$'.number_format($row->floor_amount, 4);
        })
        ->editColumn('diff_amount', function ($row) {
            return '$'.number_format($row->diff_amount, 4);
        })
        ->editColumn('actual_invest_rtr', function ($row) {
            return '$'.number_format($row->actual_invest_rtr, 4);
        })
        ->editColumn('floor_invest_rtr', function ($row) {
            return '$'.number_format($row->floor_invest_rtr, 4);
        })
        ->editColumn('diff_invest_rtr', function ($row) {
            return '$'.number_format($row->diff_invest_rtr, 4);
        })
        ->rawColumns(['Merchant', 'Investor', 'Action'])
        ->with('diff_amount', FFM::dollar($datas['diff_amount']))
        ->with('diff_invest_rtr', FFM::dollar($datas['diff_invest_rtr']))
        ->make(true);
    }

    public function getPennyInvestmentList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left details-control'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Investor', 'data' => 'Investor', 'name' => 'Investor', 'className'=>'text-capitalize text-left'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'merchant %', 'data' => 'merchant_completed_percentate', 'name' => 'merchant_completed_percentate', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'amount', 'data' => 'amount', 'name' => 'amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'invest rtr', 'data' => 'invest_rtr', 'name' => 'invest_rtr', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'under writing fee', 'data' => 'under_writing_fee', 'name' => 'under_writing_fee', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'pre paid', 'data' => 'pre_paid', 'name' => 'pre_paid', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'commission amount', 'data' => 'commission_amount', 'name' => 'commission_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'total investment', 'data' => 'total_investment', 'name' => 'total_investment', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'expected mgmnt fee amount', 'data' => 'expected_mgmnt_fee_amount', 'name' => 'expected_mgmnt_fee_amount', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'paid mgmnt fee', 'data' => 'paid_mgmnt_fee', 'name' => 'paid_mgmnt_fee', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'mgmnt fee diff', 'data' => 'mgmnt_fee_diff', 'name' => 'mgmnt_fee_diff', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'paid participant ishare', 'data' => 'paid_participant_ishare', 'name' => 'paid_participant_ishare', 'className'=>'text-capitalize text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'user balance amount', 'data' => 'user_balance_amount', 'name' => 'user_balance_amount', 'className'=>'text-capitalize text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) {
            $requestData['merchant_id'] = $data['merchant_id'];
        }
        if (isset($data['percentage'])) {
            $requestData['percentage'] = $data['percentage'];
        }
        $datas = $this->PennyAdjustment->getPennyInvestmentCheck($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row->merchant_id).'">'.$row->Merchant.'</a>';
        })
        ->editColumn('Investor', function ($row) {
            return '<a href="'.url('admin/investors/portfolio/'.$row->investor_id).'">'.$row->Investor.'</a>';
        })
        ->addColumn('List', function ($row) {
            $List = DB::table('payment_investors')
            ->where('merchant_id', $row->merchant_id)
            ->where('user_id', $row->investor_id)
            ->orderByDesc('participent_payment_id')
            ->get()->toArray();

            return $List;
        })
        ->editColumn('merchant_completed_percentate', function ($row) {
            return '$'.number_format($row->merchant_completed_percentate, 4);
        })
        ->editColumn('amount', function ($row) {
            return '$'.number_format($row->amount, 4);
        })
        ->editColumn('invest_rtr', function ($row) {
            return '$'.number_format($row->invest_rtr, 4);
        })
        ->editColumn('under_writing_fee', function ($row) {
            return '$'.number_format($row->under_writing_fee, 4);
        })
        ->editColumn('pre_paid', function ($row) {
            return '$'.number_format($row->pre_paid, 4);
        })
        ->editColumn('commission_amount', function ($row) {
            return '$'.number_format($row->commission_amount, 4);
        })
        ->editColumn('total_investment', function ($row) {
            return '$'.number_format($row->total_investment, 4);
        })
        ->editColumn('expected_mgmnt_fee_amount', function ($row) {
            return '$'.number_format($row->expected_mgmnt_fee_amount, 4);
        })
        ->editColumn('paid_mgmnt_fee', function ($row) {
            return '$'.number_format($row->paid_mgmnt_fee, 4);
        })
        ->editColumn('mgmnt_fee_diff', function ($row) {
            return '$'.number_format($row->mgmnt_fee_diff, 4);
        })
        ->editColumn('paid_participant_ishare', function ($row) {
            return '$'.number_format($row->paid_participant_ishare, 4);
        })
        ->editColumn('user_balance_amount', function ($row) {
            return '$'.number_format($row->user_balance_amount, 4);
        })
        ->rawColumns(['Merchant', 'Investor'])
        ->with('amount', FFM::dollar($datas['amount']))
        ->with('invest_rtr', FFM::dollar($datas['invest_rtr']))
        ->with('under_writing_fee', FFM::dollar($datas['under_writing_fee']))
        ->with('pre_paid', FFM::dollar($datas['pre_paid']))
        ->with('commission_amount', FFM::dollar($datas['commission_amount']))
        ->with('total_investment', FFM::dollar($datas['total_investment']))
        ->with('expected_mgmnt_fee_amount', FFM::dollar($datas['expected_mgmnt_fee_amount']))
        ->with('paid_mgmnt_fee', FFM::dollar($datas['paid_mgmnt_fee']))
        ->with('mgmnt_fee_diff', FFM::dollar($datas['mgmnt_fee_diff']))
        ->with('paid_participant_ishare', FFM::dollar($datas['paid_participant_ishare']))
        ->with('user_balance_amount', FFM::dollar($datas['user_balance_amount']))
        ->make(true);
    }
    
    public function getMerchantRTRAndInvestorRtrList($data = [])
    {
        if (isset($data['columRequest'])) {
            return [
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant', 'data' => 'Merchant', 'name' => 'Merchant', 'className'=>'text-capitalize text-left'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Merchant RTR', 'data' => 'merchant_rtr', 'name' => 'merchant_rtr', 'className'=>'text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Syndication %', 'data' => 'syndication_percentage', 'name' => 'syndication_percentage', 'className'=>'text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Investor RTR', 'data' => 'investor_rtr', 'name' => 'investor_rtr', 'className'=>'text-right'],
                ['orderable' => true, 'visible'=>true, 'searchable'=>true, 'title' => 'Difference', 'data' => 'difference', 'name' => 'difference', 'className'=>'text-right'],
            ];
        }
        $requestData = [];
        if (isset($data['merchant_id'])) { $requestData['merchant_id'] = $data['merchant_id']; }
        $datas = $this->PennyAdjustment->getMerchantRTRAndInvestorRtrCheck($requestData);
        $count = $datas['count'];
        $data = $datas['data'];

        return \DataTables::of($data)
        ->setTotalRecords($count)
        ->editColumn('Merchant', function ($row) {
            return '<a href="'.url('admin/merchants/view/'.$row['merchant_id']).'">'.$row['Merchant'].'</a>';
        })
        ->editColumn('merchant_rtr', function ($row) {
            return FFM::dollar($row['merchant_rtr']);
        })
        ->editColumn('merchant_rtr', function ($row) {
            return FFM::dollar($row['merchant_rtr']);
        })
        ->editColumn('syndication_percentage', function ($row) {
            return FFM::percent($row['syndication_percentage']);
        })
        ->editColumn('difference', function ($row) {
            return FFM::dollar($row['difference']);
        })
        ->rawColumns(['Merchant'])
        ->make(true);
    }
}
