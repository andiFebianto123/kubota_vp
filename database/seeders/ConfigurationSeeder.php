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
        
        ];

       foreach($arr_seeders as $key => $seed) {
            Configuration::updateOrCreate($seed[0],$seed[1]);
       }
    }
}