<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;

/**
 * Class MaterialOuthouseCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MaterialOuthouseSummaryPerItemCrudController extends CrudController
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
        CRUD::setModel(\App\Models\MaterialOuthouseSummaryPerItem::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material-outhouse-summary-per-item');
        CRUD::setEntityNameStrings('material outhouse summary', 'mo per item');

        if(Constant::checkPermission('Read Summary MO')){
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
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('create');
        // $this->crud->addClause('join', 'po_line', 'po_line', 'po_line.po_line');
        // $this->crud->addClause('join', 'po_line', 'po_num', 'po_line.po_num');
        $this->crud->addClause(
            'join',
            'po_line',
            function ($query) {
                $query->on('material_outhouse.po_num', '=', 'po_line.po_num')
                ->on('material_outhouse.po_line', '=', 'po_line.po_line')
                ->where('po_line.status', '=', 'O');
            }
        );

        // CRUD::column('id')->label('ID');;
        CRUD::column('matl_item')->label('Matl Item');
        CRUD::column('description');
        CRUD::column('po_num');
        CRUD::column('lot_qty')->label('Qty Dikirim');
        CRUD::column('qty_issued')->label('Qty Processed');
        CRUD::column('remaining_qty')->label('Remaining Qty');

    }
   
}
