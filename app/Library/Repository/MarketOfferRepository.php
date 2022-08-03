<?php

namespace App\Library\Repository;

use App\Jobs\CommonJobs;
use App\Library\Repository\Interfaces\IMarketOfferRepository;
use App\MailLog;
use App\MarketOffers;
use App\Merchant;
use App\Template;
use App\User;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Settings;
use Illuminate\Support\Facades\Schema;

class MarketOfferRepository implements IMarketOfferRepository
{
    public function __construct()
    {
        $this->table = new MarketOffers();
        if(Schema::hasTable('settings')){
            $this->admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        }
    }

    public function createRequest($request)
    {
        $template_subject = '';
        $template_content = '';
        $template = Template::where('id', $request->template)->first();
        if ($template) {
            if ($template->enable == 1) {
                $template_subject = $template->subject;
                $template_content = $template->template;
            } else {
                throw new Exception('Please enable the <a target="_blank" href="'.URL("/admin/template/edit", [$template->id]).'">template</a>.');
            }
        } else {
            throw new Exception('No templates found. please add <a target="_blank" href="'.URL("/admin/template/create").'">templates</a> to continue.');
        }
        $offer = MarketOffers::create(['offers'=>$request->offer, 'title'=>$request->title, 'type'=>$request->type, 'template_id'=>$request->template]);

        $message['title'] = $request->title;
        $message['offer'] = $request->offer;
        $message['investors'] = $request->investors;
        $message['merchants'] = $request->merchants;
        $type = $request->type;

        //$message['template_type']=$type;

        $alert = '';
        $error = '';
        $data = [];

        if ($request->investors) {
            foreach ($request->investors as $key => $value) {
                $message['user_id'] = $value;
                $message['template_type'] = 'investor';

                $investor = User::where('id', $value)->first()->toArray();

                if ($type == 'email') {
                    // header('Content-type: text/plain');
                    $msg['title'] = $message['title'];
                    $msg['subject'] = $message['title'];
                    $msg['offer'] = $message['offer'];
                    $msg['to_mail'] = $investor['email'];
                    $msg['status'] = 'marketing_offer';
                    $msg['name'] = $investor['name'];
                    $msg['unqID'] = unqID();
                    $msg['template'] = $request->template; //template_id

                    /***************************** implement queue jobs here *********************************/

                    try {
                        $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                        dispatch($emailJob);
                        $msg['to_mail'] = $this->admin_email;
                        $emailJob = (new CommonJobs($msg));
                        dispatch($emailJob);
                        if ($template->assignees) {
                            $template_assignee = explode(',', $template->assignees);
                            $bcc_mails = [];
                            foreach ($template_assignee as $assignee) {
                                $role_mails = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                $role_mails = array_diff($role_mails, [$investor['email']]);
                                $bcc_mails[] = $role_mails;        
                            }
                            $msg['to_mail'] = Arr::flatten($bcc_mails);
                            $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                            dispatch($emailJob);    
                        }
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                } elseif ($type == 'mobile') {
                    $param_s = [
                        'subject' => $template_subject,
                        'name' => $investor['name'],
                        'offer' => $message['offer'],
                        'title' => $message['title']
                    ];
                    $message['heading'] = $this->templateShortCode($param_s);
                    $param_t = [
                        'template' => $template_content,
                        'name' => $investor['name'],
                        'offer' => $message['offer'],
                        'title' => $message['title']
                    ];
                    $message['content'] = $this->templateShortCode($param_t);
                    \EventHistory::pushInvestorOffer($message);
                } else {
                }

                DB::table('investors_offers')->insert(['investor_id'=>$value, 'offer_id'=>$offer->id]);

                $alert .= $investor['name'].'  '.$type.' sent successfully <br>';
            }
        }

        // merchant section mail send

        if ($request->merchants) {
            foreach ($request->merchants as $key => $value) {
                $user_id = Merchant::where('id', $value)->value('user_id');
                $merchant = User::where('id', $user_id);
                $message['template_type'] = 'merchant';

                if ($merchant->count() != 0) {
                    $merchant = $merchant->first()->toArray();

                    if (! empty($merchant)) {
                        $message['user_id'] = $merchant['id'];

                        if ($type == 'email') {
                            // header('Content-type: text/plain');
                            $msg['title'] = $message['title'];
                            $msg['subject'] = $message['title'];
                            $msg['offer'] = $message['offer'];
                            $msg['to_mail'] = $merchant['email'];
                            $msg['status'] = 'marketing_offer';
                            $msg['name'] = $merchant['name'];
                            $msg['unqID'] = unqID();
                            $msg['template'] = $request->template;

                            /***************************** implement queue jobs here *********************************/
                            $template_data = DB::table('template')->where('id', $request->template)->first();
                            if ($template_data) {
                                $subject = $template_data->subject;
                                $param_s = ['subject' => $subject, 'name' => $merchant['name'], 'title' => $message['title']];
                                $subject = $this->templateShortCode($param_s);                        
                                $message['subject'] = $subject;
                            }
                            if ($merchant['email'] == null) {
                                $values = [
                                    'title' => $message['subject'],
                                    'type' => 4,
                                    'to_mail' => '-',
                                    'status' => 'failed',
                                    'to_user_type' => 'merchant',
                                    'to_id' => $value,
                                    'to_name' => $merchant['name'],
                                    'failed_message'=> 'email is null',
                                    'creator_id' => (Auth::check()) ? Auth::user()->id : null
                                ];
                                MailLog::create($values);
                            }
                            try {
                                $email_template = Template::where([
                                    ['id', '=', $request->template], ['enable', '=', 1],
                                ])->first();
                                if ($email_template) {
                                    $values = [
                                        'title' => $message['subject'],
                                        'type' => 4,
                                        'to_mail' => $merchant['email'],
                                        'to_user_type' =>   'merchant',
                                        'to_id' => $value,
                                        'to_name' => $merchant['name'],
                                        'status'  => 'success',
                                        'creator_id' => (Auth::check()) ? Auth::user()->id : null
                                    ];
                                    MailLog::create($values);
                                    $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                                    dispatch($emailJob);
                                    $msg['to_mail'] = $this->admin_email;
                                    $emailJob = (new CommonJobs($msg));
                                    dispatch($emailJob);
                                    if ($template->assignees) {
                                        $template_assignee = explode(',', $template->assignees);
                                        $bcc_mails = [];
                                        foreach ($template_assignee as $assignee) {
                                            $bcc_mails[] = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();   
                                        }
                                        $msg['to_mail'] = Arr::flatten($bcc_mails);
                                        $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                                        dispatch($emailJob);  
                                    }
                                }
                            } catch (\Exception $e) {
                                $values = [
                                    'title' => $message['subject'],
                                    'type' => 4,
                                    'to_mail' => $merchant['email'],
                                    'status'  => 'failed',
                                    'to_user_type' => 'merchant',
                                    'to_id' => $value,
                                    'to_name' => $merchant['name'],
                                    'failed_message'=> $e->getMessage(),
                                    'creator_id' => (Auth::check()) ? Auth::user()->id : null
                                ];
                                MailLog::create($values);
                                echo $e->getMessage();
                            }
                        } elseif ($type == 'mobile') {
                            $param_s = [
                                'subject' => $template_subject,
                                'name' => $merchant['name'],
                                'offer' => $message['offer'],
                                'title' => $message['title']
                            ];
                            $message['heading'] = $this->templateShortCode($param_s);
                            $param_t = [
                                'template' => $template_content,
                                'name' => $merchant['name'],
                                'offer' => $message['offer'],
                                'title' => $message['title']
                            ];
                            $message['content'] = $this->templateShortCode($param_t);
                            \EventHistory::pushMerchantOffer($message);
                        }

                        $alert .= $merchant['name'].'  '.$type.' sent successfully <br>';
                    } else {
                    }
                } else {
                    $name = Merchant::where('id', $value)->value('name');
                    $alert .= $name.' '.$type.' not sent successfully <br>';
                }

                DB::table('merchant_market_offers')->insert(['merchant_id'=>$value, 'offer_id'=>$offer->id]);
            }
        }

        $data['offer'] = $offer;
        $data['msg'] = $alert;

        return $data;
    }

    public function updateRequest($request)
    {
        $offer = MarketOffers::where('id', $request->offer_id)->update(['offers'=>$request->offer, 'title'=>$request->title, 'type'=>$request->type, 'template_id'=>$request->template]);

        $message['title'] = $request->title;
        $message['content'] = $request->offer;
        $message['investors'] = $request->investors;
        $message['merchants'] = $request->merchants;
        $type = $request->type;
        $alert = '';
        $error = '';
        $data = [];


        if ($offer) {
            if (! empty($request->merchants)) {
                $message['template_type'] = 'merchant';
                DB::table('merchant_market_offers')->where('offer_id', $request->offer_id)->delete();

                foreach ($request->merchants as $key => $value) {
                    $user_id = Merchant::where('id', $value)->value('user_id');
                    $merchant = User::where('id', $user_id);    
                    $merchant_name = Merchant::where('id', $value)->value('name');
                    if ($merchant->count() != 0) {
                        $merchant = $merchant->first()->toArray();

                        if (! empty($merchant)) {
                            if ($type == 'email') {
                                $message['user_id'] = $value;

                                // header('Content-type: text/plain');
                                $msg['title'] = $message['title'];
                                $msg['subject'] = $message['title'];
                                $msg['offer'] = $message['content'];
                                $msg['to_mail'] = $merchant['email'];
                                $msg['status'] = 'marketing_offer';
                                $msg['name'] = $merchant_name;
                                $msg['unqID'] = unqID();
                                $msg['template'] = $request->template;

                                /***************************** implement queue jobs here *********************************/
                                $template_data = DB::table('template')->where('id', $request->template)->first();
                                if ($template_data) {
                                    $subject = $template_data->subject;
                                    $param_s = ['subject' => $subject, 'name' => $merchant_name, 'title' => $message['title']];
                                    $subject = $this->templateShortCode($param_s);                        
                                    $message['subject'] = $subject;
                                }
                                if ($merchant['email'] == null) {
                                    $values = [
                                        'title' => $message['subject'],
                                        'type' => 4,
                                        'to_mail' => '-',
                                        'status' => 'failed',
                                        'to_user_type' => 'merchant',
                                        'to_id' => $value,
                                        'to_name' => $merchant_name,
                                        'failed_message'=> 'email is null',
                                        'creator_id' => (Auth::check()) ? Auth::user()->id : null
                                    ];
                                    MailLog::create($values);
                                }
                                try {
                                    $email_template = Template::where([
                                        ['id', '=', $request->template], ['enable', '=', 1],
                                    ])->first();
                                    if ($email_template) {
                                        $values = [
                                            'title' => $message['subject'],
                                            'type' => 4,
                                            'to_mail' => $merchant['email'],
                                            'to_user_type' =>   'merchant',
                                            'to_id' => $value,
                                            'to_name' => $merchant_name,
                                            'status'  => 'success',
                                            'creator_id' => (Auth::check()) ? Auth::user()->id : null
                                        ];
                                        MailLog::create($values);
                                        $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                                        dispatch($emailJob);
                                        $msg['to_mail'] = $this->admin_email;
                                        $emailJob = (new CommonJobs($msg));
                                        dispatch($emailJob);
                                        if ($template_data && $template_data->assignees) {
                                            $template_assignee = explode(',', $template_data->assignees);
                                            $bcc_mails = [];
                                            foreach ($template_assignee as $assignee) {
                                                $bcc_mails[] = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                            }
                                            $msg['to_mail'] = Arr::flatten($bcc_mails);
                                            $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                                            dispatch($emailJob);
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $values = [
                                        'title' => $message['subject'],
                                        'type' => 4,
                                        'to_mail' => $merchant['email'],
                                        'status'  => 'failed',
                                        'to_user_type' => 'merchant',
                                        'to_id' => $value,
                                        'to_name' => $merchant_name,
                                        'failed_message'=> $e->getMessage(),
                                        'creator_id' => (Auth::check()) ? Auth::user()->id : null
                                    ];
                                    MailLog::create($values);
                                    echo $e->getMessage();
                                }
                            } elseif ($type == 'mobile') {
                                $message['user_id'] = $value;

                                \EventHistory::pushMerchantOffer($message);
                            }

                            $alert .= $merchant_name.' '.$type.' sent successfully <br>';
                        } else {
                        }
                    } else {
                        $alert .= $merchant_name.' '.$type.' not sent successfully .<br>';
                    }

                    DB::table('merchant_market_offers')->insert(['merchant_id'=>$value, 'offer_id'=>$request->offer_id]);
                }
            }

            if (! empty($request->investors)) {
                $message['template_type'] = 'investor';
                foreach ($request->investors as $key => $value) {
                    $message['user_id'] = $value;
                    $investor = User::where('id', $value)->first()->toArray();
                    $template_data = DB::table('template')->where('id', $request->template)->first();
                    if ($template_data) {
                        $subject = $template_data->subject;
                        $param_s = ['subject' => $subject, 'name' => $investor['name'], 'title' => $message['title']];
                        $subject = $this->templateShortCode($param_s);                        
                        $message['subject'] = $subject;
                    }
                    if ($type == 'email') {
                        header('Content-type: text/plain');
                        $msg['title'] = $message['title'];
                        $msg['subject'] = $message['title'];
                        $msg['offer'] = $message['content'];
                        $msg['to_mail'] = $investor['email'];
                        $msg['status'] = 'marketing_offer';
                        $msg['name'] = $investor['name'];
                        $msg['unqID'] = unqID();
                        $msg['template'] = $request->template;
                        /***************************** implement queue jobs here *********************************/

                        try {
                            $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                            dispatch($emailJob);
                            $msg['to_mail'] = $this->admin_email;
                            $emailJob = (new CommonJobs($msg));
                            dispatch($emailJob);
                            if ($template_data && $template_data->assignees) {
                                $template_assignee = explode(',', $template_data->assignees);
                                $bcc_mails = [];
                                foreach ($template_assignee as $assignee) {
                                    $bcc_mails[] = app('App\Library\Repository\RoleRepository')->allUserRoleData($assignee)->pluck('email')->toArray();
                                }
                                $msg['to_mail'] = Arr::flatten($bcc_mails);
                                $emailJob = (new CommonJobs($msg))->delay(now()->addMinutes(60));
                                dispatch($emailJob);
                            }
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    } elseif ($type == 'mobile') {
                        \EventHistory::pushInvestorOffer($message);
                    } else {
                    }

                    DB::table('investors_offers')->insert(['investor_id'=>$value, 'offer_id'=>$request->offer_id]);

                    $alert .= $investor['name'].' '.$type.' sent successfully <br>';
                }
            }
        }

        $data['offer'] = $offer;
        $data['msg'] = $alert;

        return $data;
    }
    public function templateShortCode($param, $type ='mobile')
    {
        if (array_key_exists('subject', $param)) {
            $template = $param['subject'];
        } else {
            $template = $param['template'];
        }
        foreach ($param as $key => $p) {
            if (array_key_exists($key, $param)) {
                $template = str_replace('['.$key.']', $param[$key], $template);
            }
        }
        if ($type == 'mobile') {
            $template = str_replace("\r\n","",$template);
            $template = str_replace('&nbsp;', ' ', $template);
            $template = str_replace('</p>', '  ', $template); 
            return strip_tags($template);   
        }
        return $template;
    }
}
