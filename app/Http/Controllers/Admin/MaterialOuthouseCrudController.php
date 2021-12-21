<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MaterialOuthouseRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MaterialOuthouseCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MaterialOuthouseCrudController extends CrudController
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
        CRUD::setModel(\App\Models\MaterialOuthouse::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material-outhouse');
        CRUD::setEntityNameStrings('material outhouse', 'material outhouses');
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
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('create');

        CRUD::column('id')->label('ID');
        CRUD::column('instruction_num')->label('Instruction Num');
        CRUD::column('po_num')->label('PO Num');
        CRUD::column('po_line')->label('PO Line');
        CRUD::column('seq');
        CRUD::column('matl_item')->label('Matl Item');
        CRUD::column('description');
        CRUD::column('lot_seq')->label('Lot Seq');
        CRUD::column('lot');
        CRUD::column('lot_qty')->label('Lot Qty');

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
        CRUD::setValidation(MaterialOuthouseRequest::class);

        CRUD::field('id');
        CRUD::field('instruction_num');
        CRUD::field('po_num');
        CRUD::field('po_line');
        CRUD::field('seq');
        CRUD::field('matl_item');
        CRUD::field('description');
        CRUD::field('lot_seq');
        CRUD::field('lot');
        CRUD::field('lot_qty');
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
