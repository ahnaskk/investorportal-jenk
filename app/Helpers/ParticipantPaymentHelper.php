<?php

namespace App\Helpers;

use App\MerchantUser;
use App\ParticipentPayment;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Views\MerchantUserView;
use App\PaymentInvestors;
use FFM;
use Yajra\DataTables\Html\Builder;
use App\Merchant;
use App\Settings;
use App\TermPaymentDate;
use Illuminate\Support\Facades\URL;
use PaymentHelper;

class ParticipantPaymentHelper
{
    public function getCtdByDate(int $userId, string $start_date = '', string $end_date = '')
    {
        return ParticipentPayment::join('payment_investors', 'participent_payments.id', 'payment_investors.participent_payment_id')->join('users', 'users.id', 'payment_investors.user_id')->where('user_id', $userId)->groupBy(DB::raw('YEAR(participent_payments.payment_date)'))->groupBy(DB::raw('MONTH(participent_payments.payment_date)'))->where('participent_payments.payment_date', '>=', $start_date)->where('participent_payments.payment_date', '<=', $end_date)->select(DB::raw('SUM( payment_investors.actual_participant_share - mgmnt_fee) as ctd_month'), DB::raw('YEAR(participent_payments.payment_date) as year'), DB::raw('MONTH(participent_payments.payment_date) as month'))->get();
    }

    public function getMerchantExtra(int $merchantId = 0, int $userId = 0)
    {
        $merchant = ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->join('payment_investors', function ($join) use ($userId) {
            $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
            if (! empty($userId)) {
                $join->where('payment_investors.user_id', '=', $userId);
            }
        })->select(DB::raw('SUM(mgmnt_fee) AS mgmnt_fee'), DB::raw('SUM(actual_participant_share - mgmnt_fee) AS paid_to_participant'), DB::raw('SUM(payment) AS amount'), DB::raw('SUM(actual_participant_share) AS participant_share'))->where('participent_payments.status', 1)->first();

        return [$merchant ? $merchant->mgmnt_fee : 0, $merchant ? $merchant->paid_to_participant : 0, $merchant ? $merchant->amount : 0, $merchant ? $merchant->participant_share : 0];
    }

    public function getMerchantPaymentSum(int $merchantId)
    {
        return ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->sum('payment');
    }

    public function getMerchantPayments(int $merchantId = 0, array $investorIds = [], int $companyId = 0, int $offset = 0, int $limit = 0)
    {
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $query = ParticipentPayment::select(['participent_payments.id', 'participent_payments.reason', 'participent_payments.payment_date', 'participent_payments.creator_id', 'payment', 'merchants.factor_rate as factor_rate', 'merchants.commission as commission', 'merchants.m_s_prepaid_status as s_prepaid_status', 'payment_investors.participant_share', DB::raw('SUM( (actual_participant_share) - payment_investors.mgmnt_fee) as final_participant_share '), DB::raw('SUM(payment_investors.mgmnt_fee) as mangt_fee '), DB::raw('SUM(payment_investors.profit) as profit_value'), DB::raw('SUM(payment_investors.principal) as principal'), DB::raw('SUM(payment_investors.actual_participant_share) as participant_share'), DB::raw('SUM(merchant_user.invest_rtr) as invest_rtr'), DB::raw('(sum(merchant_user.invest_rtr)) - (sum(payment_investors.actual_participant_share)) as bal_rtr'), DB::raw('sum(payment_investors.balance) as balance'), DB::raw('IF((rcode.id > 0), (rcode.description), 0) as rcode, IF((rcode.id > 0), (rcode.code), "") as rcode_id')])->with('paymentAllInvestors')->leftjoin('payment_investors', 'payment_investors.participent_payment_id', '=', 'participent_payments.id')->where('model','<>','App\MerchantUser');
        if ($companyId != 0) {
            $query->leftJoin('users', 'users.id', '=', 'payment_investors.user_id')->where('users.company', $companyId);
        }
        $query->leftJoin('merchant_user', function ($join) {
            $join->on('payment_investors.user_id', 'merchant_user.user_id');
            $join->on('payment_investors.merchant_id', 'merchant_user.merchant_id');
        })->leftJoin('merchants', 'merchants.id', 'participent_payments.merchant_id')->groupBy('payment_investors.participent_payment_id');
        if (empty($permission)) {
            $query->whereIn('payment_investors.user_id', $investorIds);
        }
        if (! empty($merchantId)) {
            $query->where('participent_payments.merchant_id', $merchantId);
        }
        $query->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
        if (! empty($limit)) {
            $query->offset($offset)->limit($limit);
        }

        return $query->orderByDesc('participent_payments.payment_date')->get();
    }

