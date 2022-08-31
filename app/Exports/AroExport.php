<?php
namespace App\Exports;

use App\Helpers\Constant;
use App\Helpers\DsValidation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class AroExport implements  FromView, WithEvents
{
    public function __construct($attrs)
    {
        $this->count_data = 0;
        $this->filter_vend_num = $attrs['filter_vend_num'];
        $this->filter_po_num = $attrs['filter_po_num'];
        $this->filter_item = $attrs['filter_item'];
        $this->filter_status_aro = $attrs['filter_status_aro'];
        $this->header_range = $attrs['header_range']; // default M
        $this->style_range = $attrs['style_range']; // default I
    }


    public function view(): View
    {
        $filters = [];
        $manyData = 0;
        if(!strpos(strtoupper(Constant::getRole()), 'PTKI')){
            $filters[] = ['vend_num', '=', backpack_auth()->user()->vendor->vend_num  ];
        }
       
        if($this->filter_vend_num){
            $filters[] = ['vend_num', '=', $this->filter_vend_num];
        }

        $pos = PurchaseOrder::where($filters);
        if($this->filter_po_num){
            $pos = $pos->whereIn('po_num', $this->filter_po_num);
        }
        
        if($this->filter_status_aro){
            $statusAro = $this->filter_status_aro;
            if($statusAro == 'ACCEPTED'){
                $pos->join(DB::Raw("(SELECT po_num as valid_num, po_change as valid_change FROM po_line WHERE accept_flag = 1 GROUP BY po_num, po_change) valid"), function($join){
                    $join->on( "po.po_num", "=", "valid.valid_num");
                    $join->on("po.po_change", "=", "valid.valid_change");
                });
            }
            else if($statusAro == "REJECT"){
                $pos->join(DB::Raw("(SELECT po_num as valid_num, po_change as valid_change FROM po_line WHERE accept_flag = 2 GROUP BY po_num, po_change) valid"), function($join){
                    $join->on( "po.po_num", "=", "valid.valid_num");
                    $join->on("po.po_change", "=", "valid.valid_change");
                });
            }
            else if($statusAro == "OPEN"){
                $pos->join(DB::Raw("(SELECT po_num as valid_num, po_change as valid_change FROM po_line WHERE accept_flag NOT IN (1,2) GROUP BY po_num, po_change) valid"), function($join){
                    $join->on( "po.po_num", "=", "valid.valid_num");
                    $join->on("po.po_change", "=", "valid.valid_change");
                });
            }
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
                                ->whereRaw(DB::raw("po_line.po_change =
                                (
                                  select Max(pl.po_change)
                                  from po_line as pl 
                                  where pl.po_num = po_line.po_num
                                  and pl.po_line = po_line.po_line
                                )"))
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->groupBy('po_change', 'po_num', 'po_line')
                                ->orderBy('po_line.id', 'desc')
                                ->get();
                                
            $cols = collect($po_lines)->unique('po_line')->sortBy('po_line');

            foreach ($cols as $key => $col) {
                $arrPoLines[] = [
                    'po_num' => $col->po_num,
                    'po_line' => $col->po_line,
                    'status' => $col->status,
                    'item' => $col->item,
                    'description' => $col->description,
                    'due_date' => $col->due_date,
                    'unit_price' => $col->unit_price,
                    'order_qty' => $col->order_qty,
                    'po_change' => $col->po_change,
                    'accept_flag' => $col->accept_flag
                ];
                $manyData++;
            }
            $this->count_data = $manyData;
        }
        $data['po_lines'] = $arrPoLines;
        $data['arr_po_line_status'] = (new Constant())->statusOFC();
        $data['arr_po_line_aro'] = (new Constant())->statusARO();
    
        return view('exports.excel.purchase_order_accept_reject_open', $data);
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

                $arrColumns = range('A',  $this->header_range);
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $manyData = $this->count_data +1;
                $event->sheet->getDelegate()->getStyle('A1:'.$this->header_range.'1')->applyFromArray($styleHeader);
                $event->sheet->getDelegate()->getStyle('B2:'.$this->style_range.$manyData)->applyFromArray($styleGroupProtected);
                $event->sheet->protectCells('B2:'.$this->style_range.'10', 'PHP');
            },
        ];
    }
}