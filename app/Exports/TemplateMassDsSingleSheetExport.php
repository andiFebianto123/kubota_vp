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

    public function __construct($title, $po_num)
    {
        $this->po_num = $po_num;
        $this->title = $title;
    }

    // public function registerEvents(): array
    // {
    //     return [
    //         AfterSheet::class    => function(AfterSheet $event) {
    //             $arr_columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    //             foreach ($arr_columns as $key => $col) {
    //                 $event->sheet->getColumnDimension($col)->setAutoSize(true);
    //                 $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
    //             }
    //         },
    //     ];

    // }



    // public function view(): View
    // {
    //     $po_lines = PurchaseOrderLine::where('po_num', $this->po_num)->get();

    //     $data['po_lines'] = $po_lines;

    //     return view('exports.excel.template-mass-ds', $data);
    // }

    // public function query()
    // {
    //     return PurchaseOrderLine::query()->where('po_num', $this->po_num);
    // }

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

    // public function map($po_line): array
    // {
    //     return [
    //         $po_line->po_num,
    //         $po_line->po_line,
    //         '',
    //         '',
    //         '',
    //         ''
    //     ];
    // }

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
        $po_lines = PurchaseOrderLine::where('po_num', $this->po_num)
                ->where('status', 'O')
                ->where('accept_flag', 1)
                ->get();
        $arr_po_lines = [];
        foreach ($po_lines as $key => $po_line) {
            $arr_po_line = [];
            $arr_po_line['po_num'] = $po_line->po_num;
            $arr_po_line['po_line'] = $po_line->po_line;
            $arr_po_line['ds_delivery_date'] = '';
            $arr_po_line['serial_number'] = '';
            $arr_po_line['petugas_vendor'] = '';
            $arr_po_line['no_surat_jalan'] = '';
            $arr_po_line['order_qty'] = '';
            $arr_po_lines[] = $arr_po_line;
        }
        return $arr_po_lines;
    }


    public function title(): string
    {
        return $this->title;
    }
}