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
        CRUD::setModel(\App\Models\IssuedMaterialOuthouse::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/histori-mo-summary-per-item');
        CRUD::setEntityNameStrings('histori mo summary per item', 'Summary MO History Per Item');

        $sql_date = "";
        if (request('shipped_date')) {
            $due_date = request('shipped_date');
            $due_date_d = json_decode($due_date);

            $sql_date = "AND (delivery.shipped_date >= '".$due_date_d->from."' AND delivery.shipped_date <= '".$due_date_d->to." 23:59:59')";
        }

        $sql = "(SELECT SUM(issue_qty) FROM issued_material_outhouse imo 
                    JOIN delivery ON (delivery.ds_num = imo.ds_num AND delivery.ds_line = imo.ds_line)
                    WHERE imo.matl_item = issued_material_outhouse.matl_item 
                    ".$sql_date."
                    ) AS sum_qty_order";

        $this->crud->query = $this->crud->query->select(
            'issued_material_outhouse.id as id', 
            'issued_material_outhouse.matl_item', 
            'issued_material_outhouse.description', 
            'delivery.shipped_date', 
            'po.vend_num', 
            DB::raw($sql)
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

    public function create(){
        return abort(404);
    }

    public function edit(){
        return abort(404);
    }

    public function show(){
        return abort(404);
    }
    
    protected function setupListOperation()
    {
        $this->crud->removeButton('show');
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('create');

        $this->crud->query->join('delivery', function($join){
            $join->on('issued_material_outhouse.ds_num', '=', 'delivery.ds_num');
            $join->on('issued_material_outhouse.ds_line', '=', 'delivery.ds_line');
        });

        $this->crud->query->join('po', function($join){
            $join->on('delivery.po_num', '=', 'po.po_num');
        });

        if(in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor'
            ],
            url('admin/test/ajax-vendor-options'),
            function($value) { 
                $this->crud->addClause('where', 'vend_num', $value);
            });
        }else{
            $this->crud->addClause('where', 'vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }
        $this->crud->groupBy('issued_material_outhouse.matl_item');

        if(Constant::getRole() == 'Admin PTKI'){
            CRUD::column('vend_num')->label('Vend Num');
        }

        CRUD::column('matl_item')->label('Matl Item');
        CRUD::column('description');
        CRUD::column('sum_qty_order')->label('Qty Total');
        // CRUD::column('shipped_date')->label('Shipped Date');
        $this->crud->addFilter([
            'type'  => 'date_range_hmo',
            'name'  => 'shipped_date',
            'label' => 'Date range'
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            session()->flash('filter_due_date', $value);
            $this->crud->addClause('where', 'delivery.shipped_date', '>=', $dates->from);
            $this->crud->addClause('where', 'delivery.shipped_date', '<=', $dates->to . ' 23:59:59');
        });
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
