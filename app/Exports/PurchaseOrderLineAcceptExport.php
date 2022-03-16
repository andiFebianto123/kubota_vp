<?php
namespace App\Exports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PurchaseOrderLineAcceptExport implements FromView, WithEvents
{
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $arrColumns = ['A', 'B', 'C', 'D', 'E'];
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
            },
        ];

    }


    public function view(): View
    {
        $purchaseOrderLines = PurchaseOrderLine::leftJoin('purchase_orders', 'purchase_orders.id', 'purchase_order_lines.purchase_order_id')
                            ->get(['purchase_order_lines.id as id', 'purchase_orders.number as number', 'purchase_order_lines.po_line as po_line'
                            ,'purchase_order_lines.item as item', 'purchase_order_lines.description as description', 'purchase_order_lines.order_qty'
                            ,'purchase_order_lines.u_m', 'purchase_order_lines.unit_price']);

        $data['purchase_order_lines'] = $purchaseOrderLines;

        return view('exports.excel.purchase_order_line_accept', $data);
    }
}