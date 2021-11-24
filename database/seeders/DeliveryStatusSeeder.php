<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeliveryStatus;

class DeliveryStatusSeeder extends Seeder
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
              "item" => "0084A-00020-420-2",
              "description" => "SUB,ASSY CONNECTING ROD EA11-N",
              "unit_price" => "104260",
              "tax_status" => "PPN10",
              "shipped_qty" => "1075",
              "received_qty" => "1075",
              "rejected_qty" => "0",
              "bank" => "BCA",
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
              "item" => "0084A-00020-420-2",
              "description" => "SUB, ASSY CONNECTING ROD EA8-N",
              "unit_price" => "104260",
              "tax_status" => "PPN10",
              "shipped_qty" => "1055",
              "received_qty" => "1035",
              "rejected_qty" => "20",
              "bank" => "BCA",
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
              "item" => "0084A-00020-420-2",
              "description" => "SUB,ASSY CONNECTING ROD EA11-N",
              "unit_price" => "104260",
              "tax_status" => "PPN10",
              "shipped_qty" => "75",
              "received_qty" => "70",
              "rejected_qty" => "5",
              "bank" => "BCA",
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
              "po_line" => "1",
              "po_release" => "0",
              "item" => "0084A-00019-420-2",
              "description" => "SUB,ASSY CONNECTING ROD EA14-N",
              "unit_price" => "104260",
              "tax_status" => "PPN10",
              "shipped_qty" => "1075",
              "received_qty" => "1075",
              "rejected_qty" => "0",
              "bank" => "BCA",
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
              "unit_price" => "104260",
              "tax_status" => "PPN10",
              "shipped_qty" => "1075",
              "received_qty" => "1075",
              "rejected_qty" => "0",
              "bank" => "BCA",
              "petugas_vendor" => "",
              "no_surat_jalan_vendor" => "",
            ],
            ["ds_num" => "V02503214123425"],
          ],
          [
            [
              "ds_num" => "V02503214123425",
              "ds_line" => "3",
              "po_num" => "PU00006435",
              "po_line" => "2",
              "po_release" => "0",
              "item" => "0084A-00019-420-2",
              "description" => "SUB, ASSY CONNECTING ROD EA8-N",
              "unit_price" => "104260",
              "tax_status" => "PPN10",
              "shipped_qty" => "1075",
              "received_qty" => "1075",
              "rejected_qty" => "0",
              "bank" => "BCA",
              "petugas_vendor" => "",
              "no_surat_jalan_vendor" => "",
            ],
            ["ds_num" => "V02503214123425"],
          ],
        ];

       foreach($arr_seeders as $key => $seed) {
          DeliveryStatus::updateOrCreate($seed[0],$seed[1]);
       }
    }
}