<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ConfigurationRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constant;
use App\Models\Configuration;

class ConfigurationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Configuration::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/configuration');
        CRUD::setEntityNameStrings('configuration', 'configurations');
        if(Constant::checkPermission('Read Configuration')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
    }

  
    protected function setupListOperation()
    {
        $this->crud->removeButton('show');
        $this->crud->removeButton('create');
        $this->crud->removeButton('delete');
        if(!Constant::checkPermission('Update Configuration')){
            $this->crud->removeButton('update');
        }

        CRUD::column('label');
        CRUD::column('value');
    }

 
    protected function setupCreateOperation()
    {
        if(!Constant::checkPermission('Create Configuration')){
            $this->crud->denyAccess('create');
        }

        CRUD::setValidation(ConfigurationRequest::class);
        $this->crud->addField(
            [
                'name'  => 'label',
                'type'  => 'text',
                'label' => 'Label',
                'attributes' => [
                    'readonly'    => 'readonly',
                    'disabled'    => 'disabled',
                ], 
            ]);
        CRUD::field('value');
    }

    
    protected function setupUpdateOperation()
    {
        if(!Constant::checkPermission('Update Configuration')){
            $this->crud->denyAccess('update');
        }
        $this->setupCreateOperation();
    }


    public function exportDb()
    {
        $filename = asset('docs/'.date('YmdHis').'.sql');
        DB::unprepared(file_get_contents($filename));
        
        return $filename;
    }
}
