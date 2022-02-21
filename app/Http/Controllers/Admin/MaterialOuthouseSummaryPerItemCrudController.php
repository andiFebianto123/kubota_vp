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
        $this->crud->query = $this->crud->query->select('material_outhouse.id as id', 'material_outhouse.po_num as po_num', 
        'material_outhouse.po_num as po_line','lot_qty', 'po.vend_num', 'matl_item', 'material_outhouse.description',
            DB::raw("(SUM(lot_qty) - IFNULL((SELECT SUM(issue_qty) FROM issued_material_outhouse imo WHERE imo.matl_item = material_outhouse.matl_item), 0)) AS remaining_qty")
        );
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
        $this->crud->query->join('po', function($join){
            $join->on('material_outhouse.po_num', '=', 'po.po_num');
        });
        $this->crud->addClause(
            'join',
            'po_line',
            function ($query) {
                $query->on('material_outhouse.po_num', '=', 'po_line.po_num')
                ->on('material_outhouse.po_line', '=', 'po_line.po_line')
                ->where('po_line.status', '=', 'O');
            }
        );
        $this->crud->groupBy('material_outhouse.matl_item');
        $this->crud->query->having('remaining_qty', '>', 0);

        if(Constant::getRole() == 'Admin PTKI'){
            CRUD::column('vend_num')->label('Vend Num');
        }
        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }

        CRUD::column('matl_item')->label('Matl Item');
        CRUD::column('description');
        CRUD::column('remaining_qty')->label('Available Material');

    }
   
}
