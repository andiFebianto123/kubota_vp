<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class MaterialOuthouseCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MaterialOuthouseSummaryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\MaterialOuthouseSummary::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material-outhouse-summary');
        CRUD::setEntityNameStrings('summary mo', 'summary mo');
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

        CRUD::column('id');
        CRUD::column('matl_item');
        CRUD::column('description');
        CRUD::column('lot_qty');
        CRUD::column('qty_issued');
        CRUD::column('remaining_qty');
       
        // CRUD::column([
        //     'name'     => 'remaining_qty',
        //     'label'    => 'Qty Remain',
        //     'type'     => 'closure',
        //     'function' => function($entry) {
        //         $qty_issued = IssuedMaterialOuthouse::where('matl_item', $entry->matl_item)->sum('qty_issued');
        //         $qty = $entry->lot_qty - $qty_issued;
        //         return $qty;
        //     }
        // ]);

    }

   
}
