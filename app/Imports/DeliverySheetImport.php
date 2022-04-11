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
use Exception;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\WithStartRow;
HeadingRowFormatter::default('none');

class DeliverySheetImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public function __construct($attrs)
    {
        $this->insert_or_update = $attrs['insert_or_update'];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
           $this->singleRow($row);
        }
    }

    private function singleRow($row)
    {
        $rowPoNum = $row['PO'];
        $rowPoLine = $row['PO LINE'];
        $rowQty = $row['Qty'];
        $rowDeliveryDate = $row['DS Delivery Date (ex. 2021-12-30)'];
        $rowPetugasVendor = $row['Petugas Vendor'];
        $rowDoNumberVendor = $row['No Surat Jalan'];

        $existPo = PurchaseOrderLine::where('po_num',  $rowPoNum)
                    ->where('po_line', $rowPoLine)
                    ->exists();
        
        $filled = 0;
        if (isset($rowPetugasVendor) ) {
            $filled ++;
        }
        if (isset($rowDoNumberVendor) ) {
            $filled ++;
        }
        if ($existPo && isset($rowQty) && $rowQty > 0 && is_numeric($rowQty) && isset($rowDeliveryDate)) {
            $tud = TempUploadDelivery::firstOrNew([
                'po_num' => $rowPoNum,
                'po_line' => $rowPoLine,
                'user_id' => backpack_auth()->user()->id,
            ]);
            $tud->po_num = $rowPoNum;
            $tud->po_line = $rowPoLine;
            $tud->user_id = backpack_auth()->user()->id;
            $tud->shipped_qty = $rowQty;
            $tud->delivery_date = (isset($rowDeliveryDate)) ? $this->transformDate($rowDeliveryDate): "";
            $tud->petugas_vendor = $rowPetugasVendor;
            $tud->no_surat_jalan_vendor = $rowDoNumberVendor;
            $tud->save();
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