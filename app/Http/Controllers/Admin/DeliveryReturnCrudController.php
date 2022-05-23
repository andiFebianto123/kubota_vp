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
use App\Http\Requests\DeliveryRepairRequest;
use App\Http\Requests\DeliveryRequest;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use App\Library\ExportXlsx;
use App\Models\DeliveryRepair;
use App\Models\DeliveryReturn;
use App\Models\IssuedMaterialOuthouse;
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
        if(Constant::checkPermission('Create Delivery Return')){
            $this->crud->addButtonFromModelFunction('line', 'create_ds_return', 'createDsReturn', 'end');
        }
        if(Constant::checkPermission('Close Delivery Return')){
            $this->crud->addButtonFromView('line', 'closed_ds_return', 'closed_ds_return', 'end');
        }
        $this->crud->exportRoute = url('admin/delivery-statuses-export');
        $this->crud->query->join('delivery_status', function($join){
            $join->on('delivery_status.ds_num', '=', 'delivery_repair.ds_num_reject');
            $join->on('delivery_status.ds_line', '=', 'delivery_repair.ds_line_reject');
        });

        $sqlAvailableQty = "(repair_qty
                            -
                            (IFNULL((SELECT sum(shipped_qty) from delivery dlv 
                                    WHERE dlv.ref_ds_num = delivery_repair.ds_num_reject  
                                    AND dlv.ref_ds_line = delivery_repair.ds_line_reject
                                AND dlv.ds_type IN ('0P', '1P')
                            ),0))
                            -
                            (IFNULL((SELECT sum(shipped_qty) from delivery dlv 
                                    WHERE dlv.ref_ds_num = delivery_repair.ds_num_reject  
                                    AND dlv.ref_ds_line = delivery_repair.ds_line_reject
                                AND dlv.ds_type IN ('R0', 'R1')
                            ),0))) > 0";

        $this->crud->query = $this->crud->query->whereRaw($sqlAvailableQty);
        $this->crud->addClause('where', 'repair_type', 'RETURN');
        $this->crud->groupBy('ds_num_reject');
        $this->crud->groupBy('ds_line_reject');
        $this->crud->query = $this->crud->query->select('delivery_repair.*','delivery_status.item', 'delivery_status.description', 
        'delivery_status.received_qty', 'delivery_status.po_num', 'delivery_status.po_line', 'delivery_status.shipped_qty', 'delivery_status.rejected_qty');

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
                return $query->orderBy('delivery_status.po_num', $columnDirection)
                ->select('delivery_repair.*','delivery_status.item', 'delivery_status.description', 
                'delivery_status.received_qty', 'delivery_status.po_num', 'delivery_status.po_line', 'delivery_status.shipped_qty', 'delivery_status.rejected_qty');        
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
                $availableQty = (new DsValidation())->availableQtyReturn($entry->ds_num_reject, $entry->ds_line_reject);

                return $availableQty;
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
        if(!Constant::checkPermission('Create Delivery Return')){
            abort(403);
        }

        CRUD::setValidation(DeliveryRequest::class);

        $dsNum = request('num');
        $dsLine = request('line');

        $deliveryStatus = DeliveryStatus::where('ds_num', $dsNum)
                        ->where('ds_line', $dsLine)
                        ->first();

        if (!isset($deliveryStatus)) {
            abort(403);
        }
        
        $deliveryRepair = DeliveryRepair::where('ds_num_reject', $dsNum)
                        ->where('ds_line_reject', $dsLine)
                        ->first();

        $deliveryReturns = DeliveryReturn::join('delivery as dlv', function($join){
                                $join->on('dlv.ds_num', '=', 'delivery_return.ds_num');
                                $join->on('dlv.ds_line', '=', 'delivery_return.ds_line');
                            })
                            ->where('ref_ds_num', $dsNum)
                            ->where('ref_ds_line', $dsLine)
                            ->get(['delivery_return.*', 'dlv.no_surat_jalan_vendor','dlv.shipped_qty',
                            'dlv.ref_ds_num', 'dlv.ref_ds_line', 'dlv.petugas_vendor']);

       
        $availableQty = (new DsValidation())->availableQtyReturn($dsNum, $dsLine);

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
            'type' => 'number_qty_return',
            'name' => 'shipped_qty',
            'label' => 'Qty',
            'actual_qty' => $deliveryStatus->shipped_qty,
            'default' => $availableQty,
            'attributes' => [
                'data-max' =>  $availableQty,
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
        $data['deliveryRepair'] = $deliveryRepair;
        $data['availableQty'] = $availableQty; 
        
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


    public function store(DeliveryRepairRequest $request)
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $request = $this->crud->getRequest();

        $strDsNum = $request->input('ds_num');
        $strDsLine = $request->input('ds_line');
        $petugasVendor = $request->input('petugas_vendor');
        $noSuratJalanVendor = $request->input('no_surat_jalan_vendor');
        $shippedQty = $request->input('shipped_qty');
        $shippedDate = $request->input('shipped_date');

        $delivery = Delivery::where('ds_num', $strDsNum)->where('ds_line', $strDsLine)->first();

        $dsNum =  (new Constant())->codeDs($delivery->po_num, $delivery->po_line, $shippedDate);
        $availableQty = (new DsValidation())->availableQtyReturn($strDsNum, $strDsLine);

        if ($availableQty < $shippedQty) {
            $errors = ['shipped_qty' => 'Jumlah Qty melebihi batas maksimal'];

            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => "Qty Alert",
                'errors' => $errors
            ], 422);
        }

        $dsType = '0P';
        
        DB::beginTransaction();

        try{

            $insertDsheet = new Delivery();
            $insertDsheet->ds_num = $dsNum['single'];
            $insertDsheet->po_num = $delivery->po_num;
            $insertDsheet->po_line = $delivery->po_line;
            $insertDsheet->po_release = $delivery->po_release;
            $insertDsheet->po_change = $delivery->po_change;
            $insertDsheet->ds_line = $dsNum['line'];
            $insertDsheet->ds_type = $dsType;
            $insertDsheet->item = $delivery->item;
            $insertDsheet->description = $delivery->description;
            $insertDsheet->u_m = $delivery->u_m;
            $insertDsheet->due_date = $delivery->due_date;
            $insertDsheet->unit_price = $delivery->unit_price;
            $insertDsheet->wh = $delivery->wh;
            $insertDsheet->location = $delivery->location;
            $insertDsheet->tax_status = $delivery->tax_status;
            $insertDsheet->currency = $delivery->currency;
            $insertDsheet->shipped_qty = $shippedQty;
            $insertDsheet->shipped_date = $shippedDate;
            $insertDsheet->order_qty = $delivery->order_qty;
            $insertDsheet->w_serial = $delivery->w_serial;
            $insertDsheet->petugas_vendor = $petugasVendor;
            $insertDsheet->no_surat_jalan_vendor = $noSuratJalanVendor;
            $insertDsheet->ref_ds_num = $strDsNum;
            $insertDsheet->ref_ds_line = $strDsLine;
            $insertDsheet->created_by = backpack_auth()->user()->id;
            $insertDsheet->updated_by = backpack_auth()->user()->id;
            $insertDsheet->save();

            // Insert delivery status
            $insertDstatus = new DeliveryStatus();
            $insertDstatus->ds_num = $dsNum['single'];
            $insertDstatus->po_num = $delivery->po_num;
            $insertDstatus->po_line = $delivery->po_line;
            $insertDstatus->po_release = $delivery->po_release;
            $insertDstatus->ds_line = $dsNum['line'];
            $insertDstatus->ds_type = $dsType;
            $insertDstatus->item = $delivery->item;
            $insertDstatus->description = $delivery->description;
            $insertDstatus->unit_price = $delivery->unit_price;
            $insertDstatus->shipped_qty = $shippedQty;
            $insertDstatus->petugas_vendor = $petugasVendor;
            $insertDstatus->no_surat_jalan_vendor = $noSuratJalanVendor;
            $insertDstatus->ref_ds_num = $strDsNum;
            $insertDstatus->ref_ds_line = $strDsLine;
            $insertDstatus->created_by = backpack_auth()->user()->id;
            $insertDstatus->updated_by = backpack_auth()->user()->id;
            $insertDstatus->save();

            // Insert delivery sheet
            $insertReturn = new DeliveryReturn();
            $insertReturn->ds_num = $dsNum['single'];
            $insertReturn->ds_line = $dsNum['line'];
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
                'redirect_to' => url('admin/delivery-return/create-ds?num='.$strDsNum.'&line='.$strDsLine),
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


    public function closeDs(Request $request)
    {
        if(!Constant::checkPermission('Close Delivery Return')){
            abort(403);
        }

        $id = $request->input('id');
        
        $dr = DeliveryRepair::where('id', $id)->first();
        $availableQty = (new DsValidation())->availableQtyReturn($dr->ds_num_reject, $dr->ds_line_reject);

        $delivery = Delivery::where('ds_num', $dr->ds_num_reject)->where('ds_line', $dr->ds_line_reject)->first();

        $dsNum =  (new Constant())->codeDs($delivery->po_num, $delivery->po_line, $availableQty);

        if ($availableQty <= 0) {

            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => "Qty Sudah diclosed",
            ], 422);
        }

        $dsType = 'R0';
        
        DB::beginTransaction();

        try{
            $insertDsheet = new Delivery();
            $insertDsheet->ds_num = $dsNum['single'];
            $insertDsheet->po_num = $delivery->po_num;
            $insertDsheet->po_line = $delivery->po_line;
            $insertDsheet->po_release = $delivery->po_release;
            $insertDsheet->po_change = $delivery->po_change;
            $insertDsheet->ds_line = $dsNum['line'];
            $insertDsheet->ds_type = $dsType;
            $insertDsheet->item = $delivery->item;
            $insertDsheet->description = $delivery->description;
            $insertDsheet->u_m = $delivery->u_m;
            $insertDsheet->due_date = $delivery->due_date;
            $insertDsheet->unit_price = $delivery->unit_price;
            $insertDsheet->wh = $delivery->wh;
            $insertDsheet->location = $delivery->location;
            $insertDsheet->tax_status = $delivery->tax_status;
            $insertDsheet->currency = $delivery->currency;
            $insertDsheet->shipped_qty = $availableQty;
            $insertDsheet->shipped_date = now();
            $insertDsheet->order_qty = $delivery->order_qty;
            $insertDsheet->w_serial = $delivery->w_serial;
            $insertDsheet->petugas_vendor = $delivery->petugas_vendor;
            $insertDsheet->no_surat_jalan_vendor = $delivery->no_surat_jalan_vendor;
            $insertDsheet->ref_ds_num = $delivery->ds_num;
            $insertDsheet->ref_ds_line = $delivery->ds_line;
            $insertDsheet->created_by = backpack_auth()->user()->id;
            $insertDsheet->updated_by = backpack_auth()->user()->id;
            $insertDsheet->save();

            // Insert delivery status
            $insertDstatus = new DeliveryStatus();
            $insertDstatus->ds_num = $dsNum['single'];
            $insertDstatus->po_num = $delivery->po_num;
            $insertDstatus->po_line = $delivery->po_line;
            $insertDstatus->po_release = $delivery->po_release;
            $insertDstatus->ds_line = $dsNum['line'];
            $insertDstatus->ds_type = $dsType;
            $insertDstatus->item = $delivery->item;
            $insertDstatus->description = $delivery->description;
            $insertDstatus->unit_price = $delivery->unit_price;
            $insertDstatus->shipped_qty = $availableQty;
            $insertDstatus->petugas_vendor = $delivery->petugas_vendor;
            $insertDstatus->no_surat_jalan_vendor = $delivery->no_surat_jalan_vendor;
            $insertDstatus->ref_ds_num = $delivery->ds_num;
            $insertDstatus->ref_ds_line = $delivery->ds_line;
            $insertDstatus->created_by = backpack_auth()->user()->id;
            $insertDstatus->updated_by = backpack_auth()->user()->id;
            $insertDstatus->save();

            // Insert delivery sheet
            $insertReturn = new DeliveryReturn();
            $insertReturn->ds_num = $dsNum['single'];
            $insertReturn->ds_line = $dsNum['line'];
            $insertReturn->ds_type = $dsType;
            $insertReturn->qty = $availableQty;
            $insertReturn->created_by = backpack_auth()->user()->id;
            $insertReturn->updated_by = backpack_auth()->user()->id;
            $insertReturn->save();
        
            DB::commit();

            $message = 'Delivery Return Closed';

            Alert::success($message)->flash();

            return true;

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


    public function destroy($id)
    {
        $deliveryReturn = DeliveryReturn::where('id', $id)->first();
        if (isset($deliveryReturn)) {
            DB::beginTransaction();
            try {
                DeliveryStatus::where('ds_num', $deliveryReturn->ds_num)
                ->where('ds_line', $deliveryReturn->ds_line)->delete();
                Delivery::where('ds_num', $deliveryReturn->ds_num)
                ->where('ds_line', $deliveryReturn->ds_line)->delete();
                DeliveryReturn::where('id', $id)->delete();
                
                DB::commit();

            } catch(\Exception $e){
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'alert' => 'danger',
                    'message' => $e->getMessage(),
                    'validation_errors' => []
                ], 500);
            }
        }

        return true;
    }


}
