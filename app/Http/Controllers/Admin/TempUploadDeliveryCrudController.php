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
use Illuminate\Support\Facades\Request;
use Prologue\Alerts\Facades\Alert;
use PDF;

/**
 * Class TempUploadDeliveryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TempUploadDeliveryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\TempUploadDelivery::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/temp-upload-delivery');
        CRUD::setEntityNameStrings('temp upload delivery', 'temp upload deliveries');
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
        $this->crud->removeButton('show');
        $this->crud->addButtonFromView('top', 'insertfromtemp', 'insertfromtemp', 'beginning');
        $this->crud->addButtonFromView('top', 'insertprintfromtemp', 'insertprintfromtemp', 'beginning');
        $this->crud->addButtonFromView('top', 'canceltemp', 'canceltemp', 'end');
        // $this->crud->addButtonFromModelFunction('top', 'insert_db', 'insertToDB', 'beginning');
        // $this->crud->addButtonFromModelFunction('top', 'cancel_db', 'cancelInsert', 'end');
        $this->crud->addClause('where','user_id', backpack_auth()->user()->id);
        $this->crud->orderBy('po_num', 'asc');        
        $this->crud->orderBy('po_line', 'asc');        

        CRUD::addColumn([
            'name'     => 'po_po_line',
            'label'    => 'PO',
            'type'     => 'closure',
            'function' => function($entry) {
                return $entry->po_num. '-'.$entry->po_line;
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
            'label'     => 'Petugas Vendor', // Table column heading
            'name'      => 'petugas_vendor', 
        ]);
        CRUD::addColumn([
            'label'     => 'DO Number Vendor', // Table column heading
            'name'      => 'no_surat_jalan_vendor', 
        ]);
        CRUD::addColumn([
            'label'     => 'Validation', // Table column heading
            'name'      => 'validation_text', 
            'type'      => 'model_function',
            'function_name' => 'getValidationText',
            'limit' => 255
        ]);
       
        // Alert::success("Successfully Save Multiple DS!")->flash();

    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TempUploadDeliveryRequest::class);
  
        CRUD::field('petugas_vendor');
        CRUD::field('no_surat_jalan_vendor');
        CRUD::field('order_qty');
        CRUD::field('serial_number');
    }


    private function insertMassData(){
        $data_temps = TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->get();
        $arr_ids = [];

        foreach ($data_temps as $key => $data_temp) {
            $po_line = PurchaseOrderLine::where('po_num', $data_temp->po_num)->where('po_line', $data_temp->po_line)->first();
            $ds_num =  (new Constant())->codeDs($data_temp->po_num, $data_temp->po_line, $data_temp->shipped_date);
            $ds_line = $ds_num['line'];

            $insert = new Delivery();
            $insert->ds_num = $ds_num['single'];
            $insert->group_ds_num = $ds_num['group'];
            $insert->po_line = $data_temp->po_line;
            $insert->po_num = $data_temp->po_num;
            $insert->po_release = 0;
            $insert->ds_line = $ds_line;
            $insert->item = $po_line->item;
            $insert->description = $po_line->description;
            $insert->u_m = $po_line->u_m;
            $insert->due_date = $po_line->due_date;
            $insert->unit_price = $po_line->unit_price;
            $insert->wh = $po_line->wh;
            $insert->location = $po_line->location;
            $insert->tax_status = $po_line->tax_status;
            $insert->currency = $po_line->currency;
            $insert->shipped_qty = $data_temp->shipped_qty;
            $insert->shipped_date = date('Y-m-d', strtotime($data_temp->shipped_date));
            $insert->order_qty = $po_line->order_qty;
            $insert->w_serial = ($data_temp->serial_number) ? $data_temp->serial_number : 0;
            $insert->petugas_vendor = $data_temp->petugas_vendor;
            $insert->no_surat_jalan_vendor = $data_temp->no_surat_jalan_vendor;

            if ($po_line->status == 'O' && $po_line->accept_flag == 1 && $data_temp->category_validation != 'danger') {
                $insert->save();
                $arr_ids[] = $insert->id;

                if ($po_line->outhouse_flag == 1 && isset($data_temp->data_attr)) {
                    $data_attrs = json_decode($data_temp->data_attr);
                    foreach ($data_attrs->attributes as $key => $da) {
                        $material_outhouse = MaterialOuthouse::where('id', $da->id)->first();
                        if (isset($material_outhouse)) {
                            $insert_outhouse = new IssuedMaterialOuthouse();
                            $insert_outhouse->ds_num = $ds_num['single'];
                            $insert_outhouse->ds_line = $ds_line;
                            $insert_outhouse->ds_detail = $po_line->item;
                            $insert_outhouse->matl_item = $material_outhouse->matl_item;
                            $insert_outhouse->description =  $material_outhouse->description;
                            $insert_outhouse->lot =  $material_outhouse->lot;
                            $insert_outhouse->issue_qty =  $da->qty;
                            $insert_outhouse->save();
                        }
                    }
                }
            }
        }

        TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->delete();

        return ['status' => true, 'arr_ids' => $arr_ids];
    }

    public function insertToDb(Request $request)
    {
        return $this->insertMassData()['status'];
    }


    public function printInsertToDb(Request $request)
    {
        $arr_ids = $this->insertMassData()['arr_ids'];

        $arr_param['print_all'] = false;
        $arr_param['po_num'] = '-';
        $arr_param['po_line'] = '-';
        $arr_param['print_delivery'] = $arr_ids;
        $arr_param['with_price'] = 'yes';

        $str_param = base64_encode(serialize($arr_param));

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses Generate PDF',
            'newtab' => true,
            'redirect_to' => url('admin/delivery-export-mass-pdf').'?param='.$str_param ,
            'validation_errors' => []
        ], 200);

    }


    public function cancelToDb(Request $request)
    {
        TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->delete();
    }


    public function edit($id)
    {
        CRUD::setValidation(DeliveryRequest::class);

        // $this->crud->hasAccessOrFail('update');
        $entry = $this->crud->getCurrentEntry();
        $po_line = PurchaseOrderLine::where('po_num', $entry->po_num)->where('po_line', $entry->po_line)->first();

        $ds_validation = new DsValidation();
        $args = ['po_num' => $entry->po_num, 'po_line' => $entry->po_line, 'order_qty' => $po_line->order_qty ];
        $current_qty = $ds_validation->currentMaxQty($args)['datas'];

        $arr_po_line_status = (new Constant())->statusOFC();

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
                'data-max' =>  $current_qty,
              ], 
        ]);
        if($po_line->w_serial == 1){
            $this->crud->addField(
                [
                    'name'  => 'sn_childs[]',
                    'label' => 'Serial Number',
                    'type'  => 'upload_serial_number',
                ],
            );
        }

        if($po_line->outhouse_flag == 1){
            $outhouse_materials = MaterialOuthouse::where('po_num', $entry->po_num)
                                    ->where('po_line', $entry->po_line);

            $this->crud->addField(
                [
                    'name'  => 'material_issues',
                    'label' => 'Material Issue',
                    'type'  => 'outhouse_table',
                    'current_qty' => $current_qty,
                    'total_qty_per' => $outhouse_materials->sum('qty_per'),
                    'table_body' => $outhouse_materials->get(),
                    'data_table' => (isset($entry->data_attr)) ? json_decode($entry->data_attr): null
                ],
            );
        }

        $arr_filters = [];
        $arr_filters[] = ['po_line.item', '=', $po_line->item];
        $arr_filters[] = ['po_line.po_num', '=', $entry->po_num];
        $arr_filters[] = ['po_line.po_line', '<', $entry->po_line];
        $args = ['filters' => $arr_filters, 'due_date' => $po_line->due_date ];

        $unfinished_po_line = (new DsValidation())->unfinishedPoLine($args);
        
        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['po_line'] = $po_line;
        $data['title'] = trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $data['id'] = $id;
        $data['arr_po_line_status'] = $arr_po_line_status;
        $data['unfinished_po_line'] = $unfinished_po_line;

        return view('vendor.backpack.crud.form-edit-temp', $data);
    }


    public function update($id)
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $request = $this->crud->getRequest();
        $shipped_qty = $request->input('shipped_qty');
        $shipped_date = $request->input('shipped_date');
        $petugas_vendor = $request->input('petugas_vendor');
        $no_surat_jalan_vendor = $request->input('no_surat_jalan_vendor');
        $sn_childs = $request->input('sn_childs');
        $outhouse_ids = $request->input('outhouse_ids');
        $material_issues = $request->input('material_issues');

        $arr_datas = [];
        if (isset($sn_childs)) {
            $arr_datas = ['type' =>'serial_number'];

            foreach ($sn_childs as $j => $snc) {
                $arr_datas['attributes'][] = $snc;
            }
        }

        if (isset($outhouse_ids)) {
            $arr_datas = ['type' =>'material_outhouse'];

            foreach ($outhouse_ids as $k => $oi) {
                $arr_datas['attributes'][] = ['id' => $oi, 'qty' => $material_issues[$k]];
            }
        }

        $data_attr = json_encode($arr_datas);
        
        $change = TempUploadDelivery::where('id', $id)->first();
        $change->shipped_qty = $shipped_qty;
        $change->delivery_date = $shipped_date;
        $change->petugas_vendor = $petugas_vendor;
        $change->no_surat_jalan_vendor = $no_surat_jalan_vendor;
        $change->data_attr = $data_attr;
        $change->save();

        $message = 'Sukses Update !';

        Alert::success($message)->flash();

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => $message,
            'redirect_to' => url('admin/temp-upload-delivery'),
            'validation_errors' => []
        ], 200);
    }
}
