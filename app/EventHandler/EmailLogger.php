<?php

namespace App\EventHandler;

use Illuminate\Support\Facades\DB;
use Illuminate\Mail\Events\MessageSent;

class EmailLogger
{
    /**
     * Handle the event.
     *
     * @param MessageSent $event
     */
    public function handle(MessageSent $event)
    {
        // dd($event->failures);
        $message = $event->message;

        DB::table('email_log')->insert([
            'status' => "Success",
            'date' => date('Y-m-d H:i:s'),
            'from' => $this->formatAddressField($message, 'From'),
            'to' => $this->formatAddressField($message, 'To'),
            'cc' => $this->formatAddressField($message, 'Cc'),
            'bcc' => $this->formatAddressField($message, 'Bcc'),
            'reply_to' => $this->formatAddressField($message, 'Reply-To'),
            'subject' => $message->getSubject(),
            'headers' => (string)$message->getHeaders(),
        ]);
    }

    /**
     * Format address strings for sender, to, cc, bcc.
     *
     * @param $message
     * @param $field
     * @return null|string
     */
    function formatAddressField($message, $field)
    {
        $headers = $message->getHeaders();

        if (!$headers->has($field)) {
            return null;
        }

        $mailboxes = $headers->get($field)->getFieldBodyModel();

        $strings = [];
        foreach ($mailboxes as $email => $name) {
            $mailboxStr = $email;
            if (null !== $name) {
                $mailboxStr = $name . ' <' . $mailboxStr . '>';
            }
            $strings[] = $mailboxStr;
        }
        return implode(', ', $strings);
    }
}
