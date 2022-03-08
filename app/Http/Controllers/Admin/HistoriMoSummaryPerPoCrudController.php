<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use App\Models\MaterialOuthouseSummaryPerPo;
use App\Models\IssuedMaterialOuthouse;
use App\Models\MaterialOuthouse;

/**
 * Class HistoriMoSummaryPerPoCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class HistoriMoSummaryPerPoCrudController extends CrudController
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/histori-mo-summary-per-po');
        CRUD::setEntityNameStrings('histori mo summary per po', 'Summary MO History per PO');

        $sql = "(SELECT SUM(issue_qty) FROM issued_material_outhouse imo 
                    WHERE imo.ds_num = delivery.ds_num AND imo.ds_line = delivery.ds_line
                    ) AS sum_qty_order";
        
        $this->crud->query = $this->crud->query->select(
            'issued_material_outhouse.id as id', 
            'delivery.po_num as po_num', 
            'delivery.po_line as po_line',
            'delivery.u_m', 
            'delivery.description', 
            'delivery.shipped_date', 
            'po.vend_num', 
            DB::raw($sql)
        );
        
        $this->crud->query->join('delivery', function($join){
            $join->on('issued_material_outhouse.ds_num', '=', 'delivery.ds_num');
            $join->on('issued_material_outhouse.ds_line', '=', 'delivery.ds_line');
        });
        $this->crud->query->join('po', function($join){
            $join->on('delivery.po_num', '=', 'delivery.po_num');
        });

        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }

        $this->crud->query->groupBy('delivery.po_num');

        if(Constant::checkPermission('Read History Summary MO')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }

        $this->crud->addColumn([
            'type'           => 'checkbox_mopo',
            'name'           => 'bulk_actions',
            'label'          => ' <input type="checkbox" class="crud_bulk_actions_main_checkbox" style="width: 16px; height: 16px;" />',
            'searchLogic'    => false,
            'orderable'      => false,
            'visibleInModal' => false,
        ]);
        $this->crud->enableDetailsRow();
    }

    public function create(){
        return abort(404);
    }

    public function edit(){
        return abort(404);
    }

    public function show(){
        return abort(404);
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

        CRUD::column('po_num')->label('PO Number');
        CRUD::column('description');
        CRUD::column('sum_qty_order')->label('Qty Used');
        CRUD::column('u_m')->label('UM');
        CRUD::column('shipped_date')->label('Shipped Date');
        $this->crud->addFilter([
            'type'  => 'date_range_hmo',
            'name'  => 'shipped_date',
            'label' => 'Date range'
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            session()->flash('filter_due_date', $value);
            $dates = json_decode($value);
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
        // CRUD::setValidation(HistoriMoSummaryPerPoRequest::class);

        

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


    public function showDetailsRow($id)
    {
        // $this->crud->hasAccessOrFail('details_row');
        $entry = $this->crud->getEntry($id);

        $this->data['entry'] = $entry;
        $this->data['crud'] = $this->crud;

        $filters = [];
        if (session()->has('filter_due_date')) {
            $due_date = session()->get('filter_due_date');
            $due_date_d = json_decode($due_date);
            $filters[] = ['delivery.shipped_date', '>=', $due_date_d->from];
            $filters[] = ['delivery.shipped_date', '<=', $due_date_d->to . ' 23:59:59'];
        }

        $delivery = Delivery::where('ds_num', $entry->ds_num)
                    ->where('ds_line', $entry->ds_line)
                    ->first();

        $data_materials = IssuedMaterialOuthouse::join('delivery', function($join){
                                $join->on('issued_material_outhouse.ds_num', '=', 'delivery.ds_num');
                                $join->on('issued_material_outhouse.ds_line', '=', 'delivery.ds_line');
                            })
                            ->where('delivery.po_num', $delivery->po_num)
                            ->where($filters)
                            ->groupBy('issued_material_outhouse.matl_item')
                            ->get(['issued_material_outhouse.id', 'issued_material_outhouse.matl_item', 
                            'issued_material_outhouse.description', 'issued_material_outhouse.issue_qty', 
                            'delivery.due_date'
                            ]);

        $this->data['data_materials'] = $data_materials;
        return view('crud::details_row', $this->data);
    }
}
