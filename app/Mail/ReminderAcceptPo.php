<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderAcceptPo extends Mailable
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

        if ($mailBccs == "") {
            return $this->subject($this->details['title'].' - [' . $this->details['po_num'] . ']' )
                    ->replyTo(env('MAIL_REPLY_TO',""), 'Reply to Admin')
                    ->markdown('emails.sample-mail');
        }else{
            return $this->subject($this->details['title'].' - [' . $this->details['po_num'] . ']' )
                    ->replyTo(env('MAIL_REPLY_TO',""), 'Reply to Admin')
                    ->bcc($arrMailBcc, 'Admin Kubota')
                    ->markdown('emails.sample-mail');
        }        
    }
}
