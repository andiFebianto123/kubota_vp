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

class TemplateMassDsExport implements  FromView, WithEvents
{
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $arr_columns = range('A', 'K');
                foreach ($arr_columns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
            },
        ];

    }


    public function view(): View
    {
        $po_lines = PurchaseOrderLine::where('status', 'O')
                ->where('accept_flag', 1)
                ->get();

        $data['po_lines'] = $po_lines;
    
        return view('exports.excel.template-mass-ds', $data);
    }
}