    public function getMerchantPaymentsByDate(int $merchantId = 0, array $investorIds = [], int $companyId = 0, int $offset = 0, int $limit = 0)
    {
        $permission = (Auth::user()->hasRole(['company'])) ? 0 : 1;
        $query = ParticipentPayment::select(['participent_payments.id', 'participent_payments.reason', 'participent_payments.payment_date', 'payment', 'merchants.funded', 'merchants.max_participant_fund', 'merchants.factor_rate as factor_rate', 'merchants.rtr', 'merchants.commission as commission', 'merchants.m_s_prepaid_status as s_prepaid_status', 'payment_investors.participant_share', DB::raw('SUM( (actual_participant_share) - payment_investors.mgmnt_fee) as final_participant_share '), DB::raw('SUM(payment_investors.overpayment) as overpayment'), DB::raw('SUM(payment_investors.profit) as profit_value'), DB::raw('SUM(payment_investors.principal) as principal'), DB::raw('SUM(payment_investors.actual_participant_share) as participant_share'), DB::raw('SUM(merchant_user.invest_rtr) as invest_rtr'), DB::raw('(sum(merchant_user.invest_rtr))-(sum(payment_investors.actual_participant_share)) as bal_rtr'), DB::raw('sum(payment_investors.balance) as balance'), DB::raw('IF((rcode.id > 0), (rcode.code), 0) as rcode, IF((rcode.id > 0), (rcode.code), "") as rcode_id')])->with('paymentAllInvestors')->join('payment_investors', 'payment_investors.participent_payment_id', '=', 'participent_payments.id');
        if ($companyId != 0) {
            $query->leftJoin('users', 'users.id', '=', 'payment_investors.user_id')->where('users.company', $companyId);
        }
        $query->leftJoin('merchant_user', function ($join) {
            $join->on('payment_investors.user_id', 'merchant_user.user_id');
            $join->on('payment_investors.merchant_id', 'merchant_user.merchant_id');
        })->leftJoin('merchants', 'merchants.id', 'participent_payments.merchant_id')->groupBy('payment_investors.participent_payment_id');
        if (empty($permission)) {
            $query->whereIn('payment_investors.user_id', $investorIds);
        }
        if (! empty($merchantId)) {
            $query->where('participent_payments.merchant_id', $merchantId);
        }
        $query->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');

        $query->orderBy('participent_payments.created_at','DESC');
        return $query->get();
    }

    public function getUniqueDatePayments(int $merchantId = 0, int $paymentType = 0, int $companyId = 0, $skipZero = false):int
    {
        $query = ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->where('participent_payments.is_payment', 1);
        if (! empty($companyId)) {
            $query->join('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')->join('users', 'users.id', 'payment_investors.user_id')->where('users.company', $companyId);
        }
        if (! empty($paymentType)) {
            $query->where('payment_type', $paymentType);
        }
        if ($skipZero) {
            $query->where('payment', '!=', 0);
        }

        return $query->groupBy('participent_payments.payment_date')->select('id')->get()->count();
    }

