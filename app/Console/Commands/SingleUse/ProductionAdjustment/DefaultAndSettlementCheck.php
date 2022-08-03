<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\User;
use App\Merchant;
use App\ParticipentPayment;
use App\PaymentInvestors;
use App\MerchantUser;
use App\Settings;
use PayCalc;
use Auth;
use FFM;
use DB;
use App\Jobs\CommonJobs;
use Carbon\Carbon;
use App\Exports\Data_arrExport;
use App\Models\Views\MerchantUserView;
use Illuminate\Support\Facades\Schema;

class DefaultAndSettlementCheck extends Command
{
    protected $signature = 'check:defaultAndSettlementCount {merchantId=""}';
    protected $description = 'To Check no of rows inserted in the default & Settlement cases';
    public function __construct() {
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
        parent::__construct();
    }
    public function handle() {
        Auth::login(User::first());
        $merchantId   = $this->argument('merchantId') ?? '';
        $merchantId   = str_replace('"', '', $merchantId);
        echo "\n morerows";
        $this->morerows($merchantId);
        echo "\n extrarows";
        $this->extrarows($merchantId);
        echo "\n checkNegativeProfitForInvestorVise";
        $this->checkNegativeProfitForInvestorVise($merchantId);
        echo "\n checkNegativeProfitForMerchantInvestorVise";
        $this->checkNegativeProfitForMerchantInvestorVise($merchantId);
        echo "\n checkNegativeProfitForMerchantVise";
        $this->checkNegativeProfitForMerchantVise($merchantId);
        echo "\n profitAndPrincipalCheck";
        $this->profitAndPrincipalCheck($merchantId);
        return 0;
    }
    public function checkNegativeProfitForMerchantVise($merchantId) {
        $List = MerchantUser::where('paid_profit','<',0);
        if($merchantId){
            $List = $List->where('merchant_id',$merchantId);
        }
        $List = $List->where('merchant_id','!=',9783);
        $List = $List->select(
            'merchant_id',
            'paid_profit',
            'paid_principal',
        );
        $List = $List->groupBy('merchant_id');
        $List = $List->get();
        $List = $List->toArray();
        if (count($List)) {
            $title = 'Investors Negative Profit Value For Merchant Vise';
            echo "\n Sending mail for $title \n";
            $titles = [ 'Merchant ID', 'Profit' ,'Principal' ];
            $values = [ 'merchant_id', 'paid_profit','paid_principal'];
            $this->sendMail($List, $titles, $values, $title);
        }
    }
    public function checkNegativeProfitForMerchantInvestorVise($merchantId) {
        $List = MerchantUser::where('paid_profit','<',0);
        if($merchantId){
            $List = $List->where('merchant_id',$merchantId);
        }
        $List = $List->where('merchant_id','!=',9783);
        $List = $List->select(
            'merchant_id',
            'user_id',
            'paid_profit',
            'paid_principal',
        );
        $List = $List->get();
        $List = $List->toArray();
        if (count($List)) {
            $title = 'Investors Negative Profit Value For Merchant Investor Vise';
            echo "\n Sending mail for $title \n";
            $titles = [ 'Merchant ID','Investor ID', 'Profit' ,'Principal' ];
            $values = [ 'merchant_id','user_id' , 'paid_profit','paid_principal'];
            $this->sendMail($List, $titles, $values, $title);
        }
    }
    public function checkNegativeProfitForInvestorVise($merchantId) {
        $List = MerchantUser::where('paid_profit','<',0);
        if($merchantId){
            $List = $List->where('merchant_id',$merchantId);
        }
        $List = $List->where('merchant_id','!=',9783);
        $List = $List->select(
            'user_id',
            DB::raw('sum(paid_profit) as paid_profit'),
            DB::raw('sum(paid_principal) as paid_principal'),
        );
        $List = $List->groupBy('user_id');
        $List = $List->get();
        $List = $List->toArray();
        if (count($List)) {
            $title = 'Investors Negative Profit Value For Investor Vise';
            echo "\n Sending mail for $title \n";
            $titles = [ 'Investor ID', 'Profit' ,'Principal' ];
            $values = [ 'user_id' , 'paid_profit','paid_principal'];
            $this->sendMail($List, $titles, $values, $title);
        }
    }
    public function profitAndPrincipalCheck($merchantId) {
        $data         = [];
        $dataMerchant = [];
        $Merchants = new Merchant;
        if($merchantId){
            $Merchants = $Merchants->where('id',$merchantId);
        }
        // $Merchants = $Merchants->where('id',8808);
        $Merchants = $Merchants->whereIn('sub_status_id',[4,22,18,19,20]);
        // $Merchants = $Merchants->limit(100);
        $Merchants = $Merchants->select(
            'id',
            'name',
            'sub_status_id',
        );
        $Merchants = $Merchants->get();
        foreach ($Merchants as $key => $Merchant) {
            echo "\n merchant $Merchant->id";
            $single['merchant_id']   = $Merchant->id;
            $single['Merchant']      = $Merchant->name;
            $sub_status_id           = $Merchant->sub_status_id;
            $single['sub_status_id'] = $sub_status_id;
            $single['sub_status']    = $Merchant->payStatus;
            $singleMerchant          = $single;
            $singleMerchant['actual_profit']      = 0;
            $singleMerchant['actual_principal']   = 0;
            $singleMerchant['existing_principal'] = 0;
            $singleMerchant['existing_profit']    = 0;
            $singleMerchant['principal_diff']     = 0;
            $singleMerchant['profit_diff']        = 0;
            $MerchantUsers = MerchantUser::where('merchant_id',$Merchant->id);
            $MerchantUsers = $MerchantUsers->select(
                'id',
                'user_id',
                'invest_rtr',
                'paid_participant_ishare',
                'paid_principal',
                'paid_profit',
                DB::raw('(merchant_user.amount + merchant_user.commission_amount + merchant_user.pre_paid + merchant_user.under_writing_fee+merchant_user.up_sell_commission) as investment_amount')
            );
            $MerchantUsers = $MerchantUsers->get();
            foreach ($MerchantUsers as $key => $MerchantUser) {
                $single['investor_id']  = $MerchantUser->user_id;
                
                $PaymentInvestors = PaymentInvestors::where('merchant_id',$Merchant->id);
                $PaymentInvestors = $PaymentInvestors->where('user_id',$MerchantUser->user_id);
                $PaymentInvestors = $PaymentInvestors->where('participant_share','!=',0);
                $PaymentInvestors = $PaymentInvestors->select(
                    'merchant_id',
                    DB::raw('round(sum(participant_share),2) as participant_share'),
                    DB::raw('round(sum(mgmnt_fee),2) as mgmnt_fee'),
                    DB::raw('round(sum(principal),2) as principal'),
                    DB::raw('round(sum(profit),2) as profit'),
                );
                $PaymentInvestors = $PaymentInvestors->groupBy('merchant_id');
                $PaymentInvestors = $PaymentInvestors->first();
                if($PaymentInvestors){
                    $principal = 0;
                    $profit    = 0;
                    if($MerchantUser['invest_rtr']>$PaymentInvestors['participant_share']){
                        if (in_array($sub_status_id, [4, 22, 18, 19, 20])) {
                            if($PaymentInvestors['participant_share'] >= $MerchantUser['investment_amount']){
                                $profit = $MerchantUser['investment_amount'] - $PaymentInvestors['principal'];
                                if($profit>$PaymentInvestors['profit']){ $profit = $PaymentInvestors['profit']; }
                                if($profit>0){
                                    $profit = round(-$profit,2);
                                } else {
                                    $profit = round($profit,2);
                                }
                            } else {
                                $profit = -$PaymentInvestors['profit'];
                                if($profit>0){
                                    $profit = round(-$profit,2);
                                } else {
                                    $profit = round($profit,2);
                                }
                            }
                            $principal = $profit*-1;
                        } else {
                            $profit    = 0;
                            $principal = 0;
                        }
                    }
                    
                    $PaymentInvestorStatusChange = PaymentInvestors::where('merchant_id',$Merchant->id);
                    $PaymentInvestorStatusChange = $PaymentInvestorStatusChange->where('user_id',$MerchantUser->user_id);
                    $PaymentInvestorStatusChange = $PaymentInvestorStatusChange->where('participant_share','=',0);
                    $PaymentInvestorStatusChange = $PaymentInvestorStatusChange->select(
                        'merchant_id',
                        DB::raw('round(sum(principal),2) as principal'),
                        DB::raw('round(sum(profit),2) as profit'),
                    );
                    $PaymentInvestorStatusChange = $PaymentInvestorStatusChange->groupBy('merchant_id');
                    $PaymentInvestorStatusChange = $PaymentInvestorStatusChange->first();
                    if($PaymentInvestorStatusChange){
                        $existing_profit    = $PaymentInvestorStatusChange['profit'];
                        $existing_principal = $PaymentInvestorStatusChange['principal'];
                        if($existing_profit!=$profit) {
                            $single['actual_profit']      = $profit;
                            $single['actual_principal']   = $principal;
                            $single['existing_principal'] = $existing_principal;
                            $single['existing_profit']    = $existing_profit;
                            $single['principal_diff']     = round($single['existing_principal'] - $single['actual_principal'],2);
                            $single['profit_diff']        = round($single['existing_profit'] - $single['actual_profit'],2);
                            $singleMerchant['actual_profit']        += $single['actual_profit'];
                            $singleMerchant['actual_principal']     += $single['actual_principal'];
                            $singleMerchant['existing_principal']   += $single['existing_principal'];
                            $singleMerchant['existing_profit']      += $single['existing_profit'];
                            $singleMerchant['principal_diff']       += $single['principal_diff'];
                            $singleMerchant['profit_diff']          += $single['profit_diff'];
                            $data[]=$single;
                        }
                    }
                }
            }
            if($singleMerchant['principal_diff']|| $singleMerchant['profit_diff']){
                $dataMerchant[]=$singleMerchant;
            }
        }
        if (count($dataMerchant)) {
            $title = 'Profit And Principal values Merchant Vise';
            echo "\n Sending mail for $title \n";
            $titles = ['Merchant ID','Merchant', 'sub_status_id','sub_status','actual_principal','existing_principal','principal_diff','actual_profit','existing_profit','profit_diff'];
            $values = ['merchant_id','Merchant', 'sub_status_id','sub_status','actual_principal','existing_principal','principal_diff','actual_profit','existing_profit','profit_diff'];
            $this->sendMail($dataMerchant, $titles, $values, $title);
        }
        if (count($data)) {
            $title = 'Profit And Principal values Invsetor Vise';
            echo "\n Sending mail for $title \n";
            $titles = ['Merchant ID','Merchant','investor_id', 'sub_status_id','sub_status','actual_principal','existing_principal','principal_diff','actual_profit','existing_profit','profit_diff'];
            $values = ['merchant_id','Merchant','investor_id', 'sub_status_id','sub_status','actual_principal','existing_principal','principal_diff','actual_profit','existing_profit','profit_diff'];
            $this->sendMail($data, $titles, $values, $title);
        }
    }
    public function extrarows($merchantId) {
        $list = new ParticipentPayment;
        if($merchantId){
            $list = $list->where('merchant_id',$merchantId);
        }
        $list = $list->whereHas('merchant', function ($query) {
            $query->whereNotIn('sub_status_id',[4,22,18,19,20]);
        });
        $list = $list->where('participent_payments.reason','LIKE','%Changed to%');
        $list = $list->select(
            'merchant_id',
            'participent_payments.reason',
        );
        $list = $list->get();
        $data=[];
        foreach ($list as $key => $value) {
            $single['merchant_id']   = $value->merchant_id;
            $single['sub_status_id'] = $value->Merchant->sub_status_id;
            $single['sub_status']    = $value->Merchant->payStatus;
            $single['reason']        = $value->reason;
            $data[]=$single;
        }
        if (count($data)) {
            $title = 'Merchants Non Default And Settlement unwatned rows';
            echo "\n Sending mail for $title \n";
            $titles = ['Merchant ID','sub_status', 'Reason'];
            $values = ['merchant_id','sub_status', 'reason'];
            $this->sendMail($data, $titles, $values, $title);
        }
    }
    public function morerows($merchantId) {
        $list = new ParticipentPayment;
        $list = $list->whereHas('merchant', function ($query) {
            $query->whereIn('sub_status_id',[4,22,18,19,20]);
        });
        if($merchantId){
            $list = $list->where('merchant_id',$merchantId);
        }
        $list = $list->where('participent_payments.reason','LIKE','%Changed to%');
        $list = $list->groupBy('merchant_id');
        $list = $list->havingRaw('count(participent_payments.merchant_id)>1');
        $list = $list->select(
            'merchant_id',
            DB::raw('count(participent_payments.merchant_id) as count'),
        );
        $list = $list->get();
        $data=[];
        foreach ($list as $key => $value) {
            $single['merchant_id']   = $value->merchant_id;
            $single['sub_status_id'] = $value->Merchant->sub_status_id;
            $single['sub_status']    = $value->Merchant->payStatus;
            $single['count']         = $value->count;
            $data[]=$single;
        }
        if (count($data)) {
            $title = 'Merchants Default And Settlement row count';
            echo "\n Sending mail for $title \n";
            $titles = ['Merchant ID','sub_status', 'Count'];
            $values = ['merchant_id','sub_status', 'count'];
            $this->sendMail($data, $titles, $values, $title);
        }
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
