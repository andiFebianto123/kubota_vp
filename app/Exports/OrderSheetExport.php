<?php
namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class OrderSheetExport implements FromView, WithEvents
{
    public function __construct($data_fc)
    {
        $this->data_fc = $data_fc;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $arr_columns = range('A', 'O');
                foreach ($arr_columns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
            },
        ];

    }


    public function view(): View
    {
       
        $data = $this->data_fc;

        return view('exports.excel.order-sheet', $data);
    }
}