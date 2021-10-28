<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TempUploadDelivery;

class TempUploadDeliverySeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
              "po_line_id" => 6,
              "user_id" => 1,
              'order_qty' => 32,
              "petugas_vendor" => 'Aryo',
              'no_surat_jalan_vendor' => "-",
              "serial_number" => 'KBT0012930',
            ],
            ["serial_number" => "KBT0012930"],
          ],
          [
            [
              "po_line_id" => 6,
              "user_id" => 1,
              'order_qty' => 20,
              "petugas_vendor" => 'Armo',
              'no_surat_jalan_vendor' => "-",
              "serial_number" => 'KBT0012931',
            ],
            ["serial_number" => "KBT0012931"],
          ],
          [
            [
              "po_line_id" => 6,
              "user_id" => 1,
              'order_qty' => 12,
              "petugas_vendor" => 'Biryo',
              "serial_number" => 'KBT0012932',
              'no_surat_jalan_vendor' => "-",
            ],
            ["serial_number" => "KBT0012932"],
          ],
          [
            [
              "po_line_id" => 6,
              "user_id" => 1,
              'order_qty' => 10,
              "petugas_vendor" => 'Anto',
              "serial_number" => 'KBT0012933',
              'no_surat_jalan_vendor' => "-",
            ],
            ["serial_number" => "KBT0012933"],
          ],
        
        ];

       foreach($arr_seeders as $key => $seed) {
            TempUploadDelivery::updateOrCreate($seed[0],$seed[1]);
       }
    }
}