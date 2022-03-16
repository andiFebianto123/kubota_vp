<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\GeneralMessageRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use App\Models\GeneralMessage;

class GeneralMessageCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(GeneralMessage::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/general-message');
        CRUD::setEntityNameStrings('general message', 'general messages');
        if(Constant::checkPermission('Read General Message')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
    }
    

    protected function setupListOperation()
    {
        $this->crud->removeButton('show');
        if(!Constant::checkPermission('Update General Message')){
            $this->crud->removeButton('update');
        }
        if(!Constant::checkPermission('Create General Message')){
            $this->crud->removeButton('create');
        }
        if(!Constant::checkPermission('Delete General Message')){
            $this->crud->removeButton('delete');
        }
        
        CRUD::column('title');
        CRUD::column('content');
        CRUD::column('category');
    }


    protected function setupCreateOperation()
    {
        if(!Constant::checkPermission('Create General Message')){
            $this->crud->denyAccess('create');
        }
        CRUD::setValidation(GeneralMessageRequest::class);
        $this->crud->addField([
            'name'            => 'category',
            'label'           => 'Category',
            'type'            => 'select_from_array',
            'options'         => ['help' => 'Help', 'information' => 'Information'],
        ]);
        $this->crud->addField([
            'label'     => 'Title', // Table column heading
            'name'      => 'title', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        $this->crud->addField([
            'label'     => 'Content', // Table column heading
            'name'      => 'content', // the column that contains the ID of that connected entity;
            'type' => 'tinymce',
        ]);
    }


    protected function setupUpdateOperation()
    {
        if(!Constant::checkPermission('Update General Message')){
            $this->crud->denyAccess('update');
        }
        $this->setupCreateOperation();
    }

}
