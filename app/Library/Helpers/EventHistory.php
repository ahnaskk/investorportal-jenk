<?php

namespace App\Library\Helpers;

use App\Jobs\sendtoInvestors;
use App\Mail\pushNotifyInvestor;
use App\Mailboxrow;
use App\Settings;
use App\User;
use Carbon\Carbon;
use FFM;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Pusher;

class EventHistory
{
    
    public function pushNotifyAdmin($message, $investor_id = 0)
    {
        $message['timestamp'] = time();
        $options = ['cluster' => 'us2', 'useTLS' => true];
        $content = ['en' => 'New documents uploaded'];
        $heading = ['en' => $message['merchant_name']];
        $message['user_ids'] = $investor_id;
        $investor_app_id = config('app.investor_app_id');
        $investor_channel_id = config('app.channel_document_id');
        $fields = [
            'app_id'                    => $investor_app_id,
            'include_external_user_ids' => [$investor_id],
            'contents'                  => $content,
            'headings'                  => $heading,
            'app_url'                   => 'investor://notificationStack/notification',
            'android_channel_id'        => $investor_channel_id,
            'android_visibility'        => 0,
            'android_group'             => 'Uploaded new document',
        ];
        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: Basic OGE0NTUzNDAtNjM1MC00ZTk4LTgzMTYtODBkODAzNTY2NWEw']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $message['content'] = isset($message['content']) ? $message['content'] : '';
        $message['title'] = isset($message['title']) ? $message['title'] : '';
        $message['timestamp'] = isset($message['timestamp']) ? $message['timestamp'] : '';
        $json_data = [
            'url'       => $message['url'],
            'date'      => date('d/m/Y'),
            'filename'  => $message['filename'],
            'extension' => $message['extension'],
        ];
        $message['json_data'] = json_encode($json_data, true);
        $this->addToMailbox($message, $investor_id);
    }

