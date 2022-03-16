<?php
namespace App\Exports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class TemplateMassDsSingleSheetExport implements FromArray, WithTitle,  WithHeadings, ShouldAutoSize
{
    use Exportable;

    public function __construct($title, $poNum)
    {
        $this->po_num = $poNum;
        $this->title = $title;
    }


    public function headings(): array
    {

        return [
            'PO',
            'PO Line',
            'DS Delivery Date',
            'Serial Number',
            'Petugas Vendor',
            'No Surat Jalan',
            'Order Qty',
        ];
    }


    public function map($row): array
    {
        return [
            $row['po_num'],
            $row['po_line'],
            $row['ds_delivery_date'],
            $row['serial_number'],
            $row['petugas_vendor'],
            $row['no_surat_jalan'],
            $row['order_qty'],
        ];
    }


    public function array(): array
    {
        $poLines = PurchaseOrderLine::where('po_num', $this->po_num)
                ->where('status', 'O')
                ->where('accept_flag', 1)
                ->get();
        $arrPoLines = [];
        foreach ($poLines as $key => $poLine) {
            $arrPoLine = [];
            $arrPoLine['po_num'] = $poLine->po_num;
            $arrPoLine['po_line'] = $poLine->po_line;
            $arrPoLine['ds_delivery_date'] = '';
            $arrPoLine['serial_number'] = '';
            $arrPoLine['petugas_vendor'] = '';
            $arrPoLine['no_surat_jalan'] = '';
            $arrPoLine['order_qty'] = '';
            $arrPoLines[] = $arrPoLine;
        }
        return $arrPoLines;
    }


    public function title(): string
    {
        return $this->title;
    }
}