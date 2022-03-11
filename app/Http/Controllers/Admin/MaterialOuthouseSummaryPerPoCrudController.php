<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use Illuminate\Support\Facades\DB;
use App\Models\MaterialOuthouseSummaryPerPo;
use App\Models\IssuedMaterialOuthouse;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrder;

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
            'pl.description'
        );

        if(Constant::checkPermission('Read Summary MO')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
        // $this->crud->enableBulkActions();
        $this->crud->addColumn([
            'type'           => 'checkbox_mopo',
            'name'           => 'bulk_actions',
            'label'          => '<input type="checkbox" class="crud_bulk_actions_main_checkbox" style="width: 16px; height: 16px;" />',
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
        $this->crud->query->join('po_line as pl', function($join){
            $join->on('material_outhouse.po_num', '=', 'pl.po_num');
            $join->on('material_outhouse.po_line', '=', 'pl.po_line');
        });
        $this->crud->query->join('po', function($join){
            $join->on('material_outhouse.po_num', '=', 'po.po_num');
        });
        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }

        $this->crud->addClause('where', 'pl.status', '=', 'O');

        $this->crud->groupBy('material_outhouse.po_num');
        $this->crud->groupBy('material_outhouse.po_line');

        CRUD::column('po_num')->label('PO Number');
        CRUD::column('po_line')->label('PO Line');

        CRUD::addColumn([
            'label'     => 'Status', // Table column heading
            'name'      => 'status', // the column that contains the ID of that connected entity;
            'type' => 'closure',
            'function' => function($entry) {
                if($entry->status == 'O'){
                    return 'Ordered';
                }
            }
        ]);

        // CRUD::column('matl_item')->label('Item');
        CRUD::column('description');
        CRUD::column('order_qty')->label('Qty Order');
        CRUD::column('u_m')->label('UM');
        $this->crud->addColumn([
            'name'  => 'due_date', // The db column name
            'label' => 'Due Date', // Table column heading
            'type'  => 'date',
            'format' => 'Y-M-d'
        ]);

        $this->crud->setListView('crud::list-mo-po');
    }

    public function showDetailsRow($id)
    {
        // $this->crud->hasAccessOrFail('details_row');

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;

        $po = PurchaseOrder::where('po_num', $this->data['entry']->po_num)->first();

        // dd($this->data['entry']);

        $sql = "SELECT
                mo1.matl_item,
                mo1.description,                     
                ((SELECT SUM(lot_qty)  
                    FROM material_outhouse mo2
                    JOIN po ON po.po_num = mo2.po_num
                    WHERE mo2.matl_item = mo1.matl_item 
                    AND mo2.po_num = '".$this->data['entry']->po_num."'
                    AND mo2.po_line = '".$this->data['entry']->po_line."') -        
                    (IFNULL((SELECT SUM(issue_qty) FROM issued_material_outhouse imo                      
                    JOIN delivery 
                    ON (delivery.ds_num = imo.ds_num AND delivery.ds_line = imo.ds_line)  
                    JOIN po ON po.po_num = delivery.po_num
                    WHERE imo.matl_item = mo1.matl_item
                    AND delivery.po_num = '".$this->data['entry']->po_num."'
                    AND delivery.po_line = '".$this->data['entry']->po_line."'
                    ), 0))
                ) AS remaining_qty                
                FROM material_outhouse mo1 
                WHERE mo1.po_num = '".$this->data['entry']->po_num."' 
                AND mo1.po_line = '".$this->data['entry']->po_line."' 
                GROUP BY mo1.matl_item";

        $data_materials = DB::select($sql);

        // $data_materials = MaterialOuthouse::where('po_num', $this->data['entry']->po_num)
        //                     ->where('po_line', $this->data['entry']->po_line)
        //                     ->groupBy('matl_item')
        //                     ->get();
                            
        $this->data['data_materials'] = $data_materials;

        return view('crud::details_row', $this->data);
    }



}
