<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MaterialOuthouseRequest;
use App\Models\MaterialOuthouse;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;


class MaterialOuthouseCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(MaterialOuthouse::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material-outhouse');
        CRUD::setEntityNameStrings('material outhouse', 'material outhouses');
    }

    
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

    }

 
    protected function setupCreateOperation()
    {
        $this->crud->denyAccess('create');
    }

    
    protected function setupUpdateOperation()
    {
        $this->crud->denyAccess('update');
    }


    protected function setupShowOperation()
    {
        $this->crud->denyAccess('show');
    }
}
