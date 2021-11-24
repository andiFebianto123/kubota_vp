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
use App\Models\PurchaseOrder;
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

class DeliverySheetSingleImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
           $this->singleRow($row);
        }
    }

    private function singleRow($row)
    {
        $row_po_num = $row['PO'];
        $row_po_line = $row['PO Line'];
        $row_qty = $row['Order Qty'];
        $row_delivery_date = $row['DS Delivery Date'];
        $row_petugas_vendor = $row['Petugas Vendor'];
        $row_serial_number = $row['Serial Number'];
        $row_do_number_vendor = $row['No Surat Jalan'];

        if ($row_po_num) {
            $insert = new TempUploadDelivery();
            $insert->po_num = $row_po_num;
            $insert->po_line = $row_po_line;
            $insert->user_id = backpack_auth()->user()->id;
            $insert->order_qty = $row_qty;
            $insert->delivery_date= $row_delivery_date;
            $insert->serial_number= $row_serial_number;
            $insert->petugas_vendor	 = $row_petugas_vendor;
            $insert->no_surat_jalan_vendor	 = $row_do_number_vendor;
            $insert->save();
        }
        
    }

    public function headingRow(): int
    {
        return 1;
    }
}