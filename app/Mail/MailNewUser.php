<?php

namespace App\Mail;

use App\Helpers\Constant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailNewUser extends Mailable
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
        $mailBccs = env('MAIL_USER_BCC',"");
        $arrMailBcc = (new Constant())->emailHandler($mailBccs, 'array');

        if ($mailBccs == "") {
            return $this->subject('New Account')
                ->replyTo(env('MAIL_REPLY_TO',""), 'Reply to Admin')
                ->markdown('emails.mail-user');
        }else{
            return $this->subject('New Account')
                ->replyTo(env('MAIL_REPLY_TO',""), 'Reply to Admin')
                ->bcc($arrMailBcc, 'Admin Kubota')
                ->markdown('emails.mail-user');
        }     

        
    }
}
