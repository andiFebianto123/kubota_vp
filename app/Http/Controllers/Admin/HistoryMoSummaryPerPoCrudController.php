<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use App\Models\Delivery;
use App\Models\IssuedMaterialOuthouse;
use Illuminate\Support\Facades\DB;


class HistoryMoSummaryPerPoCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(IssuedMaterialOuthouse::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/history-mo-summary-per-po');
        CRUD::setEntityNameStrings('histori mo summary per po', 'Summary MO History per PO');

        $firstDate = date('Y-m-d',strtotime('first day of this month'));
        $startDate = $firstDate;
        $endDate = now();
        
        if (request('shipped_date')) {
            $dueDate = request('shipped_date');
            $dueDateD = json_decode($dueDate);
            $startDate = $dueDateD->from;
            $endDate = $dueDateD->to;
        }

        $sql = "(SELECT SUM(order_qty) FROM delivery dlv
                WHERE delivery.po_num = dlv.po_num AND delivery.po_line = dlv.po_line
                AND (delivery.shipped_date >= '".$startDate."' 
                AND delivery.shipped_date <= '".$endDate." 23:59:59')
                AND dlv.ds_type != '0R'
                ) AS sum_qty_order";
        
        $this->crud->query = $this->crud->query->select(
            'issued_material_outhouse.id as id', 
            'delivery.po_num as po_num', 
            'delivery.po_line as po_line',
            'delivery.u_m', 
            'delivery.description', 
            'delivery.due_date', 
            'delivery.shipped_date', 
            'po.vend_num', 
            DB::raw($sql)
        );
        
        $this->crud->query->join('delivery', function($join){
            $join->on('issued_material_outhouse.ds_num', '=', 'delivery.ds_num');
            $join->on('issued_material_outhouse.ds_line', '=', 'delivery.ds_line');
        });
        $this->crud->query->join('po', function($join){
            $join->on('delivery.po_num', '=', 'po.po_num');
        });

        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }
        if(Constant::checkPermission('Read History Summary MO')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
        $this->crud->addColumn([
            'type'           => 'checkbox_mopo',
            'name'           => 'bulk_actions',
            'label'          => '<input type="checkbox" class="crud_bulk_actions_main_checkbox" style="width: 16px; height: 16px;" />',
            'searchLogic'    => false,
            'orderable'      => false,
            'visibleInModal' => false,
        ]);
        $this->crud->query->groupBy('delivery.po_num');
        $this->crud->enableDetailsRow();
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
        
        CRUD::addColumn([
            'label'     => 'PO Number', 
            'name'      => 'po_num',
            'type' => 'text',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('delivery.po_num', 'like', '%'.$searchTerm.'%');
            },
        ]);
        CRUD::column('po_line')->label('PO Line');
        CRUD::addColumn([
            'label'     => 'Description', 
            'name'      => 'description',
            'type' => 'text',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('delivery.description', 'like', '%'.$searchTerm.'%');
            },
        ]);
        CRUD::column('sum_qty_order')->label('Qty Order');
        CRUD::column('u_m')->label('UM');
        CRUD::addColumn([
            'name'  => 'due_date',
            'label' => 'Due Date', 
            'type' => 'closure',
            'orderable'  => true, 
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->orderBy('delivery.due_date', $columnDirection);
            },
            'function' => function($entry) {
                return date('Y-m-d', strtotime($entry->due_date));
            },
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('delivery.due_date', 'like', '%'.$searchTerm.'%');
            },
        ]);

        $this->crud->addFilter([
            'type'  => 'date_range_hmo',
            'name'  => 'shipped_date',
            'label' => 'Date range',
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            $this->crud->addClause('where', 'delivery.shipped_date', '>=', $dates->from);
            $this->crud->addClause('where', 'delivery.shipped_date', '<=', $dates->to . ' 23:59:59');
          });
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


    public function showDetailsRow($id)
    {
        $entry = $this->crud->getEntry($id);
        $url_parent = parse_url(request()->headers->get('referer'));

        $this->data['entry'] = $entry;
        $this->data['crud'] = $this->crud;

        $firstDate = date('Y-m-d',strtotime('first day of this month'));
        $startDate = $firstDate;
        $endDate = now();
       
        if (array_key_exists("query", $url_parent)) {
            parse_str($url_parent['query'], $param_url);

            $dueDate = $param_url['shipped_date'];
            $dueDateD = json_decode($dueDate);
            $startDate = $dueDateD->from;
            $endDate = $dueDateD->to;
        }

        $delivery = Delivery::where('ds_num', $entry->ds_num)
                    ->where('ds_line', $entry->ds_line)
                    ->first();

        $sql = "SELECT 
                    pimo.matl_item, 
                    pimo.description, 
                    pimo.issue_qty, 
                    delivery.due_date, 
                    (SELECT SUM(issue_qty) FROM issued_material_outhouse imo 
                        JOIN delivery 
                        ON (delivery.ds_num = imo.ds_num AND delivery.ds_line = imo.ds_line)
                        WHERE imo.matl_item = pimo.matl_item 
                        AND delivery.po_num = '". $delivery->po_num."'
                        AND delivery.po_line = '". $delivery->po_line."'
                        AND delivery.ds_type != '0R'
                        AND (delivery.shipped_date >= '".$startDate."' 
                        AND delivery.shipped_date <= '".$endDate." 23:59:59')
                    ) AS m_total_qty
                FROM issued_material_outhouse pimo
                JOIN delivery
                ON (pimo.ds_num = delivery.ds_num 
                    AND pimo.ds_line = delivery.ds_line
                )
                WHERE delivery.po_num = '".$delivery->po_num."'
                AND delivery.po_line = '". $delivery->po_line."'
                AND delivery.ds_type != '0R'
                AND (delivery.shipped_date >= '".$startDate."' 
                AND delivery.shipped_date <= '".$endDate." 23:59:59')
                GROUP BY pimo.matl_item";

        $data_materials = DB::select($sql);

        $this->data['data_materials'] = $data_materials;
        
        return view('crud::details_row_history', $this->data);
    }
}
