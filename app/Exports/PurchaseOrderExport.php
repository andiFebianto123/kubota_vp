<?php
namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PurchaseOrderExport implements FromView, WithEvents
{
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $arr_columns = ['A', 'B', 'C', 'D', 'E'];
                foreach ($arr_columns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
            },
        ];

    }


    public function view(): View
    {
        $purchase_orders = PurchaseOrder::leftJoin('vendors', 'vendors.number', 'purchase_orders.vendor_number')
                            ->get(['purchase_orders.id as id', 'purchase_orders.number as number', 'vendors.number as vendor_number'
                            ,'purchase_orders.po_date as po_date', 'purchase_orders.email_flag as email_flag']);

        $data['purchase_orders'] = $purchase_orders;

        return view('exports.excel.purchaseorder', $data);
    }
}