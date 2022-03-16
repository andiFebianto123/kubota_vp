<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithDrawings;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


class OrderSheetExport implements FromView, WithEvents, WithDrawings
{
    public function __construct($dataFc)
    {
        $this->data_fc = $dataFc;
    }


    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Kubota logo');
        $drawing->setPath(public_path('/img/logokubotaforearth.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('J1');

        return $drawing;
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $arrColumns = range('A', 'O');
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
            },
        ];
    }


    public function view(): View
    {
        $data = $this->data_fc;

        return view('exports.excel.order_sheet', $data);
    }
}