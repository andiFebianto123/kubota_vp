<?php

namespace App\Imports;

use App\Helpers\LeadershipSyncHelper;
use App\Models\Church;
use App\Models\CountryList;
use App\Models\RcDpwList;
use App\Models\ChurchEntityType;
use App\Models\Personel;
use App\Models\StructureChurch;
use App\Models\CoordinatorChurch;
use App\Models\MinistryRole;
use App\Models\PurchaseOrderLine;
use App\Models\StatusHistoryChurch;
use App\Models\TempUploadDelivery;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\WithStartRow;
HeadingRowFormatter::default('none');

class DeliverySheetImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public function  __construct($attrs)
    {
      $this->filename = $attrs['filename'];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
           $this->singleRow($row);
        }
    }

    private function singleRow($row)
    {
        $row_po_line = $row['PO Line'];
        $row_delivery_sheet_number = $row['Delivery Sheet Number'];
        $row_item_number = $row['Item Number'];
        $row_qty = $row['Qty'];
        $row_petugas_vendor = $row['Petugas Vendor'];
        $row_serial_number = $row['Serial Number'];
        $row_do_number_vendor = $row['DO Number Vendor'];

        $expl_po_line = explode("-",$row_po_line);

        $po_line = PurchaseOrderLine::leftJoin('purchase_orders',  'purchase_order_lines.purchase_order_id', 'purchase_orders.id')    
                    ->where('purchase_orders.number', $expl_po_line[0])
                    ->where('purchase_order_lines.po_line', $expl_po_line[1])
                    ->get('purchase_order_lines.id as id')
                    ->first();

        $insert = new TempUploadDelivery();
        $insert->po_line_id = $po_line->id;
        $insert->user_id = backpack_auth()->user()->id;
        $insert->order_qty = $row_qty;
        $insert->ds_num= $row_delivery_sheet_number;
        $insert->serial_number= $row_serial_number;
        $insert->petugas_vendor	 = $row_petugas_vendor;
        $insert->no_surat_jalan_vendor	 = $row_do_number_vendor;
        $insert->save();
    }

    public function headingRow(): int
    {
        return 1;
    }
}