<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TemplateSerialNumberExport;
use App\Helpers\Constant;
use App\Helpers\DsValidation;
use App\Http\Requests\DeliveryRequest;
use App\Imports\SerialNumberImport;
use App\Models\Delivery;
use App\Models\DeliveryReject;
use App\Models\DeliveryRepair;
use App\Models\DeliveryStatus;
use App\Models\IssuedMaterialOuthouse;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrderLine;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeliveryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Delivery::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery');
        CRUD::setEntityNameStrings('delivery sheet', 'delivery sheets');
        $this->crud->denyAccess('list');
        if(Constant::checkPermission('Read Delivery Sheet in Table')){
            $this->crud->allowAccess('list');
        }
    }

    public function create(){
        return abort(404);
    }


    public function edit(){
        return abort(404);
    }


    protected function setupListOperation()
    {
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');
        $this->crud->enableBulkActions();

        $this->crud->addButtonFromView('top', 'bulk_print_ds_no_price', 'bulk_print_ds_no_price', 'end');

        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $this->crud->addButtonFromView('top', 'bulk_print_label', 'bulk_print_label', 'beginning');
        }else{
            if(!Constant::checkPermission('Delete Delivery Sheet in Table')){
                $this->crud->removeButton('delete');
            }
            if(Constant::checkPermission('Print Label')){
                $this->crud->addButtonFromView('top', 'bulk_print_label', 'bulk_print_label', 'beginning');
            }
            $this->crud->addClause('join', 'po', 'po.po_num', 'delivery.po_num');
            $this->crud->addClause('where', 'po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
            $this->crud->query = $this->crud->query->select('delivery.*', 'po.vend_num');
        }


        CRUD::addColumn([
            'label'     => 'DS Number',
            'name'      => 'ds_num',
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'DS Line',
            'name'      => 'ds_line',
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Shipped Date',
            'name'      => 'shipped_date',
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'PO',
            'name'      => 'po_po_line',
            'orderable'  => true,
            'searchLogic' => function ($query, $column, $searchTerm) {
                if ($column['name'] == 'po_po_line') {
                    $searchOnlyPo = str_replace("-", "", $searchTerm);
                    $query->orWhere('delivery.po_num', 'like', '%'.$searchOnlyPo.'%');
                    if (str_contains($searchTerm, '-')) {
                        $query->orWhere(function($q) use ($searchTerm) {
                            $searchWithSeparator = explode("-", $searchTerm);
                            $q->where('delivery.po_num', 'like', '%'.$searchWithSeparator[0].'%')
                              ->Where('delivery.po_line', 'like', '%'.$searchWithSeparator[1].'%');
                        });
                    }
                }
            },
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->orderBy('delivery.po_num', $columnDirection)->select('delivery.*');
            }
        ]);
        CRUD::addColumn([
            'label'     => 'Order Qty',
            'name'      => 'order_qty',
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Shipped Qty',
            'name'      => 'shipped_qty',
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'DO Number',
            'name'      => 'no_surat_jalan_vendor',
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Operator',
            'name'      => 'petugas_vendor',
            'type' => 'text',
        ]);

        if(Constant::getRole() == 'Admin PTKI'){
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor'
            ],
            url('admin/filter-vendor/ajax-itempo-options'),
            function($value) {
                $dbGet = Delivery::join('po', 'po.po_num', 'delivery.po_num')
                ->select('delivery.id as id')
                ->where('po.vend_num', $value)
                ->get()
                ->mapWithKeys(function($po, $index){
                    return [$index => $po->id];
                });
                $this->crud->addClause('whereIn', 'id', $dbGet->unique()->toArray());
            });
        }

    }


    protected function setupCreateOperation()
    {

        CRUD::setValidation(DeliveryRequest::class);

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
            'name' => 'po_line_id',
            'value' => request('po_line_id')
        ]);
        $this->crud->addField([
            'type' => 'text',
            'name' => 'petugas_vendor',
            'default' => Auth::guard('backpack')->user()->name
        ]);
        CRUD::field('no_surat_jalan_vendor');
        CRUD::field('order_qty');
        CRUD::field('serial_number');
    }


    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }


    public function show()
    {
        $entry = $this->crud->getCurrentEntry();

        $deliveryStatus = DeliveryStatus::where('ds_num', $entry->ds_num )
                            ->where('ds_line', $entry->ds_line)
                            ->first();

        $deliveryRejects = DeliveryReject::where('ds_num', $entry->ds_num )
                            ->where('ds_line', $entry->ds_line)
                            ->get();

        $deliveryRepairs = DeliveryRepair::where('ds_num_reject', $entry->ds_num )
                            ->where('ds_line_reject', $entry->ds_line)
                            ->get();

        $qtyRejectCount = DeliveryReject::where('ds_num', $entry->ds_num )
                            ->where('ds_line', $entry->ds_line)
                            ->sum('rejected_qty');

        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['delivery_show'] = $this->detailDS($entry->id)['delivery_show'];
        $data['delivery_status'] = $deliveryStatus;
        $data['delivery_rejects'] = $deliveryRejects;
        $data['delivery_repairs'] = $deliveryRepairs;
        $data['qty_reject_count'] = $qtyRejectCount;
        $data['issued_mos'] =$this->detailDS($entry->id)['issued_mos'];
        $data['qr_code'] = $this->detailDS($entry->id)['qr_code'];

        $can_access = false;
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $can_access = true;
        }else{
            $po = Delivery::join('po', 'po.po_num', 'delivery.po_num')
                    ->where('delivery.id', $entry->id )->first();
            if (backpack_auth()->user()->vendor->vend_num == $po->vend_num) {
                $can_access = true;
            }
        }

        if ($can_access) {
            return view('vendor.backpack.crud.delivery_show', $data);
        }else{
            abort(404);
        }
    }


    private function detailDS($id)
    {
        $deliveryShow = Delivery::leftjoin('po_line', function ($join) {
                            $join->on('po_line.po_num', 'delivery.po_num')
                                ->on('po_line.po_line', 'delivery.po_line');
                        })
                        ->leftJoin('po', 'po.po_num', 'delivery.po_num')
                        ->leftJoin('vendor', 'vendor.vend_num', 'po.vend_num')
                        ->where('delivery.id', $id)
                        ->get(['delivery.id as id','delivery.ds_num','delivery.ds_line','delivery.shipped_date', 'po_line.due_date',
                        'delivery.po_release','po_line.item','delivery.u_m', 'vendor.vend_num as vendor_number',
                        'vendor.currency as vendor_currency','vendor.vend_name as vendor_name', 'delivery.no_surat_jalan_vendor',
                        'po_line.item_ptki','po.po_num as po_number','po_line.po_line as po_line', 'delivery.order_qty as order_qty',
                        'delivery.shipped_qty', 'delivery.unit_price', 'delivery.currency', 'delivery.tax_status', 'delivery.description',
                        'delivery.wh', 'delivery.location', 'po_line.inspection_flag'])
                        ->first();

        $issued_mos = IssuedMaterialOuthouse::where('ds_num', $deliveryShow->ds_num )
                        ->where('ds_line', $deliveryShow->ds_line)->get();

        $qr_code = "DSW|";
        $qr_code .= $deliveryShow->ds_num."|";
        $qr_code .= $deliveryShow->ds_line."|";
        $qr_code .= $deliveryShow->po_number."|";
        $qr_code .= $deliveryShow->po_line."|";
        $qr_code .= $deliveryShow->po_release."|";
        $qr_code .= $deliveryShow->item_ptki."|";
        $qr_code .= $deliveryShow->shipped_qty."|";
        $qr_code .= $deliveryShow->u_m."|";
        $qr_code .= $deliveryShow->unit_price."|";
        $qr_code .= date("Y-m-d", strtotime($deliveryShow->shipped_date))."|";
        $qr_code .= $deliveryShow->no_surat_jalan_vendor;

        $data['delivery_show'] = $deliveryShow;
        $data['qr_code'] = $qr_code;
        $data['issued_mos'] = $issued_mos;

        return $data;
    }


    public function store(Request $request)
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $request = $this->crud->getRequest();

        $poLineId = $request->input('po_line_id');
        $shippedQty = $request->input('shipped_qty');
        $shippedDate = $request->input('shipped_date');
        $petugasVendor = $request->input('petugas_vendor');
        $noSuratJalanVendor = $request->input('no_surat_jalan_vendor');
        $materialIds = $request->input('material_ids');
        $moIssueQtys = $request->input('mo_issue_qty');
        $snChilds = $request->input('sn_childs');

        $poLine = PurchaseOrderLine::where('po_line.id', $poLineId)
                ->leftJoin('po', 'po.po_num', 'po_line.po_num' )
                ->first();

        // ds num generator from global function
        $dsNum =  (new Constant())->codeDs($poLine->po_num, $poLine->po_line, $shippedDate);

        $alertFor = "";
        $args = [
            'po_num' => $poLine->po_num,
            'po_line' => $poLine->po_line ,
            'order_qty' => $shippedQty
        ];
        // DS validation function available at App\Helpers\DsValidation
        $cmq = (new DsValidation())->currentMaxQty($args);
        if ($poLine->outhouse_flag == 1) {
            $alertFor = " Outhouse";
            $cmq =  (new DsValidation())->currentMaxQtyOuthouse($args);
        }
        if ($cmq['datas'] < $shippedQty) {
            $errors = ['shipped_qty' => 'Jumlah Qty melebihi batas maksimal'];

            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => "Qty Alert ".$alertFor,
                'errors' => $errors
            ], 422);
        }

        DB::beginTransaction();

        try{
            // Insert delivery sheet
            $insertDsheet = new Delivery();
            $insertDsheet->ds_num = $dsNum['single'];
            $insertDsheet->po_num = $poLine->po_num;
            $insertDsheet->po_line = $poLine->po_line;
            $insertDsheet->po_release = $poLine->po_release;
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

            // this rule for po with serial number
            if ( $poLine->w_serial == 1 && isset($snChilds)) {
                foreach ($snChilds as $key => $snChild) {
                    if (isset($snChild)) {
                        $uid = backpack_auth()->user()->id;
                        // sql query to prevent duplicate ds_detail
                        $sqlQuery = "INSERT INTO delivery_serial (ds_num,
                                    ds_line,
                                    no_mesin,
                                    created_by,
                                    updated_by,
                                    created_at,
                                    updated_at,
                                    ds_detail )
                                    SELECT '".$insertDsheet->ds_num."',
                                    '".$insertDsheet->ds_line."',
                                    '".$snChild."',
                                    '".$uid ."',
                                    '".$uid ."',
                                    '".now() ."',
                                    '".now() ."',
                                    COUNT(*)+1
                                    FROM delivery_serial
                                    WHERE ds_num = '".$insertDsheet->ds_num."'
                                    AND ds_line = '".$insertDsheet->ds_line."'";

                        DB::statement($sqlQuery);
                    }
                }
            }

            // this rule for po with material outhouse
            if ( $poLine->outhouse_flag == 1 && isset($materialIds)) {
                $anyErrors = false;
                $intDsDetail = 1;
                foreach ($materialIds as $key => $materialId) {
                    $mo = MaterialOuthouse::where('id', $materialId)->first();
                    $moIssueQty = $moIssueQtys[$key];
                    $issuedQty =  $shippedQty * $mo->qty_per;
                    $remainingQty =  $mo->remaining_qty;

                    $insertImo = new IssuedMaterialOuthouse();
                    $insertImo->ds_num = $insertDsheet->ds_num;
                    $insertImo->ds_line = $insertDsheet->ds_line;
                    $insertImo->ds_detail = $intDsDetail++;
                    $insertImo->matl_item = $mo->matl_item;
                    $insertImo->description = $mo->description;
                    $insertImo->lot =  $mo->lot;
                    $insertImo->issue_qty = $moIssueQty;
                    $insertImo->created_by = backpack_auth()->user()->id;
                    $insertImo->updated_by = backpack_auth()->user()->id;
                    $insertImo->save();
                    if ($issuedQty > $remainingQty ) {
                        $anyErrors = true;
                    }
                }

                if ($anyErrors) {
                    DB::rollBack();

                    $errors = ['mo_issue_qty' => 'Jumlah Qty melebihi batas maksimal'];

                    return response()->json([
                        'status' => false,
                        'alert' => 'danger',
                        'message' => "Qty Alert",
                        'errors' => $errors
                    ], 422);
                }
            }

            DB::commit();

            $message = 'Delivery Sheet Created';

            Alert::success($message)->flash();

            return response()->json([
                'status' => true,
                'alert' => 'success',
                'message' => $message,
                'redirect_to' => url('admin/purchase-order-line/'.$poLineId.'/show'),
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


    public function exportPdf()
    {
        $id = request('id');
        $withPrice = request('wh');

        $data['delivery_show'] = $this->detailDS($id)['delivery_show'];
        $data['qr_code'] = $this->detailDS($id)['qr_code'];
        $data['issued_mos'] = $this->detailDS($id)['issued_mos'];
        $data['with_price'] = $withPrice;

    	$pdf = PDF::loadview('exports.pdf.delivery_sheet',$data);
        $filename = 'ds-'.date('YmdHis').'.pdf';

        return $pdf->download($filename);
    }


    public function exportMassPdf()
    {
        $strParam = request('param');
        $arrParam = unserialize(base64_decode($strParam));

        $printAll = $arrParam['print_all'];
        $poNum = $arrParam['po_num'];
        $poLine = $arrParam['po_line'];
        $printDeliveries = $arrParam['print_delivery'];
        $withPrice = $arrParam['with_price'];

        if ($printAll) {
            $deliveries = Delivery::where('po_num', $poNum)
                            ->where('po_line', $poLine)
                            ->get();
        }else{
            $deliveries = Delivery::whereIn('id', $printDeliveries)
                            ->get();
        }

        $arrDeliveries = [];
        foreach ($deliveries as $key => $delivery) {
            $arrDeliveries[] = [
                'delivery_show' => $this->detailDS($delivery->id)['delivery_show'],
                'qr_code' => $this->detailDS($delivery->id)['qr_code'],
                'issued_mos' =>  $this->detailDS($delivery->id)['issued_mos'],
                'with_price' => $withPrice
            ];
        }

        $data['deliveries'] = $arrDeliveries;

    	$pdf = PDF::loadview('exports.pdf.delivery_sheet_multiple',$data);
        $filename = 'ds-'.date('YmdHis').'.pdf';

        return $pdf->download($filename);
    }


    public function exportMassPdfPost(Request $request)
    {
        $printAll = $request->print_deliveries;
        $poNum = $request->po_num;
        $poLine = $request->po_line;
        $printDeliveries = $request->print_delivery;
        $withPrice = 'yes';

        $arrParam['print_all'] = $printAll;
        $arrParam['po_num'] = $poNum;
        $arrParam['po_line'] = $poLine;
        $arrParam['print_delivery'] = $printDeliveries;
        $arrParam['with_price'] = $withPrice;

        $strParam = base64_encode(serialize($arrParam));

        if (!isset($printDeliveries)) {
            return response()->json([
                'status' => false,
                'message' => 'Pilih Minimal 1 DS'
                ], 200);
        }
        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses Generate PDF',
            'newtab' => true,
            'redirect_to' => url('admin/delivery-export-mass-pdf').'?param='.$strParam ,
            'validation_errors' => []
        ], 200);
    }


    public function exportMassPdfPost2(Request $request)
    {
        $printAll = $request->print_deliveries;
        $poNum = $request->po_num;
        $poLine = $request->po_line;
        $printDeliveries = $request->print_delivery;
        $withPrice = 'no';

        $arrParam['print_all'] = $printAll;
        $arrParam['po_num'] = $poNum;
        $arrParam['po_line'] = $poLine;
        $arrParam['print_delivery'] = $printDeliveries;
        $arrParam['with_price'] = $withPrice;

        $strParam = base64_encode(serialize($arrParam));

        if (!isset($printDeliveries)) {
            return response()->json([
                'status' => false,
                'message' => 'Pilih Minimal 1 DS'
                ], 200);
        }
        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses Generate PDF',
            'newtab' => true,
            'redirect_to' => url('admin/delivery-export-mass-pdf').'?param='.$strParam ,
            'validation_errors' => []
        ], 200);
    }


    public function exportTemplateSerialNumber()
    {
        $qty = request('qty');
        $filename = 'template-sn-'.date('YmdHis').'.xlsx';

        return Excel::download(new TemplateSerialNumberExport($qty), $filename);
    }


    public function importSn(Request $request)
    {
        $rules = [
            'file_sn' => 'required|mimes:xlsx,xls',
        ];

        $allowedQty = $request->allowed_qty;
        $file = $request->file('file_sn');
        $attrs['filename'] = $file;

        $rows = Excel::toArray(new SerialNumberImport($attrs), $file )[0];

        unset($rows[0]);
        $valueRow = [];
        $validRow = 0;
        foreach ($rows as $key => $value) {
            if (isset($value[1])) {
                $valueRow[] = ['serial_number' => $value[1]];
                $validRow ++;
            }
        }
        if ($allowedQty <= sizeof($rows) && $allowedQty > 0) {

            return response()->json([
                'status' => true,
                'alert' => 'success',
                'datas' => $valueRow
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Jumlah Qty dan Serial Number tidak sama!'
                ], 200);
        }
    }


    public function destroy($id)
    {
        $delivery = Delivery::where('id', $id)->first();
        if (isset($delivery)) {
            IssuedMaterialOuthouse::where('ds_num', $delivery->ds_num)
                                ->where('ds_line', $delivery->ds_line)
                                ->delete();
            Delivery::where('id', $id)->delete();
            DeliveryStatus::where('ds_num', $delivery->ds_num)
                            ->where('ds_line', $delivery->ds_line)
                            ->delete();
        }

        return true;
    }
}
