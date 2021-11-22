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
             "po_num" => "PU00006434",
             "vend_num" => "V001303",                
             "po_change" => 1,
             "po_date" => now(),
             "email_flag" => now()
            ],
            ["po_num" => "PU00006434"],
          ],
          [
            [
                "po_num" => "PU00006435",
                "vend_num" => "V001303",                
                "po_change" => 0,
                "email_flag" => null,
                "po_date" => now()
            ],
           ["po_num" => "PU00006435"],
          ],
          [
            [
                "po_num" => "PU00006436",
                "vend_num" => "V001303",                
                "po_date" => now(),
                "po_change" => 0,
                "email_flag" => now()
            ],
           ["po_num" => "PU00006436"],
          ],
          [
            [
                "po_num" => "PU00006424",
                "vend_num" => "V002011",                
                "po_date" => now(),
                "po_change" => 0,
                "email_flag" => now()
            ],
           ["po_num" => "PU00006424"],
          ],
          [
            [
                "po_num" => "PU00006425",
                "vend_num" => "V002062",                
                "po_date" => now(),
                "po_change" => 2,
                "email_flag" => now()
                ],
            ["po_num" => "PU00006425"],
          ],
          
        ];

       foreach($arr_seeders as $key => $seed) {
            PurchaseOrder::updateOrCreate($seed[0],$seed[1]);
       }
    }
}