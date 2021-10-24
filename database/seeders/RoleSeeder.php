<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
             "label" => "Admin",
             "name" => "admin",
            ],
            ["name" => "admin"],
          ],
          [
            [
             "label" => "Vendor",
             "name" => "vendor",
            ],
            ["name" => "vendor"],
          ],
          [
            [
             "label" => "Finance",
             "name" => "finance",
            ],
            ["name" => "finance"],
          ],
        ];

       foreach($arr_seeders as $key => $seed) {
          Role::updateOrCreate($seed[0],$seed[1]);
       }
    }
}