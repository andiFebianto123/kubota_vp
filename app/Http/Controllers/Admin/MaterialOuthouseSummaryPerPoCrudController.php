<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use Illuminate\Support\Facades\DB;


class MaterialOuthouseSummaryPerPoCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


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
        $this->crud->addColumn([
            'type'           => 'checkbox_mopo',
            'name'           => 'bulk_actions',
            'label'          => '<input type="checkbox" class="crud_bulk_actions_main_checkbox" style="width: 16px; height: 16px;" />',
            'searchLogic'    => false,
            'orderable'      => false,
            'visibleInModal' => false,
        ]);
        $this->crud->enableDetailsRow();
        if(Constant::checkPermission('Read Summary MO')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
    }


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
        $this->crud->addClause('where', 'pl.status', '=', 'O');
        $this->crud->groupBy('material_outhouse.po_num');
        $this->crud->groupBy('material_outhouse.po_line');

        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }

        CRUD::column('po_num')->label('PO Number');
        CRUD::column('po_line')->label('PO Line');
        CRUD::addColumn([
            'label'     => 'Status', 
            'name'      => 'status',
            'type' => 'closure',
            'function' => function($entry) {
                if($entry->status == 'O'){
                    return 'Ordered';
                }
            },
            'searchLogic' => function ($query, $column, $searchTerm) {
                if ($column['name'] == 'status') {
                    $rest = substr($searchTerm, 0, 1);
                    $query->orWhere('pl.status', 'like', '%'.$rest.'%');
                }
            },
        ]);
        CRUD::addColumn([
            'label'     => 'Description', 
            'name'      => 'description',
            'type' => 'text',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('pl.description', 'like', '%'.$searchTerm.'%');
            },
        ]);
        CRUD::column('order_qty')->label('Qty Order');
        CRUD::column('u_m')->label('UM');
        CRUD::addColumn([
            'name'  => 'due_date',
            'label' => 'Due Date', 
            'type' => 'closure',
            'orderable'  => true, 
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->orderBy('pl.due_date', $columnDirection);
            },
            'function' => function($entry) {
                return date('Y-m-d', strtotime($entry->due_date));
            },
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('pl.due_date', 'like', '%'.$searchTerm.'%');
            },
        ]);

        $this->crud->setListView('crud::list_mo_po');
    }


    public function showDetailsRow($id)
    {
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;

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
                    AND delivery.ds_type != '0R'
                    AND delivery.po_num = '".$this->data['entry']->po_num."'
                    AND delivery.po_line = '".$this->data['entry']->po_line."'
                    ), 0))
                ) AS m_available_qty                
                FROM material_outhouse mo1 
                WHERE mo1.po_num = '".$this->data['entry']->po_num."' 
                AND mo1.po_line = '".$this->data['entry']->po_line."' 
                GROUP BY mo1.matl_item";

        $data_materials = DB::select($sql);

        $this->data['data_materials'] = $data_materials;

        return view('crud::details_row', $this->data);
    }


    protected function setupCreateOperation()
    {
        $this->crud->denyAccess('create');
    }

    
    protected function setupUpdateOperation()
    {
        $this->crud->denyAccess('update');
    }


    protected function setupShowOperation()
    {
        $this->crud->denyAccess('show');
    }

}
