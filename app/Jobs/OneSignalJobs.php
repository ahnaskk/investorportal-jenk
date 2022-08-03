<?php

namespace App\Jobs;

use EventHistory;
use FFM;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class OneSignalJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message = $this->details;

        $title = Config::set('app.investor_title', $this->details['title']);

        $options = [
            'cluster' => 'us2',
            'useTLS' => true,
         ];

        $content = [
            'en' => 'Payment of '.FFM::dollar($this->details['payment']).' was successfully updated by '.$this->details['merchant_name'].' in your account.',
            ];

        $heading = [
            'en' => $this->details['merchant_name'],
        ];

        $investors = $this->details['investors'];
        $investor_app_id = config('app.investor_app_id');
        $investor_channel_id = config('app.channel_payment_id');

        /*   if($app_mode=='production')
           {
                $investor_channel_id='9df9881e-0785-4d6f-bb30-e13fe4d44479';

           }else
           {
                $investor_channel_id='e3835036-d25d-4f1e-9b4c-e5fc24de3d64';
           }*/

        $fields = [

            'app_id' => $investor_app_id,
            'include_external_user_ids' => $investors,
            'contents' => $content,
            'headings' => $heading,
            'app_url' => 'investor://notificationStack/notification',
            'android_channel_id' => $investor_channel_id,
            'android_visibility' => 0,
            'android_group' => 'Payments Notification',
        ];

        $fields = json_encode($fields);

        //  $client = new \GuzzleHttp\Client();

        //  $response = $client->request('POST', 'https://onesignal.com/api/v1/notifications');

        // $url = "https://onesignal.com/api/v1/notifications";
        // $response = $client->createRequest("POST", $url, $fields);
        // $response = $client->send($response);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8',
                           'Authorization: OGE0NTUzNDAtNjM1MC00ZTk4LTgzMTYtODBkODAzNTY2NWEw', ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);


        $message['content'] = isset($this->details['content']) ? $this->details['content'] : '';
        $message['title'] = isset($this->details['title']) ? $this->details['title'] : '';
        $message['timestamp'] = isset($this->details['timestamp']) ? $this->details['timestamp'] : '';
        $message['type'] = 'investor_payments';

        $message['user_ids'] = json_encode($this->details['investors'], true);
        $message['user_ids'] = str_replace('"', '', (string) $message['user_ids']);

        $json_data = [
            'merchant_id'=>$this->details['merchant_id'],
        ];

        $message['json_data'] = json_encode($json_data, true);

        EventHistory::addToMailbox($message, $message['merchant_id']);
    }
}
