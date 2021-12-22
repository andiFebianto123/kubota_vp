<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliverySerialRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class DeliverySerialCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DeliverySerialCrudController extends CrudController
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
        CRUD::setModel(\App\Models\DeliverySerial::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery-serial');
        CRUD::setEntityNameStrings('delivery serial', 'delivery serials');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->removeButton('show');
        $this->crud->removeButton('create');
        $this->crud->removeButton('delete');

        CRUD::column('id')->label('ID');
        CRUD::column('ds_num')->label('DS Num');
        CRUD::column('ds_line')->label('DS Line');
        CRUD::column('ds_detail')->label('DS Detail');
        CRUD::addColumn([
            'label'     => 'PO', // Table column heading
            'name'      => 'po_po_line', // the column that contains the ID of that connected entity;
            'type'     => 'closure',
            'function' => function($entry) {
                $val = $entry->delivery->po_num."-".$entry->delivery->po_line;
                return $val;
            }
        ]);
        CRUD::column('no_mesin')->label('No Mesin');
        CRUD::addColumn([
            'label'     => 'Created By', // Table column heading
            'name'      => 'created_by', // the column that contains the ID of that connected entity;
            'entity'    => 'userCreate', 
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addColumn([
            'label'     => 'Updated By', // Table column heading
            'name'      => 'updated_by', // the column that contains the ID of that connected entity;
            'entity'    => 'userUpdate', 
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
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
        CRUD::setValidation(DeliverySerialRequest::class);

        CRUD::field('no_mesin');
        $this->crud->addField([
            'name'  => 'updated_by', 
            'type'  => 'hidden', 
            'value' => backpack_auth()->user()->id
        ]);

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
