<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Delivery;

class DeliverySeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
             "ds_num" => "V02503214123644",
             "ds_line" => "1",
             "po_num" => "PU00006434",
             "po_line" => 1,
             "po_release" => "0",
             "item" => "0084A-00021-420-2",
             "description" => "SUB,ASSY CONNECTING ROD EA11-N",
             "order_qty" => "1075",
             "u_m" => "PC",
             "due_date" => "2014-12-23 00:00:00",
             "unit_price" => "104260",
             "wh" => "P1",
             "location" => "P1-EC15",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "shipped_qty" => "1075",
             "shipped_date" => "2014-12-23 00:00:00",
             "petugas_vendor" => "",
             "no_surat_jalan_vendor" => "",
            ],
            ["ds_num" => "V02503214123644"],
          ],
          [
            [
             "ds_num" => "V02503214123524",
             "ds_line" => "3",
             "po_num" => "PU00006434",
             "po_line" => 2,
             "po_release" => "0",
             "item" => "0084A-00021-420-2",
             "description" => "SUB, ASSY CONNECTING ROD EA8-N",
             "order_qty" => "522",
             "u_m" => "PC",
             "due_date" => "2014-12-16 00:00:00",
             "unit_price" => "105470",
             "wh" => "P1",
             "location" => "P1-EB08",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "shipped_qty" => "522",
             "shipped_date" => "2014-12-16 00:00:00",
             "petugas_vendor" => "",
             "no_surat_jalan_vendor" => "",
            ],
            ["ds_num" => "V02503214123524"],
          ],
          [
            [
             "ds_num" => "V02503214123524",
             "ds_line" => "2",
             "po_num" => "PU00006434",
             "po_line" => 3,
             "po_release" => "0",
             "item" => "0084A-00021-420-2",
             "description" => "SUB,ASSY CONNECTING ROD EA11-N",
             "order_qty" => "1200",
             "u_m" => "PC",
             "due_date" => "2014-12-16 00:00:00",
             "unit_price" => "104260",
             "wh" => "P1",
             "location" => "P1-EC15",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "shipped_qty" => "1200",
             "shipped_date" => "2014-12-16 00:00:00",
             "petugas_vendor" => "",
             "no_surat_jalan_vendor" => "",
            ],
            ["ds_num" => "V02503214123524"],
          ],
          [
            [
             "ds_num" => "V02503214123524",
             "ds_line" => "1",
             "po_num" => "PU00006435",
             "po_line" => "1",
             "po_release" => "0",
             "item" => "0084A-00019-420-2",
             "description" => "SUB,ASSY CONNECTING ROD EA14-N",
             "order_qty" => "168",
             "u_m" => "PC",
             "due_date" => "2014-12-16 00:00:00",
             "unit_price" => "116670",
             "wh" => "P1",
             "location" => "P1-EC14",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "shipped_qty" => "168",
             "shipped_date" => "2014-12-16 00:00:00",
             "petugas_vendor" => "",
             "no_surat_jalan_vendor" => "",
            ],
            ["ds_num" => "V02503214123524"],
          ],
          [
            [
             "ds_num" => "V02503214123425",
             "ds_line" => "3",
             "po_num" => "PU00006435",
             "po_line" => "2",
             "po_release" => "0",
             "item" => "0084A-00019-420-2",
             "description" => "SUB,ASSY CONNECTING ROD EA11-N",
             "order_qty" => "600",
             "u_m" => "PC",
             "due_date" => "2014-12-09 00:00:00",
             "unit_price" => "104260",
             "wh" => "P1",
             "location" => "P1-EC15",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "shipped_qty" => "600",
             "shipped_date" => "2014-12-09 00:00:00",
             "petugas_vendor" => "",
             "no_surat_jalan_vendor" => "",
            ],
            ["ds_num" => "V02503214123425"],
          ],
          [
            [
             "ds_num" => "V02503214123425",
             "ds_line" => "2",
             "po_num" => "PU00006435",
             "po_line" => 3,
             "po_release" => "0",
             "item" => "0084A-00019-420-2",
             "description" => "SUB, ASSY CONNECTING ROD EA8-N",
             "order_qty" => "126",
             "u_m" => "PC",
             "due_date" => "2014-12-09 00:00:00",
             "unit_price" => "105470",
             "wh" => "P1",
             "location" => "P1-EB08",
             "tax_status" => "PPN10",
             "currency" => "IDR",
             "shipped_qty" => "126",
             "shipped_date" => "2014-12-09 00:00:00",
             "petugas_vendor" => "",
             "no_surat_jalan_vendor" => "",
            ],
            ["ds_num" => "V02503214123425"],
          ],
          
        ];

       foreach($arr_seeders as $key => $seed) {
          Delivery::updateOrCreate($seed[0],$seed[1]);
       }
    }
}