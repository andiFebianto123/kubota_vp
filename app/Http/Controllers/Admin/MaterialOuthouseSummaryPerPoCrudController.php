<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use Illuminate\Support\Facades\DB;

/**
 * Class MaterialOuthouseCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MaterialOuthouseSummaryPerPoCrudController extends CrudController
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
        CRUD::setModel(\App\Models\MaterialOuthouseSummaryPerPo::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material-outhouse-summary-per-po');
        CRUD::setEntityNameStrings('material outhouse summary', 'mo per PO');
        $this->crud->query = $this->crud->query->select('material_outhouse.id as id', 'material_outhouse.po_num as po_num', 
        'material_outhouse.po_num as po_line','lot_qty'
        );

        if(Constant::checkPermission('Read Summary MO')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }

        // $this->crud->setListView('vendor.backpack.crud.list-mo-per-po');

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
        $this->crud->query->join('po_line as pl', function($join){
            $join->on('material_outhouse.po_num', '=', 'pl.po_num');
            $join->on('material_outhouse.po_line', '=', 'pl.po_line');
        });
        // $this->crud->addClause(
        //     'join',
        //     'po_line',
        //     function ($query) {
        //         $query->on('material_outhouse.po_num', '=', 'po_line.po_num')
        //         ->on('material_outhouse.po_line', '=', 'po_line.po_line')
        //         ->where('po_line.status', '=', 'O');
        //     }
        // );

        $this->crud->groupBy('material_outhouse.po_num');

        CRUD::column('po_num')->label('PO Num');
        // CRUD::column('matl_item')->label('Matl Item');
        // CRUD::column('description');
        CRUD::column('lot_qty')->label('Qty Dikirim');
        CRUD::column('qty_issued')->label('Qty Processed');
        CRUD::column('remaining_qty')->label('Remaining Qty');

    }
   
}
