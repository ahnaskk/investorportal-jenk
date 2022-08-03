<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Merchant;
use App\User;
use App\SubStatus;
use MerchantHelper;
use DB;
use Auth;
use FFM;
use App\Jobs\CommonJobs;
use Carbon\Carbon;
use App\Exports\Data_arrExport;
use App\Library\Repository\Interfaces\IMerchantRepository;
class DefaultMerchantCheck extends Command
{
    protected $signature = 'check:defaultMerchant {date}';
    protected $description = 'To change status to Default/Default+';
    public function __construct(IMerchantRepository $merchant) {
        $this->merchant = $merchant;
        parent::__construct();
    }
    public function handle() {
        $arguments = $this->arguments();
        $date = $arguments['date']??date('Y-m-d');
        $date = date('Y-m-d H:i:s',strtotime($date));
        Auth::login(User::first());
        $Merchant = new Merchant;
        $Merchant = $Merchant->select(['merchants.id','sub_status_id', 'merchants.name', 'funded', 'pmnts', 'date_funded', 'marketplace_status', 'paid_count', 'commission', 'rtr', 'merchants.complete_percentage', 'last_payment_date', 'users.lag_time']);
        // $Merchant = $Merchant->where('mail_send_status', '!=', 111);
        $Merchant = $Merchant->leftjoin('users', 'users.id', 'merchants.lender_id');
        $Merchant = $Merchant->where('merchants.active_status', 1);
        $Merchant = $Merchant->where('merchants.sub_status_id', 1);
        $Merchant = $Merchant->where('merchants.complete_percentage', '<', 99);
        $Merchant = $Merchant->where(function ($query) use($date) {
            $query->whereRaw('date(last_payment_date) <  DATE_SUB("'.$date.'", INTERVAL (lag_time+30) DAY)');
        });
        $Merchant = $Merchant->orderByDesc('merchants.id');
        $Merchant = $Merchant->get();
        $list=[];
        foreach ($Merchant as $key => $value) {
            try {
                $merchant_id=$value->id;
                $now =  Carbon::parse(now());
                $single['merchant_id']         = $merchant_id;
                $single['merchant_name']       = $value->name;
                $single['sub_status_id']       = $value->payStatus;
                $single['changed_sub_status']  = '';
                $single['date_funded']         = FFM::date($value->date_funded);
                $single['paid_count']          = $value->paid_count;
                $single['last_payment_date']   = FFM::date($value->last_payment_date);
                $single['lag_time']            = $value->lag_time;
                $single['no_of_days']          = $now->diffInDays($value->last_payment_date)+$single['lag_time'];
                $single['complete_percentage'] = $value->complete_percentage;
                DB::beginTransaction();
                $statusList = $this->merchant->get_substatus_list($value);
                if(in_array(4,$statusList)){
                    $single['changed_sub_status']  = SubStatus::where('id',4)->value('name');
                    $return = MerchantHelper::changeSubStatusFn($merchant_id, 4);
                } elseif(in_array(20,$statusList)) {
                    $single['changed_sub_status']  = SubStatus::where('id',20)->value('name');
                    $return = MerchantHelper::changeSubStatusFn($merchant_id, 20);
                }
                $return = json_decode($return->content(), true);
                $message = $merchant_id." ".$return['msg']."  \n";
                echo $message;
                $list[]=$single;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
            }
        }
        $title = 'Default merchants list';
        echo "\n Sending mail for $title \n";
        $header       = ['Merchant ID','Merchant Name', 'Sub Status','Changed Status', 'Date Funded', 'Paid Count','Last Payment Date','Lag Time','No Of Days', 'Complete Percentage'];
        $headerValues = ['merchant_id','merchant_name', 'sub_status_id','changed_sub_status', 'date_funded', 'paid_count','last_payment_date','lag_time', 'no_of_days','complete_percentage'];
        $this->sendMail($list, $header, $headerValues, $title);
        return 0;
    }
    public function sendMail($data, $header, $headerValues, $title) {
        if (count($data)) {
            $msg['title']         = 'Merchant Default Check';
            $msg['status']        = 'merchant_unit_test';
            $msg['subject']       = $msg['title'];
            $msg['template_type'] = 'merchant_unit';
            $msg['date_time']            = FFM::datetime(Carbon::now()->toDateTimeString());
            $msg['content']['date']      = FFM::date(Carbon::now()->toDateString());
            $msg['content']['date_time'] = $msg['date_time'];
            $msg['content']['type']      = $title;
            $msg['content']['count']     = count($data);
            $msg['to_mail'] = 'emailnotification22@gmail.com';
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
