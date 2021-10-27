<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliveryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

/**
 * Class DeliveryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DeliveryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Delivery::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery');
        CRUD::setEntityNameStrings('delivery', 'deliveries');
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

        $this->crud->addButtonFromModelFunction('top', 'pdf_check', 'pdfCheck', 'beginning');
        $this->crud->addButtonFromModelFunction('top', 'pdf_export', 'pdfExport', 'end');

        $this->crud->enableBulkActions();

        CRUD::column('ds_num');
        // CRUD::addColumn([
        //     'label'     => 'PO', // Table column heading
        //     'name'      => 'purchaseOrder', // the column that contains the ID of that connected entity;
        //     'entity'    => 'vendor', 
        //     'type' => 'relationship',
        //     'attribute' => 'number',
        // ]);
        CRUD::addColumn([
            'label'     => 'Shipped Date', // Table column heading
            'name'      => 'shipped_date', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Order Qty', // Table column heading
            'name'      => 'order_qty', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Shipped Qty', // Table column heading
            'name'      => 'shipped_qty', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'DO Number', // Table column heading
            'name'      => 'no_surat_jalan_vendor', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Operator', // Table column heading
            'name'      => 'operator', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
       
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(DeliveryRequest::class);

        CRUD::field('id');
        CRUD::field('ds_num');
        CRUD::field('ds_type');
        CRUD::field('po_line');
        CRUD::field('po_release');
        CRUD::field('description');
        CRUD::field('order_qty');
        CRUD::field('w_serial');
        CRUD::field('u_m');
        CRUD::field('due_date');
        CRUD::field('unit_price');
        CRUD::field('wh');
        CRUD::field('location');
        CRUD::field('tax_status');
        CRUD::field('currency');
        CRUD::field('shipped_qty');
        CRUD::field('shipped_date');
        CRUD::field('petugas_vendor');
        CRUD::field('no_surat_jalan_vendor');
        CRUD::field('group_ds_num');
        CRUD::field('ref_ds_num');
        CRUD::field('ref_ds_line');
        CRUD::field('created_by');
        CRUD::field('updated_by');
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
       
        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['qr_code'] = "test";

        return view('vendor.backpack.crud.delivery-show', $data);
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
