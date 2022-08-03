<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 6/11/17
 * Time: 12:07 PM.
 */

namespace App\Library\Repository;

use App\Library\Repository\Interfaces\IParticipantPaymentRepository;
use App\Library\Repository\Interfaces\IRoleRepository;
use App\LiquidityLog;
use App\Merchant;
use App\MerchantUser;
use App\ParticipentPayment;
use App\User;
use App\UserDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PayCalc;
use InvestorHelper;

class ParticipantPaymentRepository implements IParticipantPaymentRepository
{
    private $table;

    private $merchant_table;

    public function __construct(IRoleRepository $role)
    {
        $this->table = new ParticipentPayment();
        $this->merchant_table = new Merchant();
        $this->role = $role;
    }

    public function getMerchantPayments($id, $company_id = 0, $investor_id = 0, $agent_account = null)
    {
        (Auth::user()->hasRole(['company'])) ? $permission = 0 : $permission = 1;
        $userId = Auth::user()->id;
        $investor_ids = $this->role->allInvestors()->pluck('id');
        $data1 = ParticipentPayment::select([
        'participent_payments.id',
        'participent_payments.reason',
        'participent_payments.creator_id',
        'payment_date',
        'participent_payments.merchant_id',
        'participent_payments.created_at',
        'payment',
        'revert_id',
        'paid_count',
        'merchants.factor_rate as factor_rate',
        'merchants.commission as commission',
        'merchants.pmnts',
        'merchants.m_s_prepaid_status as s_prepaid_status',
        'payment_investors.actual_participant_share',
        'participent_payments.mode_of_payment',
        'participent_payments.approved_by',
        'participent_payments.approved_at',
        DB::raw('sum(payment_investors.profit) as profit_value'),
        DB::raw('sum(payment_investors.actual_overpayment) as overpayemnt'),
        DB::raw('sum(payment_investors.principal) as principal'),
        DB::raw('sum(payment_investors.balance) as balance'),
        DB::raw('IF((rcode.id>0), (rcode.description), 0) as rcode, IF((rcode.id>0), (rcode.code), "") as rcode_id'),
        DB::raw('sum(actual_participant_share-payment_investors.mgmnt_fee) as final_participant_share'),
        DB::raw('sum(payment_investors.mgmnt_fee) as mgmnt_fee'),
        DB::raw('sum(merchant_user.invest_rtr) as invest_rtr'),
        DB::raw('(sum(merchant_user.invest_rtr))-(sum(payment_investors.actual_participant_share)) as bal_rtr'),
      ])
      ->with(['paymentAllInvestors'=>function ($query) use ($investor_ids, $permission, $company_id, $investor_id) {
          if (empty($permission)) {
              $query->whereIn('payment_investors.user_id', $investor_ids);
          }
          $query->leftJoin('users', 'users.id', 'payment_investors.user_id');
          if ($company_id != 0) {
              $query->where('users.company', $company_id);
          }
          if ($investor_id != 0) {
              $query->where('users.id', $investor_id);
          }

          $query->where(function ($query) {
              $query->where('payment_investors.participant_share', '!=', 0);
              $query->orWhere('payment_investors.profit', '!=', 0);
              $query->orWhere('payment_investors.principal', '!=', 0);
          });
      }])
      ->leftJoin('merchants', 'merchants.id', 'participent_payments.merchant_id')
      ->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode')
      ->leftjoin('payment_investors', 'payment_investors.participent_payment_id', 'participent_payments.id')
      ->leftJoin('merchant_user', function ($join) use ($company_id) {
          $join->on('payment_investors.user_id', 'merchant_user.user_id');
          $join->on('payment_investors.merchant_id', 'merchant_user.merchant_id');
      });
        if (empty($permission)) {
            $data1 = $data1->whereIn('payment_investors.user_id', $investor_ids);
        }
        $data1 = $data1->leftJoin('users', 'payment_investors.user_id', 'users.id');
        if ($company_id != 0) {
            $data1 = $data1->where('users.company', $company_id);
        }
        if ($investor_id != 0) {
            $data1 = $data1->where('users.id', $investor_id);
        }
        if ($agent_account != null) {
            $data1 = $data1->where('payment_investors.user_id', '<>', $agent_account);
        }
        $data1 = $data1->where('participent_payments.is_payment', 1);
        $data = $data1->where('participent_payments.merchant_id', $id)
        ->orderByRaw('participent_payments.payment_date DESC, participent_payments.id DESC')
        ->groupBy('payment_investors.participent_payment_id');

        return $data;
    }

    public function datatable($select)
    {
        return $this->table->with('merchant')->select($select)->where('payment_date', '<', Carbon::today());
    }

