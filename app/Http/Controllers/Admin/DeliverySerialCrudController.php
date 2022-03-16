<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliverySerialRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class DeliverySerialCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\DeliverySerial::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery-serial');
        CRUD::setEntityNameStrings('delivery serial', 'delivery serials');
    }


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
            'label'     => 'PO', 
            'name'      => 'po_po_line', 
            'type'     => 'closure',
            'function' => function($entry) {
                $val = $entry->delivery->po_num."-".$entry->delivery->po_line;
                return $val;
            }
        ]);
        CRUD::column('no_mesin')->label('No Mesin');
        CRUD::addColumn([
            'label'     => 'Created By', 
            'name'      => 'created_by', 
            'entity'    => 'userCreate', 
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::addColumn([
            'label'     => 'Updated By', 
            'name'      => 'updated_by', 
            'entity'    => 'userUpdate', 
            'type' => 'relationship',
            'attribute' => 'name',
        ]);
        CRUD::column('created_by');
        CRUD::column('updated_by');
        CRUD::column('created_at');
        CRUD::column('updated_at');
    }


    protected function setupCreateOperation()
    {
        $this->crud->denyAccess('create');

        CRUD::setValidation(DeliverySerialRequest::class);

        CRUD::field('no_mesin');
        $this->crud->addField([
            'name'  => 'updated_by', 
            'type'  => 'hidden', 
            'value' => backpack_auth()->user()->id
        ]);
    }
    

    protected function setupUpdateOperation()
    {
        $this->crud->denyAccess('update');

        $this->setupCreateOperation();
    }

    public function show()
    {
        $this->crud->denyAccess('show');
    }
}
