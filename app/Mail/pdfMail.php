<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class pdfMail extends Mailable
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
        // pdf attachment with queue mail
        return $this->from('no-reply@velocitygorupusa.com')
                    ->view('emails.template')
                    ->subject($this->details['options'].' Report for '.$this->details['investor_name'])
                    ->attach($this->details['attach'])
                    ->with(['title'=>$this->details['title'], 'content'=>$this->details['content']]);
    }
}