    public function generatePayment($merchant, $date, $generate_batch_color = 0)
    {
        echo 'Not using from sep 10th 2019';
        exit;
        $funded_amount = $merchant->funded; //To calculate participant share
        $total_ctd = 0;
        foreach ($merchant->participantPayment as $paymet) {
            $total_ctd = $total_ctd + $paymet->payment;    // Total amount
        }

        // $total_ctd = $merchant->ctd;
        $payment_amount = $merchant->payment_amount;
        /*Get the investors for the merchant*/
        $investors = MerchantUser::select('amount', 'user_id', 'mgmnt_fee_percentage', 'syndication_fee_percentage')->where('merchant_id', $merchant->id)->where('status', 1)->get();
        if ($total_ctd + $payment_amount <= $merchant->rtr) { //If payment not exceeded.
            ////
            //$merchant->payStatus = "Active Advance";
            $this_payment_amount = $payment_amount;
        } else { //Payment completed
                    $merchant->sub_status_id = 9; //$merchant->id; //update status to completed
                    $merchant->save();
            //$payStatus == "Completed";
            $this_payment_amount = ($merchant->rtr) - ($total_ctd);
        }
//                if($merchant->id==7631){
//                }

        foreach ($investors as $key => $investor) {
            $is_duplicate_payment = ParticipentPayment::where('user_id', $investor->user_id)->where('merchant_id', $merchant->id)->where('payment_date', $date)->count();

            if (! $is_duplicate_payment) {
                $syndication_fee = $merchant->m_s_prepaid_status ? 0 : PayCalc::getSyndicationFee($this->table->participant_share, $investor->syndication_fee_percentage);

                $this->flush();
                $this->table->payment = $this_payment_amount; //Payment

                $this->table->participant_share = PayCalc::getParticipantShareValue($this->table->payment, $investor->amount, $funded_amount);

                $this->table->mgmnt_fee = PayCalc::getMgmntFee($this->table->participant_share, $investor->mgmnt_fee_percentage);
                $this->table->syndication_fee = $syndication_fee;

                $this->table->transaction_type = 1;
                $this->table->final_participant_share = $this->table->participant_share - $this->table->mgmnt_fee - $this->table->syndication_fee; //
                $this->table->payment_date = $date; //fzl->toDateString();
                $this->table->merchant_id = $merchant->id;
                $this->table->user_id = $investor->user_id;
                $this->table->status = 1;
                $this->table->generate_batch_color = $generate_batch_color;
                $this->table->save();
                $amount = $this->table->participant_share - $this->table->mgmnt_fee - $this->table->syndication_fee; //-$this_payment_amount;
                $model = Merchant::find($merchant->id);
                $final_liquidity = $model->liquidity + $amount;

                $liquidity_old = UserDetails::sum('liquidity');
                InvestorHelper::update_liquidity($investor->user_id, 'Payment Generation', $merchant->id);
                $liquidity_new = UserDetails::sum('liquidity');
                $liquidity_change = $liquidity_new - $liquidity_old;

                $aggregated_liquidity = UserDetails::sum('liquidity');
                $input_array = ['aggregated_liquidity'=>$aggregated_liquidity, 'final_liquidity' => $final_liquidity, 'name_of_deal'=>'Payment', 'member_id' => $merchant->id, 'liquidity_change' => $liquidity_change, 'member_type' => 'merchant', 'description' => 'Generate Payment'];
                if ($amount != 0) {
                    // $insert = LiquidityLog::insert($input_array);
                }

                $model->liquidity += $liquidity_change;
                $model->save();
            } else {
                return 'Payment generated before';
            }
        }
    }

    public function flush()
    {
        $this->table = new ParticipentPayment();
    }

    public function getAllByMerchantId($select, $id, $builder = null)
    {
        if ($builder) {
            //return DB::table('participent_payments')->where('merchant_id',$id);
            $return = $this->table->select()->where('merchant_id', $id);

            return $return;
            //  dd($return);
        }

        return $this->table->select()->where('merchant_id', $id)->get();
    }

    public function getAllByMerchantInvestorId($select, $merchant_id, $user_id, $builder = null)
    {
        if ($builder) {
            //return DB::table('participent_payments')->where('merchant_id',$merchant_id);
            $return = $this->table->select()->where('participent_payments.merchant_id', $merchant_id)
             ->join('payment_investors', function ($join) use ($user_id) {
                 $join->on('payment_investors.participent_payment_id', '=', 'participent_payments.id');
                 $join->where('payment_investors.user_id', '=', $user_id);
             })->leftJoin('rcode', 'rcode.id', 'participent_payments.rcode')

          //  ->where('user_id', $user_id)
            ->with('transactionType');

            return $return;
            //  dd($return);
        }

        return $this->table->select()->where('merchant_id', $id)->where('user_id', $user_id)->get();
    }

    public function getAllByMerchantIdAdmin($select, $id, $builder = null)
    {
        //not using

        if ($builder) {
            //return DB::table('participent_payments')->where('merchant_id',$id);
            $return = $this->table->select()->where('merchant_id', $id);

            return $return;
            //  dd($return);
        }

        return $this->table->select()->where('merchant_id', $id)->get();
    }

    public function openItems($select)
    {
        return $this->table->with('merchant')->whereHas('merchant', function ($query) {
            $query->whereOpenItem(true);
        })->with('paymentAllInvestors')->select($select)->orderByDesc('payment_date');
    }

    public function allPayments($select)
    {
        return $this->table->with('merchant')->has('merchant')->select($select)->orderByDesc('payment_date');
    }
}
