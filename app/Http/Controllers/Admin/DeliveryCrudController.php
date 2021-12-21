<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TemplateSerialNumberExport;
use App\Helpers\Constant;
use App\Http\Requests\DeliveryRequest;
use App\Imports\SerialNumberImport;
use App\Models\Delivery;
use App\Models\DeliverySerial;
use App\Models\DeliveryStatus;
use App\Models\IssuedMaterialOuthouse;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrderLine;
use App\Models\PurchaseOrder;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Endroid\QrCode\Writer\PngWriter;
use SimpleSoftwareIO\QrCode\Facades\QrCode as FacadesQrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class DeliveryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DeliveryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Delivery::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery');
        CRUD::setEntityNameStrings('delivery sheet', 'delivery sheet');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');

        $this->crud->addButtonFromView('top', 'bulk_print_ds', 'bulk_print_ds', 'beginning');
        $this->crud->addButtonFromView('top', 'bulk_print_label', 'bulk_print_label', 'beginning');

        $this->crud->enableBulkActions();

        CRUD::addColumn([
            'label'     => 'DS Number', // Table column heading
            'name'      => 'ds_num', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Shipped Date', // Table column heading
            'name'      => 'shipped_date', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Order Qty', // Table column heading
            'name'      => 'order_qty', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Shipped Qty', // Table column heading
            'name'      => 'shipped_qty', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'DO Number', // Table column heading
            'name'      => 'no_surat_jalan_vendor', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Operator', // Table column heading
            'name'      => 'operator', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        $this->crud->addFilter([
            'name'        => 'vendor',
            'type'        => 'select2_ajax',
            'label'       => 'Name Vendor',
            'placeholder' => 'Pick a vendor'
        ],
        url('admin/test/ajax-vendor-options'),
        function($value) {
            // SELECT d.id, d.ds_num, d.po_num, p.vend_num FROM `delivery` d
            // JOIN po p ON p.po_num = d.po_num
            // WHERE p.vend_num = 'V001303'
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

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
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
        CRUD::field('petugas_vendor');
        CRUD::field('no_surat_jalan_vendor');
        CRUD::field('order_qty');
        CRUD::field('serial_number');
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    function show()
    {
        $entry = $this->crud->getCurrentEntry();

        $delivery_status = DeliveryStatus::where('ds_num', $entry->ds_num )
                            ->where('ds_line', $entry->ds_line)
                            ->first();

        $vendor = PurchaseOrder::join('vendor', 'vendor.vend_num', 'po.vend_num')
        ->where('po.po_num', $delivery_status->po_num)
        ->select('vendor.currency as currency')
        ->first();

        $data['format_currency'] = $vendor->currency;
        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['delivery_show'] = $this->detailDS($entry->id)['delivery_show'];
        $data['delivery_status'] = $delivery_status;
        $data['qr_code'] = $this->detailDS($entry->id)['qr_code'];


        return view('vendor.backpack.crud.delivery-show', $data);
    }

    private function detailDS($id)
    {
        $delivery_show = Delivery::leftjoin('po_line', function ($join) {
                            $join->on('po_line.po_num', 'delivery.po_num')
                                ->orOn('po_line.po_line', 'delivery.po_line');
                        })
                        ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                        // ->leftJoin('delivery_statuses', 'delivery_statuses.ds_num', 'deliveries.ds_num')
                        ->leftJoin('vendor', 'vendor.vend_num', 'po.vend_num')
                        ->where('delivery.id', $id)
                        ->get(['delivery.id as id','delivery.ds_num','delivery.ds_line','delivery.shipped_date', 'po_line.due_date', 'delivery.po_release','po_line.item','delivery.u_m',
                        'vendor.vend_num as vendor_number','vendor.currency as vendor_currency', 'vendor.vend_num as vendor_name', 'delivery.no_surat_jalan_vendor','po_line.item_ptki',
                        'po.po_num as po_number','po_line.po_line as po_line', 'delivery.order_qty as order_qty', 'delivery.shipped_qty', 'delivery.unit_price', 'delivery.currency', 'delivery.tax_status', 'delivery.description', 'delivery.wh', 'delivery.location'])
                        ->first();
        $qr_code = "DSW|";
        $qr_code .= $delivery_show->ds_num."|";
        $qr_code .= $delivery_show->ds_line."|";
        $qr_code .= $delivery_show->po_number."|";
        $qr_code .= $delivery_show->po_line."|";
        $qr_code .= $delivery_show->po_release."|";
        $qr_code .= $delivery_show->item_ptki."|";
        $qr_code .= $delivery_show->shipped_qty."|";
        $qr_code .= $delivery_show->u_m."|";
        $qr_code .= $delivery_show->unit_price."|";
        $qr_code .= date("Y-m-d", strtotime($delivery_show->shipped_date))."|";
        $qr_code .= $delivery_show->no_surat_jalan_vendor;

        $data['delivery_show'] = $delivery_show;
        $data['qr_code'] = $qr_code;

        return $data;
    }

    public function store(Request $request)
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $request = $this->crud->getRequest();

        $po_line_id = $request->input('po_line_id');
        $shipped_qty = $request->input('shipped_qty');
        $shipped_date = $request->input('shipped_date');
        $petugas_vendor = $request->input('petugas_vendor');
        $no_surat_jalan_vendor = $request->input('no_surat_jalan_vendor');
        $material_ids = $request->input('material_ids');
        $mo_issue_qtys = $request->input('mo_issue_qty');
        $sn_childs = $request->input('sn_childs');

        $po_line = PurchaseOrderLine::where('po_line.id', $po_line_id)
                ->leftJoin('po', 'po.po_num', 'po_line.po_num' )
                ->first();
        
        $ds_num =  (new Constant())->codeDs($po_line->po_num, $po_line->po_line, $shipped_date);

        DB::beginTransaction();

        try{
            $insert_d = new Delivery();
            $insert_d->ds_num = $ds_num['single'];
            $insert_d->po_num = $po_line->po_num;
            $insert_d->po_line = $po_line->po_line;
            $insert_d->po_release = $po_line->po_release;
            $insert_d->ds_line = $ds_num['line'];
            $insert_d->item = $po_line->item;
            $insert_d->description = $po_line->description;
            $insert_d->u_m = $po_line->u_m;
            $insert_d->due_date = $po_line->due_date;
            $insert_d->unit_price = $po_line->unit_price;
            $insert_d->wh = $po_line->wh;
            $insert_d->location = $po_line->location;
            $insert_d->tax_status = $po_line->tax_status;
            $insert_d->currency = $po_line->currency;
            $insert_d->shipped_qty = $shipped_qty;
            $insert_d->shipped_date = $shipped_date;
            $insert_d->order_qty = $po_line->order_qty;
            $insert_d->w_serial = $po_line->w_serial;
            $insert_d->petugas_vendor = $petugas_vendor;
            $insert_d->no_surat_jalan_vendor = $no_surat_jalan_vendor;
            $insert_d->created_by = backpack_auth()->user()->id;
            $insert_d->updated_by = backpack_auth()->user()->id;
            $insert_d->save();

            if ( $po_line->w_serial == 1) {

                foreach ($sn_childs as $key => $sn_child) {
                    if (isset($sn_child)) {
                        $count = DeliverySerial::where('ds_num', $insert_d->ds_num)->where('ds_line', $insert_d->ds_line)->count();
                        $ds_detail = $count+1;

                        $insert_sn = new DeliverySerial();
                        $insert_sn->ds_num = $insert_d->ds_num;
                        $insert_sn->ds_line = $insert_d->ds_line;
                        $insert_sn->ds_detail = $ds_detail;
                        $insert_sn->no_mesin = $sn_child;
                        $insert_sn->created_by = backpack_auth()->user()->id;
                        $insert_sn->updated_by = backpack_auth()->user()->id;
                        $insert_sn->save();
                    }
                    
                }
            }

            if ( $po_line->outhouse_flag == 1) {
                foreach ($material_ids as $key => $material_id) {
                    $mo = MaterialOuthouse::where('id', $material_id)->first();
                    $mo_issue_qty = $mo_issue_qtys[$key];

                    $insert_imo = new IssuedMaterialOuthouse();
                    $insert_imo->ds_num = $insert_d->ds_num;
                    $insert_imo->ds_line = $insert_d->ds_line;
                    $insert_imo->ds_detail = 123;
                    $insert_imo->matl_item = $mo->matl_item;
                    $insert_imo->description = $mo->description;
                    $insert_imo->lot =  $mo->lot;
                    $insert_imo->issue_qty = $mo_issue_qty;
                    $insert_imo->created_by = backpack_auth()->user()->id;
                    $insert_imo->updated_by = backpack_auth()->user()->id;
                    $insert_imo->save();
                }
            }
            DB::commit();

            $message = 'Delivery Sheet Created';

            Alert::success($message)->flash();

            return response()->json([
                'status' => true,
                'alert' => 'success',
                'message' => $message,
                'redirect_to' => url('admin/purchase-order-line/'.$po_line_id.'/show'),
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
        $with_price = request('wp');

        $data['delivery_show'] = $this->detailDS($id)['delivery_show'];
        $data['qr_code'] = $this->detailDS($id)['qr_code'];
        $data['with_price'] = $with_price;

    	$pdf = PDF::loadview('exports.pdf.delivery-sheet',$data);
        return $pdf->stream();

        // return $pdf->download('delivery-sheet-'.date('YmdHis').'-pdf');
    }
    

    public function exportMassPdf()
    {
        $str_param = request('param');
        $arr_param = unserialize(base64_decode($str_param));

        $print_all = $arr_param['print_all'];
        $po_num = $arr_param['po_num'];
        $po_line = $arr_param['po_line'];
        $print_deliveries = $arr_param['print_delivery'];
        $with_price = $arr_param['with_price'];

        if ($print_all) {
            $deliveries = Delivery::where('po_num', $po_num)
                        ->where('po_line', $po_line)
                        ->get();
        }else{
            $deliveries = Delivery::whereIn('id', $print_deliveries)
                    ->get();
        }

        $arr_deliveries = [];

        foreach ($deliveries as $key => $delivery) {
            $arr_deliveries[] = [
                'delivery_show' => $this->detailDS($delivery->id)['delivery_show'],
                'qr_code' => $this->detailDS($delivery->id)['qr_code'],
                'with_price' => $with_price
            ];
        }

        $data['deliveries'] = $arr_deliveries;

    	$pdf = PDF::loadview('exports.pdf.delivery-sheet-multiple',$data);
        // return $pdf->stream();

        return $pdf->download('delivery-sheet-'.date('YmdHis').'-pdf');
    }


    public function exportMassPdfPost(Request $request)
    {
        $print_all = $request->print_deliveries;
        $po_num = $request->po_num;
        $po_line = $request->po_line;
        $print_deliveries = $request->print_delivery;
        $with_price = 'yes';
        
        $arr_param['print_all'] = $print_all;
        $arr_param['po_num'] = $po_num;
        $arr_param['po_line'] = $po_line;
        $arr_param['print_delivery'] = $print_deliveries;
        $arr_param['with_price'] = $with_price;

        $str_param = base64_encode(serialize($arr_param));

        if (!isset($print_deliveries)) {
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
            'redirect_to' => url('admin/delivery-export-mass-pdf').'?param='.$str_param ,
            'validation_errors' => []
        ], 200);
    }


    public function exportTemplateSerialNumber()
    {
        $qty = request('qty');

        return Excel::download(new TemplateSerialNumberExport($qty), 'template-sn-'.date('YmdHis').'.xlsx');

    }

    public function importSn(Request $request)
    {
        $rules = [
            'file_sn' => 'required|mimes:xlsx,xls',
        ];

        $allowed_qty = $request->allowed_qty;
        $file = $request->file('file_sn');
        

        $attrs['filename'] = $file;

        $rows = Excel::toArray(new SerialNumberImport($attrs), $file )[0];

        unset($rows[0]);
        $value_row = [];
        $valid_row = 0;
        foreach ($rows as $key => $value) {
            if (isset($value[1])) {
                $value_row[] = ['serial_number' => $value[1]];
                $valid_row ++;
            }
        }
        if ($allowed_qty <= sizeof($rows) && $allowed_qty > 0) {
            
            return response()->json([
                'status' => true,
                'alert' => 'success',
                'datas' => $value_row
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
        Delivery::where('id', $id)->delete();
        return true;
    }
}
