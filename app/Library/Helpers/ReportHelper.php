<?php
namespace App\Library\Helpers;
use App\Merchant;
use App\PaymentInvestors;
use App\Settings;
use App\User;
use App\MerchantUser;
use App\Label;
use App\ParticipentPayment;
use App\SubStatus;
use Carbon\Carbon;
use DataTables;
use FFM;
use Form;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Permissions;
use App\Exports\Data_arrExport;
class ReportHelper
{
    public function __construct() {
        $this->loggedUser = Auth::user();
    }
    
    public function TaxTableColumn() {
        return [
            ['data' => 'id', 'name'                      => 'id', 'title'                      => 'Merchant Id', 'orderable'                     => true],
            ['data' => 'merchant_name', 'name'           => 'merchant_name', 'title'           => 'Merchant', 'orderable'                => true],
            ['data' => 'status', 'name'                  => 'status', 'title'                  => 'Status', 'orderable'                  => false],
            ['data' => 'factor_rate', 'name'             => 'factor_rate', 'title'             => 'Factor Rate', 'orderable'             => false,'className'=>'text-right'],
            ['data' => 'date_funded', 'name'             => 'date_funded', 'title'             => 'Date Funded', 'orderable'             => true],
            ['data' => 'Lender', 'name'                  => 'Lender', 'title'                  => 'Lender Name', 'orderable'             => true],
            ['data' => 'rtr', 'name'                     => 'rtr', 'title'                     => 'Full RTR', 'orderable'                => true,'className'=>'text-right'],
            ['data' => 'our_rtr', 'name'                 => 'our_rtr', 'title'                 => 'Our RTR', 'orderable'                 => true,'className'=>'text-right'],
            ['data' => 'funded', 'name'                  => 'funded', 'title'                  => 'Funded Amount', 'orderable'           => true,'className'=>'text-right'],
            ['data' => 'our_funded', 'name'              => 'our_funded', 'title'              => 'Our Funded Amount', 'orderable'       => true,'className'=>'text-right','searchable'                => false],
            ['data' => 'syndication_balance', 'name'     => 'syndication_balance', 'title'     => 'IP Gross Balance', 'orderable'        => true,'className'=>'text-right','searchable'                => false],
            ['data' => 'syndication_net_balance', 'name' => 'syndication_net_balance', 'title' => 'IP Net Balance', 'orderable'          => true,'className'=>'text-right','searchable'                => false],
            ['data' => 'total_payments', 'name'          => 'total_payments', 'title'          => 'Total Payments', 'orderable'          => true,'className'=>'text-right','searchable'                => false],
            ['data' => 'velocity_gross_balance', 'name'  => 'velocity_gross_balance', 'title'  => 'Velocity Gross Balance', 'orderable'  => true,'className'=>'text-right','searchable'                => false],
            ['data' => 'velocity_owned_paid', 'name'     => 'velocity_owned_paid', 'title'     => 'Velocity MCA Rev (hist)', 'orderable' => true,'className'=>'text-right','searchable'                => false],
            ['data' => 'collected_mgmnt_fee', 'name'     => 'collected_mgmnt_fee', 'title'     => 'Management Fee Collected', 'orderable'=> true,'className'=>'text-right','searchable'                => false],
            ['data' => 'collected_syndication_fee','name'=> 'collected_syndication_fee','title'=> 'Syndication Fee Collected', 'orderable'=> true,'className'=>'text-right','searchable'                => false],
            ['data' => 'collected_commission', 'name'    => 'collected_commission', 'title'    => 'Commission Collected', 'orderable'     => true,'className'=>'text-right','searchable'                => false],
        ];
    }
    public function TaxReportData($request) {
        $velocity_owned = User::investors()->where('velocity_owned',1)->pluck('id')->toArray();
        $velocity_owned = (count($velocity_owned) > 0) ? implode(',', $velocity_owned) : '';
        $SpecialAccount = DB::table('users')->join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount = $SpecialAccount->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id', 'users.id')->toArray();
        $SpecialAccount = implode(',', $SpecialAccount);

        $total_full_rtr = $total_our_rtr = $total_funded_amount = $total_our_funded_amount = $total_syndication_balance = $total_syndication_net_balance = $total_velocity_owned_paid = $total_velocity_gross_balance = $payments_total = 0;
        $from_date_merchant_query = $to_date_merchant_query = '';
        $disabled_company=User::companies()->where('company_status',0)->pluck('users.id','users.id');
        if(count($disabled_company)){
            $disabled_company = $disabled_company->toArray();
        }
        $disabled_company_investors=User::whereIn('company',$disabled_company)->pluck('users.id','users.id');
        if(count($disabled_company_investors)){
            $disabled_company_investors = $disabled_company_investors->toArray();
        }
        $merchantUserQuery = $to_date_query = $velocity_user_query = $non_velocity_merchant_user_query = $velocity_merchant_user_query = $from_date_query = $filter_query = $merchant_user_filter_query = $special_accnt_query = $non_velocity_user_query = '';
        $disabled_company_investors = (count($disabled_company_investors) > 0) ? implode(',', $disabled_company_investors) : '';
        if($disabled_company_investors)
        $merchantUserQuery .=' AND merchant_user.user_id NOT IN ('.$disabled_company_investors.')';
        if($velocity_owned){
        $velocity_user_query .= ' AND payment_investors.user_id IN ('.$velocity_owned.')';
        $velocity_merchant_user_query .= ' AND merchant_user.user_id IN ('.$velocity_owned.')';
        $non_velocity_merchant_user_query .= ' AND merchant_user.user_id NOT IN ('.$velocity_owned.')';
        $non_velocity_user_query .= ' AND payment_investors.user_id NOT IN ('.$velocity_owned.')';//123,78,77,68,41,33,20,140
         }else{
        $velocity_user_query .= ' AND payment_investors.user_id IN (NULL)';
        $velocity_merchant_user_query .= ' AND merchant_user.user_id IN (NULL)';   
         }
        $sDate = $request['start_date'];
        $eDate = $request['end_date'];
        if ($eDate!= null) {
             $to_date_query .= " AND payment_date <= '$eDate'";
             $to_date_merchant_query  .= " AND date_funded <= '$eDate'";
        } 
        if ($sDate!= null) {
            $from_date_query .= " AND payment_date >= '$sDate'";
            $from_date_merchant_query .= " AND date_funded >= '$sDate'";
       } 


        $Merchant = new Merchant;
        $Merchant = $Merchant->when($request['label']??[], function($q, $value){
            return $q->whereIn('label', $value);
        });
        $Merchant = $Merchant->when($request['lender_ids']??[], function($q, $value){
            return $q->whereIn('lender_id', $value);
        });
        $Merchant = $Merchant->when($request['merchant_ids']??[], function($q, $value){
            return $q->whereIn('id', $value);
        });
        $Merchant = $Merchant->when($request['sub_status_ids']??[], function($q, $value){
            return $q->whereIn('sub_status_id', $value);
        });
        $Merchant = $Merchant->where('active_status',1);
        $merchant_ids = clone $Merchant;
        $merchant_ids = $Merchant->pluck('id','id')->toArray();
        $merchant_ids = implode(',', $merchant_ids);
        if($merchant_ids){
        $filter_query .= ' AND participent_payments.merchant_id IN ('.$merchant_ids.')';
        $merchant_user_filter_query .= ' AND merchant_user.merchant_id IN ('.$merchant_ids.')';
        }
        $special_accnt_query .= ' AND payment_investors.user_id NOT IN ('.$SpecialAccount.')';
        if($disabled_company_investors)
        $special_accnt_query .= '  AND payment_investors.user_id NOT IN ('.$disabled_company_investors.')';
        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(payment_investors.participant_share) as investor_paid_amount,SUM(payment_investors.mgmnt_fee) as mgmnt_fee,SUM(payment_investors.participant_share-payment_investors.mgmnt_fee) as net_investor_paid_amount,
        participent_payments.merchant_id FROM payment_investors  
        LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id 
        WHERE participent_payments.merchant_id > 0 $filter_query $to_date_query $special_accnt_query GROUP BY participent_payments.merchant_id) as merch_payment_sub"),'merch_payment_sub.merchant_id', '=', 'merchants.id');

        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(payment_investors.participant_share) as investor_paid_amount,SUM(payment_investors.mgmnt_fee) as mgmnt_fee,SUM(payment_investors.participant_share-payment_investors.mgmnt_fee) as net_investor_paid_amount,
        participent_payments.merchant_id FROM payment_investors  
        LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id 
        WHERE participent_payments.merchant_id > 0 $filter_query $velocity_user_query $to_date_query $from_date_query $special_accnt_query GROUP BY participent_payments.merchant_id) as vel_merch_payment_sub"),'vel_merch_payment_sub.merchant_id', '=', 'merchants.id');

        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(payment_investors.participant_share) as investor_paid_amount,SUM(payment_investors.mgmnt_fee) as mgmnt_fee,SUM(payment_investors.participant_share-payment_investors.mgmnt_fee) as net_investor_paid_amount,
        participent_payments.merchant_id FROM payment_investors  
        LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id 
        WHERE participent_payments.merchant_id > 0 $filter_query $non_velocity_user_query $to_date_query $from_date_query $special_accnt_query GROUP BY participent_payments.merchant_id) as fee_collected"),'fee_collected.merchant_id', '=', 'merchants.id');

        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(payment_investors.participant_share) as investor_paid_amount,SUM(payment_investors.mgmnt_fee) as mgmnt_fee,
        participent_payments.merchant_id FROM payment_investors  
        LEFT JOIN participent_payments on payment_investors.participent_payment_id=participent_payments.id 
        WHERE participent_payments.merchant_id > 0 $filter_query $velocity_user_query $to_date_query $special_accnt_query GROUP BY participent_payments.merchant_id) as vel_merch_payment_bal_sub"),'vel_merch_payment_bal_sub.merchant_id', '=', 'merchants.id');

        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(merchant_user.amount) as amount,SUM(merchant_user.pre_paid) as syndication_fee,SUM(merchant_user.commission_amount) as commission_amount,merchant_user.merchant_id,SUM(merchant_user.invest_rtr) as our_rtr,SUM(merchant_user.invest_rtr-invest_rtr*mgmnt_fee/100) as net_our_rtr FROM merchant_user  
        LEFT JOIN merchants on merchants.id=merchant_user.merchant_id 
        WHERE merchant_user.merchant_id > 0 $merchant_user_filter_query $merchantUserQuery
        GROUP BY merchant_user.merchant_id) as merchant_user_sub"),'merchant_user_sub.merchant_id', '=', 'merchants.id');

        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(merchant_user.amount) as amount,SUM(merchant_user.pre_paid) as syndication_fee,SUM(merchant_user.commission_amount) as commission_amount,merchant_user.merchant_id,SUM(merchant_user.invest_rtr) as our_rtr,SUM(merchant_user.invest_rtr-invest_rtr*mgmnt_fee/100) as net_our_rtr FROM merchant_user  
        LEFT JOIN merchants on merchants.id=merchant_user.merchant_id 
        WHERE merchant_user.merchant_id > 0 $merchant_user_filter_query $merchantUserQuery $velocity_merchant_user_query
        GROUP BY merchant_user.merchant_id) as vel_merchant_user_sub"),'vel_merchant_user_sub.merchant_id', '=', 'merchants.id');

        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(merchant_user.pre_paid) as syndication_fee,merchant_user.merchant_id,SUM(merchant_user.invest_rtr) as our_rtr,SUM(merchant_user.invest_rtr-invest_rtr*mgmnt_fee/100) as net_our_rtr FROM merchant_user  
        LEFT JOIN merchants on merchants.id=merchant_user.merchant_id 
        WHERE merchant_user.merchant_id > 0 $from_date_merchant_query $to_date_merchant_query $merchant_user_filter_query $merchantUserQuery $non_velocity_merchant_user_query
        GROUP BY merchant_user.merchant_id) as all_merchant_user_fee"),'all_merchant_user_fee.merchant_id', '=', 'merchants.id');

        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(merchant_user.amount) as amount,SUM(merchant_user.pre_paid) as syndication_fee,SUM(merchant_user.commission_amount) as commission_amount,merchant_user.merchant_id,SUM(merchant_user.invest_rtr) as our_rtr,SUM(merchant_user.invest_rtr-invest_rtr*mgmnt_fee/100) as net_our_rtr FROM merchant_user  
        LEFT JOIN merchants on merchants.id=merchant_user.merchant_id 
        WHERE merchant_user.merchant_id > 0 $from_date_merchant_query $to_date_merchant_query $merchant_user_filter_query $merchantUserQuery
        GROUP BY merchant_user.merchant_id) as all_merchant_commission"),'all_merchant_commission.merchant_id', '=', 'merchants.id');

        $Merchant = $Merchant->leftJoin(DB::raw("(SELECT SUM(payment) as total_payments,participent_payments.merchant_id FROM participent_payments  
        LEFT JOIN merchants on merchants.id=participent_payments.merchant_id 
        WHERE participent_payments.merchant_id > 0 AND participent_payments.is_payment = 1  $filter_query $to_date_query $from_date_query
        GROUP BY participent_payments.merchant_id) as participant_payments_dt"),'participant_payments_dt.merchant_id', '=', 'merchants.id');
    
        $Merchant = $Merchant->select('merchants.id','merchants.sub_status_id','merchants.lender_id','merchants.name','date_funded','merchants.funded','merchants.factor_rate','merchants.rtr','merch_payment_sub.investor_paid_amount','merchant_user_sub.amount as our_funded','merch_payment_sub.mgmnt_fee','merchant_user_sub.our_rtr','all_merchant_user_fee.syndication_fee as collected_syndication_fee',DB::raw("(merchant_user_sub.our_rtr-IF(merch_payment_sub.investor_paid_amount,merch_payment_sub.investor_paid_amount,0)) as syndication_balance"),DB::raw("(merchant_user_sub.net_our_rtr-IF(merch_payment_sub.net_investor_paid_amount,merch_payment_sub.net_investor_paid_amount,0)) as syndication_net_balance"),"vel_merch_payment_sub.investor_paid_amount as velocity_owned_paid",DB::raw("(vel_merchant_user_sub.our_rtr-IF(vel_merch_payment_bal_sub.investor_paid_amount,vel_merch_payment_bal_sub.investor_paid_amount,0)) as velocity_gross_balance"),'participant_payments_dt.total_payments',DB::raw("(all_merchant_commission.commission_amount) as collected_commission"),DB::raw("(merchant_user_sub.syndication_fee-IF(vel_merchant_user_sub.syndication_fee,vel_merchant_user_sub.syndication_fee,0)) as collected_syndication_feenn"),'fee_collected.mgmnt_fee as collected_mgmnt_fee');
        
        $result['data'] = $Merchant;
        $Merchant =  $Merchant->get();
        $total['total_full_rtr'] = array_sum(array_column($Merchant->toArray(), 'rtr'));//
        $total['total_our_rtr'] = array_sum(array_column($Merchant->toArray(), 'our_rtr'));//$total_our_rtr;
        $total['total_funded_amount'] = array_sum(array_column($Merchant->toArray(), 'funded'));//$total_funded_amount;
        $total['total_our_funded_amount'] = array_sum(array_column($Merchant->toArray(), 'our_funded'));//$total_our_funded_amount;
        $total['total_syndication_balance'] = array_sum(array_column($Merchant->toArray(), 'syndication_balance'));//$total_syndication_balance;
        $total['total_syndication_net_balance'] = array_sum(array_column($Merchant->toArray(), 'syndication_net_balance'));//$total_syndication_net_balance;
        $total['total_velocity_owned_paid'] = array_sum(array_column($Merchant->toArray(), 'velocity_owned_paid'));//$total_velocity_owned_paid;
        $total['total_velocity_gross_balance'] = array_sum(array_column($Merchant->toArray(), 'velocity_gross_balance'));//$total_velocity_gross_balance;
        $total['payments_total'] = array_sum(array_column($Merchant->toArray(), 'total_payments'));//$payments_total;
        $total['total_mgmnt_fee_collected'] = array_sum(array_column($Merchant->toArray(), 'collected_mgmnt_fee'));
        $total['total_syndication_fee_collected'] = array_sum(array_column($Merchant->toArray(), 'collected_syndication_fee'));
        $total['total_commission_collected'] = array_sum(array_column($Merchant->toArray(), 'collected_commission'));
        $result['total'] = $total;
        return $result;
    }
    public function TaxReportDataOld($request) {
        $Merchant = new Merchant;
        $Merchant = $Merchant->when($request['label']??[], function($q, $value){
            return $q->whereIn('label', $value);
        });
        $Merchant = $Merchant->when($request['lender_ids']??[], function($q, $value){
            return $q->whereIn('lender_id', $value);
        });
        $Merchant = $Merchant->when($request['merchant_ids']??[], function($q, $value){
            return $q->whereIn('id', $value);
        });
        $Merchant = $Merchant->when($request['sub_status_ids']??[], function($q, $value){
            return $q->whereIn('sub_status_id', $value);
        });
        $Merchant = $Merchant->get();
        $disabled_company=User::companies()->where('company_status',0)->pluck('users.id','users.id');
        if(count($disabled_company)){
            $disabled_company = $disabled_company->toArray();
        }
        $disabled_company_investors=User::whereIn('company',$disabled_company)->pluck('users.id','users.id');
        if(count($disabled_company_investors)){
            $disabled_company_investors = $disabled_company_investors->toArray();
        }
        $SpecialAccount = DB::table('users')->join('user_has_roles', 'user_has_roles.model_id', 'users.id');
        $SpecialAccount = $SpecialAccount->whereIn('user_has_roles.role_id', [User::AGENT_FEE_ROLE,User::OVERPAYMENT_ROLE]);
        $SpecialAccount = $SpecialAccount->pluck('users.id', 'users.id')->toArray();
        $total_full_rtr = $total_our_rtr = $total_funded_amount = $total_our_funded_amount = $total_syndication_balance = $total_syndication_net_balance = $total_velocity_owned_paid = $total_velocity_gross_balance = $payments_total = 0;
        foreach ($Merchant as $key => $value) {
            $ParticipentPayment = new ParticipentPayment;
            $ParticipentPayment = $ParticipentPayment->where('model','App\ParticipentPayment');
            $ParticipentPayment = $ParticipentPayment->where('merchant_id',$value->id);
            $ParticipentPayment = $ParticipentPayment->when($request['start_date']??'', function($q, $value){
                return $q->whereDate('payment_date','>=', $value);
            });
            $ParticipentPayment = $ParticipentPayment->when($request['end_date']??'', function($q, $value){
                return $q->whereDate('payment_date','<=', $value);
            });
            $total_payments = clone $ParticipentPayment;
            $total_payments = $total_payments->sum('payment');
            $MerchantUser = new MerchantUser;
            $MerchantUser = $MerchantUser->where('merchant_id',$value->id);
            $MerchantUser = $MerchantUser->whereNotIn('user_id',$disabled_company_investors);
            if(count($SpecialAccount) > 0){
            $MerchantUser = $MerchantUser->whereNotIn('user_id',$SpecialAccount);
            }
            $our_rtr            = clone $MerchantUser;
            $our_funded         = clone $MerchantUser;
            $velocity_owned_rtr = clone $MerchantUser;
            $anticipated_fee    = clone $MerchantUser;
            $our_rtr    = $our_rtr->sum('invest_rtr');
            $our_funded = $our_funded->sum('amount');
            $PaymentInvestors = new PaymentInvestors;
            $PaymentInvestors = $PaymentInvestors->join('participent_payments','participent_payments.id','participent_payment_id');
            $PaymentInvestors = $PaymentInvestors->where('participent_payments.merchant_id',$value->id);
            $PaymentInvestors = $PaymentInvestors->whereNotIn('user_id',$disabled_company_investors);
            // $PaymentInvestors = $PaymentInvestors->when($request['start_date']??'', function($q, $value){
            //     return $q->whereDate('payment_date','>=', $value);
            // });
            $PaymentInvestors = $PaymentInvestors->when($request['end_date']??'', function($q, $value){
                return $q->whereDate('payment_date','<=', $value);
            });
            if(count($SpecialAccount) > 0){
                $PaymentInvestors = $PaymentInvestors->whereNotIn('user_id',$SpecialAccount);
            }
            $investor_paid_amount    = clone $PaymentInvestors;
            $investor_paid_mgmt      = clone $PaymentInvestors;
            $velocity_owned_paid     = clone $PaymentInvestors;
            $velocity_owned_paid     = $velocity_owned_paid->whereIN('user_id',[123,78,77,68,41,33,20,140]);
            $actual_velocity_owned_paid = clone $velocity_owned_paid;
            $actual_velocity_owned_paid = $actual_velocity_owned_paid->when($request['start_date']??'', function($q, $value){
                return $q->whereDate('payment_date','>=', $value);
            });
            $actual_velocity_owned_paid = $actual_velocity_owned_paid->sum('participant_share');
            $investor_paid_amount    = $investor_paid_amount->sum('participant_share');
            $investor_paid_mgmt      = $investor_paid_mgmt->sum('mgmnt_fee');
            $velocity_owned_rtr      = $velocity_owned_rtr->whereIN('user_id',[123,78,77,68,41,33,20,140]);
            $velocity_owned_rtr      = $velocity_owned_rtr->sum('invest_rtr');
            $velocity_owned_paid     = $velocity_owned_paid->sum('participant_share');
            $velocity_gross_balance  = $velocity_owned_rtr-$velocity_owned_paid;
            $anticipated_fee         = $anticipated_fee->sum(DB::raw('invest_rtr*mgmnt_fee/100'));
            $ctd                     = $investor_paid_amount-$investor_paid_mgmt;
            $syndication_balance     = $our_rtr-$investor_paid_amount;
            $syndication_net_balance = $our_rtr-($ctd+$anticipated_fee);
            $Merchant[$key]['our_rtr']                 = $our_rtr;
            $Merchant[$key]['our_funded']              = $our_funded;
            $Merchant[$key]['syndication_balance']     = $syndication_balance;
            $Merchant[$key]['syndication_net_balance'] = $syndication_net_balance;
            $Merchant[$key]['total_payments']          = $total_payments;
            $Merchant[$key]['velocity_gross_balance']  = $velocity_gross_balance;
            $Merchant[$key]['velocity_owned_paid']     = $actual_velocity_owned_paid;
            $total_full_rtr = $total_full_rtr+$value->rtr;
            $total_our_rtr = $total_our_rtr+$our_rtr;
            $total_funded_amount = $total_funded_amount+$value->funded;
            $total_our_funded_amount = $total_our_funded_amount+$our_funded;
            $total_syndication_balance = $total_syndication_balance+$syndication_balance;
            $total_syndication_net_balance = $total_syndication_net_balance+$syndication_net_balance;
            $total_velocity_owned_paid = $total_velocity_owned_paid+$actual_velocity_owned_paid;
            $total_velocity_gross_balance = $total_velocity_gross_balance+$velocity_gross_balance;
            $payments_total = $payments_total+$total_payments;
        }
        $total['total_full_rtr'] = $total_full_rtr;
        $total['total_our_rtr'] = $total_our_rtr;
        $total['total_funded_amount'] = $total_funded_amount;
        $total['total_our_funded_amount'] = $total_our_funded_amount;
        $total['total_syndication_balance'] = $total_syndication_balance;
        $total['total_syndication_net_balance'] = $total_syndication_net_balance;
        $total['total_velocity_owned_paid'] = $total_velocity_owned_paid;
        $total['total_velocity_gross_balance'] = $total_velocity_gross_balance;
        $total['payments_total'] = $payments_total;
        $result['data'] = $Merchant;
        $result['total'] = $total;
        return $result;
    }
    public function TaxReportDataTable($request) {
        $result = $this->TaxReportData($request);
        $data = $result['data'];
        $total = $result['total'];
        return \DataTables::of($data)
        ->editColumn('id', function ($row) { 
            return $row->id; 
        })
        ->addColumn('merchant_name', function ($row) { 
            $url=\URL::to('/admin/merchants/view', $row['id']);
            return "<a target='_blank' href='".$url."'>".$row->name."</a>";
        })
        ->addColumn('status', function ($row) { return $row->payStatus; 
        })
        ->addColumn('Lender', function ($row) { return $row->lendor->name;
         })
        ->editColumn('factor_rate', function ($row) { return round($row->factor_rate,4); })
        ->addColumn('rtr', function ($row) { return FFM::dollar($row->rtr); })
        ->addColumn('our_rtr', function ($row) { return FFM::dollar($row->our_rtr);
         })
        ->editColumn('funded', function ($row) { return FFM::dollar($row->funded); 
        })
        ->editColumn('our_funded', function ($row) { return FFM::dollar($row->our_funded); 
        })
        ->editColumn('date_funded', function ($row) { return FFM::date($row->date_funded); })
        ->editColumn('syndication_net_balance', function ($row) { return FFM::dollar($row->syndication_net_balance); 
        })
        ->editColumn('syndication_balance', function ($row) { return FFM::dollar($row->syndication_balance); 
        })
        ->editColumn('total_payments', function ($row) { return FFM::dollar($row->total_payments); 
        })
        ->editColumn('velocity_gross_balance', function ($row) { return FFM::dollar($row->velocity_gross_balance);
         })
        ->editColumn('velocity_owned_paid', function ($row) { return FFM::dollar($row->velocity_owned_paid);
         })
         ->editColumn('collected_mgmnt_fee', function ($row) { return FFM::dollar($row->collected_mgmnt_fee);
         })
         ->editColumn('collected_syndication_fee', function ($row) { return FFM::dollar($row->collected_syndication_fee);
         })
         ->editColumn('collected_commission', function ($row) { return FFM::dollar($row->collected_commission);
         })
        ->addIndexColumn()
        ->rawColumns(['merchant_name'])
        ->with('total_full_rtr', FFM::dollar($total['total_full_rtr']))
        ->with('total_our_rtr', FFM::dollar($total['total_our_rtr']))
        ->with('total_funded_amount', FFM::dollar($total['total_funded_amount']))
        ->with('total_our_funded_amount', FFM::dollar($total['total_our_funded_amount']))
        ->with('total_syndication_balance', FFM::dollar($total['total_syndication_balance']))
        ->with('total_syndication_net_balance', FFM::dollar($total['total_syndication_net_balance']))
        ->with('total_velocity_owned_paid', FFM::dollar($total['total_velocity_owned_paid']))
        ->with('total_velocity_gross_balance', FFM::dollar($total['total_velocity_gross_balance']))
        ->with('payments_total', FFM::dollar($total['payments_total']))
        ->with('total_mgmnt_fee_collected', FFM::dollar($total['total_mgmnt_fee_collected']))
        ->with('total_syndication_fee_collected', FFM::dollar($total['total_syndication_fee_collected']))
        ->with('total_commission_collected', FFM::dollar($total['total_commission_collected']))


        ->make(true);
    }
    public function TaxPageDetails($tableBuilder) {
        $page_title = 'Tax Report';
        $tableBuilder->ajax([
            'url'  => route('admin::reports::TaxReportData'),
            'type' => 'post',
            'data' => 'function(d){
                d._token         = "'.csrf_token().'";
                d.merchant_ids   = $("#merchant_ids").val();
                d.label          = $("#label").val();
                d.lender_ids     = $("#lender_ids").val();
                d.sub_status_ids = $("#sub_status_ids").val();
                d.start_date     = $("#start_date").val();
                d.end_date       = $("#end_date").val();
            }'
        ]);
        $tableBuilder->parameters([
            'order' => [4,'desc'],
            'footerCallback' => 'function(t,o,a,l,m){
                var n=this.api();
                o=table.ajax.json();
                 $(n.column(6).footer()).html(o.total_full_rtr);
                 $(n.column(7).footer()).html(o.total_our_rtr);
                 $(n.column(8).footer()).html(o.total_funded_amount);
                 $(n.column(9).footer()).html(o.total_our_funded_amount);
                 $(n.column(10).footer()).html(o.total_syndication_balance);
                 $(n.column(11).footer()).html(o.total_syndication_net_balance);
                 $(n.column(12).footer()).html(o.payments_total);
                 $(n.column(13).footer()).html(o.total_velocity_gross_balance);
                 $(n.column(14).footer()).html(o.total_velocity_owned_paid);
                 $(n.column(15).footer()).html(o.total_mgmnt_fee_collected);
                 $(n.column(16).footer()).html(o.total_syndication_fee_collected);
                 $(n.column(17).footer()).html(o.total_commission_collected);
                 
            }'
        ]);
        $tableBuilder->columns($this->TaxTableColumn());
        $sub_statuses = SubStatus::pluck('name', 'id')->toArray();
        $labels = Label::pluck('name','id')->toArray();
        $lenders = User::getLenders()->pluck('name','users.id');
        $return['tableBuilder'] = $tableBuilder;
        $return['page_title']   = $page_title;
        $return['sub_statuses'] = $sub_statuses;
        $return['labels']       = $labels;
        $return['lenders']      = $lenders;
        return $return;
    }
    public function TaxReportExportData($request){
        $fileName = 'Tax Report '.FFM::datetimeExcel(date('Y-m-d H:i:s')).'.csv';
        $result_arr = $this->TaxReportData($request);
        $result = $result_arr['data']->orderBy('date_funded','DESC')->get();
        $total = $result_arr['total'];
        $excel_array[] = ['No', 'Merchant Id', 'Merchant', 'Status', 'Factor Rate', 'Date Funded', 'Lender Name', 'Full RTR', 'Our RTR', 'Funded Amount','Our Funded Amount','Ip Gross Balance','Ip Net Balance','Total Payments','Velocity Gross Balance','Velocity MCA Rev(hist)','Management Fee Collected','Syndication Fee Collected','Commission Collected'];
        $i = 1;
        if (! empty($result)) {
            foreach ($result as $key => $value) {
                $excel_array[$i]['No']                     = $i;
                $excel_array[$i]['Merchant Id']                    = $value['id'];
                $excel_array[$i]['Merchant']               = $value['name'];
                $excel_array[$i]['Status']                 = $value['payStatus'];
                $excel_array[$i]['Factor Rate']            = round($value['factor_rate'],4);
                $excel_array[$i]['Date Funded']            = FFM::date($value['date_funded']);
                $excel_array[$i]['Lender Name']            = $value['lendor']['name'];
                $excel_array[$i]['Full RTR']               = FFM::dollar($value['rtr']);
                $excel_array[$i]['Our RTR']                = FFM::dollar($value['our_rtr']);
                $excel_array[$i]['Funded Amount']          = FFM::dollar($value['funded']);
                $excel_array[$i]['Our Funded Amount']      = FFM::dollar($value['our_funded']);
                $excel_array[$i]['Ip Gross Balance']       = FFM::dollar($value['syndication_balance']);
                $excel_array[$i]['Ip Net Balance']         = FFM::dollar($value['syndication_net_balance']);
                $excel_array[$i]['Total Payments']         = FFM::dollar($value['total_payments']);
                $excel_array[$i]['Velocity Gross Balance'] = FFM::dollar($value['velocity_gross_balance']);
                $excel_array[$i]['Velocity MCA Rev(hist)'] = FFM::dollar($value['velocity_owned_paid']);
                $excel_array[$i]['Management Fee Collected']  = FFM::dollar($value['collected_mgmnt_fee']);
                $excel_array[$i]['Syndication Fee Collected'] = FFM::dollar($value['collected_syndication_fee']);
                $excel_array[$i]['Commission Collected']      = FFM::dollar($value['collected_commission']);
                $i++;
            }
                $excel_array[$i]['No']                     = null;
                $excel_array[$i]['Merchant Id']                    = null;
                $excel_array[$i]['Merchant']               = null;
                $excel_array[$i]['Status']                 = null;
                $excel_array[$i]['Factor Rate']            = null;
                $excel_array[$i]['Date Funded']            = null;
                $excel_array[$i]['Lender Name']            = null;
                $excel_array[$i]['Full RTR']               = FFM::dollar($total['total_full_rtr']);
                $excel_array[$i]['Our RTR']                = FFM::dollar($total['total_our_rtr']);
                $excel_array[$i]['Funded Amount']          = FFM::dollar($total['total_funded_amount']);
                $excel_array[$i]['Our Funded Amount']      = FFM::dollar($total['total_our_funded_amount']);
                $excel_array[$i]['Ip Gross Balance']       = FFM::dollar($total['total_syndication_balance']);
                $excel_array[$i]['Ip Net Balance']         = FFM::dollar($total['total_syndication_net_balance']);
                $excel_array[$i]['Total Payments']         = FFM::dollar($total['payments_total']);
                $excel_array[$i]['Velocity Gross Balance'] = FFM::dollar($total['total_velocity_gross_balance']);
                $excel_array[$i]['Velocity MCA Rev(hist)'] = FFM::dollar($total['total_velocity_owned_paid']);
                $excel_array[$i]['Management Fee Collected']  = FFM::dollar($total['total_mgmnt_fee_collected']);
                $excel_array[$i]['Syndication Fee Collected'] = FFM::dollar($total['total_syndication_fee_collected']);
                $excel_array[$i]['Commission Collected']      = FFM::dollar($total['total_commission_collected']);
        }

        $export = new Data_arrExport($excel_array);

        return [
            'fileName' => $fileName,'export' => $export
        ];
    }
}
