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
use PhpOffice\PhpSpreadsheet\Style\Protection;

class TemplateMassDsExport implements  FromView, WithEvents
{
    public function registerEvents(): array
    {
        

        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $style_header = [
                    //Set font style
                    'font' => [
                        'bold'      =>  true,
                        'color' => ['argb' => 'ffffff'],
                    ],
        
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '66aba3',
                         ]           
                    ],
        
                ];

                $style_group_protected = [
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'ededed',
                         ]           
                    ],
        
                ];

                $arr_columns = range('A', 'L');
                foreach ($arr_columns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $event->sheet->getDelegate()->getStyle('A1:L1')->applyFromArray($style_header);
                $event->sheet->getDelegate()->getStyle('B2:H10')->applyFromArray($style_group_protected);
                $event->sheet->protectCells('B2:H10', 'PHP');

                // $event->sheet->getProtection()->setPassword('kubota');
                // $event->sheet->getProtection()->setSheet(true);
                // $event->sheet->getStyle('I2:L10')->getProtection()
                // ->setLocked(Protection::PROTECTION_UNPROTECTED);
            },
        ];
    }


    public function view(): View
    {
        $filters = [];

        $pos = PurchaseOrder::where($filters)->orderBy('po_num','asc')->get();
        $arr_po_lines = [];
        foreach ($pos as $key => $po) {
            $po_lines = PurchaseOrderLine::where('po.po_num', $po->po_num )
                                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->where('status', 'O')
                                ->where('accept_flag', 1)
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->orderBy('po_line.id', 'desc')
                                ->get();
            $cols = collect($po_lines)->unique('po_line')->sortBy('po_line');
            foreach ($cols as $key => $col) {
                $arr_po_lines[] = [
                    'po_num' => $col->po_num,
                    'po_line' => $col->po_line,
                    'item' => $col->item,
                    'description' => $col->description,
                    'unit_price' => $col->unit_price,
                    'order_qty' => $col->order_qty,
                    'po_change' => $col->po_change,
                ];
            }
            
        }

        $po_lines = PurchaseOrderLine::where('status', 'O')
                ->where('accept_flag', 1)
                ->get();

        $data['po_lines'] = $arr_po_lines;
    
        return view('exports.excel.template-mass-ds', $data);
    }
}