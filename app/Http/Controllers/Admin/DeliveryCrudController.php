<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TemplateSerialNumberExport;
use App\Helpers\Constant;
use App\Helpers\DsValidation;
use App\Exports\TemplateExportAll;
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

use App\Library\ExportXlsx;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;

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
        $this->crud->denyAccess('show');
        $this->crud->denyAccess('list');
        if(Constant::checkPermission('Read Delivery Sheet in Table')){
            $this->crud->allowAccess('list');
            $this->crud->allowAccess('show-detail');
        }
        $this->crud->allowAccess('advanced_export_excel');
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
        $this->crud->addButtonFromView('line', 'show_detail_ds', 'show_detail_ds', 'beginning');

        $this->crud->addButtonFromView('top', 'bulk_print_ds_no_price', 'bulk_print_ds_no_price', 'end');
        $this->crud->exportRoute = url('admin/delivery-sheet-export');
        $this->crud->addButtonFromView('top', 'advanced_export_excel', 'advanced_export_excel', 'end');
        // $this->crud->addButtonFromModelFunction('top', 'excel_export_advance', 'excelExportAdvance', 'end');

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
                    $url = url('admin/delivery-detail').'/'.$delivery->ds_num.'/'.$delivery->ds_line;
                    $html = "<a href='".$url."' class='btn-link'>".$entry->ref_ds_num."</a>";
                }
                
                return $html;
            }
        ]);
        CRUD::column('ref_ds_line')->label('Ref DS Line');

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


    public function deliveryDetail($dsNum, $dsLine){
        $can_access = false;

        $delivery = Delivery::join('po', 'po.po_num', 'delivery.po_num')
        ->where('delivery.ds_num', $dsNum)
        ->where('delivery.ds_line', $dsLine)
        ->select('delivery.*', 'po.vend_num')
        ->first();

        $data['crud'] = $this->crud;
        $data['ds_num'] = $dsNum;
        $data['ds_line'] = $dsLine;

        if (!isset($delivery)) {
            return view('vendor.backpack.crud.delivery_show_none', $data);
        }

        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $can_access = true;
        }else{
            if (backpack_auth()->user()->vendor->vend_num == $delivery->vend_num) {
                $can_access = true;
            }
        }

        $deliveryStatus = DeliveryStatus::where('ds_num', $dsNum )
                            ->where('ds_line', $dsLine)
                            ->first();

        $deliveryRejects = DeliveryReject::where('ds_num', $dsNum )
                            ->where('ds_line', $dsLine)
                            ->get();

        $deliveryRepairs = DeliveryRepair::where('ds_num_reject', $dsNum)
                            ->where('ds_line_reject', $dsLine)
                            ->get();

        $qtyRejectCount = DeliveryReject::where('ds_num', $dsNum)
                            ->where('ds_line', $dsLine)
                            ->sum('rejected_qty');

        $deliveryFromRef = Delivery::where('ds_num', $delivery->ref_ds_num)
                    ->where('ds_line', $delivery->ref_ds_line)
                    ->first();
        $htmlRefDsNum = '-';
        if (isset($deliveryFromRef)) {
            $url = url('admin/delivery-detail').'/'.$deliveryFromRef->ds_num.'/'.$deliveryFromRef->ds_line;
            $htmlRefDsNum = "<a href='".$url."' class='btn-link'>".$delivery->ref_ds_num."-".$delivery->ref_ds_line."</a>";
        }

        $data['delivery'] = $delivery;
        $data['delivery_show'] = $this->detailDS($delivery->id)['delivery_show'];
        $data['delivery_status'] = $deliveryStatus;
        $data['delivery_rejects'] = $deliveryRejects;
        $data['delivery_repairs'] = $deliveryRepairs;
        $data['qty_reject_count'] = $qtyRejectCount;
        $data['issued_mos'] =$this->detailDS($delivery->id)['issued_mos'];
        $data['qr_code'] = $this->detailDS($delivery->id)['qr_code'];
        $data['html_ref_ds_num'] = $htmlRefDsNum;

        if ($can_access) {
            return view('vendor.backpack.crud.delivery_show', $data);
        }else{
            return view('vendor.backpack.crud.delivery_show_none', $data);
        }
    }


    public function show()
    {
        abort(404);
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


    public function exportPdfSingleDs()
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


    public function exportPdfMassDsPost(Request $request)
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

        if (!isset($printDeliveries)) {
            return response()->json([
                'status' => false,
                'message' => 'Pilih Minimal 1 DS'
                ], 200);
        }

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
        $path = public_path('export-pdf/');

    	$pdf = PDF::loadview('exports.pdf.delivery_sheet_multiple',$data);
        $filename = 'ds-'.date('YmdHis').'.pdf';
        $pdf->save($path . '/' . $filename);
        $pdf->download($filename);

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses Generate PDF',
            'newtab' => true,
            'redirect_to' => asset('export-pdf/'.$filename) ,
            'validation_errors' => []
        ], 200);
    }


    public function exportPdfMassLabelPost(Request $request)
    {
        $printAll = $request->print_deliveries;
        $poNum = $request->po_num;
        $poLine = $request->po_line;
        $printDeliveries = $request->print_delivery;
        
        $arrParam['print_all'] = $printAll;
        $arrParam['po_num'] = $poNum;
        $arrParam['po_line'] = $poLine;
        $arrParam['print_delivery'] = $printDeliveries;

        if (!isset($printDeliveries)) {
            return response()->json([
                'status' => false,
                'message' => 'Pilih Minimal 1 DS'
                ], 200);
        }

        $printDelivery = $arrParam['print_delivery'];
        $id = 0 ;

        if($printDelivery  != null){
            $db = Delivery::join('vendor_item', 'vendor_item.item', 'delivery.item')
            ->join('po', 'po.po_num', 'delivery.po_num')
            ->whereIn('delivery.id', $printDelivery )
            ->where('vendor_item.vend_num', DB::raw('po.vend_num'))
            ->select('delivery.id as id', 'po.po_num as po_num', 'delivery.po_line as po_line', 'delivery.item as item', 
            'delivery.description as description', 'delivery.ds_num as ds_num', 'delivery.ds_line as ds_line', 'delivery.po_num as po_num', 
            'po.vend_num as vend_num', 'delivery.shipped_qty as qty', 'delivery.order_qty as order_qty', 'vendor_item.qty_per_box as qty_per_box','delivery.shipped_date as shipped_date');
        }else{
            if($id != 0){
                $db = Delivery::join('vendor_item', 'vendor_item.item', 'delivery.item')
                ->join('po', 'po.po_num', 'delivery.po_num')
                ->where('delivery.id', $id)
                ->where('vendor_item.vend_num', DB::raw('po.vend_num'))
                ->select('delivery.id as id', 'po.po_num as po_num', 'delivery.po_line as po_line', 'delivery.item as item', 
                'delivery.description as description', 'delivery.ds_num as ds_num', 'delivery.ds_line as ds_line', 'delivery.po_num as po_num', 
                'po.vend_num as vend_num', 'delivery.shipped_qty as qty', 'delivery.order_qty as order_qty', 'vendor_item.qty_per_box as qty_per_box','delivery.shipped_date as shipped_date');
            }
        }

        $data['data'] = $db->get();
        $path = public_path('export-pdf/');
        $filename = 'label-'.date('YmdHis').'.pdf';

        $pdf = PDF::loadview('exports.pdf.delivery_sheet_label', $data)->setPaper('A4');
        $pdf->save($path . '/' . $filename);
        $pdf->download($filename);

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses Generate PDF',
            'newtab' => true,
            'redirect_to' => asset('export-pdf/'.$filename) ,
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

    public function exportAdvance(){
        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $datas = DB::select($query);

            $filename = 'DS-'.date('YmdHis').'.xlsx';

            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'ds_number' => $result->ds_num,
                    'ds_line' => $result->ds_line,
                    'shipped_date' => $result->shipped_date,
                    'po' => function($result){
                        return $result->po_num.'-'.$result->po_line;
                    },
                    'order_qty' => $result->order_qty,
                    'shipped_qty' => $result->shipped_qty,
                    'do_number' => $result->no_surat_jalan_vendor,
                    'operator' => $result->petugas_vendor,
                    'ref_ds_num' => function($entry){
                        $delivery = Delivery::where('ds_num', $entry->ref_ds_num)
                        ->where('ds_line', $entry->ref_ds_line)
                        ->first();
                        $url = '';
                        if (isset($delivery)) {
                            $url = url('admin/delivery-detail').'/'.$delivery->ds_num.'/'.$delivery->ds_line;
                        }
                        return $url;
                    },
                    'ref_ds_line' => $result->ref_ds_line
                ];
            };

            $export = new ExportXlsx($filename);
    
            $styleForHeader = (new StyleBuilder())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->setBackgroundColor(Color::rgb(102, 171, 163))
                            ->build();
    
            $firstSheet = $export->currentSheet();
    
            $export->addRow(['No', 
                'DS Number',
                'DS Line',
                'Shipped Date',
                'PO',
                'Order Qty',
                'Shipped Qty',
                'DO Number',
                'Operator',
                'Ref DS Num',
                'Ref DS Line'
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
        return 0;
    }

    public function exportAdvance2(Request $request){
        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $data = DB::select($query);

            $filename = 'DS-'.date('YmdHis').'.xlsx';

            $title = "Report Delivery Sheet";

            $header = [
                'no' => 'No',
                'ds_number' => 'DS Number',
                'ds_line' => 'DS Line',
                'shipped_date' => 'Shipped Date',
                'po' => 'PO',
                'order_qty' => 'Order Qty',
                'shipped_qty' => 'Shipped Qty',
                'do_number' => 'DO Number',
                'operator' => 'Operator'
            ];

            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'ds_number' => $result->ds_num,
                    'ds_line' => $result->ds_line,
                    'shipped_date' => $result->shipped_date,
                    'po' => function($result){
                        return $result->po_num.'-'.$result->po_line;
                    },
                    'order_qty' => $result->order_qty,
                    'shipped_qty' => $result->shipped_qty,
                    'do_number' => $result->no_surat_jalan_vendor,
                    'operator' => $result->petugas_vendor,
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

                $arrColumns = range('A', 'I');
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $event->sheet->getDelegate()->getStyle('A1:I1')->applyFromArray($styleHeader);
            };

           

            return Excel::download(new TemplateExportAll($data, $header, $resultCallback, $styleHeader, $title), $filename);
        }
        return 0;
    }


}
