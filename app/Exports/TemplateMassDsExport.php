<?php
namespace App\Exports;

use App\Helpers\Constant;
use App\Helpers\DsValidation;
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
    public function __construct($attrs)
    {
        $this->count_data = 0;
        $this->filter_vend_num = $attrs['filter_vend_num'];
        $this->filter_po_num = $attrs['filter_po_num'];
        $this->filter_item = $attrs['filter_item'];
    }


    public function view(): View
    {
        $filters = [];
        $manyData = 0;

        if(!in_array(Constant::getRole(),['Admin PTKI'])){
            $filters[] = ['vend_num', '=', backpack_auth()->user()->vendor->vend_num  ];
        }
        if($this->filter_vend_num){
            $filters[] = ['vend_num', '=', $this->filter_vend_num];
        }

        $pos = PurchaseOrder::where($filters);
        if($this->filter_po_num){
            $pos = $pos->whereIn('po_num', $this->filter_po_num);
        }
        $pos = $pos->orderBy('po_num','asc')->get();
        $dsValidation = new DsValidation();

        $arrPoLines = [];
        foreach ($pos as $key => $po) {
            $po_lines = PurchaseOrderLine::where('po.po_num', $po->po_num );
            if($this->filter_item){
                $po_lines = $po_lines->whereIn('item', json_decode($this->filter_item));
            }
            $po_lines = $po_lines->leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->where('status', 'O')
                                ->where('accept_flag','!=', 2)
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->orderBy('po_line.id', 'desc')
                                ->get();
                                
            $cols = collect($po_lines)->unique('po_line')->sortBy('po_line');

            foreach ($cols as $key => $col) {
                $args2 = [
                    'po_num' => $col->po_num, 
                    'po_line' => $col->po_line, 
                ];
                
                $currentMaxQty = $dsValidation->currentMaxQty($args2);
                if ($col->outhouse_flag == 1) {
                    $currentMaxQty = $dsValidation->currentMaxQtyOuthouse($args2);       
                }

                $arrPoLines[] = [
                    'po_num' => $col->po_num,
                    'po_line' => $col->po_line,
                    'item' => $col->item,
                    'description' => $col->description,
                    'due_date' => $col->due_date,
                    'unit_price' => $col->unit_price,
                    'order_qty' => $col->order_qty,
                    'po_change' => $col->po_change,
                    'available_qty' => $currentMaxQty['datas'],
                ];
                $manyData++;
            }
            $this->count_data = $manyData;
        }
        $data['po_lines'] = $arrPoLines;
    
        return view('exports.excel.template_mass_ds', $data);
    }

    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $styleHeader = [
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

                $styleGroupProtected = [
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'ededed',
                         ]           
                    ],
        
                ];

                $arrColumns = range('A', 'N');
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $manyData = $this->count_data +1;
                $event->sheet->getDelegate()->getStyle('A1:N1')->applyFromArray($styleHeader);
                $event->sheet->getDelegate()->getStyle('B2:J'.$manyData)->applyFromArray($styleGroupProtected);
                $event->sheet->protectCells('B2:J10', 'PHP');
            },
        ];
    }
}