<?php

namespace App\Jobs;

use App\Library\Repository\Interfaces\ITemplateRepository;
use App\Mail\CommonMails;
use App\Settings;
use App\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CommonJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $details;
    protected $template;

    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ITemplateRepository $template)
    {
        // $emails = Settings::value('email');
        // $email_id_arr = explode(',', $emails);

        if (isset($this->details['bcc'])) {
            $bcc = $this->details['bcc'];
        } else {
            $bcc = [];
        }
        /******************  This is a common queue jobs for all section *************************/
        $mail_enabled = true;
        // Mail enable/disable function from template
        if ($this->details['status']) {
            $type = null;
            if (array_key_exists('template_type', $this->details)) {
                $type = $this->details['template_type'];
            }
            if ($this->details['status'] == 'payment_mail') {
                $type = $this->details['complete_per'];
            }
            if ($this->details['status'] == 'payment_send') {
                $type = $this->details['mail_to'];
            }
            $template_code = $template->getTemplateFromStatus($this->details['status'], $type);
            if ($template_code) {
                $template_status = Template::where(['temp_code' => $template_code, 'enable' => 1])->first();
                if ($template_status) {
                    $mail_enabled = true;
                } else {
                    $mail_enabled = false;
                }
            }
            if ($this->details['status'] == 'marketing_offer') {
                $template_status = Template::where(['id' => $this->details['template'], 'enable' => 1])->first();
                if ($template_status) {
                    $mail_enabled = true;
                } else {
                    $mail_enabled = false;
                }
            }
        }
        $queue = new CommonMails($this->details);
        if ($mail_enabled) {
            Mail::to($this->details['to_mail'])
            ->bcc($bcc)
            ->queue($queue);
        }
    }
}
