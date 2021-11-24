<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliveryRequest;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\PurchaseOrderLine;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use PDF;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as FacadesQrCode;

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
        CRUD::setEntityNameStrings('delivery', 'deliveries');
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

        $this->crud->addButtonFromModelFunction('top', 'pdf_check', 'pdfCheck', 'beginning');
        $this->crud->addButtonFromModelFunction('top', 'pdf_export', 'pdfExport', 'end');

        $this->crud->enableBulkActions();

        CRUD::column('ds_num');
        // CRUD::addColumn([
        //     'label'     => 'PO', // Table column heading
        //     'name'      => 'purchaseOrder', // the column that contains the ID of that connected entity;
        //     'entity'    => 'vendor', 
        //     'type' => 'relationship',
        //     'attribute' => 'number',
        // ]);
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
                        'vendor.vend_num as vendor_number', 'vendor.vend_num as vendor_name', 'delivery.no_surat_jalan_vendor',
                        'po.po_num as po_number','po_line.po_line as po_line', 'delivery.order_qty as order_qty', 'delivery.shipped_qty', 'delivery.unit_price', 'delivery.currency', 'delivery.tax_status', 'delivery.description', 'delivery.wh', 'delivery.location'])
                        ->first();
        $qr_code = "DSW|";
        $qr_code .= $delivery_show->ds_num."|";
        $qr_code .= $delivery_show->ds_line."|";
        $qr_code .= $delivery_show->po_number."|";
        $qr_code .= $delivery_show->po_line."|";
        $qr_code .= $delivery_show->po_release."|";
        $qr_code .= $delivery_show->item."|";
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
        $order_qty = $request->input('order_qty');
        $petugas_vendor = $request->input('petugas_vendor');
        $no_surat_jalan_vendor = $request->input('no_surat_jalan_vendor');
        $serial_number = $request->input('serial_number');

        $po_line = PurchaseOrderLine::where('po_line.id', $po_line_id)
                ->leftJoin('po', 'po.po_num', 'po_line.po_num' )
                ->first();
        $code = "";
        switch (backpack_auth()->user()->role->name) {
            case 'admin':
                $code = "01";
                break;
            case 'vendor':
                $code = "00";
                break;
            default:
                # code...
                break;
        }

        $ds_num = $po_line->vendor_number.date("ymd").$code;
        $insert = new Delivery();
        $insert->ds_num = $ds_num;
        $insert->po_num = $po_line->po_num;
        $insert->po_line = $po_line->po_line;
        $insert->po_release = $po_line->po_release;
        $insert->ds_line = Delivery::where('po_num', $po_line->po_num)->count()+1;
        $insert->description = $po_line->description;
        $insert->u_m = $po_line->u_m;
        $insert->due_date = $po_line->due_date;
        $insert->unit_price = $po_line->unit_price;
        $insert->wh = $po_line->wh;
        $insert->location = $po_line->location;
        $insert->tax_status = $po_line->tax_status;
        $insert->currency = $po_line->currency;
        $insert->shipped_qty = $po_line->order_qty;
        $insert->shipped_date = now();
        $insert->order_qty = $order_qty;
        $insert->w_serial = ($serial_number)?$serial_number:0;
        $insert->petugas_vendor = $petugas_vendor;
        $insert->no_surat_jalan_vendor = $no_surat_jalan_vendor;
        $insert->created_by = backpack_auth()->user()->id;
        $insert->updated_by = backpack_auth()->user()->id;
        $insert->save();

        Alert::success(trans('backpack::crud.insert_success'))->flash();
    
        return redirect()->to('admin/purchase-order-line/'.$po_line_id.'/show');
    }

    public function exportPdf()
    {
        $id = request('id');
        $with_price = request('wp');
        // $writer = new PngWriter();

        $qr_code = FacadesQrCode::format('svg')->size(200)->generate($this->detailDS($id)['qr_code']);
        // $qr_code = base64_encode(QrCode::format('svg')->size(200)->errorCorrection('H')->generate($this->detailDS($id)['qr_code']));
        // $qrCode = QrCode::create($this->detailDS($id)['qr_code'])
        // ->setSize(200);
        // $qr_code = $writer->write($qrCode);


        $data['delivery_show'] = $this->detailDS($id)['delivery_show'];
        $data['qr_code'] = $qr_code;
        $data['with_price'] = $with_price;

    	$pdf = PDF::loadview('exports.pdf.delivery-sheet',$data);
        return $pdf->stream();

        // return $pdf->download('delivery-sheet-'.date('YmdHis').'-pdf');
    }


    public function destroy($id)
    {
        Delivery::where('id', $id)->delete();
        return true;
    }
}
