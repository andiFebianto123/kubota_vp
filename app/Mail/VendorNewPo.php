<?php

namespace App\Mail;

use App\Helpers\Constant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorNewPo extends Mailable
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
        $arrMailBcc = (new Constant())->emailHandler($mailBccs, 'array');

        $hasAttachment = false;

        if(isset($this->details['pdfData'])){
            $hasAttachment = true;
            $pdfData = $this->details['pdfData'];
            $this->attachData($pdfData['file'], $pdfData['filename'], [
                'mime' => $pdfData['mime'],
            ]);
        }

        if(isset($this->details['excelData'])){
            $hasAttachment = true;
            $excelData = $this->details['excelData'];
            $this->attach($excelData['filepath'], [
                'as' => $excelData['filename'],
                'mime' => $excelData['mime'],
            ]);
        }

        if ($mailBccs == "") {
            return $this->subject('New Purchase Order - [' . $this->details['po_num'] . ']' . ($hasAttachment ? ' [Attachment]' : ''))
                    ->replyTo($this->details['buyer_email'], 'Reply to Buyer')
                    ->markdown('emails.sample-mail');
        }else{
            return $this->subject('New Purchase Order - [' . $this->details['po_num'] . ']' . ($hasAttachment ? ' [Attachment]' : ''))
                    ->replyTo($this->details['buyer_email'], 'Reply to Buyer')
                    ->bcc($arrMailBcc)
                    ->markdown('emails.sample-mail');
        }        
    }
}
