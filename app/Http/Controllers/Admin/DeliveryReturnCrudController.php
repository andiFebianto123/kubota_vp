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
use App\Helpers\DsValidation;
use App\Http\Requests\DeliveryRequest;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use App\Library\ExportXlsx;
use App\Models\DeliveryRepair;
use App\Models\DeliveryReturn;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Rap2hpoutre\FastExcel\FastExcel;

// untuk box spout
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Illuminate\Support\Facades\Auth;
use Prologue\Alerts\Facades\Alert;

class DeliveryReturnCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


    public function setup()
    { 
        CRUD::setModel(DeliveryRepair::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery-return');
        CRUD::setEntityNameStrings('delivery return', 'delivery returns');

        if(Constant::checkPermission('Read Delivery Return')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
        $this->crud->allowAccess('advanced_export_excel');
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
        $this->crud->addButtonFromModelFunction('line', 'create_ds_return', 'createDsReturn', 'end');
        $this->crud->addButtonFromModelFunction('line', 'close_ds_return', 'closeDsReturn', 'end');
        $this->crud->exportRoute = url('admin/delivery-statuses-export');
       //  $this->crud->addButtonFromView('top', 'advanced_export_excel', 'advanced_export_excel', 'end');
        $this->crud->query->join('delivery_status', function($join){
            $join->on('delivery_status.ds_num', '=', 'delivery_repair.ds_num_reject');
            $join->on('delivery_status.ds_line', '=', 'delivery_repair.ds_line_reject');
        });
        $this->crud->addClause('where', 'repair_type', 'RETURN');
        $this->crud->groupBy('ds_num_reject');
        $this->crud->groupBy('ds_line_reject');
        $this->crud->query = $this->crud->query->select('delivery_repair.*','delivery_status.item', 'delivery_status.description', 
        'delivery_status.received_qty', 'delivery_status.shipped_qty', 'delivery_status.rejected_qty');

        CRUD::column('ds_num_reject')->label('DS Num');
        CRUD::column('ds_line_reject')->label('DS Line');
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
        CRUD::column('item');
        CRUD::column('description');
        CRUD::column('shipped_qty');
        CRUD::column('received_qty');
        CRUD::column('rejected_qty')->label('Reject Qty');
        CRUD::column('repair_qty')->label('Return Qty');
        CRUD::addColumn([
            'name'     => 'available_qty',
            'label'    => 'Available Qty',
            'type'     => 'closure',
            'function' => function($entry) {
                $dsClosed = DeliveryReturn::where('ds_num', $entry->ds_num_reject)->where('ds_line', $entry->ds_line_reject)->sum('qty');
                $avail = $entry->repair_qty - $dsClosed;
                return $avail;
            },
            
        ]);
        /*
        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor'
            ],
            url('admin/filter-vendor/ajax-itempo-options'),
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
        }
        */
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


    public function createDs()
    {
        if(!Constant::checkPermission('Read PO Line Detail')){
            abort(403);
        }

        CRUD::setValidation(DeliveryRequest::class);

        $dsNum = request('num');
        $dsLine = request('line');

        $deliveryStatus = DeliveryStatus::where('ds_num', $dsNum)
                        ->where('ds_line', $dsLine)
                        ->first();

        $deliveryReturns = DeliveryReturn::where('ds_num', $dsNum)
                            ->where('ds_line', $dsLine)
                            ->get();

        $args1 = [
            'po_num' => $deliveryStatus->po_num, 
            'po_line' => $deliveryStatus->po_line, 
        ];
        $currentQty = (new DsValidation())->currentMaxQty($args1)['datas'];

        $this->crud->addField([
            'label' => 'Delivery Date From Vendor',
            'type' => 'date_picker',
            'name' => 'shipped_date',
            'default' => date("Y-m-d"),
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd/mm/yyyy',
                'language' => 'en'
             ],
        ]); 
        $this->crud->addField([
            'type' => 'hidden',
            'name' => 'ds_num',
            'value' => $dsNum
        ]);   
        $this->crud->addField([
            'type' => 'hidden',
            'name' => 'ds_line',
            'value' => $dsLine
        ]);   
        $this->crud->addField([
            'type' => 'text',
            'label' => 'Petugas Vendor',
            'name' => 'petugas_vendor',
            'default' => Auth::guard('backpack')->user()->name
        ]); 
        $this->crud->addField([
            'type' => 'text',
            'label' => 'No Surat Jalan',
            'name' => 'no_surat_jalan_vendor',
        ]); 
        $this->crud->addField([
            'type' => 'number_qty',
            'name' => 'shipped_qty',
            'label' => 'Qty',
            'actual_qty' => $deliveryStatus->shipped_qty,
            'default' => $currentQty,
            'attributes' => [
                'data-max' =>  $currentQty,
              ], 
        ]);
        

        $arrFilters = [];
        $arrFilters[] = ['po_line.item', '=', $deliveryStatus->item];
        $args = [   
            'filters' => $arrFilters, 
            'due_date' => $deliveryStatus->due_date,
            'po_num' => $deliveryStatus->po_num,
            'po_line' => $deliveryStatus->po_line,
            ];
        $unfinishedPoLine = (new DsValidation())->unfinishedPoLine($args);
        
        $data['crud'] = $this->crud;
        $data['entry'] = $deliveryStatus;
        $data['unfinished_po_line'] = $unfinishedPoLine;
        $data['deliveryReturns'] = $deliveryReturns;

        $canAccess = false;
        /*
        if(strpos(strtoupper(Constant::getRole()), 'PTKI')){
            $canAccess = true;
        }else{
            $po = PurchaseOrderLine::where('id', $entry->id )->first();
            if (backpack_auth()->user()->vendor->vend_num == $po->purchaseOrder->vend_num) {
                $canAccess = true;
            }
        }
        if ($entry->accept_flag == 2) {
            $canAccess = false;
        }
        if ($entry->accept_flag == 0 &&  $entry->status == 'O') {
            $canAccess = false;
        }
      
        if ($canAccess) {
            $layout = 'vendor.backpack.crud.delivery_return_detail';
            if ( in_array($entry->status, ['C', 'F']) ) {
                $layout = 'vendor.backpack.crud.purchase_order_line_show_readonly';
            }
        }else{
            abort(404);
        }*/

        return view('vendor.backpack.crud.delivery_return_detail', $data);
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
        }
    
    }


    public function store(Request $request)
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $request = $this->crud->getRequest();

        $dsNum = $request->input('ds_num');
        $dsLine = $request->input('ds_line');
        $petugasVendor = $request->input('petugas_vendor');
        $noSuratJalanVendor = $request->input('no_surat_jalan_vendor');
        $shippedQty = $request->input('shipped_qty');

        $dsType = '0P';
        
        DB::beginTransaction();

        try{
            /*
            $insertDsheet = new Delivery();
            $insertDsheet->ds_num = $dsNum['single'];
            $insertDsheet->po_num = $poLine->po_num;
            $insertDsheet->po_line = $poLine->po_line;
            $insertDsheet->po_release = $poLine->po_release;
            $insertDsheet->po_change = $poLine->po_change;
            $insertDsheet->ds_line = $dsNum['line'];
            $insertDsheet->item = $poLine->item;
            $insertDsheet->description = $poLine->description;
            $insertDsheet->u_m = $poLine->u_m;
            $insertDsheet->due_date = $poLine->due_date;
            $insertDsheet->unit_price = $poLine->unit_price;
            $insertDsheet->wh = $poLine->wh;
            $insertDsheet->location = $poLine->location;
            $insertDsheet->tax_status = $poLine->tax_status;
            $insertDsheet->currency = $poLine->currency;
            $insertDsheet->shipped_qty = $shippedQty;
            $insertDsheet->shipped_date = $shippedDate;
            $insertDsheet->order_qty = $poLine->order_qty;
            $insertDsheet->w_serial = $poLine->w_serial;
            $insertDsheet->petugas_vendor = $petugasVendor;
            $insertDsheet->no_surat_jalan_vendor = $noSuratJalanVendor;
            $insertDsheet->created_by = backpack_auth()->user()->id;
            $insertDsheet->updated_by = backpack_auth()->user()->id;
            $insertDsheet->save();

            // Insert delivery status
            $insertDstatus = new DeliveryStatus();
            $insertDstatus->ds_num = $dsNum['single'];
            $insertDstatus->po_num = $poLine->po_num;
            $insertDstatus->po_line = $poLine->po_line;
            $insertDstatus->po_release = $poLine->po_release;
            $insertDstatus->ds_line = $dsNum['line'];
            $insertDstatus->item = $poLine->item;
            $insertDstatus->description = $poLine->description;
            $insertDstatus->unit_price = $poLine->unit_price;
            $insertDstatus->shipped_qty = $shippedQty;
            $insertDstatus->petugas_vendor = $petugasVendor;
            $insertDstatus->no_surat_jalan_vendor = $noSuratJalanVendor;
            $insertDstatus->created_by = backpack_auth()->user()->id;
            $insertDstatus->updated_by = backpack_auth()->user()->id;
            $insertDstatus->save();
            */

            // Insert delivery sheet
            $insertReturn = new DeliveryReturn();
            $insertReturn->ds_num = $dsNum;
            $insertReturn->ds_line = $dsLine;
            $insertReturn->ds_type = $dsType;
            $insertReturn->qty = $shippedQty;
            $insertReturn->created_by = backpack_auth()->user()->id;
            $insertReturn->updated_by = backpack_auth()->user()->id;
            $insertReturn->save();
        
            DB::commit();

            $message = 'Delivery Return Created';

            Alert::success($message)->flash();

            return response()->json([
                'status' => true,
                'alert' => 'success',
                'message' => $message,
                'redirect_to' => url('admin/delivery-return/create-ds?num='.$dsNum.'&line='.$dsLine),
                'validation_errors' => []
            ], 200);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => $e->getMessage(),
                'validation_errors' => []
            ], 500);
        }
    }

}
