<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MerchantEmail extends Mailable
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
        // merchant email notifcations
        return $this->from('no-reply@velocitygorupusa.com')
                      ->view('emails.template')
                      ->subject($this->details['merchant_name'].' Payment stopped ')
                      ->setBody('<font color="red">'.$this->details['content'].'</font>', 'text/html')
                      ->with(['title'=>$this->details['title'], 'content'=>$this->details['content']]);
    }
}
