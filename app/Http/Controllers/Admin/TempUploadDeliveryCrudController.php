<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TempUploadDeliveryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
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
        $this->crud->addButtonFromModelFunction('top', 'insert_db', 'insertToDB', 'beginning');
        $this->crud->addButtonFromModelFunction('top', 'cancel_db', 'cancelInsert', 'end');

        CRUD::addColumn([
            'label'     => 'Delivery Sheet Number', // Table column heading
            'name'      => 'ds_numb', // the column that contains the ID of that connected entity;
            'type'     => 'closure',
            'function' => function($entry) {
                return 'VSD000'.$entry->id;
            }
        ]);

        CRUD::addColumn([
            'label'     => 'Item Number', // Table column heading
            'name'      => 'po_line_id', // the column that contains the ID of that connected entity;
            'entity'    => 'purchaseOrderLine', 
            'type' => 'relationship',
            'attribute' => 'item',
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
}
