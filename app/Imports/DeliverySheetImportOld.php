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
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Maatwebsite\Excel\Concerns\WithStartRow;
HeadingRowFormatter::default('none');

class DeliverySheetImport implements WithMultipleSheets, SkipsUnknownSheets
{
    
    use Importable;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function sheets(): array
    {
        $sheets = [];
        $filters = [];
        $role = backpack_auth()->user()->roles->pluck('name')->first();
        if (in_array($role, ['Marketing Vendor', 'Finance Vendor', 'Warehouse Vendor'])) {
            $filters[] = ['vend_num', '=', backpack_auth()->user()->vendor->vend_num];
        }
        $pos = PurchaseOrder::where($filters)->get();
        foreach ($pos as $po) {
            $sheets[$po->po_num] = new DeliverySheetSingleImport();
        }

        return $sheets;

    }
    
    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
}
