<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use App\Models\MaterialOuthouseSummaryPerItem;
use Illuminate\Support\Facades\DB;


class MaterialOuthouseSummaryPerItemCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


    public function setup()
    {
        CRUD::setModel(MaterialOuthouseSummaryPerItem::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material-outhouse-summary-per-item');
        CRUD::setEntityNameStrings('material outhouse summary', 'mo per item');
        
        $sql = "(
            (SELECT sum(lot_qty) FROM material_outhouse mo 
            JOIN po as po1 ON (po1.po_num = mo.po_num) 
            JOIN po_line ON (po_line.po_num = mo.po_num AND po_line.po_line = mo.po_line) 
            WHERE mo.matl_item = material_outhouse.matl_item 
            AND po.vend_num = po1.vend_num
            AND po_line.status = 'O'
            ) -
            (IFNULL((SELECT SUM(issue_qty) FROM issued_material_outhouse imo 
            JOIN delivery ON (delivery.ds_num = imo.ds_num AND delivery.ds_line = imo.ds_line)
            JOIN po as po1 ON (po1.po_num = delivery.po_num) 
            JOIN po_line ON (po_line.po_num = delivery.po_num AND po_line.po_line = delivery.po_line) 
            WHERE imo.matl_item = material_outhouse.matl_item 
            AND po.vend_num = po1.vend_num
            AND po_line.status = 'O'
            ), 0))
            ) AS mavailable_material";

        $this->crud->query = $this->crud->query->select(
            'material_outhouse.id as id', 
            'material_outhouse.po_num as po_num', 
            'material_outhouse.po_line as po_line',
            'lot_qty', 
            'po.vend_num', 
            'matl_item', 
            'material_outhouse.description',
            'pl.status',
            DB::raw($sql)
        );
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
        $this->crud->query->join('po', function($join){
            $join->on('material_outhouse.po_num', '=', 'po.po_num');
        });

        $this->crud->query->join('po_line as pl', function($join){
            $join->on('material_outhouse.po_num', '=', 'pl.po_num');
            $join->on('material_outhouse.po_line', '=', 'pl.po_line');
        });

        $this->crud->groupBy('matl_item');
        $this->crud->groupBy('pl.status');
        $this->crud->addClause("where", "pl.status", "O");

        if(Constant::getRole() == 'Admin PTKI'){
            CRUD::column('vend_num')->label('Vend Num');
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor'
            ],
            url('admin/filter-vendor/ajax-itempo-options'),
            function($value) { 
                $this->crud->addClause('where', 'vend_num', $value);
            });
        }
        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }

        CRUD::column('matl_item')->label('Matl Item');
        CRUD::column('description');
        CRUD::column('mavailable_material')->label('Available Material');
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
