<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;

class PurchaseOrderSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
             "number" => "PU00006434",
             "vendor_id" => 2,
             "po_date" => now(),
             "email_flag" => now()
            ],
            ["number" => "PU00006434"],
          ],
          [
            [
                "number" => "PU00006435",
                "vendor_id" => 2,
                "po_date" => now(),
                "email_flag" => now()
            ],
           ["number" => "PU00006435"],
          ],
          [
            [
                "number" => "PU00006436",
                "vendor_id" => 2,
                "po_date" => now(),
                "email_flag" => now()
            ],
           ["number" => "PU00006436"],
          ],
          [
            [
                "number" => "PU00006424",
                "vendor_id" => 3,
                "po_date" => now(),
                "email_flag" => now()
            ],
           ["number" => "PU00006424"],
          ],
          [
            [
                "number" => "PU00006425",
                "vendor_id" => 4,
                "po_date" => now(),
                "email_flag" => now()
                ],
            ["number" => "PU00006425"],
          ],
          
        ];

       foreach($arr_seeders as $key => $seed) {
            PurchaseOrder::updateOrCreate($seed[0],$seed[1]);
       }
    }
}