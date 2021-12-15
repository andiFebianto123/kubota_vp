<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ConfigurationRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

/**
 * Class ConfigurationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ConfigurationCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Configuration::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/configuration');
        CRUD::setEntityNameStrings('configuration', 'configurations');
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

        CRUD::column('label');
        CRUD::column('value');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
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

    public function exportDb()
    {
        $filename = asset('docs/'.date('YmdHis').'.sql');
        DB::unprepared(file_get_contents($filename));
        
        return $filename;
    }
}
