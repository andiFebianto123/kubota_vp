<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PurchaseOrderRequest;
use App\Models\PurchaseOrderLine;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

/**
 * Class PurchaseOrderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PurchaseOrderCrudController extends CrudController
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
        CRUD::setModel(\App\Models\PurchaseOrder::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/purchase-order');
        CRUD::setEntityNameStrings('purchase order', 'purchase orders');
    }

    protected function setupListOperation()
    {
        $this->crud->removeButton('create');

        $this->crud->addButtonFromModelFunction('top', 'excel_export', 'excelExport', 'beginning');

        $this->crud->orderBy('id', 'asc');

        CRUD::column('id');
        CRUD::column('number');
        CRUD::addColumn([
            'label'     => 'Kode Vendor', // Table column heading
            'name'      => 'vendor_id', // the column that contains the ID of that connected entity;
            'entity'    => 'vendor', 
            'type' => 'relationship',
            'attribute' => 'number',
        ]);
        CRUD::addColumn([
            'label'     => 'Nama Vendor', // Table column heading
            'name'      => 'vendor_id', // the column that contains the ID of that connected entity;
            'entity'    => 'vendor', 
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::column('po_date');
        CRUD::column('email_flag');

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
        CRUD::setValidation(PurchaseOrderRequest::class);

        CRUD::field('id');
        CRUD::field('number');
        CRUD::field('vendor_id');
        CRUD::field('po_date');
        CRUD::field('email_flag');
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
        $po_line_unreads = PurchaseOrderLine::where('purchase_order_id', $entry->id )
                                ->where('read_at', null)
                                ->get();
        $po_line_reads = PurchaseOrderLine::where('purchase_order_id', $entry->id )
                                ->where('read_at', '!=',null)
                                ->get();
        $arr_po_line_status = [ 'O' => ['text' => 'Open', 'color' => ''], 
                                'F' => ['text' => 'Filled', 'color' => 'text-primary'], 
                                'C' => ['text' => 'Complete', 'color' => 'text-success']
                            ];

        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['po_line_reads'] = $po_line_reads;
        $data['po_line_unreads'] = $po_line_unreads;
        $data['arr_po_line_status'] = $arr_po_line_status;

        return view('vendor.backpack.crud.purchase-order-show', $data);
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
}
