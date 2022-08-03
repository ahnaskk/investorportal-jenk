<?php
namespace App\Console\Commands\SingleUse;
use App\Merchant;
use App\Settings;
use Illuminate\Console\Command;
use PayCalc;
use FFM;
use App\Jobs\CommonJobs;
use Carbon\Carbon;
use App\Exports\Data_arrExport;
use Illuminate\Support\Facades\Schema;

class MerchantCompletedPercentage extends Command
{
    protected $signature = 'update:MerchantCompletedPercentage {merchantId=""}';
    protected $description = 'To Update Actual Completed Percentage of Merchant';
    public function __construct() {
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
        parent::__construct();
    }
    public function handle() {
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $Merchants = new Merchant;
        $Merchants = $Merchants->where('id','!=','9783');
        if($merchantId){
            $Merchants = $Merchants->where('id',$merchantId);
        }
        $Merchants = $Merchants->get();
        $list=[];
        foreach ($Merchants as $Merchant) {
            $complete_percentage = round($Merchant->complete_percentage,2);
            $merchant_id         = $Merchant->id;
            $complete_per        = PayCalc::completePercentage($merchant_id);
            if ($complete_percentage != $complete_per) {
                Merchant::find($merchant_id)->update(['complete_percentage' => $complete_per]);
                $single['merchant_id']    = $merchant_id;
                $single['merchant_name']  = $Merchant->name;
                $single['old_percentage'] = $Merchant->complete_percentage;
                $single['new_percentage'] = $complete_per;
                echo "\n".$merchant_id.'=';
                echo $Merchant->complete_percentage.':'.$complete_per;
                $list[] = $single;
            }
        }
        $title = 'Merchant Completed Percentage Diffrence list';
        if(count($list)){
            echo "\n Sending mail for $title \n";
            $header       = [ 'MID', 'Merchant Name', 'Old Percentage', 'New Percentage' ];
            $headerValues = [ 'merchant_id', 'merchant_name', 'old_percentage', 'new_percentage' ];
            $this->sendMail($list, $header, $headerValues, $title);
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
