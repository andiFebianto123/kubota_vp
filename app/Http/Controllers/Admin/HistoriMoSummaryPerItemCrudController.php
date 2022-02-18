<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\HistoriMoSummaryPerItemRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use Illuminate\Support\Facades\DB;

/**
 * Class HistoriMoSummaryPerItemCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class HistoriMoSummaryPerItemCrudController extends CrudController
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/histori-mo-summary-per-item');
        CRUD::setEntityNameStrings('histori mo summary per item', 'Summary MO History Per Item');
        $this->crud->query = $this->crud->query->select('material_outhouse.id as id', 'material_outhouse.po_num as po_num', 
        'material_outhouse.po_num as po_line','lot_qty', 'po.vend_num', 'matl_item', 'material_outhouse.description','pl.status',
            DB::raw("(SUM(lot_qty) - IFNULL((SELECT SUM(issue_qty) FROM issued_material_outhouse imo WHERE imo.matl_item = material_outhouse.matl_item), 0)) AS remaining_qty")
        );
        if(Constant::checkPermission('Read History Summary MO')){
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
        $this->crud->query->join('po_line as pl', function($join){
            $join->on('material_outhouse.po_num', '=', 'pl.po_num');
            $join->on('material_outhouse.po_line', '=', 'pl.po_line');
            // ->where('pl.status', '=', 'O');
        });
        if(in_array(Constant::getRole(), ['Marketing Vendor', 'Finance Vendor', 'Warehouse Vendor'])){
            $this->crud->addClause('where', 'vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }
        $this->crud->groupBy('material_outhouse.matl_item');
        $this->crud->query->havingRaw("(`pl`.`status` = 'C' or `pl`.`status` = 'F') or (`pl`.`status` = 'O' AND remaining_qty = 0)");

        if(Constant::getRole() == 'Admin PTKI'){
            CRUD::column('vend_num')->label('Vend Num');
        }
        

        CRUD::column('matl_item')->label('Matl Item');
        CRUD::column('description');
        CRUD::column('remaining_qty')->label('Available Material');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(HistoriMoSummaryPerItemRequest::class);

        

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
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
