<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliveryStatusRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use Illuminate\Support\Facades\DB;
use App\Exports\TemplateExportAll;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use App\Library\ExportXlsx;
use App\Models\PurchaseOrderLine;
use Rap2hpoutre\FastExcel\FastExcel;

// untuk box spout
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;


class DeliveryStatusCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


    public function setup()
    { 
        CRUD::setModel(DeliveryStatus::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery-status');
        CRUD::setEntityNameStrings('delivery status', 'delivery statuses');

        if(Constant::checkPermission('Read Delivery Status in Table')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
        $this->crud->allowAccess('advanced_export_excel');

        $this->crud->query->leftJoin('po', 'po.po_num', '=', 'delivery_status.po_num')
        ->leftJoin('vendor', 'vendor.vend_num', '=', 'po.vend_num')
        ->select(DB::raw("delivery_status.*, vendor.currency"));
    }


    protected function setupShowOperation()
    {
        $this->crud->denyAccess('show');
    }

    protected function setupListOperation()
    {
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('show');
        // $this->crud->addButtonFromModelFunction('top', 'excel_export_advance', 'excelExportAdvance', 'end');
        $this->crud->exportRoute = url('delivery-statuses-export');
        $this->crud->addButtonFromView('top', 'advanced_export_excel', 'advanced_export_excel', 'end');
        $this->crud->addClause('where', 'ds_type', '!=', 'R0');
        $this->crud->addClause('where', 'ds_type', '!=', 'R1');

        CRUD::column('id')->label('ID');
        CRUD::column('ds_num')->label('DS Num');
        CRUD::column('ds_line')->label('DS Line');
        CRUD::column('ds_type')->label('DS Type');
        CRUD::addColumn([
            'label'     => 'PO',
            'name'      => 'po_po_line',
            'orderable'  => true,
            'searchLogic' => function ($query, $column, $searchTerm) {
                if ($column['name'] == 'po_po_line') {
                    $searchOnlyPo = str_replace("-", "", $searchTerm);
                    $query->orWhere('delivery_status.po_num', 'like', '%'.$searchOnlyPo.'%');
                    if (str_contains($searchTerm, '-')) {
                        $query->orWhere(function($q) use ($searchTerm) {
                            $searchWithSeparator = explode("-", $searchTerm);
                            $q->where('delivery_status.po_num', 'like', '%'.$searchWithSeparator[0].'%')
                              ->Where('delivery_status.po_line', 'like', '%'.$searchWithSeparator[1].'%');
                        });
                    }
                }
            },
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->orderBy('delivery_status.po_num', $columnDirection)->select('delivery_status.*');
            }
        ]);
        
        CRUD::column('description')->label('Desc');
        CRUD::column('grn_num')->label('GRN Num');
        CRUD::column('grn_line')->label('GRN Line');
        CRUD::addColumn([
            'label'     => 'Received Flag', // Table column heading
            'name'      => 'received_flag', // the column that contains the ID of that connected entity;
            'type' => 'flag_checked_html',
        ]);
        CRUD::column('received_date')->label('Received Date');
        CRUD::column('payment_plan_date')->label('Due Date');
        CRUD::addColumn([
            'label'     => 'Validated Flag', // Table column heading
            'name'      => 'validate_by_fa_flag', // the column that contains the ID of that connected entity;
            'type' => 'flag_checked_html',
        ]);
        CRUD::addColumn([
            'label'     => 'Payment in Process Flag', // Table column heading
            'name'      => 'payment_in_process_flag', // the column that contains the ID of that connected entity;
            'type' => 'flag_checked_html',
        ]);
        CRUD::addColumn([
            'label'     => 'Executed Flag', // Table column heading
            'name'      => 'executed_flag', // the column that contains the ID of that connected entity;
            'type' => 'flag_checked_html',
        ]);
        CRUD::column('payment_date')->label('Payment Date');
        CRUD::column('tax_status')->label('Tax Status');
        CRUD::column('payment_ref_num')->label('Payment Ref Num');
        CRUD::column('bank');
        CRUD::column('shipped_qty')->label('Shipped Qty');
        CRUD::column('received_qty')->label('Received Qty');
        CRUD::column('rejected_qty')->label('Rejected Qty');
        if(Constant::checkPermission('Show Price In Delivery Status Menu')){
            CRUD::addColumn([
                'label'     => 'Unit Price', // Table column heading
                'name'      => 'unit_price', // the column that contains the ID of that connected entity;
                'type'     => 'closure',
                'function' => function($entry) {
                    $currency = "";
                    $poLine = PurchaseOrderLine::where('po_num', $entry->po_num)->where('po_line', $entry->po_line)->first();

                    if (isset($poLine)) {
                        $currency = $poLine->currency;
                    }
                    $val = number_format($entry->unit_price, 0, ',', '.');
                    return $currency." ".$val;
                }
            ]);
            CRUD::addColumn([
                'name'     => 'total',
                'label'    => 'Total',
                'type'     => 'closure',
                'function' => function($entry) {
                    $currency = "";
                    $poLine = PurchaseOrderLine::where('po_num', $entry->po_num)->where('po_line', $entry->po_line)->first();
                    if (isset($poLine)) {
                        $currency = $poLine->currency;
                    }
                    $val = number_format($entry->total, 0, ',', '.');
                    return $currency." ".$val;
                }
            ]);
        }
        CRUD::column('petugas_vendor')->label('Petugas Vendor');
        CRUD::column('no_faktur_pajak')->label('No Faktur Pajak');
        CRUD::column('no_surat_jalan_vendor')->label('No Surat Jalan Vendor');
       // CRUD::column('ref_ds_num')->label('Ref DS Num');
        CRUD::addColumn([
            'name'     => 'ref_ds_num',
            'label'    => 'Ref DS Num',
            'type'     => 'closure',
            'function' => function($entry) {
                $delivery = Delivery::where('ds_num', $entry->ref_ds_num)
                    ->where('ds_line', $entry->ref_ds_line)
                    ->first();
                $html = '';
                if (isset($delivery)) {
                    $url = url('delivery-detail').'/'.$delivery->ds_num.'/'.$delivery->ds_line;
                    $html = "<a href='".$url."' class='btn-link'>".$entry->ref_ds_num."</a>";
                }
                
                return $html;
            },
            'orderable'  => true, 
            'searchLogic' => function ($query, $column, $searchTerm) {
                if ($column['name'] == 'ref_ds_num') {
                    $q = '';
                    $searchOnlyPo = str_replace("-", "", $searchTerm);
                    $q = $query->orWhere('delivery_status.ref_ds_num', 'like', '%'.$searchOnlyPo.'%');
                    if (str_contains($searchTerm, '-')) {
                        $q = $query->orWhere(function($q) use ($searchTerm) {
                            $searchWithSeparator = explode("-", $searchTerm);
                            $q->where('delivery_status.ref_ds_num', 'like', '%'.$searchWithSeparator[0].'%')
                              ->Where('delivery_status.ref_ds_line', 'like', '%'.$searchWithSeparator[1].'%');
                        });
                    }
                    return $q;
                }
            },
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->orderBy('delivery_status.ref_ds_num', $columnDirection);
            }
        ]);
        CRUD::column('ref_ds_line')->label('Ref DS Line');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Vendor Name',
                'placeholder' => 'Pick a vendor'
            ],
            url('filter-vendor/ajax-itempo-options'),
            function($value) {
                // SELECT d.id, d.ds_num, d.po_num, p.vend_num FROM `delivery` d
                // JOIN po p ON p.po_num = d.po_num
                // WHERE p.vend_num = 'V001303'
                $dbGet = \App\Models\DeliveryStatus::join('po', 'po.po_num', 'delivery_status.po_num')
                ->select('delivery_status.id as id')
                ->where('po.vend_num', $value)
                ->get()
                ->mapWithKeys(function($po, $index){
                    return [$index => $po->id];
                });
                $this->crud->addClause('whereIn', 'delivery_status.id', $dbGet->unique()->toArray());
            });
        }else{
            $this->crud->addClause('where', 'po.vend_num', backpack_auth()->user()->vendor->vend_num);

            // $this->crud->query->join('po as pofilter', function($join){
            //     $join->on('delivery_status.po_num', '=', 'pofilter.po_num')
            //     ->where('pofilter.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
            // });
        }
    }


    protected function setupCreateOperation()
    {
        $this->crud->denyAccess('create');
    }

    
    protected function setupUpdateOperation()
    {
        $this->crud->denyAccess('update');

        $this->setupCreateOperation();
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

    public function exportAdvanceOld(){
        ini_set('memory_limit', '-1');

        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $datas = DB::select($sqlQuery);

            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'id' => $result->id,
                    'ds_num' => $result->ds_num,
                    'ds_line' => $result->ds_line,
                    'ds_type' => $result->ds_type,
                    'po_relase' => $result->po_release,
                    // 'desc' => $result->description,
                    'grn_num' => $result->grn_num ?? '',
                    'grn_line' => $result->grn_line ?? '',
                    'received_flag' => $result->received_flag ?? '0',
                    'received_date' => $result->received_date ?? '',
                    'due_date' => $result->payment_plan_date ?? '',
                    'validated_flag' => $result->validate_by_fa_flag ?? '0',
                    'payment_in_process_flag' => $result->payment_in_process_flag ?? '0',
                    'executed_flag' => $result->executed_flag ?? '0',
                    'payment_date' => $result->payment_date ?? '',
                    'tax_status' => $result->tax_status ?? '',
                    'payment_ref_num' => $result->payment_ref_num ?? '',
                    'bank' => $result->bank ?? '',
                    'shipped_qty' => $result->shipped_qty ?? '',
                    'received_qty' => $result->received_qty ?? '',
                    'rejected_qty' => $result->rejected_qty ?? '',
                    'unit_price' => function($entry){
                        // $ds = DeliveryStatus::where('id', $entry->id)->first();
                        // if($ds !== null){
                        //     $currency = $ds->purchaseOrder->vendor->currency;
                        //     $val = number_format($entry->unit_price, 0, ',', '.');
                        //     return $currency." ".$val;
                        // }
                        $currency = $entry->currency;
                        $val = number_format($entry->unit_price, 0, ',', '.');
                        return $currency." ".$val;
                    },
                    'total' => function($entry){
                        // $ds = DeliveryStatus::where('id', $entry->id)->first();
                        // if($ds !== null){
                        //     $currency = $ds->purchaseOrder->vendor->currency;
                        //     $val = number_format($entry->total, 0, ',', '.');
                        //     return $currency." ".$val;
                        // }
                        $currency = $entry->currency;
                        $val = number_format($entry->total, 0, ',', '.');
                        return $currency." ".$val;
                    },
                    // 'petugas_vendor' => is_string($result->petugas_vendor) ?$result->petugas_vendor:'-',
                    'no_faktur_pajak' => $result->no_faktur_pajak ?? '',
                    // 'no_surat_jalan_vendor' => $result->no_surat_jalan_vendor ?? '',
                    'ref_ds_num' => $result->ref_ds_num ?? '',
                    'ref_ds_line' => $result->ref_ds_line ?? '',
                    'created' => $result->created_at ?? '',
                    'updated' => $result->updated_at ?? '',
                ];
            };


            $filename = 'DST-'.date('YmdHis').'.xlsx';

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
    
            $export->addRow(['No', 
                'ID', 'DS Num', 'DS Line', 'DS Type', 'PO Relase', 
                'Desc', 'GRN Num', 'GRN Line', 'Received Flag', 'Received Date', 'Due Date', 'Validate Flag',
                'Payment In Process Flag', 'Executed Flag', 'Payment Date', 'Tax Status', 'Payment Ref Num',
                'Bank', 'Shipped Qty', 'Received Qty', 'Rejected Qty', 'Unit Price', 'Total', 'Petugas Vendor',
                'No Faktur Pajak', 'No Surat Jalan Vendor', 'Ref DS Num', 'Ref DS Line', 'Created', 'Updated'
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


    private function dataChunks($datas) {
        foreach ($datas as $data) {
            yield $data;
        }
    }


    public function exportAdvance(){
        ini_set('memory_limit', '-1');

        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $datas = DB::select($query);

            $filename = 'DST-'.date('YmdHis').'.xlsx';

            $styleForHeader = (new StyleBuilder())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->setBackgroundColor(Color::rgb(102, 171, 163))
                            ->build();

            return (new FastExcel($this->dataChunks($datas)))
                ->headerStyle($styleForHeader)
                ->download($filename);
            /*
            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'id' => $result->id,
                    'ds_num' => $result->ds_num,
                    'ds_line' => $result->ds_line,
                    'ds_type' => $result->ds_type,
                    'po_relase' => $result->po_release,
                    'desc' => $result->description,
                    'grn_num' => $result->grn_num,
                    'grn_line' => $result->grn_line,
                    'received_flag' => function($result){
                        if($result->received_flag == 1){
                            return 1;
                            // return "???";
                        } else {
                            // return "x";
                        }            
                        return 0;            
                    },
                    'received_date' => $result->received_date,
                    'due_date' => $result->payment_plan_date,
                    'validated_flag' => function($result){
                        if($result->validate_by_fa_flag == 1){
                            return 1;
                            // return "???";
                        } else {
                            // return "x";
                        }  
                        return 0;                      
                    },
                    'payment_in_process_flag' => function($result){
                        if($result->payment_in_process_flag == 1){
                            return 1;
                            // return "???";
                        } else {
                            // return "x";
                        }            
                        return 0;            
                    },
                    'executed_flag' => function($result){
                        if($result->executed_flag == 1){
                            return 1;
                            // return "???";
                        } else {
                            // return "x";
                        }  
                        return 0;                      
                    },
                    'payment_date' => $result->payment_date,
                    'tax_status' => $result->tax_status,
                    'payment_ref_num' => $result->payment_ref_num,
                    'bank' => $result->bank,
                    'shipped_qty' => $result->shipped_qty,
                    'received_qty' => $result->received_qty,
                    'rejected_qty' => $result->rejected_qty,
                    'unit_price' => function($entry){
                        // $ds = DeliveryStatus::where('id', $entry->id)->first();
                        // if($ds !== null){
                        //     $currency = $ds->purchaseOrder->vendor->currency;
                        //     $val = number_format($entry->unit_price, 0, ',', '.');
                        //     return $currency." ".$val;
                        // }
                        $currency = $entry->currency;
                        $val = number_format($entry->unit_price, 0, ',', '.');
                        return $currency." ".$val;
                    },
                    'total' => function($entry){
                        // $ds = DeliveryStatus::where('id', $entry->id)->first();
                        // if($ds !== null){
                        //     $currency = $ds->purchaseOrder->vendor->currency;
                        //     $val = number_format($entry->total, 0, ',', '.');
                        //     return $currency." ".$val;
                        // }
                        $currency = $entry->currency;
                        $val = number_format($entry->total, 0, ',', '.');
                        return $currency." ".$val;
                    },
                    'petugas_vendor' => $result->petugas_vendor,
                    'no_faktur_pajak' => $result->no_faktur_pajak,
                    'no_surat_jalan_vendor' => $result->no_surat_jalan_vendor,
                    'ref_ds_num' => $result->ref_ds_num,
                    'ref_ds_line' => $result->ref_ds_line,
                    'created' => $result->created_at,
                    'updated' => $result->updated_at
                ];
            };*/


            // $GLOBALS['col'] = '<cols>';
            // $GLOBALS['col'] .= '<col min="1" max="1" width="10" customWidth="1"/>';
            // $GLOBALS['col'] .= '<col min="2" max="2" width="15" customWidth="1"/>';
            // $GLOBALS['col'] .= "</cols>";
    
            // $export = new ExportXlsx($filename);
    
            $styleForHeader = (new StyleBuilder())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->setBackgroundColor(Color::rgb(102, 171, 163))
                            ->build();
    
            // $firstSheet = $export->currentSheet();
    
            // $export->addRow(['No', 
            //     'ID', 'DS Num', 'DS Line', 'DS Type', 'PO Relase', 
            //     'Desc', 'GRN Num', 'GRN Line', 'Received Flag', 'Received Date', 'Due Date', 'Validate Flag',
            //     'Payment In Process Flag', 'Executed Flag', 'Payment Date', 'Tax Status', 'Payment Ref Num',
            //     'Bank', 'Shipped Qty', 'Received Qty', 'Rejected Qty', 'Unit Price', 'Total', 'Petugas Vendor',
            //     'No Faktur Pajak', 'No Surat Jalan Vendor', 'Ref DS Num', 'Ref DS Line', 'Created', 'Updated'
            // ], $styleForHeader);

            $styleForBody = (new StyleBuilder())
                            ->setFontColor(Color::BLACK)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->build();

            $increment = 1;



            // (new FastExcel($datas))->export('invoices.csv');

            // (new FastExcel($datas))->export($filename, function ($result) {
            //     return [
            //         'no' => '<number>',
            //         'id' => $result->id,
            //         'ds_num' => $result->ds_num,
            //         'ds_line' => $result->ds_line,
            //         'ds_type' => $result->ds_type,
            //         'po_relase' => $result->po_release,
            //         'desc' => $result->description,
            //         'grn_num' => $result->grn_num,
            //         'grn_line' => $result->grn_line,
            //         'received_flag' => function($result){
            //             if($result->received_flag == 1){
            //                 return 1;
            //                 // return "???";
            //             } else {
            //                 // return "x";
            //             }            
            //             return 0;            
            //         },
            //         'received_date' => $result->received_date,
            //         'due_date' => $result->payment_plan_date,
            //         'validated_flag' => function($result){
            //             if($result->validate_by_fa_flag == 1){
            //                 return 1;
            //                 // return "???";
            //             } else {
            //                 // return "x";
            //             }  
            //             return 0;                      
            //         },
            //         'payment_in_process_flag' => function($result){
            //             if($result->payment_in_process_flag == 1){
            //                 return 1;
            //                 // return "???";
            //             } else {
            //                 // return "x";
            //             }            
            //             return 0;            
            //         },
            //         'executed_flag' => function($result){
            //             if($result->executed_flag == 1){
            //                 return 1;
            //                 // return "???";
            //             } else {
            //                 // return "x";
            //             }  
            //             return 0;                      
            //         },
            //         'payment_date' => $result->payment_date,
            //         'tax_status' => $result->tax_status,
            //         'payment_ref_num' => $result->payment_ref_num,
            //         'bank' => $result->bank,
            //         'shipped_qty' => $result->shipped_qty,
            //         'received_qty' => $result->received_qty,
            //         'rejected_qty' => $result->rejected_qty,
            //         // 'unit_price' => function($entry){
            //         //     $currency = $entry->currency;
            //         //     $val = number_format($entry->unit_price, 0, ',', '.');
            //         //     return $currency." ".$val;
            //         // },
            //         // 'total' => function($entry){
            //         //     $currency = $entry->currency;
            //         //     $val = number_format($entry->total, 0, ',', '.');
            //         //     return $currency." ".$val;
            //         // },
            //         'petugas_vendor' => $result->petugas_vendor,
            //         'no_faktur_pajak' => $result->no_faktur_pajak,
            //         'no_surat_jalan_vendor' => $result->no_surat_jalan_vendor,
            //         'ref_ds_num' => $result->ref_ds_num,
            //         'ref_ds_line' => $result->ref_ds_line,
            //         'created' => $result->created_at,
            //         'updated' => $result->updated_at
            //     ];
            // });
            

            /*
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
            */
        }
    
    }
}
