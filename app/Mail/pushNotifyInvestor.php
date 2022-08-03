<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class pushNotifyInvestor extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new  instance.
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
        return $this->from('no-reply@velocitygorupusa.com')
                    ->view('emails.template')
                    ->subject('New fund available in marketplace | Velocitygroupusa')
                    ->with(['title'=>$this->details['title'], 'content'=>$this->details['content'], 'status'=>$this->details['status'], 'merchant_id'=>$this->details['merchant_id']]);
    }
}