    public function pushInvestorOffer($message)
    {
        $options = ['cluster' => 'us2', 'useTLS' => true];
        $user_id = '';
        $common_app_id = '';
        $offer_channel_id = '';
        $app_url = '';
        if ($message['template_type'] == 'investor') {
            $common_app_id = config('app.investor_app_id');
            $offer_channel_id = config('app.channel_newdeal_id');
            $investor_app_rest_api_key = config('app.investor_app_rest_api_key');
            $user_id = $message['user_id'];
            $content = ['en' => $message['content']];
            $heading = ['en' => $message['heading']];
            $fields = [
                'app_id'                    => $common_app_id,
                'include_external_user_ids' => ["$user_id"],
                'app_url'                   => $app_url, 
                'contents'                  => $content,
                'headings'                  => $heading,
                'android_channel_id'        => $offer_channel_id,
                'android_visibility'        => 0,
                'android_group'             => 'Marketing Notifications',
            ];
            $fields = json_encode($fields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: Basic '.$investor_app_rest_api_key]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            $message['content'] = isset($message['content']) ? $message['content'] : '';
            $message['title'] = isset($message['title']) ? $message['title'] : '';
            $message['timestamp'] = isset($message['timestamp']) ? $message['timestamp'] : '';
            $message['type'] = 'Marketing_Offer';
            $json_data = ['user_id' => $message['user_id']];
            $message['json_data'] = json_encode($json_data, true);
            $this->addToMailbox($message, $message['user_id']);
        }
    }

    public function pushMerchantOffer($message)
    {
        $options = ['cluster' => 'us2', 'useTLS' => true];
        $user_id = '';
        $common_app_id = '';
        $offer_channel_id = '';
        $app_url = '';
        if ($message['template_type'] == 'merchant') {
            $common_app_id = config('app.merchant_app_id');
            $offer_channel_id = config('app.channel_offer_id');
            $app_url = 'merchantportal://notifications';
            $merchant_app_rest_api_key = config('app.merchant_app_rest_api_key');
            $check = User::where('id', $message['user_id']);
            if ($check->count() == 1) {
                $check_array = $check->select('id')->first()->toArray();
                $user_id = $check_array['id'];
            } else {
                return;
            }
            $content = ['en' => $message['content']];
            $heading = ['en' => $message['heading']];
            $fields = [
                'app_id'                    => $common_app_id,
                'include_external_user_ids' => ["$user_id"],
                'app_url'                   => $app_url,
                'contents'                  => $content,
                'headings'                  => $heading,
                'android_channel_id'        => $offer_channel_id,
                'android_visibility'        => 0,
                'android_group'             => 'Marketing Notifications',
            ];
            $fields = json_encode($fields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: Basic '.$merchant_app_rest_api_key]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            $message['content'] = isset($message['content']) ? $message['content'] : '';
            $message['title'] = isset($message['title']) ? $message['title'] : '';
            $message['timestamp'] = isset($message['timestamp']) ? $message['timestamp'] : '';
            $message['type'] = 'Marketing_Offer';
            $message['user_ids'] = json_encode([$message['user_id']], true);
            $message['user_ids'] = str_replace('"', '', (string) $message['user_ids']);
            $json_data = ['user_id' => $message['user_id']];
            $message['json_data'] = json_encode($json_data, true);
            $this->addToMailbox($message, $message['user_id']);
        }
    }

    public function pushNotifyInvestor($message)
    {
        $options = ['cluster' => 'us2', 'useTLS' => true];
        $content = ['en' => $message['content']];
        $heading = ['en' => $message['merchant_name']];
        $investors = $message['investors'];
        $investor_app_id = config('app.investor_app_id');
        $investor_channel_id = config('app.channel_newdeal_id');
        $fields = [
            'app_id'                    => $investor_app_id,
            'include_external_user_ids' => $investors,
            'contents'                  => $content,
            'headings'                  => $heading,
            'app_url'                   => 'investor://notificationStack/notification',
            'android_channel_id'        => $investor_channel_id,
            'android_visibility'        => 0,
            'android_group'             => 'New Merchant created',
        ];
        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: Basic OGE0NTUzNDAtNjM1MC00ZTk4LTgzMTYtODBkODAzNTY2NWEw']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $to_email_id = Settings::first()->pluck('email');
        $to_email_id = explode(',', $to_email_id);
        Log::info('Request Cycle with Queues Begins');
        $emailJob = (new sendtoInvestors($message))->delay(Carbon::now()->addMinutes(5));
        dispatch($emailJob);
        Log::info('Request Cycle with Queues Ends');
        $this->addToMailbox($message);
    }

    public function moveToOneSignal($data_array)
    {
        if ($data_array['app_status'] == 'merchant_app') {
            $app_id = config('app.merchant_app_id');
            $app_url = 'merchantportal://notifications';
            $merchant_app_rest_api_key = config('app.merchant_app_rest_api_key');
        } elseif ($data_array['app_status'] == 'investor_app') {
            $app_id = config('app.investor_app_id');
            $app_url = 'investor://notificationStack/notification';
            $investor_app_rest_api_key = config('app.investor_app_rest_api_key');
        }
        $options = ['cluster' => 'us2', 'useTLS' => true];
        $content = ['en' => $data_array['content']];
        $heading = ['en' => 'Investor Payment Notification'];
        $user_id = $data_array['user_id'];
        $fields = [
            'app_id'                        => $app_id,
            'include_external_user_ids'     => ["$user_id"],
            'app_url'                       => $app_url,
            'contents'                      => $content,
            'headings'                      => $heading,
            'android_channel_id'            => $data_array['channel_id'],
            'android_visibility'            => 0,
            'android_group'                 => $data_array['title'],
            'channel_for_external_user_ids' => 'push',
            'included_segments'             => ['notificationEnabled'],
        ];
        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        if ($data_array['app_status'] == 'merchant_app') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: Basic '.$merchant_app_rest_api_key]);
        } elseif ($data_array['app_status'] == 'investor_app') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: Basic '.$investor_app_rest_api_key]);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $message['content'] = isset($data_array['content']) ? $data_array['content'] : '';
        $message['title'] = isset($data_array['title']) ? $data_array['title'] : '';
        $message['timestamp'] = isset($data_array['timestamp']) ? $data_array['timestamp'] : '';
        if ($data_array['app_status'] == 'merchant_app') {
            $message['type'] = 'merchant_payments';
            $message['user_id'] = $user_id;
        } elseif ($data_array['app_status'] == 'investor_app') {
            $message['type'] = 'investor_payments';
            $message['user_id'] = 0;
        }
        $message['user_ids'] = json_encode([$user_id], true);
        $message['user_ids'] = str_replace('"', '', (string) $message['user_ids']);
        $json_data = ['count' => isset($data_array['count']) ? $data_array['count'] : ''];
        $message['json_data'] = json_encode($json_data, true);
        $this->addToMailbox1($message);
    }

    public function addToMailbox1($message)
    {
        $mailboxdb = new Mailboxrow();
        $mailboxdb->content = isset($message['content']) ? $message['content'] : '';
        $mailboxdb->title = isset($message['title']) ? $message['title'] : '';
        $mailboxdb->user_id = isset($message['user_id']) ? $message['user_id'] : '';
        $mailboxdb->type = isset($message['type']) ? $message['type'] : '';
        $mailboxdb->json_data = isset($message['json_data']) ? $message['json_data'] : '';
        $mailboxdb->user_ids = isset($message['user_ids']) ? $message['user_ids'] : '';
        $mailboxdb->timestamp = time();
        $mailboxdb->investor_public = 1;
        $mailboxdb->save();
    }

    public function addToMailbox($message, $user_id = 0)
    {
        $mailboxdb = new Mailboxrow();
        $mailboxdb->content = isset($message['content']) ? $message['content'] : '';
        $mailboxdb->title = isset($message['title']) ? $message['title'] : '';
        $mailboxdb->user_id = isset($user_id) ? $user_id : '';
        $mailboxdb->type = isset($message['type']) ? $message['type'] : '';
        $mailboxdb->json_data = isset($message['json_data']) ? $message['json_data'] : '';
        $mailboxdb->user_ids = isset($message['user_ids']) ? $message['user_ids'] : '';
        $mailboxdb->timestamp = time();
        $mailboxdb->investor_public = 1;
        $mailboxdb->save();
    }

    public function pushNotifyMerchant($message)
    {
        $options = [
            'cluster' => 'us2',
            'useTLS'  => true,
        ];
        $check = User::where('merchant_id_m', $message['merchant_id']);
        if ($check->count() == 1) {
            $check_array = $check->select('id')->first()->toArray();
            $user_id = $check_array['id'];
        } else {
            return;
        }
        $content = ['en' => 'Payment of '.$message['payment'].' was successfully updated in your account.'];
        $heading = ['en' => $message['merchant_name']];
        $merchant_app_id = config('app.merchant_app_id');
        $merchant_payment_channel_id = config('app.merchant_payment_channel_id');
        $fields = [
            'app_id'                    => $merchant_app_id,
            'include_external_user_ids' => ["$user_id"],
            'app_url'                   => 'merchantportal://notifications',
            'contents'                  => $content,
            'headings'                  => $heading,
            'android_channel_id'        => $merchant_payment_channel_id,
            'android_visibility'        => 0,
            'android_group'             => 'Payments Notification',
        ];
        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: OGE0NTUzNDAtNjM1MC00ZTk4LTgzMTYtODBkODAzNTY2NWEw']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $message['content'] = isset($message['content']) ? $message['content'] : '';
        $message['title'] = isset($message['title']) ? $message['title'] : '';
        $message['timestamp'] = isset($message['timestamp']) ? $message['timestamp'] : '';
        $message['type'] = 'merchant_payments';
        $json_data = ['merchant_id' => $message['merchant_id']];
        $message['json_data'] = json_encode($json_data, true);
        $this->addToMailbox($message, $message['merchant_id']);
    }

    public function pushNotifyInvestorPayments_old($message)
    {
        $title = Config::set('app.investor_title', $message['title']);
        $options = ['cluster' => 'us2', 'useTLS' => true];
        $content = ['en' => 'Payment of '.FFM::dollar($message['payment']).' was successfully updated by '.$message['merchant_name'].' in your account.'];
        $heading = ['en' => $message['merchant_name']];
        $investors = $message['investors'];
        $investor_app_id = config('app.investor_app_id');
        $app_mode = config('app.env');
        if ($app_mode == 'production') {
            $investor_channel_id = '9df9881e-0785-4d6f-bb30-e13fe4d44479';
        } else {
            $investor_channel_id = 'e3835036-d25d-4f1e-9b4c-e5fc24de3d64';
        }
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8', 'Authorization: OGE0NTUzNDAtNjM1MC00ZTk4LTgzMTYtODBkODAzNTY2NWEw']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $pusher = new Pusher(config('settings.pusher_app_key'), config('settings.pusher_app_secret'), config('settings.pusher_app_id'), ['cluster' => config('settings.pusher_app_cluster')]);
        $message['content'] = isset($message['content']) ? $message['content'] : '';
        $message['title'] = isset($message['title']) ? $message['title'] : '';
        $message['timestamp'] = isset($message['timestamp']) ? $message['timestamp'] : '';
        $message['type'] = 'investor_payments';
        $message['user_ids'] = json_encode($message['investors'], true);
        $message['user_ids'] = str_replace('"', '', (string) $message['user_ids']);
        $json_data = ['merchant_id' => $message['merchant_id']];
        $message['json_data'] = json_encode($json_data, true);
        $pusher->trigger('merchant', 'merchant.notified', ['content' => $message['content'], 'title' => $message['title'], 'timestamp' => $message['timestamp']]);
        $this->addToMailbox($message, $message['merchant_id']);
    }

    public function toPushBeam()
    {
        return ['apns' => ['aps' => ['alert' => ['title' => 'hello', 'body' => 'hello']]], 'fcm' => ['notification' => ['title' => 'hello', 'body' => 'hello']]];
    }
}
