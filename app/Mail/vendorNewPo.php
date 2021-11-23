<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class vendorNewPo extends Mailable
{
    use Queueable, SerializesModels;
    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
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
        // return $this->markdown('emails.sample-mail');
        return $this->subject('Mail from '.env('MAIL_FROM_ADDRESS',""))
        ->replyTo(env('MAIL_REPLY_TO',""), 'Reply to Admin')
        ->markdown('emails.sample-mail');
    }
}
