<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliveryRequest;
use App\Models\Delivery;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use PDF;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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
            'name' => 'articles',
            'default' => date("Y-m-d"),
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd/mm/yyyy',
                'language' => 'en'
             ],
        ]);        
        CRUD::field('petugas_vendor');
        CRUD::field('no_surat_jalan_vendor');
        CRUD::field('order_qty');
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
        
        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['delivery_show'] = $this->detailDS($entry->id)['delivery_show'];
        $data['qr_code'] = $this->detailDS($entry->id)['qr_code'];

        return view('vendor.backpack.crud.delivery-show', $data);
    }

    private function detailDS($id)
    {
        $delivery_show = Delivery::leftJoin('purchase_order_lines', 'purchase_order_lines.id', 'deliveries.po_line_id')
                        ->leftJoin('purchase_orders', 'purchase_orders.id', 'purchase_order_lines.purchase_order_id')
                        // ->leftJoin('delivery_statuses', 'delivery_statuses.ds_num', 'deliveries.ds_num')
                        ->leftJoin('vendors', 'vendors.number', 'purchase_orders.vendor_number')
                        ->where('deliveries.id', $id)
                        ->get(['deliveries.id as id','deliveries.ds_num','deliveries.ds_line','deliveries.shipped_date', 'purchase_order_lines.due_date', 'deliveries.po_release','purchase_order_lines.item','deliveries.u_m',
                        'vendors.number as vendor_number', 'vendors.name as vendor_name', 'deliveries.no_surat_jalan_vendor',
                        'purchase_orders.number as po_number','purchase_order_lines.po_line as po_line', 'deliveries.order_qty as order_qty', 'deliveries.shipped_qty', 'deliveries.unit_price', 'deliveries.currency', 'deliveries.tax_status', 'deliveries.description', 'deliveries.wh', 'deliveries.location'])
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
