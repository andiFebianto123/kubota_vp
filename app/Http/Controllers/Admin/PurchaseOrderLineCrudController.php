<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PurchaseOrderLineAcceptExport;
use App\Helpers\Constant;
use App\Helpers\DsValidation;
use App\Http\Requests\DeliveryRequest;
use App\Http\Requests\PurchaseOrderLineRequest;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use PDF;
use Prologue\Alerts\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderLineCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(PurchaseOrderLine::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/purchase-order-line');
        CRUD::setEntityNameStrings('purchase order line', 'purchase order lines');
        
    }


    public function show()
    {
        if(!Constant::checkPermission('Read PO Line Detail')){
            abort(403);
        }

        CRUD::setValidation(DeliveryRequest::class);

        $entry = $this->crud->getCurrentEntry();
        $arrPoLineStatus = (new Constant())->statusOFC();

        $po = PurchaseOrder::where("po_num", $entry->po_num)
                ->join('vendor', 'vendor.vend_num', 'po.vend_num')
                ->get('vendor.currency as vendor_currency')
                ->first();
        $deliveries = Delivery::where("po_num", $entry->po_num)
                        ->where("po_line", $entry->po_line)
                        ->get();
        $realtimeDsQty = Delivery::where("po_num", $entry->po_num)
                            ->where("po_line", $entry->po_line)
                            ->sum('shipped_qty');
        $deliveryStatuses = DeliveryStatus::where("po_num", $entry->po_num)
                            ->where("po_line", $entry->po_line)
                            ->get();

        $currentQty = 0;
        if ($entry->order_qty > $realtimeDsQty) {
            $currentQty = $entry->order_qty -  $realtimeDsQty;
        }
        if($entry->outhouse_flag == 1){
            $args = [
                'po_num' => $entry->po_num, 
                'po_line' => $entry->po_line, 
                'order_qty' => 0
            ];
            $currentQty = (new DsValidation())->currentMaxQtyOuthouse($args)['datas'];
        }

        $this->crud->addField([
            'label' => 'Delivery Date From Vendor',
            'type' => 'date_picker',
            'name' => 'shipped_date',
            'default' => date("Y-m-d"),
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd/mm/yyyy',
                'language' => 'en'
             ],
        ]); 
        $this->crud->addField([
            'type' => 'hidden',
            'name' => 'po_line_id',
            'value' => $entry->id
        ]);   
        $this->crud->addField([
            'type' => 'text',
            'label' => 'Petugas Vendor',
            'name' => 'petugas_vendor',
            'default' => Auth::guard('backpack')->user()->name
        ]); 
        $this->crud->addField([
            'type' => 'text',
            'label' => 'No Surat Jalan',
            'name' => 'no_surat_jalan_vendor',
        ]); 
        $this->crud->addField([
            'type' => 'number_qty',
            'name' => 'shipped_qty',
            'label' => 'Qty',
            'actual_qty' => $entry->shipped_qty,
            'default' => $currentQty,
            'attributes' => [
                'data-max' =>  $currentQty,
              ], 
        ]);
        if($entry->w_serial == 1){
            $this->crud->addField(
                [
                    'name'  => 'sn_childs[]',
                    'label' => 'Serial Number',
                    'type'  => 'upload_serial_number',
                ],
            );
        }
        if($entry->outhouse_flag == 1){
            $outhouse_materials = MaterialOuthouse::where('po_num', $entry->po_num)
                                    ->where('po_line', $entry->po_line)
                                    ->groupBy('matl_item');
            $this->crud->addField(
                [
                    'name'  => 'mo_issue_qty',
                    'label' => 'Material Issue',
                    'type'  => 'outhouse_table',
                    'current_qty' => $currentQty,
                    'total_qty_per' => $outhouse_materials->sum('qty_per'),
                    'table_body' => $outhouse_materials->get()
                ],
            );
        }

        $arrFilters = [];
        $arrFilters[] = ['po_line.item', '=', $entry->item];
        $args = [   
            'filters' => $arrFilters, 
            'due_date' => $entry->due_date,
            'po_num' => $entry->po_num,
            'po_line' => $entry->po_line,
            ];
        $unfinishedPoLine = (new DsValidation())->unfinishedPoLine($args);
        
        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['po'] = $po;
        $data['arr_po_line_status'] = $arrPoLineStatus;
        $data['unfinished_po_line'] = $unfinishedPoLine;
        $data['deliveries'] = $deliveries;
        $data['delivery_statuses'] = $deliveryStatuses;

        $canAccess = false;
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $canAccess = true;
        }else{
            $po = PurchaseOrderLine::where('id', $entry->id )->first();
            if (backpack_auth()->user()->vendor->vend_num == $po->purchaseOrder->vend_num) {
                $canAccess = true;
            }
        }
        if ($entry->accept_flag == 2) {
            $canAccess = false;
        }
        if ($entry->accept_flag == 0 &&  $entry->status == 'O') {
            $canAccess = false;
        }
        if ($canAccess) {
            $layout = 'vendor.backpack.crud.purchase_order_line_show';
            if ( in_array($entry->status, ['C', 'F']) ) {
                $layout = 'vendor.backpack.crud.purchase_order_line_show_readonly';
            }
        }else{
            abort(404);
        }

        return view($layout, $data);
    }


    private function optionMaterial($poNum, $poLine){
        $mos = MaterialOuthouse::where('po_num', $poNum)
                ->where('po_line', $poLine)
                ->groupBy('matl_item')
                ->get();

        $arr_opt = [];
        foreach ($mos as $key => $mo) {
            $arr_opt[$mo->id] = $mo->matl_item.' - '.$mo->description;
        }
        return $arr_opt;
    }


    public function exportExcelAccept()
    {
        $filename = 'poline-'.date('YmdHis').'.xlsx';
        return Excel::download(new PurchaseOrderLineAcceptExport, $filename);
    }
    

    public function exportPdfAccept()
    {
        $purchase_order_lines = PurchaseOrderLine::leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->get(['po_line.id as id', 'po.po_num as number', 'po_line.po_line as po_line'
                                ,'po_line.item as item', 'po_line.description as description', 'po_line.order_qty'
                                ,'po_line.u_m', 'po_line.unit_price', 'vendor.vend_name as vendor_name']);

        $data['purchase_order_lines'] = $purchase_order_lines;
    	$pdf = PDF::loadview('exports.pdf.po_line_accept',$data);

        return $pdf->download('poline-'.date('YmdHis').'-pdf');
    }


    public function unread($id)
    {
        $poLine = PurchaseOrderLine::where('id', $id)->first();
        $poLine->accept_flag = 0;
        $poLine->read_by = null;
        $poLine->read_at = null;
        $poLine->save();

        Alert::success("Data has already unread!")->flash();

        return redirect()->back();
    }


    
    

    // function exportPdfLabelInstant($id){
    //     $delivery = Delivery::join('vendor_item', 'vendor_item.item', 'delivery.item')
    //             ->join('po', 'po.po_num', 'delivery.po_num')
    //             ->where('delivery.id', $id)
    //             ->where('vendor_item.vend_num', DB::raw('po.vend_num'))
    //             ->select('delivery.id as id', 'po.po_num as po_num', 'delivery.po_line as po_line', 'delivery.item as item', 
    //             'delivery.description as description', 'delivery.ds_num as ds_num', 'delivery.po_num as po_num', 
    //             'po.vend_num as vend_num', 'delivery.shipped_qty as qty', 'vendor_item.qty_per_box as qty_per_box')
    //             ->get();
        
    //     $data['data'] = $delivery;
        
    //     $pdf = PDF::loadview('exports.pdf.delivery_sheet_label', $data)->setPaper('A4');

    //     return $pdf->stream();
    // }
}
