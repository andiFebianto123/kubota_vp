<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Exports\TemplateExportAll;
use Maatwebsite\Excel\Facades\Excel;


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
        $this->crud->addButtonFromModelFunction('top', 'excel_export_advance', 'excelExportAdvance', 'end');

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
                    AND delivery.ds_type IN ('00','01')
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

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $totalRows = $this->crud->model->count();
        $filteredRows = $this->crud->query->toBase()->getCountForPagination();
        $startIndex = request()->input('start') ?: 0;
        // if a search term was present
        if (request()->input('search') && request()->input('search')['value']) {
            // filter the results accordingly
            $this->crud->applySearchTerm(request()->input('search')['value']);
            // recalculate the number of filtered rows
            $filteredRows = $this->crud->count();
        }
        // start the results according to the datatables pagination
        if (request()->input('start')) {
            $this->crud->skip((int) request()->input('start'));
        }
        // limit the number of results according to the datatables pagination
        if (request()->input('length')) {
            $this->crud->take((int) request()->input('length'));
        }
        // overwrite any order set in the setup() method with the datatables order
        if (request()->input('order')) {
            // clear any past orderBy rules
            $this->crud->query->getQuery()->orders = null;
            foreach ((array) request()->input('order') as $order) {
                $column_number = (int) $order['column'];
                $column_direction = (strtolower((string) $order['dir']) == 'asc' ? 'ASC' : 'DESC');
                $column = $this->crud->findColumnById($column_number);
                if ($column['tableColumn'] && ! isset($column['orderLogic'])) {
                    // apply the current orderBy rules
                    $this->crud->orderByWithPrefix($column['name'], $column_direction);
                }

                // check for custom order logic in the column definition
                if (isset($column['orderLogic'])) {
                    $this->crud->customOrderBy($column, $column_direction);
                }
            }
        }

        // show newest items first, by default (if no order has been set for the primary column)
        // if there was no order set, this will be the only one
        // if there was an order set, this will be the last one (after all others were applied)
        // Note to self: `toBase()` returns also the orders contained in global scopes, while `getQuery()` don't.
        $orderBy = $this->crud->query->toBase()->orders;
        $table = $this->crud->model->getTable();
        $key = $this->crud->model->getKeyName();

        $hasOrderByPrimaryKey = collect($orderBy)->some(function ($item) use ($key, $table) {
            return (isset($item['column']) && $item['column'] === $key)
                || (isset($item['sql']) && str_contains($item['sql'], "$table.$key"));
        });

        if (! $hasOrderByPrimaryKey) {
            $this->crud->orderByWithPrefix($this->crud->model->getKeyName(), 'DESC');
        }

        $entries = $this->crud->getEntries();

        $dbStatement = getSQL($this->crud->query);

        session(["sqlSyntax" => $dbStatement]);

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows, $startIndex);
    } 

    public function exportAdvance(Request $request){
        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $data = DB::select($query);

            $filename = 'MO-po'.date('YmdHis').'.xlsx';

            $title = "Report MO per PO";

            $header = [
                'no' => 'No',
                'po_number' => 'PO Number',
                'po_line' => 'PO Line',
                'status' => 'Status',
                'description' => 'Description',
                'qty_order' => 'Qty Order',
                'um' => 'UM',
                'due_date' => 'Due Date'
            ];

            $resultCallback = function($result){
               return [
                    'no' => '<number>',
                    'po_number' => $result->po_num,
                    'po_line' => $result->po_line,
                    'status' => function($entry) {
                        if($entry->status == 'O'){
                            return 'Ordered';
                        }
                        return '';
                    },
                    'description' => $result->description,
                    'qty_order' => $result->order_qty,
                    'um' => $result->u_m,
                    'due_date' => $result->due_date
                ];
            };

            $styleHeader = function(\Maatwebsite\Excel\Events\AfterSheet $event){
                $styleHeader = [
                    //Set font style
                    'font' => [
                        'bold'      =>  true,
                        'color' => ['argb' => 'ffffff'],
                    ],
        
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '66aba3',
                         ]           
                    ],
        
                ];

                $styleGroupProtected = [
                    //Set background style
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'ededed',
                         ]           
                    ],
        
                ];

                $arrColumns = range('A', 'H');
                // $totalColom = 31;
                // for($i = 1; $i<=$totalColom; $i++){
                //     $col = getNameFromNumber($i);
                //     $event->sheet->getColumnDimension($col)->setAutoSize(true);
                //     $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                // }
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $event->sheet->getDelegate()->getStyle('A1:H1')->applyFromArray($styleHeader);
            };

            $export = new TemplateExportAll($data, $header, $resultCallback, $styleHeader, $title);

            return Excel::download($export, $filename);
        }
        return 0;
    } 
}
