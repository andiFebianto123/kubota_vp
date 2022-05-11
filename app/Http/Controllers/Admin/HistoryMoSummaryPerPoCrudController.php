<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use App\Models\Delivery;
use App\Models\IssuedMaterialOuthouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Exports\TemplateExportAll;
use Maatwebsite\Excel\Facades\Excel;

use App\Library\ExportXlsx;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;

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

        // $sql = "(SELECT SUM(shipped_qty) FROM delivery dlv
        //         WHERE delivery.po_num = dlv.po_num 
        //         AND delivery.po_line = dlv.po_line
        //         AND delivery.shipped_date >= '".$startDate."' 
        //         AND delivery.shipped_date <= '".$endDate." 23:59:59'
        //         AND dlv.ds_type IN ('00','01')
        //         ) AS sum_qty_order";
        
        $this->crud->query = $this->crud->query->select(
            'issued_material_outhouse.id as id', 
            'delivery.po_num as po_num', 
            'delivery.po_line as po_line',
            'delivery.u_m', 
            'delivery.description', 
            'delivery.due_date', 
            'delivery.shipped_date', 
            'po.vend_num', 
            'pl.order_qty'
        );
        
        $this->crud->query->join('delivery', function($join){
            $join->on('issued_material_outhouse.ds_num', '=', 'delivery.ds_num');
            $join->on('issued_material_outhouse.ds_line', '=', 'delivery.ds_line');
        });
        $this->crud->query->join('po', function($join){
            $join->on('delivery.po_num', '=', 'po.po_num');
        });
        $this->crud->query->join('po_line as pl', function($join){
            $join->on('issued_material_outhouse.po_num', '=', 'pl.po_num');
            $join->on('issued_material_outhouse.po_line', '=', 'pl.po_line');
        });

        if(!strpos(strtoupper(Constant::getRole()), 'PTKI')){
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
        $this->crud->allowAccess('advanced_export_excel');

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
            session()->put('filter_shipped_date',null);
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
        CRUD::column('order_qty')->label('Qty Order');
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

        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor'
            ],
            url('admin/filter-vendor/ajax-itempo-options'),
            function($value) { 
                $this->crud->addClause('where', 'po.vend_num', $value);
            });
        }

        $this->crud->addFilter([
            'type'  => 'date_range_hmo',
            'name'  => 'shipped_date',
            'label' => 'Date range',
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            session()->put('filter_shipped_date', $value);

            $dates = json_decode($value);
            $this->crud->addClause('where', 'delivery.shipped_date', '>=', $dates->from);
            $this->crud->addClause('where', 'delivery.shipped_date', '<=', $dates->to . ' 23:59:59');
          });
          $this->crud->exportRoute = url('admin/history-mo-po-export');
        $this->crud->addButtonFromView('top', 'advanced_export_excel', 'advanced_export_excel', 'end');

        //   $this->crud->addButtonFromModelFunction('top', 'excel_export_advance2', 'excelExportAdvance2', 'end');
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
        $filterDate = session()->get('filter_shipped_date');

        $this->data['entry'] = $entry;
        $this->data['crud'] = $this->crud;

        $firstDate = date('Y-m-d',strtotime('first day of this month'));
        $startDate = $firstDate;
        $endDate = now();
       
        if ($filterDate) {
            $dueDate = $filterDate;
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
                        AND delivery.ds_type IN ('00','01')
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
                AND delivery.ds_type IN ('00','01')
                AND (delivery.shipped_date >= '".$startDate."' 
                AND delivery.shipped_date <= '".$endDate." 23:59:59')
                GROUP BY pimo.matl_item";

        

        $data_materials = DB::select($sql);

        $this->data['data_materials'] = $data_materials;
        
        return view('crud::details_row_history', $this->data);
    }

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $totalRows = $this->crud->model->count();
        $cloneQuery = clone $this->crud->query;
        $queryWithSelect = $cloneQuery->select('matl_item');
        $filteredRows = $queryWithSelect->toBase()->getCountForPagination();
        // $filteredRows = $this->crud->query->toBase()->getCountForPagination();
        $startIndex = request()->input('start') ?: 0;
        // if a search term was present
        if (request()->input('search') && request()->input('search')['value']) {
            // filter the results accordingly
            $this->crud->applySearchTerm(request()->input('search')['value']);
            // recalculate the number of filtered rows
            $filteredRows = $this->crud->count();
        }else{
            $filteredRows = $queryWithSelect->get()->count();
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

    public function exportAdvance(){
        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $datas = DB::select($query);

          
            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'po_number' => $result->po_num,
                    'po_line' => $result->po_line,
                    'description' => $result->description,
                    'qty_order' => $result->sum_qty_order,
                    'um' => $result->u_m,
                    'due_date' => $result->due_date
                ];
            };

            $filename = 'HMO-po'.date('YmdHis').'.xlsx';

            // $GLOBALS['col'] = '<cols>';
            // $GLOBALS['col'] .= '<col min="1" max="1" width="10" customWidth="1"/>';
            // $GLOBALS['col'] .= '<col min="2" max="2" width="15" customWidth="1"/>';
            // $GLOBALS['col'] .= "</cols>";
    
            $export = new ExportXlsx($filename);
    
            $styleForHeader = (new StyleBuilder())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->setBackgroundColor(Color::rgb(102, 171, 163))
                            ->build();
    
            $firstSheet = $export->currentSheet();
    
            $export->addRow([
                'No',
                'PO Number',
                'PO Line',
                'Description',
                'Qty Order',
                'UM',
                'Due Date'
            ], $styleForHeader);

            $styleForBody = (new StyleBuilder())
                            ->setFontColor(Color::BLACK)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->build();

            $increment = 1;
            foreach($datas as $data){
                $row = $resultCallback($data);
                $rowT = [];
                foreach($row as $key => $value){
                    if($value == "<number>"){
                        $rowT[] = $increment;
                    }else if(is_callable($value)){
                        $rowT[] = $value($data);
                    }else{
                        $rowT[] = $value;
                    }
                }
                $increment++;
                $export->addRow($rowT, $styleForBody);
            }

            $export->close();
        }
    }

    public function exportAdvance2(Request $request){
        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $data = DB::select($query);

            $filename = 'HMO-po'.date('YmdHis').'.xlsx';

            $title = "Report History MO per PO";

            $header = [
                'no' => 'No',
                'po_number' => 'PO Number',
                'po_line' => 'PO Line',
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
                    'description' => $result->description,
                    'qty_order' => $result->sum_qty_order,
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

                $arrColumns = range('A', 'G');
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
                
                $event->sheet->getDelegate()->getStyle('A1:G1')->applyFromArray($styleHeader);
            };

            $export = new TemplateExportAll($data, $header, $resultCallback, $styleHeader, $title);

            return Excel::download($export, $filename);
        }
        return 0;
    } 
}