    public function getMerchantPaymentDetails(int $userId = 0, int $merchantId = 0, $keyword = null, $sortBy = null, $sortOrder = null)
    {
        $query = ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->join('payment_investors', function ($join) use ($userId) {
            $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
            $join->where('payment_investors.user_id', '=', $userId);
        })->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode');
        if (! empty($keyword)) {
            $query = $query->where(function ($q) use ($keyword) {
                $q->where('payment_date', 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw("DATE_FORMAT(`payment_date`, '%m-%d-%Y')"), 'LIKE', '%'.$keyword.'%')->orWhere('actual_participant_share', 'LIKE', '%'.$keyword.'%')->orWhere('mgmnt_fee', 'LIKE', '%'.$keyword.'%')->orWhere(DB::raw('`actual_participant_share` - `mgmnt_fee`'), 'LIKE', '%'.$keyword.'%');
                // ->orWhere(DB::raw('(CASE 

                //         WHEN participent_payments.mode_of_payment = "2" THEN "Credit Card" 

                //         WHEN participent_payments.mode_of_payment = "1" THEN "Ach" 

                //         ELSE "Mannual" 

                //         END)'), 'LIKE', '%'.$keyword.'%');
            });
        }
        if ($sortBy != null && $sortOrder != null) {
            if ($sortBy == 'date_settled') {
                $query = $query->orderBy('payment_date', $sortOrder);
            }
            if ($sortBy == 'total_payment') {
                $query = $query->orderBy('actual_participant_share', $sortOrder);
            }
            if ($sortBy == 'management_fee') {
                $query = $query->orderBy('mgmnt_fee', $sortOrder);
            }
            if ($sortBy == 'to_participant') {
                $query = $query->orderBy(DB::raw('`actual_participant_share` - `mgmnt_fee`'), $sortOrder);
            }
            if ($sortBy == 'transaction_type') {
                $query = $query->orderBy(DB::raw('(CASE 

                        WHEN participent_payments.mode_of_payment = "2" THEN "Credit Card" 

                        WHEN participent_payments.mode_of_payment = "1" THEN "Ach" 

                        ELSE "Mannual" 

                        END)'), $sortOrder);
            }
        }
        if ($sortBy == null) {
           // $query = $query->orderByDesc('payment_date');
            $query = $query->orderByRaw('participent_payments.payment_date DESC, participent_payments.id DESC');
        }
        $query->select('participent_payments.merchant_id', 'payment_date', DB::raw('(actual_participant_share) as payment'), 'actual_participant_share as participant_share', 'mgmnt_fee', 'final_participant_share', 'syndication_fee', 'rcode.code', DB::raw('(CASE 

                        WHEN participent_payments.mode_of_payment = "2" THEN "Credit Card" 

                        WHEN participent_payments.mode_of_payment = "1" THEN "Ach" 

                        ELSE "Manual" 

                        END) AS mode_of_payment'));
        $sumQuery = clone $query;
        $sumQuery->select(DB::raw('SUM(actual_participant_share) as total_payment'), DB::raw('SUM(actual_participant_share) as total_participant_share'), DB::raw('SUM(mgmnt_fee) as total_mgmnt_fee'), DB::raw('SUM(syndication_fee) as total_syndication_fee'), DB::raw('SUM(actual_participant_share-mgmnt_fee) as total_to_participant'), DB::raw('count(*) as count'))->first();

        return [$query, $sumQuery];
    }

    public function getMerchantLastPaymentDateToInvestor(int $merchantId = 0, int $userId = 0)
    {
        $data = ParticipentPayment::where('participent_payments.merchant_id', $merchantId)->join('payment_investors', function ($join) use ($userId) {
            $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
            if (! empty($userId)) {
                $join->where('payment_investors.user_id', '=', $userId);
            }
        })->where('payment', '>', 0)->where('participent_payments.status', 1)->orderByDesc('payment_date')->select('payment_date', )->first();
        if ($data) {
            return $data->payment_date;
        } else {
            return '';
        }
    }

    public function dateWiseInvestorPayment($merchant_id)
    {
          
        $investors =MerchantUserView::where('merchant_id',$merchant_id)->where('paid_participant_ishare','!=',0)->orderBy('company')->pluck('Investor','investor_id')->toArray();
        $dates     =ParticipentPayment::where('merchant_id',$merchant_id)->where('model','App\ParticipentPayment')->orderBy('payment_date','DESC')->pluck('payment_date')->toArray();
        $data=[];
        $title[]='#';
        $title[]='Date\Investor';
        foreach ($investors as $user_id => $name) { $title[]=$name; }
        $participant_share =new PaymentInvestors;
        $participant_share =$participant_share->where('participant_share','!=',0);
        $participant_share =$participant_share->where('payment_investors.merchant_id',$merchant_id);
        $participant_share =$participant_share->join('participent_payments','participent_payments.id','participent_payment_id');
        $participant_share =$participant_share->where('participent_payments.model','App\ParticipentPayment');
        $participant_share =$participant_share->where('participent_payments.payment','!=',0);
        $participant_share =$participant_share->groupBy('participent_payments.payment_date','user_id');
        $participant_share =$participant_share->get(['user_id','participent_payments.payment_date',DB::raw('sum(participant_share) as share')]);
        $participant_share =$participant_share->toArray();
        $data[]=$title;
        $beforData=[];
        foreach ($participant_share as $key => $value) {
            $date=FFM::date($value['payment_date']);
            $beforData[$date][$value['user_id']]=$value['share'];
        }
        $i=0;
        foreach ($beforData as $date => $users) {
            $i++;
            $single['#']=$i;
            $single['Date\Investor']=$date;
            foreach ($investors as $user_id => $name) {
                $share=$users[$user_id]??0;
                $single[$name]  =FFM::dollar($share);
                $single[$name]  =$share;
            }
            $data[] =$single;
        }

        return $data;

    }

    public function ExpectationVsGivenSingle($participent_payment_id)
    {
        $ParticipentPayment =ParticipentPayment::find($participent_payment_id);
        $merchant_id        =$ParticipentPayment->merchant_id;
        $payment            =$ParticipentPayment->payment;
        $PaymentInvestorsMain = new PaymentInvestors;
        $PaymentInvestorsMain =$PaymentInvestorsMain->where('participent_payment_id', $participent_payment_id);
        $SelectedInvestors    =$PaymentInvestorsMain->pluck('user_id','user_id')->toArray();
        $PaymentInvestors     =$PaymentInvestorsMain->get();
        $data=[];
        $Total_participant_share=$Total_expected_participant_share=$Total_Diffrence=0;
        $Total_invest_rtr = new MerchantUserView;
        $Total_invest_rtr = $Total_invest_rtr->where('merchant_id', $merchant_id);
        $Total_invest_rtr = $Total_invest_rtr->whereIn('investor_id', $SelectedInvestors);
        $Total_invest_rtr = $Total_invest_rtr->sum('invest_rtr');
        $max_participant_fund_per = new MerchantUserView;
        $max_participant_fund_per = $max_participant_fund_per->where('merchant_id', $merchant_id);
        $max_participant_fund_per = $max_participant_fund_per->select(DB::raw('funded/sum(amount) as max_participant_fund_per'));
        $max_participant_fund_per = $max_participant_fund_per->first()->max_participant_fund_per;
        $syndicate_payment =$payment/$max_participant_fund_per;
        foreach ($PaymentInvestors as $value) {
            $MerchantUserView          = MerchantUserView::select('Investor','amount','investor_share_percentage','invest_rtr');
            $MerchantUserView          = $MerchantUserView->where('merchant_id', $merchant_id);
            $MerchantUserView          = $MerchantUserView->where('investor_id', $value->user_id);
            $MerchantUserView          = $MerchantUserView->first();
            $amount                    = $MerchantUserView->amount;
            $invest_rtr                = $MerchantUserView->invest_rtr;
            $investor_share_percentage = $MerchantUserView->investor_share_percentage;
            $Investor                 = $MerchantUserView->Investor;
            $participent_payment_id     =$value->id;
            $participant_share          =$value->participant_share;
            $expected_participant_share =round($invest_rtr/$Total_invest_rtr*$syndicate_payment,2);
            $diff                       =round($expected_participant_share-$participant_share,2);
            $Total_participant_share          +=$participant_share;
            $Total_expected_participant_share +=$expected_participant_share;
            $Total_Diffrence                  +=$diff;
            $single['Investor']  =$Investor;
            $single['Given']     =FFM::dollar($participant_share);
            $single['Expected']  =FFM::dollar($expected_participant_share);
            $single['Diffrence'] =FFM::dollar($diff);
            if($diff) $data[]=$single;
        }
        return \DataTables::of($data)
        ->setTotalRecords(count($data))
        ->rawColumns(['Date'])
        ->with('Total_participant_share', FFM::dollar($Total_participant_share))
        ->with('Total_expected_participant_share', FFM::dollar($Total_expected_participant_share))
        ->with('Total_Diffrence', FFM::dollar($Total_Diffrence))
        ->addIndexColumn()
        ->make(true);

    }

    public function ExpectationVsGiven($request,$tableBuilder,$merchant_id)
    {
        $max_participant_fund_per = MerchantUserView::select(DB::raw('funded/sum(amount) as max_participant_fund_per'));
        $max_participant_fund_per=$max_participant_fund_per->where('merchant_id', $merchant_id);
        $max_participant_fund_per=$max_participant_fund_per->first()->max_participant_fund_per;
        $ParticipentPayment = ParticipentPayment::where('payment', '!=', 0)
        ->where('merchant_id', $merchant_id)
        ->where('model',"App\ParticipentPayment")
        ->orderBy('payment_date','DESC')
        ->get();
        $data=[];
        $Total_Payment=$Total_participant_share=$Total_expected_participant_share=$Total_Diffrence=0;
        foreach ($ParticipentPayment as $value) {
            $participent_payment_id=$value->id;
            $participant_share=round(PaymentInvestors::where('participent_payment_id',$participent_payment_id)->sum('participant_share'),2);
            $expected_participant_share=round($value->payment/$max_participant_fund_per,2);
            $diff=round($expected_participant_share-$participant_share,2);
            $Total_Payment                    +=$value->payment;
            $Total_participant_share          +=$participant_share;
            $Total_expected_participant_share +=$expected_participant_share;
            $Total_Diffrence                  +=$diff;
            $single['id']        =$value->id;
            $single['Date']      =FFM::date($value->payment_date);
            $single['Payment']   =FFM::dollar($value->payment);
            $single['Given']     =FFM::dollar($participant_share);
            $single['Expected']  =FFM::dollar($expected_participant_share);
            $single['Diffrence'] =FFM::dollar($diff);
            if($diff) $data[]=$single;
        }
        return \DataTables::of($data)
        ->setTotalRecords(count($data))
        ->rawColumns(['Date'])
        ->addColumn('single', function($ParticipentPayment) use ($tableBuilder){
          return view('admin.merchants.merchantViewPageComponents.expected_share_and_given_share_table_single',compact('tableBuilder','ParticipentPayment'));
        })
        ->with('Total_Payment', FFM::dollar($Total_Payment))
        ->with('Total_participant_share', FFM::dollar($Total_participant_share))
        ->with('Total_expected_participant_share', FFM::dollar($Total_expected_participant_share))
        ->with('Total_Diffrence', FFM::dollar($Total_Diffrence))
        ->addIndexColumn()
        ->make(true);

    }

    public function creditcardPayment($request,$id)
    {
        $page_title = 'Merchant Credit Card Payment';
        $Merchant = Merchant::find($id);
        $today_date = date('Y-m-d');
        $date_funded = $Merchant->date_funded;
                if ($today_date < $date_funded) {
                    return redirect()->to('admin/merchants/view/'.$id)->withErrors('You cannot enter a credit card payment before funding date!');
                }
        $mode=Settings::where('keys', 'collection_default_mode')->value('values');
        if($mode==0)
        {
            if (in_array($Merchant->sub_status_id, [18, 19, 20, 4, 22])) {
                return redirect()->back()->with('error', 'Please change the merchant status to Collection before you add payment.');
            }
        }
        $stripe_key = config('app.stripe_key');
        // $amount = session('payment_amount') ? session('payment_amount') : 15;
        $amount = $Merchant->payment_amount;
        $payment_total = ParticipentPayment::where('merchant_id', $id)->where('is_payment', 1)->sum('payment');
        $merchant_balance = $Merchant->rtr - $payment_total;

        $processing_ach_payments = TermPaymentDate::where('merchant_id', $id)->where('status', TermPaymentDate::ACHProcessing)->sum('payment_amount');
        $balance_final = $merchant_balance - $processing_ach_payments;
        if ($balance_final < $amount) {
            if ($balance_final <= 0) {
                $amount = 0;
            } else {
                $amount = sprintf('%.2f', $balance_final);
            }
        }
        if (! $request->isMethod('post')) {
            session_set('process_payment', true);
            session_set('prev_url', URL::previous());

            return view('admin.investors.investor_credit_card_payment', compact('page_title', 'Merchant', 'amount', 'stripe_key'));
        }
        if (session('process_payment')) {
            session_set('process_payment', false);
            Stripe::setApiKey(config('app.stripe_secret'));
            try {
                Charge::create(['amount' => ($request->total_amount * 100), 'currency' => 'usd', 'source' => $request->stripeToken, 'description' => 'Credit Card payment']);
            } catch (Exception $e) {
                $error = $e->getMessage();

                return view('payment.successful', compact('error'));
            }
            $add_payment =  PaymentHelper::generateAchPayment($id, date('d-m-Y'), $request->amount, null, 2);
            if ($add_payment) {
                $this->sendmail($id, $request->total_amount, $request['card-number'], 'merchant', $request->amount);

                return view('admin.investors.investor_credit_card_payment_success');
            }
        }

        return view('admin.investors.investor_credit_card_payment_success');




    }



}
