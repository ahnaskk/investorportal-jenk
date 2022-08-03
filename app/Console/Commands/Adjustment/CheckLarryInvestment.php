<?php
namespace App\Console\Commands\Adjustment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Views\MerchantUserView;
use App\ReassignHistory;
use App\MerchantUser;
use Carbon\Carbon;
use App\Exports\Data_arrExport;
use FFM;
use App\Jobs\CommonJobs;
use App\Settings;
class CheckLarryInvestment extends Command
{
    protected $signature = 'check:larry {commit=""}';
    protected $description = 'Update 25 larry morgenthal balance of payment';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $commit = $this->argument('commit') ?? '';
        $commit = str_replace('"', '', $commit);
        $ReassignHistoryMerchant_ids=ReassignHistory::where('investor1',25)->pluck('merchant_id','merchant_id')->toArray();
        $MerchantUser = new MerchantUserView;
        $MerchantUser                = $MerchantUser->where('investor_id',25);
        $non_paid_merchants          = clone $MerchantUser;
        $non_paid_merchants          = $non_paid_merchants->where('paid_participant_ishare','=',0)->pluck('merchant_id', 'merchant_id')->toArray();
        // $MerchantUser             = $MerchantUser->where('merchant_id',8012);
        $MerchantUser                = $MerchantUser->whereIn('merchant_id',$ReassignHistoryMerchant_ids);
        $MerchantUser                = $MerchantUser->whereRaw('(invest_rtr-paid_participant_ishare)!=0');
        $CompletedPercentageMerchant = clone $MerchantUser;
        $NonPaidMerchants            = clone $MerchantUser;
        $CompletedPercentageMerchant = $CompletedPercentageMerchant->where('merchant_completed_percentate','<',100);
        $CompletedPercentageMerchant = $CompletedPercentageMerchant->get();
        $NonPaidMerchants            = $NonPaidMerchants->whereIn('merchant_id',$non_paid_merchants);
        $NonPaidMerchants            = $NonPaidMerchants->get();
        $datas=[];
        DB::beginTransaction();
        echo "\n Lessthan 100 Started";
        foreach ($CompletedPercentageMerchant as $key => $value) {
            $single=$this->SingleMerchantFunction($value);
            if(isset($single['Merchant'])) $datas[]=$single;
        }
        echo "\n Lessthan 100 Ended";
        echo "\n Zero Paid started";
        foreach ($NonPaidMerchants as $key => $value) {
            $single=$this->SingleMerchantFunction($value);
            if(isset($single['Merchant'])) $datas[]=$single;
        }
        echo "\n Zero Paid Ended";
        if($commit){ DB::commit(); }
        if (count($datas)) {
            $title = 'Merchants Balance Amount And Balance Funded Adjustment Amount';
            echo "\n Sending mail for $title \n";
            $titles = ['No', 'Merchant Name', 'Merchant ID','Amount Before', 'Amount After', 'RTR Before','RTR After','Merchant Funded Before','Merchant Funded After','Diff1','Merchant RTR Before','Merchant RTR After','Diff2','Before Balance','After Balance','Adam Amount Before','Adam Amount After','Adam RTR Before','Adam RTR After','Adam Before Balance','Adam After Balance'];
            $values = [''  , 'Merchant'     , 'merchant_id','amount_before', 'amount_after', 'rtr_before','rtr_after','merchant_funded_before','merchant_funded_after','MFD'  ,'merchant_rtr_before','merchant_rtr_after','MRD'  ,'before_balance','after_balance','adam_amount_before','adam_amount_after','adam_rtr_before','adam_rtr_after','adam_before_balance','adam_after_balance'];
            $this->sendMail($datas, $titles, $values, $title);
        }
        return 0;
    }
    public function SingleMerchantFunction($value) {
        $merchant_id            = $value->merchant_id;
        $merchant_funded_before = MerchantUserView::where('merchant_id',$merchant_id)->sum('amount');
        $merchant_rtr_before    = MerchantUserView::where('merchant_id',$merchant_id)->sum('invest_rtr');
        $rtr_before             = $value->invest_rtr;
        $amount_before          = $value->amount;
        $received_amount        = $value->paid_participant_ishare;
        $before_balance         = round($rtr_before-$received_amount,2);
        $balance_funded_amount  = round($before_balance/$value->factor_rate,2);
        $amount_after           = $amount_before;
        $amount_after          -= $balance_funded_amount;
        $rtr_after              = round($amount_after*$value->factor_rate,2);
        $after_balance          = round($rtr_after-$received_amount,2);
        // $rtr_after             -= $after_balance;
        // $after_balance          = round($rtr_after-$received_amount,2);
        $MerchantUserAdam = new MerchantUser;
        $MerchantUserAdam = $MerchantUserAdam->where('user_id',15);
        $MerchantUserAdam = $MerchantUserAdam->where('merchant_id',$merchant_id);
        $MerchantUserAdam = $MerchantUserAdam->first();
        if($MerchantUserAdam){
            $MerchantUser = new MerchantUser;
            $MerchantUser = $MerchantUser->where('id',$value->id);
            $MerchantUser = $MerchantUser->first();
            $MerchantUser->amount=$amount_after;
            $MerchantUser->save();
            DB::table('merchant_user')
            ->where('id', $value->id)
            ->update([
                'invest_rtr' => $rtr_after
            ]);
            echo "\n";
            echo $merchant_id.' -> ';
            echo $before_balance.' -> ';
            echo $after_balance.' -> ';
            $adam_amount_before  = $MerchantUserAdam->amount;
            $adam_amount_after   = $adam_amount_before;
            $adam_amount_after  += $balance_funded_amount;
            $adam_rtr_before     = $MerchantUserAdam->invest_rtr;
            $adam_rtr_after      = round($adam_amount_after*$value->factor_rate,2);
            $adam_before_balance = round($adam_rtr_before-$MerchantUserAdam->paid_participant_ishare,2);
            $adam_after_balance  = round($adam_rtr_after-$MerchantUserAdam->paid_participant_ishare,2);
            $MerchantUserAdam->amount=$adam_amount_after;
            $MerchantUserAdam->save();
            DB::table('merchant_user')
            ->where('id', $MerchantUserAdam->id)
            ->update([
                'invest_rtr' => $adam_rtr_after
            ]);
            $merchant_funded_after = MerchantUserView::where('merchant_id',$merchant_id)->sum('amount');
            $merchant_rtr_after    = MerchantUserView::where('merchant_id',$merchant_id)->sum('invest_rtr');
            $single['Merchant']               = $value->Merchant;
            $single['merchant_id']            = $merchant_id;
            $single['amount_before']          = $amount_before;
            $single['amount_after']           = $amount_after;
            $single['merchant_funded_before'] = $merchant_funded_before;
            $single['merchant_funded_after']  = $merchant_funded_after;
            $single['MFD']                    = round($merchant_funded_before-$merchant_funded_after,2);
            $single['merchant_rtr_before']    = $merchant_rtr_before;
            $single['merchant_rtr_after']     = $merchant_rtr_after;
            $single['MRD']                    = round($merchant_rtr_before-$merchant_rtr_after,2);
            $single['rtr_before']             = $rtr_before;
            $single['rtr_after']              = $rtr_after;
            $single['before_balance']         = $before_balance;
            $single['after_balance']          = $after_balance;
            $single['adam_amount_before']     = $adam_amount_before;
            $single['adam_amount_after']      = $adam_amount_after;
            $single['adam_rtr_before']        = $adam_rtr_before;
            $single['adam_rtr_after']         = $adam_rtr_after;
            $single['adam_before_balance']    = $adam_before_balance;
            $single['adam_after_balance']     = $adam_after_balance;
        }
        return $single;
    }
    public function sendMail($data, $titles, $values, $title) {
        $message['title']                = 'Merchant Unit Test';
        $msg['title']                    = $message['title'];
        $msg['status']                   = 'merchant_unit_test';
        $msg['subject']                  = $message['title'];
        $message['content']['date']      = FFM::date(Carbon::now()->toDateString());
        $message['content']['date_time'] = FFM::datetime(Carbon::now()->toDateTimeString());
        $msg['date_time']                = FFM::datetime(Carbon::now()->toDateTimeString());
        $msg['template_type']            = 'merchant_unit';
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        if (count($data)) {
            $message['content']['type']  = $title;
            $message['content']['count'] = count($data);
            $msg['count']           = count($data);
            $msg['type']            = $title;
            $msg['unqID']           = unqID();
            $msg['content']         = $message['content'];
            $msg['atatchment_name'] = $message['content']['type'].'.csv';
            $msg['atatchment']      = $this->generateCSV($data, $titles, $values);
            try {
                $msg['to_mail'] = $admin_email;
                $emailJob = (new CommonJobs($msg));
                dispatch($emailJob);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
    public function generateCSV($data, $titles, $values) {
        $excel_array[] = $titles;
        $i = 1;
        foreach ($data as $key => $tr) {
            $excel_array[$i][$titles[0]]  = $i;
            $excel_array[$i][$titles[1]]  = $tr[$values[1]];
            $excel_array[$i][$titles[2]]  = $tr[$values[2]];
            $excel_array[$i][$titles[3]]  = $tr[$values[3]];
            $excel_array[$i][$titles[4]]  = $tr[$values[4]];
            $excel_array[$i][$titles[5]]  = $tr[$values[5]];
            $excel_array[$i][$titles[6]]  = $tr[$values[6]];
            $excel_array[$i][$titles[7]]  = $tr[$values[7]];
            $excel_array[$i][$titles[8]]  = $tr[$values[8]];
            $excel_array[$i][$titles[9]]  = $tr[$values[9]];
            $excel_array[$i][$titles[10]] = $tr[$values[10]];
            $excel_array[$i][$titles[11]] = $tr[$values[11]];
            $excel_array[$i][$titles[12]] = $tr[$values[12]];
            $excel_array[$i][$titles[13]] = $tr[$values[13]];
            $excel_array[$i][$titles[14]] = $tr[$values[14]];
            $excel_array[$i][$titles[15]] = $tr[$values[15]];
            $excel_array[$i][$titles[16]] = $tr[$values[16]];
            $excel_array[$i][$titles[17]] = $tr[$values[17]];
            $excel_array[$i][$titles[18]] = $tr[$values[18]];
            $excel_array[$i][$titles[19]] = $tr[$values[19]];
            $excel_array[$i][$titles[20]] = $tr[$values[20]];
            $i++;
        }
        $export = new Data_arrExport($excel_array);
        return $export;
    }
}
