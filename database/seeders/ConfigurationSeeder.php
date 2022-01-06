<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuration;

class ConfigurationSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
              "label" => "Email Reminder Day",
              "name" => "email_reminder_day",
              "value" => 5,
            ],
            ["name" => "email_reminder_day"],
          ],
          [
            [
              "label" => "Expired OTP (Day)",
              "name" => "expired_otp",
              "value" => 1,
            ],
            ["name" => "expired_otp"],
          ],
          [
            [
              "label" => "Attemp Failure OTP",
              "name" => "attemp_failure_otp",
              "value" => 5,
            ],
            ["name" => "attemp_failure_otp"],
          ],
          [
            [
              "label" => "Attemp Failure OTP",
              "name" => "attemp_failure_login",
              "value" => 5,
            ],
            ["name" => "attemp_failure_login"],
          ],
          [
            [
              "label" => "Expired Locked OTP (Minutes)",
              "name" => "locked_account_on_failure_otp",
              "value" => 5,
            ],
            ["name" => "locked_account_on_failure_otp"],
          ],
          [
            [
              "label" => "Expired Locked Login (Minutes)",
              "name" => "locked_account_on_failure_login",
              "value" => 5,
            ],
            ["name" => "locked_account_on_failure_login"],
          ],
          
        
        ];

       foreach($arr_seeders as $key => $seed) {
            Configuration::updateOrCreate($seed[0],$seed[1]);
       }
    }
}


