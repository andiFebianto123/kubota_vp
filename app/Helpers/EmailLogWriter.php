<?php
namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\DB;

class EmailLogWriter{

    /*  Insert log to email_log tabble */
    public function create($subject, $emailTo, $errorMessage, $cc = '', $bcc = '', $replyTo = ''){
        try{
            $data = [
                'status' => "Error",
                'error_log' => $errorMessage,
                'date' => date('Y-m-d H:i:s'),
                'from' => env('MAIL_FROM_ADDRESS', 'kubotavp@mail.com'),
                'to' => $emailTo,
                'cc' => $cc,
                'bcc' =>  $bcc,
                'subject' => $subject,
                'reply_to' => $replyTo
            ];
            DB::table('email_log')->insert($data);
        }
        catch(Exception $e){
            DB::rollBack();
            throw($e);
        }

    }
}