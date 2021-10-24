<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
             "username" => "admin",
             "email" => "admin@ptki.com",
             "password" => bcrypt("qwerty"),
             "role_id" => 1
            ],
            ["username" => "admin"],
          ],
          [
            [
             "username" => "V001303",
             "email" => "V001303@ptki.com",
             "password" => bcrypt("qwerty"),
             "vendor_id" => "2",
             "role_id" => 2
            ],
            ["username" => "V001303"],
          ],
          [
            [
             "username" => "V002011",
             "email" => "V002011@ptki.com",
             "password" => bcrypt("qwerty"),
             "vendor_id" => "3",
             "role_id" => 2
            ],
            ["username" => "V002011"],
          ],
          [
            [
             "username" => "V002062",
             "email" => "V002062@ptki.com",
             "password" => bcrypt("qwerty"),
             "vendor_id" => "4",
             "role_id" => 2
            ],
            ["username" => "V002062"],
          ],
          [
            [
             "username" => "V002073",
             "email" => "V002073@ptki.com",
             "password" => bcrypt("qwerty"),
             "vendor_id" => "5",
             "role_id" => 2
            ],
            ["username" => "V002073"],
          ],
        ];

       foreach($arr_seeders as $key => $seed) {
          User::updateOrCreate($seed[0],$seed[1]);
       }
    }
}