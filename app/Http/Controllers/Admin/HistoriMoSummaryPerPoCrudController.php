<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
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
        CRUD::setModel(\App\Models\MaterialOuthouseSummaryPerPo::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/histori-mo-summary-per-po');
        CRUD::setEntityNameStrings('histori mo summary per po', 'Summary MO History per PO');

        $sql = "(
            (SELECT sum(lot_qty) FROM material_outhouse mo WHERE mo.po_num = material_outhouse.po_num)  -
            (IFNULL((SELECT SUM(issue_qty) FROM issued_material_outhouse imo WHERE imo.ds_num IN (SELECT ds_num FROM delivery WHERE delivery.po_num = material_outhouse.po_num)), 0))
            ) AS mremaining_qty";
        
        $this->crud->query = $this->crud->query->select(
            'material_outhouse.id as id', 
            'material_outhouse.po_num as po_num', 
            'material_outhouse.po_line as po_line',
            'lot_qty', 
            'po.vend_num', 
            'pl.status' ,
            'matl_item', 
            'pl.u_m', 
            'pl.order_qty',
            'pl.due_date', 
            'pl.description', 
            DB::raw($sql)
        );

        // $sql =  "SELECT material_outhouse.matl_item, max(material_outhouse.description) as description, sum(material_outhouse.lot_qty) as lot_qty, (IFNULL((SELECT sum(issue_qty) FROM issued_material_outhouse WHERE issued_material_outhouse.matl_item = material_outhouse.matl_item), 0)) as issue_qty, (sum(material_outhouse.lot_qty) - IFNULL((SELECT sum(issue_qty) FROM issued_material_outhouse WHERE issued_material_outhouse.matl_item = material_outhouse.matl_item), 0)) as current_qty FROM `po_line` INNER JOIN material_outhouse on po_line.po_num = material_outhouse.po_num WHERE po_line.status = 'C' or po_line.status = 'F' GROUP BY material_outhouse.matl_item";
        
        // $this->crud->query = $this->crud->query->selectRaw($sql);

        // $this->crud->query->join('delivery as dl', function($join){
        //     $join->on('material_outhouse.po_num', '=', 'dl.po_num');
        //     $join->on('material_outhouse.po_line', '=', 'dl.po_line');
        //     // ->where('pl.status', '=', 'O');
        // });
        $this->crud->query->join('po', function($join){
            $join->on('material_outhouse.po_num', '=', 'po.po_num');
        });
        $this->crud->query->join('po_line as pl', function($join){
            $join->on('material_outhouse.po_num', '=', 'pl.po_num');
        });

        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }
        // $this->crud->query->whereRaw("(`pl`.`status` = 'C' or `pl`.`status` = 'F') or 
        //     (`pl`.`status` = 'O' AND test_qty = 600)"
        // );

        $this->crud->query->groupBy('material_outhouse.po_num');

        // $this->crud->query->havingRaw("(`pl`.`status` = 'C' or `pl`.`status` = 'F') or (`pl`.`status` = 'O' and remaining_qty <= 0)");
        $this->crud->query->havingRaw("(`pl`.`status` = 'C' or `pl`.`status` = 'F') or (`pl`.`status` = 'O' and mremaining_qty <= 0)");

        if(Constant::checkPermission('Read History Summary MO')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
        // $this->crud->enableBulkActions();
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

        // $this->crud->query->having('remaining_qty', '>', 0);

        CRUD::column('po_num')->label('PO Number');
        // CRUD::column('mremaining_qty')->label('sPO Line');
        // CRUD::column('summy')->label('Summy');
        // CRUD::column('po_num_line')->label('PO Number');
        // CRUD::column('status')->label('Status');
        // CRUD::addColumn([
        //     'label'     => 'Status', // Table column heading
        //     'name'      => 'status', // the column that contains the ID of that connected entity;
        //     'type' => 'closure',
        //     'function' => function($entry) {
        //         if($entry->status == 'O'){
        //             return 'Ordered';
        //         }else if($entry->status == 'F'){
        //             return 'Filled';
        //         }else if($entry->status == 'C'){
        //             return 'Complete';
        //         }
        //     }
        // ]);

        // CRUD::column('matl_item')->label('Item');
        CRUD::column('description');
        CRUD::column('order_qty')->label('Qty Order');
        // CRUD::column('mremaining_qty')->label('mr Order');
        // CRUD::column('available_qty')->label('aq');
        CRUD::column('u_m')->label('UM');
        CRUD::column('due_date')->label('Due Date');
        $this->crud->addFilter([
            'type'  => 'date_range_hmo',
            'name'  => 'due_date',
            'label' => 'Date range'
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            session()->flash('filter_due_date', $value);
            $dates = json_decode($value);
            $this->crud->addClause('where', 'pl.due_date', '>=', $dates->from);
            $this->crud->addClause('where', 'pl.due_date', '<=', $dates->to . ' 23:59:59');
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

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;

        $filters = [];
        if (session()->has('filter_due_date')) {
            $due_date = session()->get('filter_due_date');
            $due_date_d = json_decode($due_date);
            $filters[] = ['pl.due_date', '>=', $due_date_d->from];
            $filters[] = ['pl.due_date', '<=', $due_date_d->to . ' 23:59:59'];
        }

        $dataDetailMaterial = MaterialOuthouseSummaryPerPo::join('po_line as pl', function($join){
            $join->on('material_outhouse.po_num', '=', 'pl.po_num');
            $join->on('material_outhouse.po_line', '=', 'pl.po_line');
        });
        $dataDetailMaterial->join('po', function($join){
            $join->on('material_outhouse.po_num', '=', 'po.po_num');
        });
        
        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $dataDetailMaterial->where('vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }

        $dataDetailMaterial->where('material_outhouse.po_num', '=', $this->data['entry']->po_num)
        ->where('material_outhouse.po_line', '=', $this->data['entry']->po_line)
        ->select(
            'material_outhouse.po_line',
            'material_outhouse.matl_item', 
            'material_outhouse.description', 
            'material_outhouse.lot_qty as jumlah_lot_qty'
        )->groupBy("material_outhouse.matl_item");

        $data_materials = MaterialOuthouse::join('po_line as pl', function($join){
                                $join->on('material_outhouse.po_num', '=', 'pl.po_num');
                                $join->on('material_outhouse.po_line', '=', 'pl.po_line');
                            })
                            ->where('material_outhouse.po_num', $this->data['entry']->po_num)
                            ->where('material_outhouse.po_line', $this->data['entry']->po_line)
                            ->where($filters)
                            ->groupBy('matl_item')
                            ->get();

        // $this->data['due_date'] = $filters;
        $this->data['data_materials'] = $data_materials;
                            // $qty_issued = IssuedMaterialOuthouse::leftJoin('delivery', 'delivery.ds_num', 'issued_material_outhouse.ds_num')
        //                 ->where('delivery.po_num', $this->data['entry']->po_num)
        //                 ->where('delivery.po_line', $this->data['entry']->po_line)
        //                 ->sum('issue_qty');
        // $this->data['issued_qty'] = $qty_issued;

        // dd($this->data['data_materials']);

        // // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::details_row', $this->data);
    }
}
