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

class DeliverySheetImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
           $this->singleRow($row);
        }
    }

    private function singleRow($row)
    {
        $row_po_num = $row['PO'];
        $row_po_line = $row['PO LINE'];
        $row_qty = $row['Qty'];
        $row_delivery_date = $row['DS Delivery Date'];
        $row_petugas_vendor = $row['Petugas Vendor'];
        $row_do_number_vendor = $row['No Surat Jalan'];
        
        $filled = 0;

        if (isset($row_qty) ) {
            $filled ++;
        }
        if (isset($row_delivery_date) ) {
            $filled ++;
        }
        if (isset($row_petugas_vendor) ) {
            $filled ++;
        }
        if (isset($row_do_number_vendor) ) {
            $filled ++;
        }
        if (isset($row_po_num) && isset($row_po_line) && $filled > 0   ) {
            $insert = new TempUploadDelivery();
            $insert->po_num = $row_po_num;
            $insert->po_line = $row_po_line;
            $insert->user_id = backpack_auth()->user()->id;
            $insert->shipped_qty = $row_qty;
            $insert->delivery_date = (isset($row_delivery_date)) ? $this->transformDate($row_delivery_date):now();
            $insert->petugas_vendor	 = $row_petugas_vendor;
            $insert->no_surat_jalan_vendor = $row_do_number_vendor;
            $insert->save();
        }
        
    }

    private function transformDate($value, $format = 'Y-m-d')
    {
        try {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}