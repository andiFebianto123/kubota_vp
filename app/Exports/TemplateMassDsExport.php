<?php
namespace App\Exports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;

class TemplateMassDsExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct($auth)
    {
        $this->auth = $auth;
    }


    public function sheets(): array
    {
        $sheets = [];
        $filters = [];
        if ($this->auth->role == 'vendor') {
            $filters[] = ['vend_num', '=', $this->auth->vendor->vend_num];
        }
        $pos = PurchaseOrder::where($filters)->get();
        foreach ($pos as $po) {
            $sheets[] = new TemplateMassDsSingleSheetExport($po->po_num, $po->po_num);
        }

        return $sheets;
    }
}