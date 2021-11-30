<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PurchaseOrderLineAcceptExport;
use App\Helpers\Constant;
use App\Http\Requests\PurchaseOrderLineRequest;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use PDF;
use Prologue\Alerts\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;

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
        $entry = $this->crud->getCurrentEntry();
        $po = PurchaseOrder::where("po_num", $entry->po_num)
                ->join('vendor', 'vendor.vend_num', 'po.vend_num')
                ->get('vendor.currency as vendor_currency')
                ->first();
        $deliveries = Delivery::where("po_num", $entry->po_num)->where("po_line", $entry->po_line)->get();
        $delivery_statuses = DeliveryStatus::where("po_num", $entry->po_num)->where("po_line", $entry->po_line)->get();
        $arr_po_line_status = (new Constant())->statusOFC();

        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['po'] = $po;
        $data['arr_po_line_status'] = $arr_po_line_status;
        $data['deliveries'] = $deliveries;
        $data['delivery_statuses'] = $delivery_statuses;

        return view('vendor.backpack.crud.purchase-order-line-show', $data);
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
}
