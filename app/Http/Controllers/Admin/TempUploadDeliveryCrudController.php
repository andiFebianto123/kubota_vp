<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TempUploadDeliveryRequest;
use App\Models\Delivery;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\TempUploadDelivery;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Request;
use Prologue\Alerts\Facades\Alert;

/**
 * Class TempUploadDeliveryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TempUploadDeliveryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\TempUploadDelivery::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/temp-upload-delivery');
        CRUD::setEntityNameStrings('temp upload delivery', 'temp upload deliveries');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');
        $this->crud->removeButton('show');
        $this->crud->addButtonFromView('top', 'insertfromtemp', 'insertfromtemp', 'beginning');
        $this->crud->addButtonFromView('top', 'canceltemp', 'canceltemp', 'end');
        // $this->crud->addButtonFromModelFunction('top', 'insert_db', 'insertToDB', 'beginning');
        // $this->crud->addButtonFromModelFunction('top', 'cancel_db', 'cancelInsert', 'end');
        $this->crud->addClause('where','user_id', backpack_auth()->user()->id);
        $this->crud->orderBy('po_num', 'asc');        
        $this->crud->orderBy('po_line', 'asc');        

        CRUD::addColumn([
            'label'     => 'PO', // Table column heading
            'name'      => 'po_num', 
        ]);

        CRUD::addColumn([
            'label'     => 'PO Line', // Table column heading
            'name'      => 'po_line',
        ]);

        CRUD::addColumn([
            'label'     => 'Delivery Date', // Table column heading
            'name'      => 'delivery_date',
        ]);

        CRUD::addColumn([
            'label'     => 'Qty', // Table column heading
            'name'      => 'order_qty', 
        ]);
        CRUD::addColumn([
            'label'     => 'Serial Number', // Table column heading
            'name'      => 'serial_number', 
        ]);
        CRUD::addColumn([
            'label'     => 'Petugas Vendor', // Table column heading
            'name'      => 'petugas_vendor', 
        ]);
        CRUD::addColumn([
            'label'     => 'DO Number Vendor', // Table column heading
            'name'      => 'no_surat_jalan_vendor', 
        ]);
       
        // Alert::success("Successfully Save Multiple DS!")->flash();

    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TempUploadDeliveryRequest::class);

        

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

    public function destroy($id)
    {
        return true;
    }

    public function insertToDb(Request $request)
    {
        $data_temps = TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->get();

        $code = "";
            switch (backpack_auth()->user()->role->name) {
                case 'admin':
                    $code = "01";
                    break;
                case 'vendor':
                    $code = "00";
                    break;
                default:
                    # code...
                    break;
            }

        foreach ($data_temps as $key => $data_temp) {
            $po = PurchaseOrder::where('po_num', $data_temp->po_num)->first();
            $po_line = PurchaseOrderLine::where('po_num', $data_temp->po_num)->where('po_line', $data_temp->po_line)->first();
            $ds_num = $po->vend_num.date("ymd").$code;
            
            $insert = new Delivery();
            $insert->ds_num = $ds_num;
            $insert->po_line = $data_temp->po_line;
            $insert->po_num = $data_temp->po_num;
            $insert->po_release = 0;
            $insert->ds_line = $key+1;
            $insert->description = $po_line->description;
            $insert->u_m = $po_line->u_m;
            $insert->due_date = $po_line->due_date;
            $insert->unit_price = $po_line->unit_price;
            $insert->wh = $po_line->wh;
            $insert->location = $po_line->location;
            $insert->tax_status = $po_line->tax_status;
            $insert->currency = $po_line->currency;
            $insert->shipped_qty = $po_line->order_qty;
            $insert->shipped_date = now();
            $insert->order_qty = $data_temp->order_qty;
            $insert->w_serial = ($data_temp->serial_number) ? $data_temp->serial_number : 0;
            $insert->petugas_vendor = $data_temp->petugas_vendor;
            $insert->no_surat_jalan_vendor = $data_temp->no_surat_jalan_vendor;

            if ($po_line->status == 'O' && $po_line->accept_flag == 1) {
                $insert->save();
            }
        }

        TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->delete();
    }

    public function cancelToDb(Request $request)
    {
        // return true;
        TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->delete();
    }
}
