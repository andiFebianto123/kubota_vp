<?php

namespace Database\Seeders;

use App\Models\GeneralMessage;
use Illuminate\Database\Seeder;

class GeneralMessageSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
            [
                [
                    "title" => "How to accept PO?",
                    "content" => "Purchase Order > PO List > View Detail > Accept Order",
                    "category" => "help",
                ],["title" => "How to accept PO?",],
            ],
            [
                [
                    "title" => "How to Create Delivery Sheet?",
                    "content" => "Purchase Order > PO List > View Detail > Create",
                    "category" => "help",
                ],["title" => "How to Create Delivery Sheet?",],
            ],
            [
                [
                    "title" => "How to Check Delivery Sheet",
                    "content" => "Delivery Sheet > Delivery Sheet ",
                    "category" => "help",
                ],["title" => "How to Check Delivery Sheet",],
            ],
            [
                [
                    "title" => "How to Check Delivery Status?",
                    "content" => "Delivery Sheet > Delivery Status",
                    "category" => "help",
                ],["title" => "How to Check Delivery Status?"],
            ],
        ];

       foreach($arr_seeders as $key => $seed) {
          GeneralMessage::updateOrCreate($seed[0],$seed[1]);
       }
    }
}