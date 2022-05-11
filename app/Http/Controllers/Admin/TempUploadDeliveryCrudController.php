<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constant;
use App\Helpers\DsValidation;
use App\Http\Requests\DeliveryRequest;
use App\Http\Requests\TempUploadDeliveryRequest;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\IssuedMaterialOuthouse;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\TempUploadDelivery;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Prologue\Alerts\Facades\Alert;

class TempUploadDeliveryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\TempUploadDelivery::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/purchase-order/temp-upload-delivery');
        CRUD::setEntityNameStrings('temp upload delivery', 'temp upload deliveries');
    }


    protected function setupListOperation()
    {
        $this->crud->removeButton('create');
        $this->crud->removeButton('show');
        $this->crud->addButtonFromView('top', 'insert_from_temp', 'insert_from_temp', 'beginning');
        $this->crud->addButtonFromView('top', 'insert_print_from_temp', 'insert_print_from_temp', 'beginning');
        $this->crud->addButtonFromView('top', 'cancel_temp', 'cancel_temp', 'end');
        $this->crud->addClause('where','user_id', backpack_auth()->user()->id);
        $this->crud->orderBy('po_num', 'asc');        
        $this->crud->orderBy('po_line', 'asc');        

        CRUD::addColumn([
            'name'     => 'po_po_line',
            'label'    => 'PO',
            'type'     => 'closure',
            'function' => function($entry) {
                return $entry->po_num.'-'.$entry->po_line;
            }
        ]);

        CRUD::addColumn([
            'label'     => 'Delivery Date', // Table column heading
            'name'      => 'delivery_date',
        ]);
        CRUD::addColumn([
            'label'     => 'Item', // Table column heading
            'name'      => 'po_item', 

        ]);
        CRUD::addColumn([
            'label'     => 'Description', // Table column heading
            'name'    => 'po_description', 
        ]);
        CRUD::addColumn([
            'label'     => 'Qty', // Table column heading
            'name'      => 'shipped_qty', 
        ]);
        CRUD::addColumn([
            'label'     => 'Available Qty', // Table column heading
            'name'      => 'available_qty', 
        ]);
        CRUD::addColumn([
            'label'     => 'Petugas Vendor', // Table column heading
            'name'      => 'petugas_vendor', 
        ]);
        CRUD::addColumn([
            'label'     => 'No Surat Jalan', // Table column heading
            'name'      => 'no_surat_jalan_vendor', 
        ]);
        CRUD::addColumn([
            'label'     => 'Validation', // Table column heading
            'name'      => 'validation_text', 
            'type'      => 'model_function',
            'function_name' => 'getValidationText',
            'limit' => 1000
        ]);
    }
    

    protected function setupCreateOperation()
    {
        $this->crud->denyAccess('create');
    }

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $totalRows = $this->crud->model->count();
        $cloneQuery = clone $this->crud->query;
        $queryWithSelect = $cloneQuery->select('po_num');
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

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows, $startIndex);
    }


    private function insertMassData(){
        $dataTemps = TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->get();
        $arrIds = [];
        DB::beginTransaction();

        try{
            $successInsert = 0;
            $totalInsert = sizeof($dataTemps);
            foreach ($dataTemps as $key => $dataTemp) { 
                $poLine = PurchaseOrderLine::where('po_line.po_num', $dataTemp->po_num)
                            ->leftJoin('po', 'po.po_num', 'po_line.po_num' )
                            ->where('po_line.po_line', $dataTemp->po_line)
                            ->orderBy('po_line.po_change', 'desc')
                            ->first();
                $ds_num =  (new Constant())->codeDs($dataTemp->po_num, $dataTemp->po_line, $dataTemp->delivery_date);
                $ds_line = $ds_num['line'];
    
                $insertDlv = new Delivery();
                $insertDlv->ds_num = $ds_num['single'];
                $insertDlv->group_ds_num = $ds_num['group'];
                $insertDlv->po_line = $dataTemp->po_line;
                $insertDlv->po_num = $dataTemp->po_num;
                $insertDlv->po_change = $poLine->po_change;
                $insertDlv->po_release = $poLine->po_num."-" .$poLine->po_line;
                $insertDlv->ds_line = $ds_line;
                $insertDlv->item = $poLine->item;
                $insertDlv->description = $poLine->description;
                $insertDlv->u_m = $poLine->u_m;
                $insertDlv->due_date = $poLine->due_date;
                $insertDlv->unit_price = $poLine->unit_price;
                $insertDlv->wh = $poLine->wh;
                $insertDlv->location = $poLine->location;
                $insertDlv->tax_status = $poLine->tax_status;
                $insertDlv->currency = $poLine->currency;
                $insertDlv->shipped_qty = $dataTemp->shipped_qty;
                $insertDlv->shipped_date = date('Y-m-d', strtotime($dataTemp->delivery_date));
                $insertDlv->order_qty = $poLine->order_qty;
                $insertDlv->w_serial = ($dataTemp->serial_number) ? $dataTemp->serial_number : 0;
                $insertDlv->petugas_vendor = $dataTemp->petugas_vendor;
                $insertDlv->no_surat_jalan_vendor = $dataTemp->no_surat_jalan_vendor;
    
                if ($poLine->status == 'O' && $poLine->accept_flag == 1 && $dataTemp->category_validation != 'danger') {
                    $insertDlv->save();
    
                    $insertDlvStatus = new DeliveryStatus();
                    $insertDlvStatus->ds_num = $ds_num['single'];
                    $insertDlvStatus->po_num = $poLine->po_num;
                    $insertDlvStatus->po_line = $poLine->po_line;
                    $insertDlvStatus->po_release = $poLine->po_num."-" .$poLine->po_line;
                    $insertDlvStatus->ds_line = $ds_line;
                    $insertDlvStatus->item = $poLine->item;
                    $insertDlvStatus->description = $poLine->description;
                    $insertDlvStatus->unit_price = $poLine->unit_price;
                    $insertDlvStatus->tax_status = $poLine->tax_status;
                    $insertDlvStatus->shipped_qty = $dataTemp->shipped_qty;
                    $insertDlvStatus->petugas_vendor = $dataTemp->petugas_vendor;
                    $insertDlvStatus->no_surat_jalan_vendor = $dataTemp->no_surat_jalan_vendor;
                    $insertDlvStatus->created_by = backpack_auth()->user()->id;
                    $insertDlvStatus->updated_by = backpack_auth()->user()->id;
                    $insertDlvStatus->save();
    
                    $arrIds[] = $insertDlv->id;
    
                    if ($poLine->outhouse_flag == 1 && isset($dataTemp->data_attr)) {
                        $dataAttrs = json_decode($dataTemp->data_attr);
                        $int_ds_detail = 1;
                        foreach ($dataAttrs->attributes as $key => $da) {
                            $materialOuthouse = MaterialOuthouse::where('id', $da->id)->first();
                            if (isset($materialOuthouse)) {
                                $insertOuthouse = new IssuedMaterialOuthouse();
                                $insertOuthouse->ds_num = $ds_num['single'];
                                $insertOuthouse->ds_line = $ds_line;
                                $insertOuthouse->ds_detail = $int_ds_detail++;
                                $insertOuthouse->matl_item = $materialOuthouse->matl_item;
                                $insertOuthouse->description =  $materialOuthouse->description;
                                $insertOuthouse->lot =  $materialOuthouse->lot;
                                $insertOuthouse->issue_qty =  $da->qty;
                                $insertOuthouse->save();
                            }
                        }
                    }

                    if ( $poLine->outhouse_flag == 1) {
                        $outhouseMaterials = MaterialOuthouse::where('po_num', $poLine->po_num)
                                    ->where('po_line', $poLine->po_line)
                                    ->groupBy('matl_item')
                                    ->get();

                        foreach ($outhouseMaterials as $key => $om) {
                            $issuedQty =  $dataTemp->shipped_qty * $om->qty_per;
        
                            $insertImo = new IssuedMaterialOuthouse();
                            $insertImo->ds_num =  $ds_num['single'];
                            $insertImo->ds_line = $ds_line;
                            $insertImo->ds_detail = $poLine->item;
                            $insertImo->matl_item = $om->matl_item;
                            $insertImo->description = $om->description;
                            $insertImo->lot =  $om->lot;
                            $insertImo->issue_qty = $issuedQty;
                            $insertImo->po_num = $poLine->po_num;
                            $insertImo->po_line = $poLine->po_line;
                            $insertImo->ds_type = $ds_num['type'];
                            $insertImo->vend_num = $poLine->vend_num;
                            $insertImo->created_by = backpack_auth()->user()->id;
                            $insertImo->updated_by = backpack_auth()->user()->id;
                            $insertImo->save();
                        }
                    }
                    $successInsert++;
                }
            }
    
            $message = "";
            $alert = "";
            $status = false;
            if ($successInsert == $totalInsert && $totalInsert > 0) {
                $status = true;
                $alert = "success";
                $message = "Data has been imported successfully (".$successInsert."/". $totalInsert.")";
            }else if ($successInsert < $totalInsert && $successInsert > 0) {
                $status = true;
                $alert = "warning";
                $message = "Data has been imported successfully (".$successInsert."/". $totalInsert.")";
            }else if($successInsert == 0){
                $status = false;
                $alert = "danger";
                $message = "Failure import data (".$successInsert."/". $totalInsert.")";
            }

            if ($status) {
                // TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->delete();

                $tempUploadDeliverys = TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->get();
                if($tempUploadDeliverys->count() > 0){
                    foreach($tempUploadDeliverys as $tempUpload){
                        $tempUpload->delete();
                    }
                }

                DB::commit();
            }else{
                DB::rollback();
            }
            return ['status' => $status, 'arr_ids' => $arrIds, 'message' => $message, 'alert' => $alert];

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


    public function insertToDb(Request $request)
    {
        $imd = $this->insertMassData();
        
        return response()->json([
            'status' => $imd['status'],
            'alert' => $imd['alert'],
            'message' => $imd['message'],
            'validation_errors' => []
        ], 200);
    }


    public function printInsertToDb(Request $request)
    {
        $imd = $this->insertMassData();

        return response()->json([
            'status' => $imd['status'],
            'alert' => $imd['alert'],
            'arr_ids' => $imd['arr_ids'] ?? [],
            'message' => $imd['message'],
            'validation_errors' => []
        ], 200);

    }


    public function cancelToDb(Request $request)
    {
        // TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->delete();
        $tempUploadDeliverys = TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->get();
        if($tempUploadDeliverys->count() > 0){
            foreach($tempUploadDeliverys as $temp){
                $temp->delete();
            }
        }
    }


    public function edit($id)
    {
        CRUD::setValidation(DeliveryRequest::class);

        $entry = $this->crud->getCurrentEntry();
        $poLine = PurchaseOrderLine::where('po_num', $entry->po_num)->where('po_line', $entry->po_line)->first();

        $dsValidation = new DsValidation();
        $args = [
            'po_num' => $entry->po_num, 
            'po_line' => $entry->po_line, 
            'order_qty' => $poLine->order_qty 
        ];
        $currentQty = $dsValidation->currentMaxQty($args)['datas'];

        $arrPoLineStatus = (new Constant())->statusOFC();

        $this->crud->addField([
            'label' => 'Delivery Date From Vendor',
            'type' => 'date_picker',
            'name' => 'shipped_date',
            'default' => $entry->delivery_date,
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd/mm/yyyy',
                'language' => 'en'
             ],
        ]); 
        $this->crud->addField([
            'type' => 'hidden',
            'name' => 'po_line_id',
            'value' => $entry->id
        ]);    
        $this->crud->addField([
            'type' => 'text',
            'name' => 'petugas_vendor',
            'label' => 'Petugas Vendor',
            'value' => $entry->petugas_vendor
        ]);    
        $this->crud->addField([
            'type' => 'text',
            'label' => 'No Surat Jalan',
            'name' => 'no_surat_jalan_vendor',
            'value' => $entry->no_surat_jalan_vendor
        ]);        
        $this->crud->addField([
            'type' => 'number_qty',
            'name' => 'shipped_qty',
            'label' => 'Qty',
            'actual_qty' => $entry->shipped_qty,
            'default' => $entry->shipped_qty,
            'attributes' => [
                'data-max' =>  $currentQty,
              ], 
        ]);
        if($poLine->w_serial == 1){
            $this->crud->addField(
                [
                    'name'  => 'sn_childs[]',
                    'label' => 'Serial Number',
                    'type'  => 'upload_serial_number',
                ],
            );
        }
        if($poLine->outhouse_flag == 1){
            $outhouseMaterials = MaterialOuthouse::where('po_num', $entry->po_num)
                                    ->where('po_line', $entry->po_line);

            $this->crud->addField(
                [
                    'name'  => 'material_issues',
                    'label' => 'Material Issue',
                    'type'  => 'outhouse_table',
                    'current_qty' => $currentQty,
                    'total_qty_per' => $outhouseMaterials->sum('qty_per'),
                    'table_body' => $outhouseMaterials->get(),
                    'data_table' => (isset($entry->data_attr)) ? json_decode($entry->data_attr): null
                ],
            );
        }

        $arrFilters = [];
        $arrFilters[] = ['po_line.item', '=', $poLine->item];
        $args = [
            'filters' => $arrFilters, 
            'due_date' => $poLine->due_date,
            'po_num' => $entry->po_num,
            'po_line' => $entry->po_line,
        ];
        $unfinished_po_line = (new DsValidation())->unfinishedPoLineMass($args);
        
        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['po_line'] = $poLine;
        $data['title'] = trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $data['id'] = $id;
        $data['arr_po_line_status'] = $arrPoLineStatus;
        $data['unfinished_po_line'] = $unfinished_po_line;

        return view('vendor.backpack.crud.form_edit_temp', $data);
    }


    public function update($id)
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $entry = $this->crud->getCurrentEntry();
        $request = $this->crud->getRequest();
        $shippedQty = $request->input('shipped_qty');
        $shippedDate = $request->input('shipped_date');
        $petugas_vendor = $request->input('petugas_vendor');
        $noSuraJalanVendor = $request->input('no_surat_jalan_vendor');
        $snChilds = $request->input('sn_childs');
        $materialIds = $request->input('material_ids');
        $materialIssues = $request->input('material_issues');

        $poLine = PurchaseOrderLine::where('po_num', $entry->po_num)
                    ->where('po_line', $entry->po_line)
                    ->first();

        $alertFor = "";
        $args = [
            'po_num' => $poLine->po_num, 
            'po_line' => $poLine->po_line , 
            'order_qty' => $shippedQty
        ];
        $cmq =  (new DsValidation())->currentMaxQty($args);
        if ($poLine->outhouse_flag == 1) {
            $alertFor = " Outhouse";
            $cmq = (new DsValidation())->currentMaxQtyOuthouse($args);
        }

        if ($cmq['datas'] < $shippedQty) {
            $errors = ['shipped_qty' => 'Jumlah Qty melebihi batas maksimal '.$cmq['datas'] ];

            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => "Qty Alert ".$alertFor,
                'errors' => $errors
            ], 422);
        }

        $arrDatas = [];
        if (isset($snChilds)) {
            $arrDatas = ['type' =>'serial_number'];

            foreach ($snChilds as $j => $snc) {
                $arrDatas['attributes'][] = $snc;
            }
        }

        if (isset($materialIds)) {
            $arrDatas = ['type' =>'material_outhouse'];
            $any_errors = false;
            foreach ($materialIds as $k => $oi) {
                $mo = MaterialOuthouse::where('id', $oi)->first();
                $issuedQty = $shippedQty * $mo->qty_per;
                $lotQty = $mo->lot_qty;

                $arrDatas['attributes'][] = ['id' => $oi, 'qty' => $materialIssues[$k]];

                if ($issuedQty > $lotQty ) {
                    $any_errors = true;
                }
            }

            if ($any_errors) {
                $errors = ['mo_issue_qty' => 'Jumlah Qty melebihi batas maksimal'];

                return response()->json([
                    'status' => false,
                    'alert' => 'danger',
                    'message' => "Qty Alert",
                    'errors' => $errors
                ], 422);
            }
        }

        $dataAttr = json_encode($arrDatas);
        
        $change = TempUploadDelivery::where('id', $id)->first();
        $change->shipped_qty = $shippedQty;
        $change->delivery_date = $shippedDate;
        $change->petugas_vendor = $petugas_vendor;
        $change->no_surat_jalan_vendor = $noSuraJalanVendor;
        $change->data_attr = $dataAttr;
        $change->save();

        $message = 'Sukses Update !';

        Alert::success($message)->flash();

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => $message,
            'redirect_to' => url('admin/purchase-order/temp-upload-delivery'),
            'validation_errors' => []
        ], 200);
    }
}
