<?php
namespace App\Console\Commands\SingleUse\ProductionAdjustment;
use Illuminate\Console\Command;
use App\Merchant;
use App\MerchantUser;
use Carbon\Carbon;
use FFM;
use DB;
use App\Exports\Data_arrExport;
use App\Jobs\CommonJobs;
use App\Settings;
class MerchantRtrAndInvestorRtrCheck extends Command
{
    protected $signature = 'check:merchant_rtr_and_investor_rtr {change=false} {merchantId=""}';
    protected $description = 'Compare Merchant RTR And Investor RTR';
    public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        $change = $this->argument('change') ?? false;
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $Merchant      =new Merchant;
        $Merchant      =$Merchant->whereIn('label',[1,2]);
        $Merchant      =$Merchant->where('id','!=',9783);
        if ($merchantId) {
            $Merchant = $Merchant->where('id', $merchantId);
        }
        $Merchants     =$Merchant->get();
        $MerchantCount =$Merchant->count();
        $datas=[];
        foreach ($Merchants as $key => $Merchant) {
            DB::beginTransaction();
            $merchant_id              =$Merchant->id;
            $count                    =$MerchantCount-$key;
            $max_participant_fund     =$Merchant->max_participant_fund;
            $funded                   =$Merchant->funded;
            $factor_rate              =$Merchant->factor_rate;
            $max_participant_fund_per =$funded/$max_participant_fund;
            $investor_rtr             =MerchantUser::where('merchant_id',$merchant_id)->sum('invest_rtr');
            $investor_rtr=round($investor_rtr,2);
            if($investor_rtr){
                $merchant_rtr =$Merchant->rtr/$max_participant_fund_per;
                $merchant_rtr =round($merchant_rtr,2);
                $diff         =round($merchant_rtr-$investor_rtr,2);
                if($diff){
                    $single['merchant_name']          =$Merchant->name;
                    $single['merchant_id']            =$merchant_id;
                    $single['merchant_rtr']           =$merchant_rtr;
                    $single['syndication_percentage'] =$Merchant->max_participant_fund_per;
                    $single['investor_rtr']           =$investor_rtr;
                    $single['difference']             =$diff;
                    $single['user_id']                =0;
                    $single['user_invest_rtr']        =0;
                    $single['new_user_invest_rtr']    =0;
                    echo $count.")".$merchant_id;
                    echo " percentage ".round($Merchant->max_participant_fund_per,2)." - ";
                    echo " Merchant RTR ".$merchant_rtr." - ";
                    echo " Investor RTR ".$investor_rtr." - ";
                    echo " Diff ".$diff." - ";
                    echo "\n";
                    if(abs($diff)<0.5){
                        if($change){
                            $MerchantUsersLarry=MerchantUser::where('merchant_id',$merchant_id)->where('amount','!=',0)->where('user_id',25)->first(['id','user_id','amount','invest_rtr','paid_participant_ishare']);
                            if($MerchantUsersLarry){
                                if($diff!=0){ 
                                    $new_invest_rtr=$MerchantUsersLarry->invest_rtr;
                                    $actual_investor_rtr =round($MerchantUsersLarry->amount*$factor_rate,2);
                                    $balance =round($MerchantUsersLarry->invest_rtr-$MerchantUsersLarry->paid_participant_ishare,2);
                                    if($balance!=0){
                                        if($balance>0){
                                            if($diff<0){
                                                if(abs($diff)>=$balance){
                                                    $new_invest_rtr =$actual_investor_rtr+$balance;
                                                    $diff-=$balance;
                                                } else {
                                                    $new_invest_rtr =$actual_investor_rtr+$diff;
                                                    $diff=0;
                                                }
                                            }
                                        } else {
                                            if($diff>0){
                                                if($diff>=abs($balance)){
                                                    $new_invest_rtr =$actual_investor_rtr+abs($balance);
                                                    $diff-=abs($balance);
                                                } else {
                                                    $new_invest_rtr =$actual_investor_rtr+$diff;
                                                    $diff=0;
                                                }
                                            }
                                        }
                                        if($new_invest_rtr!=$MerchantUsersLarry->invest_rtr){
                                            $actual_investor_rtr =round($MerchantUsersLarry->amount*$factor_rate,2);
                                            $single['user_id']             =$MerchantUsersLarry->user_id;
                                            $single['user_invest_rtr']     =$MerchantUsersLarry->invest_rtr;
                                            $single['new_user_invest_rtr'] =$new_invest_rtr;
                                            echo "         ";
                                            echo $MerchantUsersLarry->user_id."-";
                                            echo $MerchantUsersLarry->invest_rtr." - ";
                                            echo "Actual ".$actual_investor_rtr." - ";
                                            echo "New ".$new_invest_rtr;
                                            echo "\n";
                                            DB::table('merchant_user')
                                            ->where('id', $MerchantUsersLarry->id)
                                            ->update([
                                                'invest_rtr'=>$new_invest_rtr,
                                            ]);
                                        }
                                    }
                                }
                            }
                            $MerchantUsers=MerchantUser::where('merchant_id',$merchant_id)->where('amount','!=',0)->orderByDESC('invest_rtr')->get(['id','user_id','amount','invest_rtr']);
                            foreach ($MerchantUsers as $MerchantUser) {
                                if($diff==0){ break; }
                                $flooor_diff=round($MerchantUser->invest_rtr-floor($MerchantUser->invest_rtr),2);
                                if($flooor_diff){
                                    $actual_investor_rtr =round($MerchantUser->amount*$factor_rate,2);
                                    $new_invest_rtr      =$actual_investor_rtr+$diff;
                                    $diff                =0;
                                    $single['user_id']             =$MerchantUser->user_id;
                                    $single['user_invest_rtr']     =$MerchantUser->invest_rtr;
                                    $single['new_user_invest_rtr'] =$new_invest_rtr;
                                    echo "         ";
                                    echo $MerchantUser->user_id."-";
                                    echo $MerchantUser->invest_rtr." - ";
                                    echo "Actual ".$actual_investor_rtr." - ";
                                    echo "New ".$new_invest_rtr;
                                    echo "\n";
                                    DB::table('merchant_user')
                                    ->where('id', $MerchantUser->id)
                                    ->update([
                                        'invest_rtr'=>$new_invest_rtr,
                                    ]);
                                }
                            }
                            if($diff){
                                foreach ($MerchantUsers as $MerchantUser) {
                                    if($diff==0){ break; }
                                    $actual_investor_rtr =round($MerchantUser->amount*$factor_rate,2);
                                    $new_invest_rtr      =$actual_investor_rtr+$diff;
                                    $diff                =0;
                                    $single['user_id']             =$MerchantUser->user_id;
                                    $single['user_invest_rtr']     =$MerchantUser->invest_rtr;
                                    $single['new_user_invest_rtr'] =$new_invest_rtr;
                                    echo "         ";
                                    echo $MerchantUser->user_id."-";
                                    echo $MerchantUser->invest_rtr."-";
                                    echo "Actual ".$actual_investor_rtr." - ";
                                    echo "New ".$new_invest_rtr;
                                    echo "\n";
                                    DB::table('merchant_user')
                                    ->where('id', $MerchantUser->id)
                                    ->update([
                                        'invest_rtr'=>$new_invest_rtr,
                                    ]);
                                }
                            }
                        }
                        $datas[]=$single;
                    }
                }
            }
            DB::commit();
        }
        if (count($datas)) {
            $title = 'Merchants with difference in invested RTR and merchant RTR';
            echo "\n Sending mail for $title \n";
            $titles = ['No', 'Merchant Name', 'Merchant ID','Syndication Percentage', 'Investor RTR', 'Merchant RTR', 'Difference','Investor ID','User RTR','New User RTR'];
            $values = ['', 'merchant_name', 'merchant_id','syndication_percentage', 'investor_rtr', 'merchant_rtr','difference','user_id','user_invest_rtr','new_user_invest_rtr'];
            $this->sendMail($datas, $titles, $values, $title);
        }
        return 0;
    }
    public function sendMail($data, $titles, $values, $title)
    {
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
    public function generateCSV($data, $titles, $values)
    {
        $excel_array[] = $titles;
        $i = 1;
        foreach ($data as $key => $tr) {
            $excel_array[$i][$titles[0]] = $i;
            $excel_array[$i][$titles[1]] = $tr[$values[1]];
            $excel_array[$i][$titles[2]] = $tr[$values[2]];
            $excel_array[$i][$titles[3]] = $tr[$values[3]];
            $excel_array[$i][$titles[4]] = $tr[$values[4]];
            $excel_array[$i][$titles[5]] = $tr[$values[5]];
            $excel_array[$i][$titles[6]] = $tr[$values[6]];
            $excel_array[$i][$titles[7]] = $tr[$values[7]];
            $excel_array[$i][$titles[8]] = $tr[$values[8]];
            $excel_array[$i][$titles[9]] = $tr[$values[9]];
            $i++;
        }
        $export = new Data_arrExport($excel_array);
        return $export;
    }
}
