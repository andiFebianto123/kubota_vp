<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliveryStatusRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class DeliveryStatusCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DeliveryStatusCrudController extends CrudController
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
        CRUD::setModel(\App\Models\DeliveryStatus::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery-status');
        CRUD::setEntityNameStrings('delivery status', 'delivery statuses');
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
        $this->crud->removeButton('delete');
        $this->crud->removeButton('show');

        CRUD::column('id');
        CRUD::column('ds_num');
        CRUD::column('ds_line');
        CRUD::column('ds_type');
        CRUD::column('po_line_id');
        CRUD::column('po_release');
        CRUD::column('description');
        CRUD::column('grn_num');
        CRUD::column('grn_line');
        CRUD::column('received_flag');
        CRUD::column('received_date');
        CRUD::column('payment_plan_date');
        CRUD::column('payment_in_process_flag');
        CRUD::column('executed_flag');
        CRUD::column('payment_date');
        CRUD::column('tax_status');
        CRUD::column('payment_ref_num');
        CRUD::column('bank');
        CRUD::column('shipped_qty');
        CRUD::column('received_qty');
        CRUD::column('rejected_qty');
        CRUD::column('unit_price');
        CRUD::column('total');
        CRUD::column('petugas_vendor');
        CRUD::column('no_faktur_pajak');
        CRUD::column('no_surat_jalan_vendor');
        CRUD::column('ref_ds_num');
        CRUD::column('ref_ds_line');
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
        CRUD::setValidation(DeliveryStatusRequest::class);

        CRUD::field('id');
        CRUD::field('ds_num');
        CRUD::field('ds_line');
        CRUD::field('ds_type');
        CRUD::field('po_line_id');
        CRUD::field('po_release');
        CRUD::field('description');
        CRUD::field('grn_num');
        CRUD::field('grn_line');
        CRUD::field('received_flag');
        CRUD::field('received_date');
        CRUD::field('payment_plan_date');
        CRUD::field('payment_in_process_flag');
        CRUD::field('executed_flag');
        CRUD::field('payment_date');
        CRUD::field('tax_status');
        CRUD::field('payment_ref_num');
        CRUD::field('bank');
        CRUD::field('shipped_qty');
        CRUD::field('received_qty');
        CRUD::field('rejected_qty');
        CRUD::field('unit_price');
        CRUD::field('total');
        CRUD::field('petugas_vendor');
        CRUD::field('no_faktur_pajak');
        CRUD::field('no_surat_jalan_vendor');
        CRUD::field('ref_ds_num');
        CRUD::field('ref_ds_line');
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
}
