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
            'pl.due_date', 
            'material_outhouse.description'
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
            $join->on('material_outhouse.po_line', '=', 'pl.po_line')
            ->where('pl.status', '=', 'O');
        });
        $this->crud->query->join('po', function($join){
            $join->on('material_outhouse.po_num', '=', 'po.po_num');
        });
        if(in_array(Constant::getRole(), ['Marketing Vendor', 'Finance Vendor', 'Warehouse Vendor'])){
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }

        $this->crud->groupBy('material_outhouse.po_num');
        $this->crud->groupBy('material_outhouse.po_line');
        // $this->crud->query->having('remaining_qty', '>', 0);

        // $this->crud->groupBy('material_outhouse.matl_item');
        // dd($this->crud->query->get());
        // if(Constant::getRole() == 'Admin PTKI'){
        //     CRUD::column('vend_num')->label('Vend Num');
        // }

        CRUD::column('po_num_line')->label('PO Number');
        // CRUD::column('status')->label('Status');
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
        CRUD::column('remaining_qty2')->label('Available Material');
        CRUD::column('u_m')->label('UM');
        CRUD::column('due_date')->label('Due Date');
        $this->crud->setListView('crud::list-mo-po');
    }

    public function showDetailsRow($id)
    {
        // $this->crud->hasAccessOrFail('details_row');

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;

        $dataDetailMaterial = MaterialOuthouseSummaryPerPo::join('po_line as pl', function($join){
            $join->on('material_outhouse.po_num', '=', 'pl.po_num');
            $join->on('material_outhouse.po_line', '=', 'pl.po_line')
            ->where('pl.status', '=', 'O');
        });
        $dataDetailMaterial->join('po', function($join){
            $join->on('material_outhouse.po_num', '=', 'po.po_num');
        });
        if(in_array(Constant::getRole(), ['Marketing Vendor', 'Finance Vendor', 'Warehouse Vendor'])){
            $dataDetailMaterial->where('vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }
        $dataDetailMaterial->where('material_outhouse.po_num', '=', $this->data['entry']->po_num)
        ->where('material_outhouse.po_line', '=', $this->data['entry']->po_line);
        $dataDetailMaterial->select(
            'material_outhouse.po_line',
            'material_outhouse.matl_item', 
            'material_outhouse.description', 
            'material_outhouse.lot_qty as jumlah_lot_qty',
            DB::raw("(SUM(material_outhouse.lot_qty) - IFNULL((
                SELECT SUM(issue_qty) FROM issued_material_outhouse 
                LEFT JOIN delivery ON delivery.ds_num = issued_material_outhouse.ds_num
                WHERE delivery.po_num = material_outhouse.po_num AND
                delivery.po_line = material_outhouse.po_line AND
    			issued_material_outhouse.matl_item = material_outhouse.matl_item
            ), 0)) as availabel_qty")
        )->groupBy("material_outhouse.matl_item");
        $data_materials = MaterialOuthouse::where('po_num', $this->data['entry']->po_num)
                            ->where('po_line', $this->data['entry']->po_line)
                            ->get();
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
