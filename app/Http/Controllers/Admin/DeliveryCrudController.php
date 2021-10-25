<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliveryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
        CRUD::column('id');
        CRUD::column('ds_num');
        CRUD::column('ds_line');
        CRUD::column('ds_type');
        CRUD::column('po_line');
        CRUD::column('po_release');
        CRUD::column('description');
        CRUD::column('order_qty');
        CRUD::column('w_serial');
        CRUD::column('u_m');
        CRUD::column('due_date');
        CRUD::column('unit_price');
        CRUD::column('wh');
        CRUD::column('location');
        CRUD::column('tax_status');
        CRUD::column('currency');
        CRUD::column('shipped_qty');
        CRUD::column('shipped_date');
        CRUD::column('petugas_vendor');
        CRUD::column('no_surat_jalan_vendor');
        CRUD::column('group_ds_num');
        CRUD::column('ref_ds_num');
        CRUD::column('ref_ds_line');
        CRUD::column('created_by');
        CRUD::column('updated_by');
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
        CRUD::setValidation(DeliveryRequest::class);

        CRUD::field('id');
        CRUD::field('ds_num');
        CRUD::field('ds_line');
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
}
