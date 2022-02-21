<?php
namespace App\Exports;

use App\Helpers\Constant;
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
        $filters = [];

        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $filters = [];
        }else{
            $filters[] = ['vendor.vend_num', '=', backpack_auth()->user()->vendor->vend_num  ];
        }

        $purchase_orders = PurchaseOrder::leftJoin('vendor', 'vendor.vend_num', 'po.vend_num')
                            ->where($filters)
                            ->get(['po.id as id', 'po.po_num as number', 'vendor.vend_num as vendor_number'
                            ,'po.po_date as po_date', 'po.email_flag as email_flag', 'po.po_change']);

        $data['purchase_orders'] = $purchase_orders;

        return view('exports.excel.purchaseorder', $data);
    }
}