<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\MerchantUser;
use App\PaymentInvestors;
use App\Settings;
use DB;
use FFM;
use App\Jobs\CommonJobs;
use Carbon\Carbon;
use App\Exports\Data_arrExport;
use Illuminate\Support\Facades\Schema;
class MerchantUserSync extends Command
{
    protected $signature = 'sync:merchant_user {merchantId=""}';
    protected $description = 'Update MerchantUser Value With Payment Investors';
    public function __construct() {
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
        parent::__construct();
    }
    public function handle() {
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $MerchantUser = new MerchantUser;
        $MerchantUser = $MerchantUser->where('merchant_id','!=','9783');
        if($merchantId){
            $MerchantUser = $MerchantUser->where('merchant_id',$merchantId);
        }
        $MerchantUser = $MerchantUser->get();
        $MerchantUserData = [];
        foreach ($MerchantUser as $key => $value) {
            DB::beginTransaction();
            $PaymentInvestors  = PaymentInvestors::where('investment_id',$value->id);
            $participant_share = round($PaymentInvestors->sum('participant_share'),2);
            $mgmnt_fee         = round($PaymentInvestors->sum('mgmnt_fee'),2);
            $principal         = round($PaymentInvestors->sum('principal'),2);
            $profit            = round($PaymentInvestors->sum('profit'),2);
            $agent_fee         = round($PaymentInvestors->sum('agent_fee'),2);
            $principal_diff                 = 0;
            $paid_participant_ishare_diff   = 0;
            $actual_paid_participant_ishare = 0;
            $principal_diff                 = 0;
            $profit_diff                    = 0;
            $paid_mgmnt_fee                 = 0;
            $agent_fee_diff                 = 0;
            if($value->paid_participant_ishare!=$participant_share){
                $paid_participant_ishare_diff=round($value->paid_participant_ishare-$participant_share,2);
            }
            if($value->actual_paid_participant_ishare!=$participant_share){
                $actual_paid_participant_ishare=round($value->actual_paid_participant_ishare-$participant_share,2);
            }
            if($value->paid_mgmnt_fee!=$mgmnt_fee){
                $paid_mgmnt_fee=round($value->paid_mgmnt_fee-$mgmnt_fee,2);
            }
            if($value->paid_principal!=$principal){
                $principal_diff=round($value->paid_principal-$principal,2);
            }
            if($value->paid_profit!=$profit){
                $profit_diff=round($value->paid_profit-$profit,2);
            }
            if($value->total_agent_fee!=$agent_fee){
                $agent_fee_diff=round($value->total_agent_fee-$agent_fee,2);
            }
            echo $key.')'.$value->merchant_id."-".$value->user_id."\n";
            if($paid_mgmnt_fee || $actual_paid_participant_ishare || $paid_participant_ishare_diff || $principal_diff || $profit_diff || $agent_fee_diff){
                $single['merchant_id']                        = $value->merchant_id;
                $single['merchant_name']                      = $value->merchant->name;
                $single['investor_id']                        = $value->user_id;
                $single['investor_name']                      = $value->Investor->name;
                $single['paid_mgmnt_fee_old']                 = $value->paid_mgmnt_fee;
                $single['paid_mgmnt_fee_new']                 = $mgmnt_fee;
                $single['actual_paid_participant_ishare_old'] = $value->actual_paid_participant_ishare;
                $single['actual_paid_participant_ishare_new'] = $participant_share;
                $single['paid_participant_ishare_old']        = $value->paid_participant_ishare;
                $single['paid_participant_ishare_new']        = $participant_share;
                $single['paid_principal_old']                 = $value->paid_principal;
                $single['paid_principal_new']                 = $principal;
                $single['paid_profit_old']                    = $value->paid_profit;
                $single['paid_profit_new']                    = $profit;
                $single['total_agent_fee_old']                = $value->total_agent_fee;
                $single['total_agent_fee_new']                = $agent_fee;
                if($paid_mgmnt_fee){
                    $value->paid_mgmnt_fee=$mgmnt_fee;
                }
                if($actual_paid_participant_ishare){
                    $value->actual_paid_participant_ishare=$participant_share;
                }
                if($paid_participant_ishare_diff){
                    $value->paid_participant_ishare=$participant_share;
                }
                if($principal_diff){
                    $value->paid_principal=$principal;
                }
                if($profit_diff){
                    $value->paid_profit=$profit;
                }
                if($agent_fee_diff){
                    $value->total_agent_fee=$agent_fee;
                }
                $single['paid_mgmnt_fee_diff']                 = $single['paid_mgmnt_fee_new']-$single['paid_mgmnt_fee_old'];
                $single['actual_paid_participant_ishare_diff'] = $single['actual_paid_participant_ishare_new']-$single['actual_paid_participant_ishare_old'];
                $single['paid_participant_ishare_diff']        = $single['paid_participant_ishare_new']-$single['paid_participant_ishare_old'];
                $single['paid_principal_diff']                 = $single['paid_principal_new']-$single['paid_principal_old'];
                $single['paid_profit_diff']                    = $single['paid_profit_new']-$single['paid_profit_old'];
                $single['total_agent_fee_diff']                = $single['total_agent_fee_new']-$single['total_agent_fee_old'];
                $MerchantUserData[]=$single;
                $value->save();  
            }
            DB::commit();
        }
        $title = 'Merchant User Vs Payment Investors Data Diffrence list';
        if(count($MerchantUserData)){
            echo "\n Sending mail for $title \n";
            $header = [
                'MID',
                'Merchant Name',
                'Investor Id',
                'Investor Name',
                'Paid mgmnt fee old',
                'Paid mgmnt fee new',
                'Paid mgmnt fee diff',
                'actual paid participant ishare old',
                'actual paid participant ishare new',
                'actual paid participant ishare diff',
                'paid participant ishare old',
                'paid participant ishare new',
                'paid participant ishare diff',
                'paid principal old',
                'paid principal new',
                'paid principal diff',
                'paid profit old',
                'paid profit new',
                'paid profit diff',
                'total agent fee old',
                'total agent fee new',
                'total agent fee diff'
            ];
            $headerValues = [
                'merchant_id',
                'merchant_name',
                'investor_id',
                'investor_name',
                'paid_mgmnt_fee_old',
                'paid_mgmnt_fee_new',
                'paid_mgmnt_fee_diff',
                'actual_paid_participant_ishare_old',
                'actual_paid_participant_ishare_new',
                'actual_paid_participant_ishare_diff',
                'paid_participant_ishare_old',
                'paid_participant_ishare_new',
                'paid_participant_ishare_diff',
                'paid_principal_old',
                'paid_principal_new',
                'paid_principal_diff',
                'paid_profit_old',
                'paid_profit_new',
                'paid_profit_diff',
                'total_agent_fee_old',
                'total_agent_fee_new',
                'total_agent_fee_diff'
            ];
            $this->sendMail($MerchantUserData, $header, $headerValues, $title);
        }
        return 0;
    }
    public function sendMail($data, $header, $headerValues, $title) {
        if (count($data)) {
            $msg['title']         = $title;
            $msg['status']        = 'merchant_unit_test';
            $msg['subject']       = $msg['title'];
            $msg['template_type'] = 'merchant_unit';
            $msg['date_time']            = FFM::datetime(Carbon::now()->toDateTimeString());
            $msg['content']['date']      = FFM::date(Carbon::now()->toDateString());
            $msg['content']['date_time'] = $msg['date_time'];
            $msg['content']['type']      = $title;
            $msg['content']['count']     = count($data);
            $msg['to_mail'] = $this->admin_email;
            $msg['count']   = count($data);
            $msg['type']    = $title;
            $msg['unqID']   = unqID();
            $exportCSV = $this->generateCSV($data, $header, $headerValues);
            $fileName  = $msg['content']['type'].'.csv';
            $msg['atatchment_name'] = $fileName;
            $msg['atatchment']      = $exportCSV;
            try {
                $emailJob = (new CommonJobs($msg));
                dispatch($emailJob);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
    public function generateCSV($data, $header, $DataValues) {
        $excel_array[] = $header;
        foreach ($data as $row) {
            foreach ($header as $header_key => $header_value) {
                $single[$header_value]=$row[$DataValues[$header_key]];
            }
            $excel_array[]=$single;
        }
        $export = new Data_arrExport($excel_array);
        return $export;
    }
}
