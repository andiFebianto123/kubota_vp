<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\HistoriMoSummaryPerItemRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use App\Models\IssuedMaterialOuthouse;
use Illuminate\Support\Facades\DB;


class HistoryMoSummaryPerItemCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(IssuedMaterialOuthouse::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/history-mo-summary-per-item');
        CRUD::setEntityNameStrings('histori mo summary per item', 'Summary MO History Per Item');

        $firstDate = date('Y-m-d',strtotime('first day of this month'));
        $startDate = $firstDate;
        $endDate = now();
        
        if (request('shipped_date')) {
            $dueDate = request('shipped_date');
            $dueDateD = json_decode($dueDate);
            $startDate = $dueDateD->from;
            $endDate = $dueDateD->to;
        }

        $sql = "(SELECT SUM(issue_qty) FROM issued_material_outhouse imo 
                JOIN delivery ON (delivery.ds_num = imo.ds_num AND delivery.ds_line = imo.ds_line)
                WHERE imo.matl_item = issued_material_outhouse.matl_item 
                AND (delivery.shipped_date >= '".$startDate."' 
                AND delivery.shipped_date <= '".$endDate." 23:59:59')
                AND delivery.ds_type != '0R'
                ) AS sum_qty_total";

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

    
    protected function setupListOperation()
    {
        $this->crud->removeButton('show');
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('create');

        $firstDate = date('Y-m-d',strtotime('first day of this month'));
        
        if (!request('shipped_date')) {
            $this->crud->addClause('where', 'delivery.shipped_date', '>=', $firstDate);
            $this->crud->addClause('where', 'delivery.shipped_date', '<=', now() . ' 23:59:59');
        }
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
            url('admin/filter-vendor/ajax-itempo-options'),
            function($value) { 
                $this->crud->addClause('where', 'vend_num', $value);
            });
        }else{
            $this->crud->addClause('where', 'vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }
        if(Constant::getRole() == 'Admin PTKI'){
            CRUD::column('vend_num')->label('Vend Num');
        }

        CRUD::column('matl_item')->label('Matl Item');
        CRUD::column('description');
        CRUD::column('sum_qty_total')->label('Qty Total');
        $this->crud->addFilter([
            'type'  => 'date_range_hmo',
            'name'  => 'shipped_date',
            'label' => 'Date range'
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            $this->crud->addClause('where', 'delivery.shipped_date', '>=', $dates->from);
            $this->crud->addClause('where', 'delivery.shipped_date', '<=', $dates->to . ' 23:59:59');
        });
        $this->crud->groupBy('issued_material_outhouse.matl_item');

    }

    
    protected function setupCreateOperation()
    {
        $this->crud->denyAccess('create');
        CRUD::setValidation(HistoriMoSummaryPerItemRequest::class);
    }


    protected function setupUpdateOperation()
    {
        $this->crud->denyAccess('update');
        $this->setupCreateOperation();
    }


    protected function setupShowOperation()
    {
        $this->crud->denyAccess('show');
    }
}
