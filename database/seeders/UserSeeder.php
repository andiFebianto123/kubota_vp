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
              "name" => "Admin Admin",
              "username" => "admin",
              "email" => "admin@ptki.com",
              "password" => bcrypt("qwerty"),
            ],
            ["username" => "admin"],
          ],
          [
            [
              "name" => "User Vendor 1",
              "username" => "V001303",
              "email" => "V001303@ptki.com",
              "password" => bcrypt("qwerty"),
              "vendor_id" => "2",
            ],
            ["username" => "V001303"],
          ],
          [
            [
              "name" => "User Vendor 2",
              "username" => "V002011",
              "email" => "V002011@ptki.com",
              "password" => bcrypt("qwerty"),
              "vendor_id" => "3",
            ],
            ["username" => "V002011"],
          ],
          [
            [
              "name" => "User Vendor 3",
              "username" => "V002062",
              "email" => "V002062@ptki.com",
              "password" => bcrypt("qwerty"),
              "vendor_id" => "4",
            ],
            ["username" => "V002062"],
          ],
          [
            [
              "name" => "User Vendor 4",
              "username" => "V002073",
              "email" => "V002073@ptki.com",
              "password" => bcrypt("qwerty"),
              "vendor_id" => "5",
            ],
            ["username" => "V002073"],
          ],
        ];

       foreach($arr_seeders as $key => $seed) {
          User::updateOrCreate($seed[0],$seed[1]);
       }
    }
}