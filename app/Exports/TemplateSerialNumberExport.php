<?php
namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class TemplateSerialNumberExport implements FromView, WithEvents
{
    public function __construct($qty)
    {
        $this->qty = $qty;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $arr_columns = range('A', 'B');
                foreach ($arr_columns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
            },
        ];

    }


    public function view(): View
    {
        $data['qty'] = $this->qty;
    
        return view('exports.excel.template-serial-number', $data);
    }
}