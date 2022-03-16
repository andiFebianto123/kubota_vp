<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PermissionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;


class PermissionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;


    public function setup()
    {
        CRUD::setModel(\App\Models\Permission::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/permission');
        CRUD::setEntityNameStrings('permission', 'permissions');
        if(Constant::checkPermission('Read Permission')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
    }

 
    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('name')->label('Permission Name');
        CRUD::addColumn([
            'label' => 'Description',
            'name' => 'description',
            'type' => 'text',
            'limit'  => 400,
        ]);
        
    }

   
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PermissionRequest::class);

        CRUD::field('id');
        CRUD::field('name');
        CRUD::field('guard_name');
        CRUD::field('description');
        CRUD::field('created_at');
        CRUD::field('updated_at');
    }

 
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
