<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\GeneralMessageRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use App\Helpers\Constant;

/**
 * Class GeneralMessageCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class GeneralMessageCrudController extends CrudController
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
        CRUD::setModel(\App\Models\GeneralMessage::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/general-message');
        CRUD::setEntityNameStrings('general message', 'general messages');
        if(Constant::checkPermission('Read General Message')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
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
