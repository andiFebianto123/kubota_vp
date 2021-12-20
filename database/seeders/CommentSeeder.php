<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 'comment','tax_invoice_id', 'user_id', 'status'
        $arr_seeders = [
            [
              [
                "comment" => "Hallo selamat siang pak...",
                "tax_invoice_id" => 1,
                "user_id" => 5,
                "status" => 1,
              ],
              ["comment" => "Hallo selamat siang pak..."],
            ],
            [
                [
                  "comment" => "Ya selamat siang juga",
                  "tax_invoice_id" => 1,
                  "user_id" => 1,
                  "status" => 1,
                ],
                ["comment" => "Ya selamat siang juga"],
              ],
          ];
        foreach($arr_seeders as $key => $seed) {
            Comment::updateOrCreate($seed[0],$seed[1]);
       }
    }
}
