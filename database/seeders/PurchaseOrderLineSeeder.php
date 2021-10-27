<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrderLine;

class PurchaseOrderLineSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
              "purchase_order_id" => "1",
              "po_line" => "1",
              "po_release" => "0",
             "po_change" => "0",
             "item" => "55330500/0424",
             "description" => "S PLAT 1/4 X 2' X 6 M",
             "order_qty" => "4",
             "inspection_flag" => 0,
             "u_m" => "PC",
             "due_date" => "2014-12-16 00:00:00",
             "unit_price" => "210000",
             "wh" => "P1",
             "location" => "",
             "tax_status" => "NOTAX",
             "currency" => "IDR",
             "item_alias" => "",
             "status" => "",
             
            ],
            ["purchase_order_id" => "1",
            "po_line" => "1",],
          ],
          [
            [
              "purchase_order_id" => "1",
              "po_line" => "2",
             "po_release" => "0",
             "po_change" => "0",
             "item" => "0084A-00009-420-2",
             "description" => "EMULCUT 500-A",
             "order_qty" => "200",
             "inspection_flag" => 0,
             "u_m" => "KG",
             "due_date" => "2015-03-16 00:00:00",
             "unit_price" => "54900",
             "wh" => "P1",
             "location" => "O2-50602",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "item_alias" => "",
             "status" => "",
             
            ],
            ["purchase_order_id" => "1",
            "po_line" => "2",],
          ],
          [
            [
              "purchase_order_id" => "1",
              "po_line" => "3",
             "po_release" => "0",
             "po_change" => "0",
             "item" => "0084A-00021-420-2",
             "description" => "PAY OFF METCLEAN LS",
             "order_qty" => "100",
             "inspection_flag" => 0,
             "u_m" => "LT",
             "due_date" => "2015-03-16 00:00:00",
             "unit_price" => "31000",
             "wh" => "P1",
             "location" => "O2-50602",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "item_alias" => "",
             "status" => "",
             
            ],
            ["purchase_order_id" => "1",
            "po_line" => "3",],
          ],
          [
            [
              "purchase_order_id" => "2",
              "po_line" => "1",
             "po_release" => "0",
             "po_change" => "0",
             "item" => "0092C-00008-425-2",
             "description" => "PROPEEL",
             "order_qty" => "100",
             "inspection_flag" => 0,
             "u_m" => "LT",
             "due_date" => "2015-03-16 00:00:00",
             "unit_price" => "70000",
             "wh" => "P1",
             "location" => "O2-50602",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "item_alias" => "",
             "status" => "",
             
            ],
            ["purchase_order_id" => "2",
            "po_line" => "1",],
          ],
          [
            [
              "purchase_order_id" => "2",
            "po_line" => "2",
             "po_release" => "0",
             "po_change" => "0",
             "item" => "0084A-00019-420-2",
             "description" => "UNINEAT CUTTING OIL",
             "order_qty" => "600",
             "inspection_flag" => 0,
             "u_m" => "LT",
             "due_date" => "2015-03-16 00:00:00",
             "unit_price" => "27000",
             "wh" => "P1",
             "location" => "O2-50602",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "item_alias" => "",
             "status" => "",
             
            ],
            ["purchase_order_id" => "2",
            "po_line" => "2",],
          ],
          
        ];

       foreach($arr_seeders as $key => $seed) {
          PurchaseOrderLine::updateOrCreate($seed[0],$seed[1]);
       }
    }
}