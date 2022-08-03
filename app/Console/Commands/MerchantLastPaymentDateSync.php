<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Merchant;
use App\ParticipentPayment;
use App\Settings;
use DB;
use FFM;
use App\Jobs\CommonJobs;
use Carbon\Carbon;
use App\Exports\Data_arrExport;
use Illuminate\Support\Facades\Schema;
class MerchantLastPaymentDateSync extends Command
{
    protected $signature = 'sync:last_payment_date {merchantId=""}';
    protected $description = 'Update Merchant last payment date';
    public function __construct() {
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
        parent::__construct();
    }
    public function handle() {
        $merchantId = $this->argument('merchantId') ?? '';
        $merchantId = str_replace('"', '', $merchantId);
        $Merchants = Merchant::orderByDESC('id');
        $Merchants = $Merchants->where('id','!=','9783');
        if($merchantId){
            $Merchants = $Merchants->where('id',$merchantId);
        }
        $Merchants = $Merchants->select(
            'id',
            'name',
            'sub_status_id',
            'complete_percentage',
            'last_payment_date',
        );
        $Merchants = $Merchants->get();
        $last_payment_date_Array=[];
        echo "Last payment date check started \n";
        foreach ($Merchants as $key => $Merchant) {
            $merchant_id = $Merchant->id;
            $last_payment_date = ParticipentPayment::whereHas('paymentInvestors', function ($query) {
                $query->where('payment_investors.participant_share', '>=', 0);
            })
            ->where('participent_payments.is_payment', 1)
            ->where('participent_payments.merchant_id', $merchant_id)
            ->where('participent_payments.payment','>=',0)
            ->orderByDesc('payment_date')
            ->max('payment_date');
            if($last_payment_date){
                if($Merchant->last_payment_date != $last_payment_date){
                    $single['merchant_name'] = $Merchant->name;
                    $single['merchant_id']   = $merchant_id;
                    $single['status']        = $Merchant->payStatus;
                    $single['percentage']    = $Merchant->complete_percentage;
                    $single['old_date']      = FFM::date($Merchant->last_payment_date);
                    $single['new_date']      = FFM::date($last_payment_date);
                    $Merchant->last_payment_date = $last_payment_date;
                    $Merchant->save();
                    $last_payment_date_Array[]=$single;
                }
            }
        }
        echo "Last payment date check completed \n";
        if($last_payment_date_Array){
            $title = 'Last payment date list';
            echo "\n Sending mail for $title \n";
            $header       = ['Merchant ID','Merchant Name','Percentage','Status', 'Old Date','New Date'];
            $headerValues = ['merchant_id','merchant_name','percentage','status', 'old_date','new_date'];
            $this->sendMail($last_payment_date_Array, $header, $headerValues, $title);
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
