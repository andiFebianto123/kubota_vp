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
        $mailBccs = env('MAIL_PO_BCC',"");
        $arrMailBcc = [];
        if (str_contains($mailBccs, ";")) {
            foreach (explode(";",$mailBccs) as $key => $mailBcc) {
                $arrMailBcc[] = $mailBcc;
            }
        }else{
            $arrMailBcc = [$mailBccs];
        }

        // return $this->markdown('emails.sample-mail');
        return $this->subject('New Purchase Order - [' . $this->details['po_num'] . ']' )
        ->replyTo(env('MAIL_REPLY_TO',""), 'Reply to Admin')
        ->bcc($arrMailBcc, 'Admin Kubota')
        ->markdown('emails.sample-mail');
    }
}
