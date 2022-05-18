<?php

namespace App\Mail;

use App\Helpers\Constant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

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
        $mailBccs = env('MAIL_USER_BCC',"");
        $arrMailBcc = (new Constant())->emailHandler($mailBccs, 'array');

        if ($mailBccs == "") {
            return $this->subject('Reset Password')
                    // ->replyTo(env('MAIL_REPLY_TO',""), 'Reply to Admin')
                    ->markdown('emails.sample-mail');
        }else{
            return $this->subject('Reset Password')
                    ->bcc($arrMailBcc)
                    // ->replyTo(env('MAIL_REPLY_TO',""), 'Reply to Admin')
                    ->markdown('emails.sample-mail');
        }  

    }
}
