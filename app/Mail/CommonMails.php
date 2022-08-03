<?php

namespace App\Mail;

use App\Template;
use Hashids\Hashids;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommonMails extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /******************  This is a common  mail queue for all section *************************/

        // merchant change default status queue job section
        if ($this->details['status'] == 'merchant_change_status') {
            if ($this->details['template_type'] == 'merchant_status_change_common') {
                $res = Template::where([
                    ['temp_code', '=', 'MCSS'], ['enable', '=', 1],
                ])->first();
                if ($res !== null) {
                    $template = $res->template;
                    if ($res->subject && strpos($res->subject, '[subject]') !== false) {
                        $res->subject = $this->details['title'];
                    }
                    $subject = $res->subject;
                    $param_s = [
                        'subject'       => $res->subject,
                        'merchant_name' => $this->details['merchant_name'],
                        'new_status'    => $this->details['new_status'],
                    ];
                    $subject = $this->templateShortCode($param_s);
                    $param_t = [
                        'template'      => $template,
                        'merchant_name' => $this->details['merchant_name'],
                        'new_status'    => $this->details['new_status'],
                    ];
                    $template = $this->templateShortCode($param_t);
                    $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                    return $this->from('no-reply@vgusa.com')
                        ->view('emails.request_mail')->subject($subject)
                        ->withSwiftMessage(function ($message) {
                            $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                        })
                        ->with(['template' => $template, 'data' => true]);
                }
            } elseif ($this->details['template_type'] == 'advance_complete_100_percent') {
                $res = Template::where([
                    ['temp_code', '=', 'MSAC'], ['enable', '=', 1],
                ])->first();
                if ($res !== null) {
                    $template = $res->template;
                    if ($res->subject && strpos($res->subject, '[subject]') !== false) {
                        $res->subject = $this->details['title'];
                    }
                    $subject = $res->subject;
                    $param_s = [
                        'subject'       => $res->subject,
                        'merchant_name' => $this->details['merchant_name'],
                    ];
                    $subject = $this->templateShortCode($param_s);
                    $param_t = [
                        'template'      => $template,
                        'merchant_name' => $this->details['merchant_name'],
                    ];
                    $template = $this->templateShortCode($param_t);
                    $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                    return $this->from('no-reply@vgusa.com')
                        ->view('emails.request_mail')->subject($subject)
                        ->withSwiftMessage(function ($message) {
                            $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                        })
                        ->with(['template' => $template, 'data' => true]);
                }
            } elseif ($this->details['template_type'] == 'pending_payment') {
                $res = Template::where([
                    ['temp_code', '=', 'MSPP'], ['enable', '=', 1],
                ])->first();
                if ($res !== null) {
                    $template = $res->template;
                    if ($res->subject && strpos($res->subject, '[subject]') !== false) {
                        $res->subject = $this->details['title'];
                    }
                    $subject = $res->subject;
                    $param_s = [
                        'subject'       =>  $res->subject,
                        'merchant_name' =>  $this->details['merchant_name'],
                        'days'          =>  $this->details['days'],
                    ];
                    $subject = $this->templateShortCode($param_s);
                    $param_t = [
                        'template'      =>  $template,
                        'merchant_name' =>  $this->details['merchant_name'],
                        'days'          =>  $this->details['days'],
                    ];
                    $template = $this->templateShortCode($param_t);
                    $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                    return $this->from('no-reply@vgusa.com')
                        ->view('emails.request_mail')->subject($subject)
                        ->withSwiftMessage(function ($message) {
                            $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                        })
                        ->with(['template' => $template, 'data' => true]);
                }
            } elseif ($this->details['template_type'] == 'merchant_status_collection') {
                return $this->from('no-reply@vgusa.com')
                ->view('emails.merchant_status')
                ->subject($this->details['title'])
                ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id']]);
            }
            // else {
            //     return $this->from('no-reply@vgusa.com')
            //         ->view('emails.merchant_status')
            //         ->subject($this->details['title'])
            //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id']]);
            // }
        } elseif ($this->details['status'] == 'new_deal') {
            $res = Template::where([
                ['temp_code', '=', 'MPLCE'], ['enable', '=', 1],
            ])->first();
            if ($res !== null) {
                $template = $res->template;
                $subject = $res->subject;
                $param_s = ['subject' => $res->subject, 'merchant_name' => $this->details['merchant_name']];
                $subject = $this->templateShortCode($param_s);
                $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('investors/marketplace'), $template);

                return $this->from('no-reply@vgusa.com')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['template' => $template, 'data' => true]);
            }
            // else {
            //     return $this->from('no-reply@vgusa.com')
            //         ->view('emails.new_deal')
            //         ->subject($this->details['title'])
            //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id']]);
            // }
        }
         elseif($this->details['status'] == 'api_error_response')
         {
              return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.api_error_response')
                ->subject($this->details['subject'])
                ->with([
                    'title' => $this->details['title'], 'content' => $this->details['content'],
                   'status' => $this->details['status'],
                ]);
         }
         elseif ($this->details['status'] == 'liquidty_alert') {
            $res = Template::where([
                ['temp_code', '=', 'LIQAL'], ['enable', '=', 1],
            ])->first();
            if ($res !== null) {
                $template = $res->template;
                $subject = $res->subject;
                $param_s = ['subject' => $res->subject, 'investor_name' => $this->details['investor_name']];
                $subject = $this->templateShortCode($param_s);
                $param_t = ['template' => $template, 'investor_name' => $this->details['investor_name'], 'amount' => $this->details['amount']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[action_link]', url('/admin/investor-transaction-log'), $template);

                return $this->from('no-reply@vgusa.com')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            }
            //else {
            //     return $this->from('no-reply@vgusa.com')
            //     ->view('emails.liquidty_alert')
            //     ->subject($this->details['title'])
            //     ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status']]);
            // }
        } elseif ($this->details['status'] == 'merchant_note') {
            $template = Template::where([
                ['temp_code', '=', 'NOTES'], ['enable', '=', 1],
            ])->first();
            if ($template !== null) {
                $param_s = ['subject' => $template->subject, 'merchant_name' => $this->details['merchant_name'], 'note' => $this->details['note'], 'author' => $this->details['author'], 'date_time' => $this->details['date_time']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name'], 'note' => $this->details['note'], 'author' => $this->details['author'], 'date_time' => $this->details['date_time']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from('no-reply@vgusa.com')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            }
            // else {
            //     return $this->from('no-reply@vgusa.com')
            //         ->view('emails.merchant_note')
            //         ->subject($this->details['title'])
            //         ->with(['title' => $this->details['title'], 'note' => $this->details['note'], 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id'], 'merchant_name' => $this->details['merchant_name'], 'author' => $this->details['author']]);
            // }
        } elseif ($this->details['status'] == 'pdf_mail') {
            // Generate PDF For investors
            if ($this->details['template_type'] == 'pdf_recurrence') {
                $template = Template::where([
                    ['temp_code', '=', 'GRPDF'], ['enable', '=', 1],
                ])->first();
                if ($template !== null) {
                    if ($template->subject && strpos($template->subject, '[subject]') !== false) {
                        $template->subject = $this->details['subject'];
                    }
                    $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor_name'], 'heading' => $this->details['heading'], 'options' => $this->details['options']];
                    $subject = $this->templateShortCode($param_s);
                    $template = $template->template;
                    $param_t = ['template' => $template, 'investor_name' => $this->details['investor_name'], 'heading' => $this->details['heading'], 'options' => $this->details['options'], 'recurrence_type' => $this->details['recurrence_type']];
                    $template = $this->templateShortCode($param_t);

                    return $this->from('no-reply@vgusa.com')
                        ->view('emails.request_mail')->subject($subject)
                        ->withSwiftMessage(function ($message) {
                            $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                        })
                        ->attachFromStorageDisk('s3', $this->details['fileName'])
                        ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
                }
            } else {
                $template = Template::where([
                    ['temp_code', '=', 'GPDF'], ['enable', '=', 1],
                ])->first();
                if ($template !== null) {
                    if ($template->subject && strpos($template->subject, '[subject]') !== false) {
                        $template->subject = $this->details['subject'];
                    }
                    $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor_name'], 'heading' => $this->details['heading'], 'options' => $this->details['options']];
                    $subject = $this->templateShortCode($param_s);
                    $template = $template->template;
                    $param_t = ['template' => $template, 'investor_name' => $this->details['investor_name'], 'heading' => $this->details['heading'], 'options' => $this->details['options']];
                    $template = $this->templateShortCode($param_t);

                    return $this->from('no-reply@vgusa.com')
                        ->view('emails.request_mail')->subject($subject)
                        ->withSwiftMessage(function ($message) {
                            $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                        })
                        ->attachFromStorageDisk('s3', $this->details['fileName'])
                        ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
                }
            }
            // else {
            //     return $this->from('no-reply@vgusa.com')
            //         ->view('emails.statement_report')
            //         ->subject($this->details['subject'])
            //         ->attach($this->details['attach'])
            //         ->withSwiftMessage(function ($message) {
            //             $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //         })
            //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status'], 'investor_name' => $this->details['investor_name'], 'heading' => $this->details['heading']]);
            // }
        } elseif ($this->details['status'] == 'roll_mail') {
            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.roll_ins_payments')
                ->subject($this->details['subject'])
                ->attach($this->details['attach'])
                ->with([
                    'title' => $this->details['title'], 'content' => $this->details['content'],
                   'status' => $this->details['status'],
                ]);
        } elseif ($this->details['status'] == 'funding_request') {
            // payment generation queue job section  , fund request  fund request email

            $template = Template::where([
                ['temp_code', '=', 'FUNDR'], ['enable', '=', 1],
            ])->first();
            if ($template !== null) {
                $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor'], 'merchant_name' => $this->details['merchant_name'], 'amount' => $this->details['amount']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'content' => $this->details['content'], 'investor_name' => $this->details['investor'], 'merchant_name' => $this->details['merchant_name'], 'amount' => $this->details['amount']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);
                $template = str_replace('[investor_view_link]', \URL::to('admin/investors/portfolio/'.$this->details['user_id']), $template);

                return $this->from('no-reply@vgusa.com')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            }
            // else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.funding_request')
            //         ->subject($this->details['subject'])
            //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id']]);
            // }
        } elseif ($this->details['status'] == 'funding_request_details') {
            $template = Template::where([
                ['temp_code', '=', 'FREDT'], ['enable', '=', 1],
            ])->first();
            if ($template !== null) {
                if ($template->subject && strpos($template->subject, '[subject]') !== false) {
                    $template->subject = $this->details['subject'];
                }
                $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor'], 'merchant_name' => $this->details['merchant_name'], 'amount' => $this->details['amount'], 'document_url' => $this->details['document_url']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'content' => $this->details['content'], 'investor_name' => $this->details['investor'], 'merchant_name' => $this->details['merchant_name'], 'amount' => $this->details['amount'], 'document_url' => $this->details['document_url']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from('no-reply@vgusa.com')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            }
            //else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.funding_request_data')
            //     ->subject($this->details['subject'])
            //     ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id'], 'doc_url'=>$this->details['document_url'], 'investor_name'=>$this->details['investor']]);
            // }
        } elseif ($this->details['status'] == '100_percent_syndicated') {
            $template = Template::where([
                ['temp_code', '=', 'MPSYF'], ['enable', '=', 1],
            ])->first();
            if ($template !== null) {
                if ($template->subject && strpos($template->subject, '[subject]') !== false) {
                    $template->subject = $this->details['subject'];
                }
                $param_s = ['subject' => $template->subject, 'merchant_name' => $this->details['merchant_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'content' => $this->details['content'], 'merchant_name' => $this->details['merchant_name']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['template' => $template, 'data' => true]);
            }
            // else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.100_percent_syndicated')
            //     ->subject($this->details['subject'])
            //     ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id']]);
            // }
        } elseif ($this->details['status'] == 'all_pending_payment') {
            // payment generation queue job section  pending payment all merchants auto

            $res = Template::where([
                ['temp_code', '=', 'PENDL'], ['enable', '=', 1],
            ])->first();
            // if ($res !== null) {
            $template = $res->template;
            $subject = $res->subject;

            $param_s = ['subject' => $res->subject];
            $subject = $this->templateShortCode($param_s);
            $param_t = ['template' => $template, 'pending_payment_table' => $this->details['pending_payment_table']];
            $template = $this->templateShortCode($param_t);
            // $template = str_replace('[merchant_view_link]',\URL::to('admin/merchants/view',$this->details['merchant_id']),$template);
            return $this->from('no-reply@vgusa.com')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.all_pending_payment')
            //         ->subject($this->details['subject'])
            //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status']]);
            // }
        } elseif ($this->details['status'] == 'deals_on_pause') {
            $res = Template::where([
                ['temp_code', '=', 'DONP'], ['enable', '=', 1],
            ])->first();
            // if ($res !== null) {
            $template = $res->template;
            $subject = $res->subject;

            $param_s = ['subject' => $res->subject];
            $subject = $this->templateShortCode($param_s);
            $param_t = ['template' => $template, 'content' => $this->details['content']];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //             ->view('emails.deals_on_pause')
            //             ->subject($this->details['subject'])
            //             ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status']]);
            // }
        } elseif ($this->details['status'] == 'payment_mail') {
            // payment generation queue job section  payment %
            if ($this->details['complete_per'] < 99) {
                $res = Template::where([
                    ['temp_code', '=', 'PAYC'], ['enable', '=', 1],
                ])->first();
                // if ($res !== null) {
                $template = $res->template;
                $subject = $res->subject;
                $percentage = round($this->details['complete_per'], 2);
                $param_s = ['subject' => $res->subject, 'merchant_name' => $this->details['merchant_name'], 'percentage' => $percentage];
                $subject = $this->templateShortCode($param_s);
                $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name'], 'percentage' => $percentage];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                        ->view('emails.request_mail')->subject($subject)
                        ->withSwiftMessage(function ($message) {
                            $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                        })
                        ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            // } else {
                //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
                //         ->view('emails.complete_per')
                //         ->subject(round($this->details['complete_per'], 2).' % completed by '.$this->details['merchant_name'])
                //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'merchant_name' => $this->details['merchant_name'], 'complete_per' => $this->details['complete_per'], 'merchant_id' => $this->details['merchant_id'], 'status' => $this->details['status']]);
                // }
            } else {
                $res = Template::where([
                    ['temp_code', '=', 'PAYCO'], ['enable', '=', 1],
                ])->first();
                // if ($res !== null) {
                $template = $res->template;
                $subject = $res->subject;
                $percentage = round($this->details['complete_per'], 2);
                $param_s = ['subject' => $res->subject, 'merchant_name' => $this->details['merchant_name'], 'percentage' => $percentage];
                $subject = $this->templateShortCode($param_s);
                $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name'], 'percentage' => $percentage];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                        ->view('emails.request_mail')->subject($subject)
                        ->withSwiftMessage(function ($message) {
                            $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                        })
                        ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
                // } else {
                //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
                //         ->view('emails.complete_per')
                //         ->subject(round($this->details['complete_per'], 2).' % completed by '.$this->details['merchant_name'])
                //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'merchant_name' => $this->details['merchant_name'], 'complete_per' => $this->details['complete_per'], 'merchant_id' => $this->details['merchant_id'], 'status' => $this->details['status']]);
                // }
            }
        } elseif ($this->details['status'] == 'pending_payment') {
            // Pending Payment Details  auto individual

            $res = Template::where([
                ['temp_code', '=', 'PENDP'], ['enable', '=', 1],
            ])->first();
            // if ($res !== null) {
            $template = $res->template;
            $subject = $res->subject;
            $days = isset($this->details['days']) ? $this->details['days'] : ' few ';
            $param_s = ['subject' => $res->subject, 'merchant_name' => $this->details['merchant_name'], 'days' => $days, 'date' => $this->details['date']];
            $subject = $this->templateShortCode($param_s);
            $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name'], 'days' => $days, 'date' => $this->details['date']];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     $days = isset($this->details['days']) ? $this->details['days'] : ' few ';

            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.pending_payment')
            //         ->subject($this->details['subject'])
            //         ->with([
            //             'title' => $this->details['title'], 'content' => $this->details['content'], 'merchant_name' => $this->details['merchant_name'],
            //             'merchant_id' => $this->details['merchant_id'], 'status' => $this->details['status'], 'days' => $days,
            //         ]);
            // }
        } elseif ($this->details['status'] == 'merchant') {
            // send to merchant mail while creating a merchant - merchant details
            $res = Template::where([
                ['temp_code', '=', 'MERD'], ['enable', '=', 1],
            ])->first();
            // if ($res !== null) {
            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $res->subject, 'creator' => $this->details['creator'], 'merchant_name' => $this->details['merchant_name'], 'merchant_details' => $this->details['merchant_details']];
            $subject = $this->templateShortCode($param_s);
            $param_t = ['template' => $template, 'creator' => $this->details['creator'], 'merchant_name' => $this->details['merchant_name'], 'merchant_details' => $this->details['merchant_details']];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.merchant_create')
            //         ->subject($this->details['subject'])
            //         ->with([
            //             'title' => $this->details['title'], 'content' => $this->details['content'], 'merchant_name' => $this->details['merchant_name'],
            //             'merchant_id' => $this->details['merchant_id'], 'status' => $this->details['status'], 'company_amounts' => $this->details['company_amounts'], 'creator' => $this->details['creator'],
            //         ]);
            // }
        } elseif ($this->details['status'] == 'merchant_login_1') {
            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.reset_password_1')
                    ->subject($this->details['subject'])
                    ->with([
                        'title' => $this->details['title'], 'merchant_name' => $this->details['merchant_name'],
                        'merchant_id' => $this->details['merchant_id'], 'status' => $this->details['status'], 'actionUrl'=>$this->details['actionUrl'],
                    ]);
        } elseif ($this->details['status'] == 'merchant_login') {
            // send to merchant mail while creating a merchant - login credentials
            $template = Template::where([
                ['temp_code', '=', 'MERC'], ['enable', '=', 1],
            ])->first();
            // if ($template !== null) {
            $param_s = ['subject' => $template->subject, 'merchant_name' => $this->details['merchant_name'], 'username' => $this->details['username']];
            $subject = $this->templateShortCode($param_s);
            $template = $template->template;
            $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name'], 'username' => $this->details['username'], 'password' => $this->details['password']];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[login_view]', \URL::to('merchants'), $template);
            $template = str_replace('[android_view]', \URL::to('coming_soon'), $template);
            $template = str_replace('[ios_view]', \URL::to('coming_soon'), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.merchant_login_data')
            //         ->subject($this->details['subject'])
            //         ->with([
            //             'title' => $this->details['title'], 'content' => $this->details['content'], 'merchant_name' => $this->details['merchant_name'],
            //             'merchant_id' => $this->details['merchant_id'], 'status' => $this->details['status'], 'username' => $this->details['username'], 'password' => $this->details['password'],
            //         ]);
            // }
        } elseif ($this->details['status'] == 'company') {
            // Creating Company
            $res = Template::where([
                ['temp_code', '=', 'COMPC'], ['enable', '=', 1],
            ])->first();
            // if ($res !== null) {
            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $res->subject, 'company_name' => $this->details['company']];
            $subject = $this->templateShortCode($param_s);
            $param_t = ['template' => $template, 'company_name' => $this->details['company']];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.company_create')
            //         ->subject($this->details['subject'])
            //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'status' => $this->details['status']]);
            // }
        } elseif ($this->details['status'] == 'payment_crm_issue') {

            // merchant api section mail
            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.template')
                ->subject($this->details['subject'])
                ->with([
                    'title' => $this->details['title'], 'content' => $this->details['content'], 'merchant_name' => $this->details['merchant_name'],
                    'merchant_id' => $this->details['merchant_id'], 'status' => $this->details['status'],
                ]);
        } elseif ($this->details['status'] == 'merchant_api') {
            if ($this->details['template_type'] == 'request_money') {
                $template = Template::where([
                    ['temp_code', '=', 'REQMM'], ['enable', '=', 1],
                ])->first();
                $param_s = ['subject' => $template->subject, 'merchant_name' => $this->details['merchant_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name'], 'amount' => $this->details['amount']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from($this->details['from_mail'])
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            } elseif ($this->details['template_type'] == 'request_payoff') {
                $template = Template::where([
                    ['temp_code', '=', 'REPOF'], ['enable', '=', 1],
                ])->first();
                $param_s = ['subject' => $template->subject, 'merchant_name' => $this->details['merchant_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from($this->details['from_mail'])
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            } elseif ($this->details['template_type'] == 'merchant_update') {
                $template = Template::where([
                    ['temp_code', '=', 'CRMMU'], ['enable', '=', 1],
                ])->first();
                $param_s = ['subject' => $template->subject, 'merchant_name' => $this->details['merchant_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from($this->details['from_mail'])
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            } elseif ($this->details['template_type'] == 'merchant_create') {
                $template = Template::where([
                    ['temp_code', '=', 'CRMMC'], ['enable', '=', 1],
                ])->first();
                $param_s = ['subject' => $template->subject, 'merchant_name' => $this->details['merchant_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from($this->details['from_mail'])
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            }
            // merchant api section mail
            // return $this->from($this->details['from_mail'])
            //     ->view('emails.template')
            //     ->subject($this->details['subject'])
            //     ->with([
            //         'title' => $this->details['title'], 'content' => $this->details['content'], 'merchant_name' => $this->details['merchant_name'],
            //         'merchant_id' => $this->details['merchant_id'], 'status' => $this->details['status'],
            //     ]);
        } elseif ($this->details['status'] == 'investor_api') {
            // merchant api section mail
            if ($this->details['template_type'] == 'investor_create') {
                $template = Template::where([
                    ['temp_code', '=', 'CRMIC'], ['enable', '=', 1],
                ])->first();
                $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'investor_name' => $this->details['investor_name']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[investor_view_link]', url('admin/investors/portfolio/'.$this->details['investor_id']), $template);

                return $this->from($this->details['from_mail'])
                    ->view('emails.request_mail')->subject($subject)
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            } elseif ($this->details['template_type'] == 'investor_update') {
                $template = Template::where([
                    ['temp_code', '=', 'CRMIU'], ['enable', '=', 1],
                ])->first();
                $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'investor_name' => $this->details['investor_name']];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[investor_view_link]', url('admin/investors/portfolio/'.$this->details['investor_id']), $template);

                return $this->from($this->details['from_mail'])
                    ->view('emails.request_mail')->subject($subject)
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            }
            // return $this->from($this->details['from_mail'])
            //     ->view('emails.template')
            //     ->subject($this->details['subject'])
            //     ->with([
            //         'title' => $this->details['title'], 'content' => $this->details['content'], 'investor_name' => $this->details['investor_name'],
            //         'investor_id' => $this->details['investor_id'], 'status' => $this->details['status'],
            //     ]);
        } elseif ($this->details['status'] == 'funding_investor_contact') {
            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                        ->view('emails.funding_investor_contact')
                        ->subject($this->details['subject'])
                        ->with(['title' => $this->details['title'], 'username' => $this->details['username'], 'content' => $this->details['content'], 'email' => $this->details['email'], 'phone' => $this->details['phone'], 'name' => $this->details['name'], 'message' => $this->details['message'], 'company' => $this->details['company']]);
        } elseif ($this->details['status'] == 'funding_investor_signup') {
            if ($this->details['template_type'] == 'admin') {
                $template = Template::where([
                    ['temp_code', '=', 'INSUA'], ['enable', '=', 1],
                ])->first();
                $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'investor_name' => $this->details['investor_name'], 'email' => $this->details['email'], 'phone' => $this->details['phone'], 'date_time' => $this->details['date_time']];
                $template = $this->templateShortCode($param_t);

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            } else {
                $template = Template::where([
                    ['temp_code', '=', 'INSUO'], ['enable', '=', 1],
                ])->first();
                $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor_name']];
                $subject = $this->templateShortCode($param_s);
                $template = $template->template;
                $param_t = ['template' => $template, 'investor_name' => $this->details['investor_name'], 'email' => $this->details['email'], 'phone' => $this->details['phone'], 'date_time' => $this->details['date_time']];
                $template = $this->templateShortCode($param_t);

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            }
            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //             ->view('emails.funding_investor_signup')
            //             ->subject($this->details['subject'])
            //             ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'email' => $this->details['email'], 'phone' => $this->details['phone'], 'name' => $this->details['name']]);
        } elseif ($this->details['status'] == 'common') {
            // debugging purpose
            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.common_mail')
                ->subject($this->details['subject'])
                ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'name' => $this->details['name']]);
        } elseif ($this->details['status'] == 'investor') {
            // investor updation email
            $template = Template::where([
                ['temp_code', '=', 'INVTR'], ['enable', '=', 1],
            ])->first();
            // if ($template !== null) {
            $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor_name'], 'username' => $this->details['username']];
            $subject = $this->templateShortCode($param_s);
            $template = $template->template;
            $param_t = ['template' => $template, 'investor_name' => $this->details['investor_name'], 'username' => $this->details['username'], 'password' => $this->details['password']];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[login_view]', \URL::to('investors'), $template);
            $template = str_replace('[android_view]', \URL::to('/coming_soon'), $template);
            $template = str_replace('[ios_view]', \URL::to('coming_soon'), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.investor')
            //         ->subject($this->details['subject'])
            //         ->with(['title' => $this->details['title'], 'content' => $this->details['content'], 'investor_name' => $this->details['investor_name'], 'status' => $this->details['status'], 'username' => $this->details['username'], 'password' => $this->details['password']]);
            // }
        } elseif ($this->details['status'] == 'account') {
            // accounts updation email
            $template = Template::where([['temp_code', '=', 'INVTR'], ['enable', '=', 1]])->first();
            // if ($template !== null) {
            $param_s = [
                  'subject'       => $template->subject,
                  'investor_name' => $this->details['account_name'],
                  'username'      => $this->details['username'],
                ];
            $subject = $this->templateShortCode($param_s);
            $template = $template->template;
            $param_t = [
                  'template'      => $template,
                  'investor_name' => $this->details['account_name'],
                  'username'      => $this->details['username'],
                  'password'      => $this->details['password'],
                ];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[login_view]', \URL::to('investors'), $template);
            $template = str_replace('[android_view]', \URL::to('/coming_soon'), $template);
            $template = str_replace('[ios_view]', \URL::to('coming_soon'), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.account')
            //     ->subject($this->details['subject'])
            //     ->with([
            //       'title'        => $this->details['title'],
            //       'content'      => $this->details['content'],
            //       'account_name' => $this->details['account_name'],
            //       'status'       => $this->details['status'],
            //       'username'     => $this->details['username'],
            //       'password'     => $this->details['password'],
            //     ]);
            // }
        } elseif ($this->details['status'] == 'funding_approval') {
            $template = Template::where([
                ['temp_code', '=', 'FREQA'], ['enable', '=', 1],
            ])->first();
            // if ($template !== null) {
            $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor']];
            $subject = $this->templateShortCode($param_s);
            $template = $template->template;
            $param_t = ['template' => $template, 'investor_name' => $this->details['investor']];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['template' => $template, 'merchant_id' => $this->details['merchant_id'], 'data' => true]);
        // } else {
            //     $content = $this->details['content'];
            //     $subject = $this->details['subject'];

            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.merchant_fund_static')
            //         ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //         ->with(['title' => $this->details['title'], 'investor' => $this->details['investor'], 'content' => $content, 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id']]);
            // }
        } elseif ($this->details['status'] == 'funding_reject') {
            $template = Template::where([
                ['temp_code', '=', 'FREQR'], ['enable', '=', 1],
            ])->first();
            // if ($template !== null) {
            $param_s = ['subject' => $template->subject, 'investor_name' => $this->details['investor'], 'merchant_name' => $this->details['merchant_name']];
            $subject = $this->templateShortCode($param_s);
            $template = $template->template;
            $param_t = ['template' => $template, 'investor_name' => $this->details['investor'], 'merchant_name' => $this->details['merchant_name']];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[merchant_view_link]', \URL::to('investors/merchants', $this->details['merchant_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['template' => $template, 'merchant_id' => $this->details['merchant_id'], 'data' => true]);
        // } else {
            //     $content = $this->details['content'];
            //     $subject = $this->details['subject'];

            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.merchant_fund_static')
            //         ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //         ->with(['title' => $this->details['title'], 'investor' => $this->details['investor'], 'content' => $content, 'status' => $this->details['status'], 'merchant_id' => $this->details['merchant_id']]);
            // }
        } elseif ($this->details['status'] == 'admin_note') {
            $content = $this->details['content'];
            $subject = $this->details['subject'];

            return $this->from('no-reply@vgusa.com', 'Maturity date based')
                ->view('emails.admin_note')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'funding request_push_note') {
            $content = $this->details['content'];
            $subject = $this->details['subject'];
            // $template = Template::where([
            //     ['temp_code', '=', 'FAPN'], ['enable', '=', 1],
            // ])->first();
            // // if ($template !== null) {
            // $param_s = [
            //     'subject'  => $template->subject
            // ];
            // $subject = $this->templateShortCode($param_s);
            // $template = $template->template;
            // $param_t = [
            //     'template' => $template,
            //     'content'  => $content,
            // ];
            // $template = $this->templateShortCode($param_t);

            // return $this->from('no-reply@vgusa.com')
            //     ->view('emails.request_mail')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            // // }
            return $this->from('no-reply@vgusa.com', 'Maturity date based')
                ->view('emails.admin_note')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'merchant_db_auto_test') {
            $content = $this->details['content'];
            $subject = $this->details['subject'];

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.merchant_db_auto_test')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'delete_old_logs') {
            $content = $this->details['content'];
            $subject = $this->details['subject'];

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.delete_old_logs')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == '30day merchant mail notification') {
            $merchant_name = $this->details['merchant_name'];
            $subject = $this->details['subject'];
            $merchant_id = $this->details['to_id'];
            $days = $this->details['days'];
            $res = Template::where([
                ['temp_code', '=', 'RECR'], ['enable', '=', 1],
            ])->first();
            // if ($res !== null) {
            $arr1['merchant_id'] = $arr2['merchant_id'] = $merchant_id;
            $arr1['day'] = $arr2['day'] = $days;
            $arr1['status'] = 1;
            $arr2['status'] = 0;
            $ser_arr1 = urlencode(serialize($arr1));
            $ser_arr2 = urlencode(serialize($arr2));

            $template = $res->template;
            $subject = $res->subject;
            $param_t = ['template' => $template, 'merchant_name' => $merchant_name];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[yes_link]', \URL::to('reconciliation-status', $ser_arr1), $template);
            $template = str_replace('[no_link]', \URL::to('reconciliation-status', $ser_arr2), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // } else {
            //     return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.merchant_active_mail')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with(['title' => $this->details['title'], 'days'=>$days, 'merchant_name' => $merchant_name, 'merchant_id'=>$merchant_id, 'status' => $this->details['status']]);
            // }
        } elseif ($this->details['status'] == 'reconcilation request mail to admin') {
            $merchant_name = $this->details['merchant_name'];
            $subject = $this->details['subject'];
            $merchant_id = $this->details['merchant_id'];

            $res = Template::where([
                ['temp_code', '=', 'RERQA'], ['enable', '=', 1],
            ])->first();
            // if ($res !== null) {
            $template = $res->template;
            $subject = $res->subject;
            $param_t = ['template' => $template, 'merchant_name' => $this->details['merchant_name']];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.reconciliation_mail_to_admin')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with(['title' => $this->details['title'], 'merchant_name' => $merchant_name, 'merchant_id' => $merchant_id]);
        } elseif ($this->details['status'] == 'ach_status_check') {
            $res = Template::where([
                ['temp_code', '=', 'MACHC'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            if ($res->subject && strpos($res->subject, '[title]') !== false) {
                $res->subject = $this->details['title'];
            }
            $subject = $res->subject;
            $param_s = ['subject' => $subject];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'              =>  $template,
                'title'                 =>  $this->details['title'],
                'count_total'           =>  $this->details['count_total'],
                'count_payment'         =>  $this->details['count_payment'],
                'count_fee'             =>  $this->details['count_fee'],
                'checked_time'          =>  $this->details['checked_time'],
                'total_settled'         =>  $this->details['total_settled'],
                'total_settled_payment' =>  $this->details['total_settled_payment'],
                'total_settled_fee'     =>  $this->details['total_settled_fee'],
                'total_rcode'           =>  $this->details['total_rcode'],
                'total_rcode_amount'    =>  $this->details['total_rcode_amount'],
                'total_rcode_fee'       =>  $this->details['total_rcode_fee'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->attach(
                    \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
                ->with(['title' => $this->details['title'], 'template'  =>  $template, 'data' => $this->details['content']]);

        // $content = $this->details['content'];
            // $subject = $this->details['subject'];

            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.ach_status_check')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->attach(
            //         \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
            //     ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status'], 'params' => $this->details['params'], 'checked_time' => $this->details['checked_time']]);
        } elseif ($this->details['status'] == 'ach_rcode_mail') {
            $res = Template::where([
                ['temp_code', '=', 'RCOML'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            if ($res->subject && strpos($res->subject, '[title]') !== false) {
                $res->subject = $this->details['title'];
            }
            $subject = $res->subject;
            $param_s = ['subject' => $subject];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'  =>  $template,
                'title'     =>  $this->details['title'],
                'rcode_report_table'   =>  $this->details['rcode_report_table'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->attach(
                    \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
                ->with(['title' => $this->details['title'], 'template' => $template, 'data' => $this->details['rcode_report_table']]);

        // $content = $this->details['content'];
            // $subject = $this->details['subject'];

            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.ach_rcode_mail')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->attach(
            //         \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
            //     ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'ach_sent_report') {
            $res = Template::where([
                ['temp_code', '=', 'MACHR'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'payment_date' => $this->details['payment_date']];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'                  =>  $template,
                'title'                     =>  $this->details['title'],
                'count_total'               =>  $this->details['count_total'],
                'count_payment'             =>  $this->details['count_payment'],
                'count_fee'                 =>  $this->details['count_fee'],
                'payment_date'              =>  $this->details['payment_date'],
                'checked_time'              =>  $this->details['checked_time'],
                'count_total_processing'    =>  $this->details['count_total_processing'],
                'count_payment_processing'  =>  $this->details['count_payment_processing'],
                'count_fee_processing'      =>  $this->details['count_fee_processing'],
                'total_processed'           =>  $this->details['total_processed'],
                'total_processed_payment'   =>  $this->details['total_processed_payment'],
                'total_processed_fee'       =>  $this->details['total_processed_fee'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
            ->view('emails.request_mail')
            ->subject($subject)
            ->withSwiftMessage(function ($message) {
                $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            })
            ->attach(
                \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
            ->with(['title' => $this->details['title'], 'template' => $template, 'data' => $this->details['content']]);

        // $content = $this->details['content'];
            // $subject = $this->details['subject'];

            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.ach_sent_report')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->attach(
            //         \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
            //     ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status'], 'payment_date' => $this->details['payment_date'], 'params' => $this->details['params'], 'checked_time' => $this->details['checked_time']]);
        } elseif ($this->details['status'] == 'ach_merchant_credit_request') {
            $res = Template::where([
                ['temp_code', '=', 'MACC'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'              =>  $template,
                'creator_name'          =>  $this->details['creator_name'],
                'merchant_name'         =>  $this->details['merchant_name'],
                'checked_time'          =>  $this->details['checked_time'],
                'payment_amount'        =>  $this->details['payment_amount'],
                'merchant_view_link'    => $this->details['merchant_view_link'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with([
                        'title' => $this->details['title'],
                        'template' => $template,
                        'data'  =>  true,
                    ]);
        // $content = $this->details['content'];
            // $subject = $this->details['subject'];

            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.ach_merchant_credit_request')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with([
            //         'title' => $this->details['title'],
            //         'content' => $content,
            //         'status' => $this->details['status'],
            //     ]);
        } elseif ($this->details['status'] == 'ach_syndication_sent_report') {
            $content = $this->details['content'];
            $subject = $this->details['subject'];

            $res = Template::where([
                ['temp_code', '=', 'ACHSR'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'payment_date' => $this->details['payment_date']];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'                  =>  $template,
                'title'                     =>  $this->details['title'],
                'count_total'               =>  $this->details['count_total'],
                'payment_date'              =>  $this->details['payment_date'],
                'checked_time'              =>  $this->details['checked_time'],
                'count_total_processing'    =>  $this->details['count_total_processing'],
                'total_processed'           =>  $this->details['total_processed'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->attach(
                    \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
                ->with(['title' => $this->details['title'], 'template' => $template, 'data' => $this->details['content']]);

        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.ach_syndication_sent_report')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->attach(
            //         \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
            //     ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status'], 'payment_date' => $this->details['payment_date'], 'params' => $this->details['params'], 'checked_time' => $this->details['checked_time']]);
        } elseif ($this->details['status'] == 'payment_paused') {
            $content = $this->details['content'];
            $subject = $this->details['subject'];

            $res = Template::where([
                ['temp_code', '=', 'PYPS'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'      =>  $template,
                'title'         =>  $this->details['title'],
                'merchant_name' =>  $this->details['merchant_name'],
                'paused_type'   =>  $this->details['paused_type'],
                'paused_by'     =>  $this->details['paused_by'],
                'paused_at'     =>  $this->details['paused_at'],
            ];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[merchant_view_link]', url('admin/merchants/view', $this->details['merchant_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with(['title' => $this->details['title'], 'template' => $template, 'data' => $this->details['content']]);
        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.payment_paused')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'payment_resumed') {
            $content = $this->details['content'];
            $subject = $this->details['subject'];

            $res = Template::where([
                ['temp_code', '=', 'PYRS'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'      =>  $template,
                'title'         =>  $this->details['title'],
                'merchant_name' =>  $this->details['merchant_name'],
                'resumed_by'    =>  $this->details['resumed_by'],
                'resumed_at'    =>  $this->details['resumed_at'],
            ];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[merchant_view_link]', url('admin/merchants/view', $this->details['merchant_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.payment_resumed')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'merchant_returnd') {
            $subject = $this->details['subject'];

            $res = Template::where([
                ['temp_code', '=', 'MRTD'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $merchant_name = isset($this->details['merchant_name']) ? $this->details['merchant_name'] : ' ';
            $param_s = ['subject' => $subject];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'      =>  $template,
                'title'         =>  $this->details['title'],
                'merchant_name' =>  $merchant_name,
            ];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[merchant_view_link]', url('admin/merchants/view', $this->details['merchant_id']), $template);
            $hashids = new Hashids();
            $merchant_id = $hashids->encode($this->details['merchant_id']);
            $url = url("pm/$merchant_id/make-payment", $this->details['amount']);
            $template = str_replace('[payment_link]', $url, $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with([
                       'title'      =>  $this->details['title'],
                       'template'   =>  $template,
                       'data'       =>  true,
                   ]);
        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.merchant_returnd')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with([
            //                'title'         => $this->details['title'],
            //                'merchant_id'   => $this->details['merchant_id'],
            //                'amount'        => $this->details['amount'],
            //                'merchant_name' => isset($this->details['merchant_name']) ? $this->details['merchant_name'] : ' ',
            //            ]);
        } elseif ($this->details['status'] == 'payment_send') {
            $subject = $this->details['subject'];
            if ($this->details['mail_to'] != 'admin') {
                $res = Template::where([
                    ['temp_code', '=', 'PYMNT'], ['enable', '=', 1],
                ])->first();

                $template = $res->template;
                $subject = $res->subject;
                $param_s = ['subject' => $subject];
                $subject = $this->templateShortCode($param_s);
                $param_t = [
                    'template'      =>  $template,
                    'title'         =>  $this->details['title'],
                    'content'       =>  $this->details['content'],
                    'merchant_name' =>  $this->details['merchant_name'],
                    'amount'        =>  $this->details['amount'],
                    'date'          =>  $this->details['date'],
                    'card_number'   =>  $this->details['card_number'],
                ];
                $template = $this->templateShortCode($param_t);

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with([
                            'title'     =>  $this->details['title'],
                            'template'  =>  $template,
                            'data'      =>  true,
                    ]);
            } else {
                $res = Template::where([
                    ['temp_code', '=', 'PYMNA'], ['enable', '=', 1],
                ])->first();

                $template = $res->template;
                $subject = $res->subject;
                $param_s = ['subject' => $subject];
                $subject = $this->templateShortCode($param_s);
                $param_t = [
                    'template'      =>  $template,
                    'title'         =>  $this->details['title'],
                    'merchant_name' =>  $this->details['merchant_name'],
                    'amount'        =>  $this->details['amount'],
                    'date'          =>  $this->details['date'],
                    'card_number'   =>  $this->details['card_number'],
                ];
                $template = $this->templateShortCode($param_t);
                $template = str_replace('[merchant_view_link]', \URL::to('admin/merchants/view', $this->details['merchant_id']), $template);

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with([
                            'title'     =>  $this->details['title'],
                            'template'  =>  $template,
                            'data'      =>  true,
                    ]);
                // return $this->from('no-reply@vgusa.com', 'Velocity Group')
                // ->view('emails.payment_send')
                // ->subject($subject)
                // ->withSwiftMessage(function ($message) {
                //     $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                // })
                // ->with([
                //            'title'         => $this->details['title'],
                //            'amount'        => $this->details['amount'],
                //            'date'          => $this->details['date'],
                //            'merchant_id'   => $this->details['merchant_id'],
                //            'merchant_name' => $this->details['merchant_name'],
                //            'card_number'   => $this->details['card_number'],
                //            'wallet_amount' => $this->details['wallet_amount'],
                //            'actual_amount' => $this->details['actual_amount'],
                //            'mail_to'       => $this->details['mail_to'],

                // ]);
            }
        } elseif ($this->details['status'] == 'investor_ach_recheck_report') {
            $res = Template::where([
                ['temp_code', '=', 'IARR'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'date' => $this->details['date']];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'              =>  $template,
                'title'                 =>  $this->details['title'],
                'totalCount'            =>  $this->details['totalCount'],
                'date'                  =>  $this->details['date'],
                'checked_time'          =>  $this->details['checked_time'],
                'debitAcceptedAmount'   =>  $this->details['debitAcceptedAmount'],
                'creditAcceptedAmount'  =>  $this->details['creditAcceptedAmount'],
                'debitReturnedAmount'   =>  $this->details['debitReturnedAmount'],
                'creditReturnedAmount'  =>  $this->details['creditReturnedAmount'],

            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($this->details['subject'])
                ->attach(
                    \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(),
                    ['as' => $this->details['atatchment_name']]
                )
                ->with([
                    'title'     =>  $this->details['title'],
                    'template'  =>  $template,
                    'data'      =>  $this->details['totalCount'],
                ]);
        } elseif ($this->details['status'] == 'investor_ach_request_send_report') {
            $res = Template::where([
                ['temp_code', '=', 'IAPR'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'date' => $this->details['date']];
            $subject = $this->templateShortCode($param_s);
            $param_t = [
                'template'              =>  $template,
                'title'                 =>  $this->details['title'],
                'totalCount'            =>  $this->details['totalCount'],
                'date'                  =>  $this->details['date'],
                'checked_time'          =>  $this->details['checked_time'],
                'debitAcceptedAmount'   =>  $this->details['debitAcceptedAmount'],
                'creditAcceptedAmount'  =>  $this->details['creditAcceptedAmount'],
                'debitProcessingAmount' =>  $this->details['debitProcessingAmount'],
                'creditProcessingAmount'=>  $this->details['creditProcessingAmount'],
                'debitReturnedAmount'   =>  $this->details['debitReturnedAmount'],
                'creditReturnedAmount'  =>  $this->details['creditReturnedAmount'],

            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($this->details['subject'])
                ->attach(
                    \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(),
                    ['as' => $this->details['atatchment_name']]
                )
                ->with([
                    'title'     =>  $this->details['title'],
                    'template'  =>  $template,
                    'data'      =>  $this->details['totalCount'],
                ]);
        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.investor_ach_request_send_report')
            //     ->subject($this->details['subject'])
            //     ->attach(
            //         \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(),
            //         ['as' => $this->details['atatchment_name']]
            //     )
            //     ->with([
            //         'title'                 => $this->details['title'],
            //         'totalCount'            => $this->details['totalCount'],
            //         'date'                  => $this->details['date'],
            //         'checked_time'          => $this->details['checked_time'],
            //         'debitAcceptedAmount'   => $this->details['debitAcceptedAmount'],
            //         'creditAcceptedAmount'  => $this->details['creditAcceptedAmount'],
            //         'debitProcessingAmount' => $this->details['debitProcessingAmount'],
            //         'creditProcessingAmount'=> $this->details['creditProcessingAmount'],
            //         'debitReturnedAmount'=> $this->details['debitReturnedAmount'],
            //         'creditReturnedAmount'=> $this->details['creditReturnedAmount'],
            //     ]);
        } elseif ($this->details['status'] == 'investor_ach_request') {
            $res = Template::where([
                ['temp_code', '=', 'ACDR'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'type' => ucfirst($this->details['type'])];
            $subject = $this->templateShortCode($param_s);
            if ($this->details['Creator'] != 'Admin') {
                $url = url('admin/investors/portfolio/'.$this->details['investor_id']);
            } else {
                $url = url('investors/dashboard');
            }
            $InvestorLink = '<a target="_blank" href="'.$url.'">'.$this->details['Investor'].'</a>';
            $type = strtoupper(str_replace('_', ' ', $this->details['type']));
            $text_type = '';
            if (in_array($type, ['DEBIT', 'SAME DAY DEBIT'])) {
                $text_type = 'Transfer to Velocity';
            } elseif (in_array($type, ['CREDIT', 'SAME DAY CREDIT'])) {
                $text_type = 'Transfer to your Bank';
            }
            if ($this->details['Creator'] == 'Admin') {
                $this->details['creator_name'] = 'Admin '.$this->details['creator_name'];
            } else {
                $this->details['creator_name'] = 'Investor '.$InvestorLink;
            }
            $param_t = [
                'template'      =>  $template,
                'title'         =>  $this->details['title'],
                'type'          =>  ucfirst($this->details['type']),
                'Creator'       =>  $this->details['Creator'],
                'creator_name'  =>  $this->details['creator_name'],
                'text_type'     =>  $text_type,
                'amount'        =>  $this->details['amount'],
                'date'          =>  $this->details['date'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($this->details['subject'])
                ->with([
                    'title'     => $this->details['title'],
                    'template'  => $template,
                    'data'      => true,
                ]);
        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.investor_ach_request')
            //     ->subject($this->details['subject'])
            //     ->with([
            //         'title'         => $this->details['title'],
            //         'Investor'      => $this->details['Investor'],
            //         'investor_id'   => $this->details['investor_id'],
            //         'type'          => $this->details['type'],
            //         'amount'        => $this->details['amount'],
            //         'date'          => $this->details['date'],
            //         'to_mail'       => $this->details['to_mail'],
            //         'Creator'       => $this->details['Creator'],
            //         'creator_name'  => $this->details['creator_name'],
            //     ]);
        } elseif ($this->details['status'] == 'investor_ach_request_returned') {
            $res = Template::where([
                ['temp_code', '=', 'ACRR'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'type' => ucfirst($this->details['type'])];
            $subject = $this->templateShortCode($param_s);

            $param_t = [
                'template'      =>  $template,
                'title'         =>  $this->details['title'],
                'type'          =>  ucfirst($this->details['type']),
                'amount'        =>  $this->details['amount'],
                'date'          =>  $this->details['date'],
                'liquidity'     =>  $this->details['liquidity'],
                'investor_name' =>  $this->details['investor_name'],
            ];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[investor_view_link]', url('admin/investors/portfolio', $this->details['investor_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->with([
                        'title'     => $this->details['title'],
                        'template'  =>  $template,
                        'data'      =>  true,
                    ]);
        } elseif ($this->details['status'] == 'investor_ach_request_settlement') {
            $res = Template::where([
                ['temp_code', '=', 'ACSR'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'type' => ucfirst($this->details['type'])];
            $subject = $this->templateShortCode($param_s);

            $param_t = [
                'template'      =>  $template,
                'title'         =>  $this->details['title'],
                'type'          =>  ucfirst($this->details['type']),
                'amount'        =>  $this->details['amount'],
                'date'          =>  $this->details['date'],
                'liquidity'     =>  $this->details['liquidity'],
                'investor_name' =>  $this->details['investor_name'],
            ];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[investor_view_link]', url('admin/investors/portfolio', $this->details['investor_id']), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->with([
                        'title'     => $this->details['title'],
                        'template'  =>  $template,
                        'data'      =>  true,
                    ]);
        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.investor_ach_request_settlement')
            //     ->subject($this->details['subject'])
            //     ->with([
            //         'title'      => $this->details['title'],
            //         'Investor'   => $this->details['Investor'],
            //         'investor_id'=> $this->details['investor_id'],
            //         'type'       => $this->details['type'],
            //         'amount'     => $this->details['amount'],
            //         'date'       => $this->details['date'],
            //         'to_mail'    => $this->details['to_mail'],
            //         'liquidity'  => $this->details['liquidity'],
            //     ]);
        } elseif ($this->details['status'] == 'pending_ach_delete_mail') {
            $res = Template::where([
                ['temp_code', '=', 'ACDP'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'date' => $this->details['date']];
            $subject = $this->templateShortCode($param_s);

            $param_t = [
                'template'      =>  $template,
                'title'         =>  $this->details['title'],
                'totalCount'    =>  $this->details['totalCount'],
                'date'          =>  $this->details['date'],
                'confirm_url'   =>  $this->details['confirm_url'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->attach(
                        \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(),
                        ['as' => $this->details['atatchment_name']]
                    )
                    ->with([
                        'title'     => $this->details['title'],
                        'template'  =>  $template,
                        'data'      =>  true,
                    ]);

        // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //         ->view('emails.pending_ach_delete_mail')
            //         ->subject($this->details['subject'])
            //         ->attach(
            //             \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(),
            //             ['as' => $this->details['atatchment_name']]
            //         )
            //         ->with([
            //             'title'                 => $this->details['title'],
            //             'totalCount'            => $this->details['totalCount'],
            //             'date'                  => $this->details['date'],
            //             'checked_time'          => $this->details['checked_time'],
            //             'confirm_url'          => $this->details['confirm_url'],
            //         ]);
        } elseif ($this->details['status'] == 'merchant_unit_test') {
            if ($this->details['template_type'] == 'ach_difference') {
                $res = Template::where([
                    ['temp_code', '=', 'MACHD'], ['enable', '=', 1],
                ])->first();

                $template = $res->template;
                $subject = $res->subject;
                $param_s = ['subject' => $subject];
                $subject = $this->templateShortCode($param_s);

                $param_t = [
                    'template'  =>  $template,
                    'title'     =>  $this->details['title'],
                    'count'     =>  $this->details['count'],
                    'date_time' =>  $this->details['date_time'],
                ];
                $template = $this->templateShortCode($param_t);

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                        ->view('emails.request_mail')
                        ->subject($subject)
                        ->withSwiftMessage(function ($message) {
                            $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                        })
                        ->attach(
                            \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
                        ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
            } else {
                $content = $this->details['content'];
                $subject = $this->details['subject'];

                return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.merchant_unit_test_mail')
                    ->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->attach(
                        \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
                    ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
            }
            // $content = $this->details['content'];
            // $subject = $this->details['subject'];

            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            // ->view('emails.merchant_unit_test_mail')
            // ->subject($subject)
            // ->withSwiftMessage(function ($message) {
            //     $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            // })
            // ->attach(
            //     \Excel::download($this->details['atatchment'], $this->details['atatchment_name'])->getFile(), ['as' => $this->details['atatchment_name']])
            // ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'two_step_enabled_verification_notification') {
            $res = Template::where([
                ['temp_code', '=', 'TWFEN'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject];
            $subject = $this->templateShortCode($param_s);

            $param_t = [
                'template'  =>    $template,
                'email'     =>    $this->details['email'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                ->view('emails.request_mail')
                ->subject($subject)
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                })
                ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);

        // $subject = $this->details['subject'];

            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.enable_two_step_verification')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with(['title' => $this->details['title'], 'email'=>$this->details['email']]);
        } elseif ($this->details['status'] == 'two_step_disabled_verification_notification') {
            $res = Template::where([
                ['temp_code', '=', 'TWFD'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject];
            $subject = $this->templateShortCode($param_s);

            $param_t = [
                'template'  =>  $template,
                'email'     =>  $this->details['email'],
            ];
            $template = $this->templateShortCode($param_t);
            $template = str_replace('[action_link]', url('/admin/two-factor-authentication'), $template);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // $subject = $this->details['subject'];

            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            //     ->view('emails.disable_two_step_verification')
            //     ->subject($subject)
            //     ->withSwiftMessage(function ($message) {
            //         $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            //     })
            //     ->with(['title' => $this->details['title'], 'email'=>$this->details['email']]);
        } elseif ($this->details['status'] == 'ach_not_sent') {
            $content = $this->details['content'];
            $subject = $this->details['subject'];

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
            ->view('emails.ach_not_sent_mail')
            ->subject($subject)
            ->withSwiftMessage(function ($message) {
                $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            })
            ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'merchant_ach_payments_deficit') {
            $res = Template::where([
                ['temp_code', '=', 'ACHDF'], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'future_payments_count' => $this->details['future_payments_count']];
            $subject = $this->templateShortCode($param_s);

            $param_t = [
                'template'  =>  $template,
                'future_payments_count'     =>  $this->details['future_payments_count'],
                'merchant_name'             =>  $this->details['merchant_name'],
                'makeup_payments'           =>  $this->details['makeup_payments'],
                'default_payment_amount'    =>  $this->details['default_payment_amount'],
                'url'                       =>  $this->details['url'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['title' => $this->details['title'], 'template' => $template, 'data' => true]);
        // $content = $this->details['content'];
            // $subject = $this->details['subject'];

            // return $this->from('no-reply@vgusa.com', 'Velocity Group')
            // ->view('emails.merchant_ach_payments_deficit')
            // ->subject($subject)
            // ->withSwiftMessage(function ($message) {
            //     $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
            // })
            // ->with(['title' => $this->details['title'], 'content' => $content, 'status' => $this->details['status']]);
        } elseif ($this->details['status'] == 'marketing_offer') {
            $template_id = $this->details['template'];

            $res = Template::where([
                ['id', '=', $template_id], ['enable', '=', 1],
            ])->first();

            $template = $res->template;
            $subject = $res->subject;
            $param_s = ['subject' => $subject, 'name' => $this->details['name'], 'title' => $this->details['title']];
            $subject = $this->templateShortCode($param_s);

            $param_t = [
                'template'  =>  $template,
                'name'      =>  $this->details['name'],
                'offer'     =>  $this->details['offer'],
                'title'     =>  $this->details['title'],
            ];
            $template = $this->templateShortCode($param_t);

            return $this->from('no-reply@vgusa.com', 'Velocity Group')
                    ->view('emails.request_mail')
                    ->subject($subject)
                    ->withSwiftMessage(function ($message) {
                        $message->getHeaders()->addTextHeader('INVPORTID', $this->details['unqID']);
                    })
                    ->with(['template' => $template, 'data' => true]);
        }
    }

    public function getUserIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        //$remote  = $_SERVER['REMOTE_ADDR'];
        $remote = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    public function templateFormatter($template, $investor = null, $content = null, $subject = null, $note = null, $merchant_name = null, $author = null, $heading = null)
    {
        if ($investor) {
            $template = str_replace('[investor]', $investor, $template);
        }

        if ($note) {
            $template = str_replace('[note]', $note, $template);
        }

        if ($content) {
            $template = str_replace('[content]', $content, $template);
        }

        if ($merchant_name) {
            $template = str_replace('[merchant_name]', $merchant_name, $template);
        }

        if ($author) {
            $template = str_replace('[author]', $author, $template);
        }

        if ($heading) {
            $template = str_replace('[date_range]', $heading, $template);
        }

        return $template;
    }

    public function templateShortCode($param)
    {
        if (array_key_exists('subject', $param)) {
            $template = $param['subject'];
        } else {
            $template = $param['template'];
        }

        if (array_key_exists('amount', $param)) {
            $template = str_replace('[amount]', \FFM::dollar($param['amount']), $template);
        }
        foreach ($param as $key => $p) {
            if (array_key_exists($key, $param)) {
                $template = str_replace('['.$key.']', $param[$key], $template);
            }
        }

        return $template;
    }
}
