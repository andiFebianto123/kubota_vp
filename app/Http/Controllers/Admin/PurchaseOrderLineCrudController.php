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

/**
 * Class PurchaseOrderLineCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PurchaseOrderLineCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\PurchaseOrderLine::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/purchase-order-line');
        CRUD::setEntityNameStrings('purchase order line', 'purchase order lines');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('id');
        CRUD::column('po_num');
        CRUD::column('po_line');
        CRUD::column('po_release');
        CRUD::column('item');
        CRUD::column('item_ptki');
        CRUD::column('w_serial');
        CRUD::column('description');
        CRUD::column('po_change');
        CRUD::column('po_change_date');
        CRUD::column('order_qty');
        CRUD::column('inspection_flag');
        CRUD::column('u_m');
        CRUD::column('due_date');
        CRUD::column('unit_price');
        CRUD::column('wh');
        CRUD::column('location');
        CRUD::column('tax_status');
        CRUD::column('currency');
        CRUD::column('item_alias');
        CRUD::column('status');
        CRUD::column('production_date');
        CRUD::column('accept_flag');
        CRUD::column('reason');
        CRUD::column('read_by');
        CRUD::column('read_at');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PurchaseOrderLineRequest::class);

        CRUD::field('id');
        CRUD::field('po_num');
        CRUD::field('po_line');
        CRUD::field('po_release');
        CRUD::field('item');
        CRUD::field('item_ptki');
        CRUD::field('w_serial');
        CRUD::field('description');
        CRUD::field('po_change');
        CRUD::field('po_change_date');
        CRUD::field('order_qty');
        CRUD::field('inspection_flag');
        CRUD::field('u_m');
        CRUD::field('due_date');
        CRUD::field('unit_price');
        CRUD::field('wh');
        CRUD::field('location');
        CRUD::field('tax_status');
        CRUD::field('currency');
        CRUD::field('item_alias');
        CRUD::field('status');
        CRUD::field('production_date');
        CRUD::field('accept_flag');
        CRUD::field('reason');
        CRUD::field('read_by');
        CRUD::field('read_at');
        CRUD::field('created_at');
        CRUD::field('updated_at');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    function show()
    {

        CRUD::setValidation(DeliveryRequest::class);

        $entry = $this->crud->getCurrentEntry();
        $po = PurchaseOrder::where("po_num", $entry->po_num)
                ->join('vendor', 'vendor.vend_num', 'po.vend_num')
                ->get('vendor.currency as vendor_currency')
                ->first();
        $deliveries = Delivery::where("po_num", $entry->po_num)->where("po_line", $entry->po_line)->get();
        $realtime_ds_qty = Delivery::where("po_num", $entry->po_num)->where("po_line", $entry->po_line)->sum('shipped_qty');
        $delivery_statuses = DeliveryStatus::where("po_num", $entry->po_num)->where("po_line", $entry->po_line)->get();
        $arr_po_line_status = (new Constant())->statusOFC();

        $current_qty = ($entry->order_qty < $realtime_ds_qty)? 0 : $entry->order_qty -  $realtime_ds_qty;

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
        CRUD::field('petugas_vendor');
        CRUD::field('no_surat_jalan_vendor');
        $this->crud->addField([
            'type' => 'number_qty',
            'name' => 'shipped_qty',
            'label' => 'Qty',
            'actual_qty' => $entry->shipped_qty,
            'default' => $current_qty,
            'attributes' => [
                'data-max' =>  $current_qty,
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
            // $this->crud->addField(
            //     [
            //         'name'  => 'material_issues',
            //         'label' => 'Material Issue',
            //         'type'  => 'upload_material_issue',
            //         'fields' => [
            //             [
            //                 'name'        => 'material_ids[]',
            //                 'label'       => "Material",
            //                 'type'        => 'select2_from_array',
            //                 'options'     => $this->optionMaterial($entry->po_num, $entry->po_line),
            //                 'allows_null' => false,
            //                 'wrapper'   => [ 
            //                     'class'      => 'form-group col-md-6'
            //                  ],
            //             ],
            //             [   // select2_from_array
            //                 'name'        => 'mo_issue_qty[]',
            //                 'label'       => "Issue Qty",
            //                 'type'        => 'number',
            //                 'wrapper'   => [ 
            //                     'class'      => 'form-group col-md-6'
            //                  ],
            //             ]
            //         ],
            //     ],
            // );
            $outhouse_materials = MaterialOuthouse::where('po_num', $entry->po_num)
                                    ->where('po_line', $entry->po_line);

            $this->crud->addField(
                [
                    'name'  => 'material_issues',
                    'label' => 'Material Issue',
                    'type'  => 'outhouse_table',
                    'current_qty' => $current_qty,
                    'total_qty_per' => $outhouse_materials->sum('qty_per'),
                    'table_body' => $outhouse_materials->get()
                    // 'fields' => [
                    //     [
                    //         'name'        => 'material_ids[]',
                    //         'label'       => "Material",
                    //         'type'        => 'select2_from_array',
                    //         'options'     => $this->optionMaterial($entry->po_num, $entry->po_line),
                    //         'allows_null' => false,
                    //         'wrapper'   => [ 
                    //             'class'      => 'form-group col-md-6'
                    //          ],
                    //     ],
                    //     [   // select2_from_array
                    //         'name'        => 'mo_issue_qty[]',
                    //         'label'       => "Issue Qty",
                    //         'type'        => 'number',
                    //         'wrapper'   => [ 
                    //             'class'      => 'form-group col-md-6'
                    //          ],
                    //     ]
                    // ],
                ],
            );
        }
        $arr_filters = [];
        $arr_filters[] = ['po_line.item', '=', $entry->item];
        $args = ['filters' => $arr_filters, 'due_date' => $entry->due_date ];
        // $arr_filters[] = ['po_line.po_num', '!=', null];
        $unfinished_po_line = (new DsValidation())->unfinishedPoLine($args);

        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['po'] = $po;
        $data['arr_po_line_status'] = $arr_po_line_status;
        $data['unfinished_po_line'] = $unfinished_po_line;
        $data['deliveries'] = $deliveries;
        $data['delivery_statuses'] = $delivery_statuses;

        return view('vendor.backpack.crud.purchase-order-line-show', $data);
    }


    private function optionMaterial($po_num, $po_line){
        $mos = MaterialOuthouse::where('po_num', $po_num)->where('po_line', $po_line)
                ->groupBy('matl_item')->get();
        $arr_opt = [];
        foreach ($mos as $key => $mo) {
            $arr_opt[$mo->id] = $mo->matl_item.' - '.$mo->description;
        }
        return $arr_opt;
    }


    public function update($id)
    {
        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();
        
        return redirect($this->crud->route);
    }

    public function destroy($id)
    {
        return true;
    }
    

    public function exportExcelAccept()
    {
        return Excel::download(new PurchaseOrderLineAcceptExport, 'poline-'.date('YmdHis').'.xlsx');

    }

    public function exportPdfAccept()
    {
        $purchase_order_lines = PurchaseOrderLine::leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->get(['po_line.id as id', 'po.po_num as number', 'po_line.po_line as po_line'
                                ,'po_line.item as item', 'po_line.description as description', 'po_line.order_qty'
                                ,'po_line.u_m', 'po_line.unit_price', 'vendor.vend_name as vendor_name']);

        $data['purchase_order_lines'] = $purchase_order_lines;
    	$pdf = PDF::loadview('exports.pdf.purchaseorderline-accept',$data);

        return $pdf->download('poline-'.date('YmdHis').'-pdf');
    }

    public function unread($id)
    {
        $po_line = PurchaseOrderLine::where('id', $id)->first();
        $po_line->accept_flag = 0;
        $po_line->read_by = null;
        $po_line->read_at = null;
        $po_line->save();

        Alert::success("Data has already unread!")->flash();

        return redirect()->back();
    }


    public function exportPdfLabelPost(Request $request)
    {
        $print_all = $request->print_deliveries;
        $po_num = $request->po_num;
        $po_line = $request->po_line;
        $print_deliveries = $request->print_delivery;
        $with_price = 'yes';
        
        $arr_param['print_all'] = $print_all;
        $arr_param['po_num'] = $po_num;
        $arr_param['po_line'] = $po_line;
        $arr_param['print_delivery'] = $print_deliveries;

        $str_param = base64_encode(serialize($arr_param));

        if (!isset($print_deliveries)) {
            return response()->json([
                'status' => false,
                'message' => 'Pilih Minimal 1 DS'
                ], 200);
        }
        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses Generate PDF',
            'newtab' => true,
            'redirect_to' => url('admin/delivery-print-label').'?param='.$str_param ,
            'validation_errors' => []
        ], 200);
    }


    public function exportPdfLabel(){
        $str_param = request('param');
        $arr_param = unserialize(base64_decode($str_param));
        $print_delivery = $arr_param['print_delivery'];
        $id = 0 ; //$arr_param['id'];

        // SELECT d.id, po.po_num, d.po_line, d.item, d.description, d.ds_num, po.vend_num, d.shipped_qty, vendor_item.qty_per_box FROM `delivery` d JOIN po ON po.po_num = d.po_num JOIN vendor_item ON vendor_item.item = d.item WHERE d.id = 1
        if($print_delivery  != null){
            $db = Delivery::join('vendor_item', 'vendor_item.item', 'delivery.item')
            ->join('po', 'po.po_num', 'delivery.po_num')
            ->whereIn('delivery.id', $print_delivery )
            ->where('vendor_item.vend_num', DB::raw('po.vend_num'))
            ->select('delivery.id as id', 'po.po_num as po_num', 'delivery.po_line as po_line', 'delivery.item as item', 'delivery.description as description', 'delivery.ds_num as ds_num', 'delivery.po_num as po_num', 'po.vend_num as vend_num', 'delivery.shipped_qty as qty', 'vendor_item.qty_per_box as qty_per_box');
            // ->groupBy('delivery.id')->get();
        }else{
            if($id != 0){
                $db = Delivery::join('vendor_item', 'vendor_item.item', 'delivery.item')
                ->join('po', 'po.po_num', 'delivery.po_num')
                ->where('delivery.id', $id)
                ->where('vendor_item.vend_num', DB::raw('po.vend_num'))
                ->select('delivery.id as id', 'po.po_num as po_num', 'delivery.po_line as po_line', 'delivery.item as item', 'delivery.description as description', 'delivery.ds_num as ds_num', 'delivery.po_num as po_num', 'po.vend_num as vend_num', 'delivery.shipped_qty as qty', 'vendor_item.qty_per_box as qty_per_box');
            // ->groupBy('delivery.id')->get();
            }
        }

        $data['data'] = $db->get();

        $pdf = PDF::loadview('exports.pdf.delivery-sheet-label', $data)->setPaper('A4');
        // return view('exports.pdf.delivery-sheet-label', $data);
        return $pdf->download('print-label-'.now().'.pdf');
        // return $pdf->stream();
    }
}
