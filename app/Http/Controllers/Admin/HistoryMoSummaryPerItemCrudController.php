<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\HistoriMoSummaryPerItemRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use App\Models\IssuedMaterialOuthouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Exports\TemplateExportAll;
use Maatwebsite\Excel\Facades\Excel;



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
                AND delivery.ds_type IN ('00','01')
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
        $this->crud->addButtonFromModelFunction('top', 'excel_export_advance', 'excelExportAdvance', 'end');

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

            $filename = 'HMO-item'.date('YmdHis').'.xlsx';

            $title = "Report History MO per Item";

            if(Constant::getRole() == 'Admin PTKI'){
                $header = [
                    'no' => 'No',
                    'vend_num' => 'Vend Num',
                    'matl_item' => 'Matl Item',
                    'description' => 'Description',
                    'qty_total' => 'Qty Total',
                ];
    
                $resultCallback = function($result){
                   return [
                        'no' => '<number>',
                        'vend_num' => $result->vend_num,
                        'matl_item' => $result->matl_item,
                        'description' => $result->description,
                        'qty_total' => $result->sum_qty_total,
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
    
                    $arrColumns = range('A', 'E');
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
                    
                    $event->sheet->getDelegate()->getStyle('A1:E1')->applyFromArray($styleHeader);
                };
            }else{
                // if vendor only
                $header = [
                    'no' => 'No',
                    'matl_item' => 'Matl Item',
                    'description' => 'Description',
                    'qty_total' => 'Qty Total',
                ];
    
                $resultCallback = function($result){
                   return [
                        'no' => '<number>',
                        'matl_item' => $result->matl_item,
                        'description' => $result->description,
                        'qty_total' => $result->sum_qty_total,
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
    
                    $arrColumns = range('A', 'D');
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
                    
                    $event->sheet->getDelegate()->getStyle('A1:D1')->applyFromArray($styleHeader);
                };
            }

            $export = new TemplateExportAll($data, $header, $resultCallback, $styleHeader, $title);

            return Excel::download($export, $filename);
        }
        return 0;
    } 

}
