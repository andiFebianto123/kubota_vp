<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TaxInvoiceRequest;
use App\Models\DeliveryStatus;
use App\Models\Delivery;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as req;
use Prologue\Alerts\Facades\Alert;
use App\Helpers\Constant;
use App\Models\TaxInvoice;
use App\Models\Comment;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Support\Facades\Validator;


class TaxInvoiceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public $crud2;

    public function setup()
    {
        CRUD::setModel(\App\Models\TaxInvoice::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tax-invoice');
        CRUD::setEntityNameStrings('faktur pajak', 'List Payment');
        $this->crud->query->join('po', 'po.po_num', 'delivery_status.po_num')
                ->join('vendor', 'vendor.vend_num', 'po.vend_num');
        $this->crud->query = $this->crud->query->select('*',
            DB::raw("(SELECT comment FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as comment"),
            DB::raw("(SELECT user_id FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as user"),
            DB::raw("(SELECT status FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as status"),
            //DB::raw("(SELECT id FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as id_comment"),
            //DB::raw("(SELECT currency FROM vendor WHERE vend_num = (SELECT vend_num FROM po WHERE po.po_num = delivery_status.po_num)) as currency")
        );

        $this->setup2();

        if(Constant::getRole() != 'Admin PTKI'){
            $this->crud->query = $this->crud->query->whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num]);
        }

        if(Constant::checkPermission('Read List Payment')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }

        $this->crud->setListView('vendor.backpack.crud.list_payment');
    }

    private function getCurrency($entry){
        $data = PurchaseOrder::where('po_num', $entry->po_num)->first();
        if($data != null){
            $vendor = Vendor::where('vend_num', $data->vend_num)->first();
            if($vendor != null){
                return $vendor->currency;
            }
        }
        return '';
    }


    protected function setupListOperation()
    {
        $this->crud->removeButton('delete');
        $this->crud->removeButton('update');
        if(!Constant::checkPermission('Create Invoice and Tax')){
            $this->crud->removeButton('create');
        }
        $this->crud->addButtonFromView('line', 'accept_faktur_pajak', 'accept_faktur_pajak', 'begining');
        $this->crud->addButtonFromView('line', 'reject_faktur_pajak', 'reject_faktur_pajak', 'end');
        if(Constant::checkPermission('Download Button List Payment')){
            $this->crud->addButtonFromModelFunction('line', 'download', 'download', 'end');
        }

        $this->crud->addButtonFromView('line_2', 'show2', 'show', 'begining');

        $this->crud->addClause('where', 'executed_flag', '=', 0);
        $this->crud->addClause('where', 'validate_by_fa_flag', '=', 1);

        CRUD::addColumn([
            'name'     => 'po_po_line',
            'label'    => 'PO',
            'type'     => 'closure',
            'function' => function($entry) {
                return $entry->po_num.'-'.$entry->po_line;
            },
            'orderable'  => true, 
            'searchLogic' => function ($query, $column, $searchTerm) {
                if ($column['name'] == 'po_po_line') {
                    $searchOnlyPo = str_replace("-", "", $searchTerm);
                    $query->orWhere('po_num', 'like', '%'.$searchOnlyPo.'%');
                    if (str_contains($searchTerm, '-')) {
                        $query->orWhere(function($q) use ($searchTerm) {
                            $searchWithSeparator = explode("-", $searchTerm);
                            $q->where('po_num', 'like', '%'.$searchWithSeparator[0].'%')
                              ->Where('po_line', 'like', '%'.$searchWithSeparator[1].'%');
                        });
                    }
                }
            },
            'orderLogic' => function ($query, $column, $columnDirection) {
                return $query->orderBy('po_num', $columnDirection);
            }
        ]);
        CRUD::addColumn([
            'name'     => 'ds_num',
            'label'    => 'DS Num',
            'type'     => 'text',
        ]);
        CRUD::addColumn([
            'name'     => 'ds_line',
            'label'    => 'DS Line',
            'type'     => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Item', // Table column heading
            'name'      => 'item', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Description', // Table column heading
            'name'      => 'description', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Payment Plan Date', // Table column heading
            'name'      => 'payment_plan_date', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Unit Price', // Table column heading
            'name'      => 'unit_price', // the column that contains the ID of that connected entity;
            'type' => 'closure',
            'function' => function($entry){
                return $entry->currency.' '.Constant::getPrice($entry->unit_price);
            }
        ]);
        CRUD::addColumn([
            'label'     => 'Qty Received', // Table column heading
            'name'      => 'received_qty', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Qty Rejected', // Table column heading
            'name'      => 'rejected_qty', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'No Faktur', // Table column heading
            'name'      => 'no_faktur_pajak', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label' => 'No Surat Jalan Vendor',
            'name' => 'no_surat_jalan_vendor',
            'type' => 'text'
        ]);
        CRUD::addColumn([
            'label'     => 'Harga Sebelum Pajak', // Table column heading
            'name'      => 'harga_sebelum_pajak', // the column that contains the ID of that connected entity;
            'type' => 'closure',
            'function' => function($entry){
                return $entry->currency. ' ' . Constant::getPrice($entry->harga_sebelum_pajak);
            }
        ]);
        CRUD::addColumn([
            'label'     => 'PPN', // Table column heading
            'name'      => 'ppn', // the column that contains the ID of that connected entity;
            'type' => 'closure',
            'function' => function($entry){
                return $entry->currency.' '.Constant::getPrice($entry->ppn);
            }
        ]);
        CRUD::addColumn([
            'label'     => 'PPH', // Table column heading
            'name'      => 'pph', // the column that contains the ID of that connected entity;
            'type' => 'closure',
            'function' => function($entry){
                return $entry->currency.' '.Constant::getPrice($entry->pph);
            }
        ]);
        CRUD::addColumn([
            'label' => 'Total',
            'name' => 'total_ppn',
            'type' => 'closure',
            'function' => function($entry){
                return $entry->currency.' '.Constant::getPrice(($entry->harga_sebelum_pajak + $entry->ppn - $entry->pph));
            }
        ]);
        CRUD::addColumn([ 
            'label' => 'Comments',
            'name' => 'comment',
            'type' => 'comment'
        ]);
        CRUD::addColumn([
            'label' => 'Confirm',
            'name' => 'confirm_flag',
            'type' => 'closure',
            'function' => function($entry){
                if($entry->confirm_flag == 0){
                    return 'Waiting';
                }else if($entry->confirm_flag == 1){
                    return 'Accept';
                }else {
                    return 'Reject';
                }
            },
            'searchLogic' => function ($query, $column, $searchTerm) {
                if ($column['name'] == 'confirm_flag') {
                    $searchLower = strtolower($searchTerm);
                    $shouldSearch = false;
                    if (str_contains('waiting', $searchLower)) {
                        $confirmFlag = "0";
                        $shouldSearch = true;
                    }else if(str_contains('accept', $searchLower)){
                        $confirmFlag = "1";
                        $shouldSearch = true;
                    }else if(str_contains('reject', $searchLower)){
                        $confirmFlag = "2";
                        $shouldSearch = true;
                    }

                    if ($shouldSearch) {
                        $query->orWhere('confirm_flag', '=', $confirmFlag);
                    }
                }
            }
        ]);
        CRUD::column('updated_at');

        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor'
            ],
            url('admin/filter-vendor/ajax-itempo-options'),
            function($value) {
                $dbGet = TaxInvoice::join('po', 'po.po_num', 'delivery_status.po_num')
                ->select('delivery_status.id as id')
                ->where('po.vend_num', $value)
                ->get()
                ->mapWithKeys(function($po, $index){
                    return [$index => $po->id];
                });
                $this->crud->addClause('whereIn', 'id', $dbGet->unique()->toArray());
            });
        }

        $this->crud->addFilter([
            'type'  => 'date_range_list_payment_top',
            'name'  => 'from_to',
            'label' => 'Payment Plan Date',
            'value' => ''
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            $this->crud->addClause('where', 'payment_plan_date', '>=', $dates->from);
            $this->crud->addClause('where', 'payment_plan_date', '<=', $dates->to);
          });
        $this->crud->button_create = 'Invoice and Tax';

        // for secondary table
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $this->crud->addFilter([
                'name'        => 'vendor2',
                'type'        => 'select2_ajax_custom',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor',
                'custom_table' => true,
            ],
            url('admin/filter-vendor/ajax-itempo-options'),
            function($value) {
                $dbGet = TaxInvoice::join('po', 'po.po_num', 'delivery_status.po_num')
                ->select('delivery_status.id as id')
                ->where('po.vend_num', $value)
                ->get()
                ->mapWithKeys(function($po, $index){
                    return [$index => $po->id];
                });
                $this->crud2 = $this->crud2->whereIn('id', $dbGet->unique()->toArray());
            });
        }

        $this->crud->addFilter([
            'type'  => 'date_range_custom',
            'name'  => 'from_to_2',
            'label' => 'Payment Plan Date',
            'value' => '',
            'default' => [null,null],
            'custom_table' => true,
        ],
        false,
        function ($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            $this->crud2 = $this->crud2->where('payment_plan_date','>=', $dates->from)
            ->where('payment_plan_date', '<=', $dates->to);
        });
    }


    
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TaxInvoiceRequest::class);
        CRUD::addField([   // Upload
            'name'      => 'file_faktur_pajak',
            'label'     => 'Faktur Pajak',
            'type'      => 'upload',
            'upload'    => true,
            'disk'      => 'uploads', // if you store files in the /public folder, please omit this; if you store them in /storage or S3, please specify it;
            // optional:
            'temporary' => 10 // if using a service, such as S3, that requires you to make temporary URLs this will make a URL that is valid for the number of minutes specified
        ]);

        CRUD::addField([   // Upload
            'name'      => 'invoice',
            'label'     => 'Invoice',
            'type'      => 'upload',
            'upload'    => true,
            'disk'      => 'uploads', // if you store files in the /public folder, please omit this; if you store them in /storage or S3, please specify it;
            // optional:
            'temporary' => 10 // if using a service, such as S3, that requires you to make temporary URLs this will make a URL that is valid for the number of minutes specified
        ]);

        CRUD::addField([   // Upload
            'name'      => 'file_surat_jalan',
            'label'     => 'Surat Jalan',
            'type'      => 'upload',
            'upload'    => true,
            'disk'      => 'uploads', // if you store files in the /public folder, please omit this; if you store them in /storage or S3, please specify it;
            // optional:
            'temporary' => 10 // if using a service, such as S3, that requires you to make temporary URLs this will make a URL that is valid for the number of minutes specified
        ]);

        CRUD::addField([
            'name'        => 'ds_nums',
            'label'       => "Delivery Status",
            'type'        => 'checklist_table',
            'table'       =>  ['table_header' => $this->deliveryStatus()['header'], 'table_body'=> $this->deliveryStatus()['body']]
        ]);

        $this->crud->setCreateView('vendor.backpack.crud.create-tax');
    }


    private function deliveryStatus(){
        $tableHeader = [
            'PO',
            'DS Num',
            'DS Line',
            'Item',
            'Description',
            'Payment Plan Date',
            'Unit Price',
            'Qty Received',
            'Qty Rejected',
            'No Faktur',
            'No Surat Jalan Vendor',
            'Harga Sebelum Pajak',
            'PPN',
            'PPH',
            'Total',
        ];
        $deliveryStatuses = DeliveryStatus::select('*',
            DB::raw("(SELECT currency FROM vendor WHERE vend_num = (SELECT vend_num FROM po WHERE po.po_num = delivery_status.po_num)) as currency"))
            ->where('validate_by_fa_flag', 1)
            ->where('executed_flag', 0)
            ->orderBy('id', 'desc');
            if(Constant::getRole() != 'Admin PTKI'){
            $deliveryStatuses = $deliveryStatuses->whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num])
            ->get();
        }else{
            $deliveryStatuses = $deliveryStatuses->get();
        }
        $tableBody = [];

        foreach ($deliveryStatuses as $key => $ds) {
            $total = $ds->harga_sebelum_pajak + $ds->ppn + $ds->pph;
            $tableBody[] =[
                'column' => [
                    $ds->po_num.'-'.$ds->po_line,
                    $ds->ds_num,
                    $ds->ds_line,
                    $ds->item,
                    $ds->description,
                    date('Y-m-d', strtotime($ds->payment_plan_date)),
                    $ds->currency.' '.Constant::getPrice($ds->unit_price),
                    $ds->received_qty,
                    $ds->rejected_qty,
                    $ds->no_faktur_pajak,
                    $ds->no_surat_jalan_vendor,
                    $ds->currency.' '.Constant::getPrice($ds->harga_sebelum_pajak),
                    $ds->currency.' '.Constant::getPrice($ds->ppn),
                    $ds->currency.' '.Constant::getPrice($ds->pph),
                    $ds->currency.' '.Constant::getPrice($total),
                ],
                'value' => $ds->id
            ];
        }

        $table['header'] = $tableHeader;
        $table['body'] = $tableBody;

        return $table;
    }
    

    protected function setupUpdateOperation()
    {
        $this->crud->denyAccess('update');
    }


    public function store(Request $request)
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $request = $this->crud->getRequest();

        $urlFileFaktur = $request->file_faktur_pajak;
        $urlFileInvoice = $request->invoice;
        $urlFileSuratJalan = $request->file_surat_jalan;
        $dsNums = $request->input('ds_nums');

        $filename = "";
        $notExistFaktur = false;
        foreach ($dsNums as $key => $ds) {
            $faktur_pajak = DeliveryStatus::where('id', $ds)->first()->file_faktur_pajak;
            if (!isset($faktur_pajak)) {
                $notExistFaktur = true;
            }
        }

        if ($notExistFaktur) {
            Validator::make($request->all(), 
                ['file_faktur_pajak' => 'required|mimes:zip,pdf,doc,docx,xls,xlsx,png,jpg,jpeg'])
                ->validate();
        }

        if ($urlFileFaktur) {
            $filename = 'faktur_pajak_'.date('ymdhis').'.'.$urlFileFaktur->getClientOriginalExtension();
            $urlFileFaktur->move('files', $filename);
            $filename = 'files/'.$filename;

            foreach ($dsNums as $key => $ds) {
                $oldFiles = DeliveryStatus::where('id', $ds)->first()->file_faktur_pajak;
                if (isset($oldFiles)) {
                    $will_unlink_file =  $oldFiles;
                    unlink(public_path($will_unlink_file));
                }
                $change = DeliveryStatus::where('id', $ds)->first();
                $change->file_faktur_pajak = $filename;
                $change->save();
            }
        }

        // input invoice
        if($urlFileInvoice){
            $filenameInvoice = 'faktur_pajak_invoice'.date('ymdhis').'.'.$urlFileInvoice->getClientOriginalExtension();
            $urlFileInvoice->move('files', $filenameInvoice);
            $filenameInvoice = 'files/'.$filenameInvoice;

            foreach ($dsNums as $key => $ds) {
                $oldFiles = DeliveryStatus::where('id', $ds)->first()->invoice;
                if (isset($oldFiles)) {
                    $will_unlink_file =  $oldFiles;
                    unlink(public_path($will_unlink_file));
                }
                $change = DeliveryStatus::where('id', $ds)->first();
                $change->invoice = $filenameInvoice;
                $change->save();
            }
        }

        // input file surat jalan
        if($urlFileSuratJalan){
            $filenameSuratJalan = 'faktur_pajak_surat_jalan'.date('ymdhis').'.'.$urlFileSuratJalan->getClientOriginalExtension();
            $urlFileSuratJalan->move('files', $filenameSuratJalan);
            $filenameSuratJalan = 'files/'.$filenameSuratJalan;

            foreach ($dsNums as $key => $ds) {
                $oldFiles = DeliveryStatus::where('id', $ds)->first()->file_surat_jalan;

                if (isset($oldFiles)) {
                    $will_unlink_file = $oldFiles;
                    unlink(public_path($will_unlink_file));
                }
                $change = DeliveryStatus::where('id', $ds)->first();
                $change->file_surat_jalan = $filenameSuratJalan;
                $change->save();
            }
        }

        $message = 'Delivery Sheet Created';

        Alert::success($message)->flash();

        return redirect()->route('tax-invoice.index');
    }


    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $oldFile = DeliveryStatus::where('id', $id)->first();
        if (isset($oldFile->file_faktur_pajak)) {
            unlink(public_path($oldFile->file_faktur_pajak));
        }
        if (isset($oldFile->invoice)) {
            unlink(public_path($oldFile->invoice));
        }
        if (isset($oldFile->file_surat_jalan)) {
            unlink(public_path($oldFile->file_surat_jalan));
        }

        $change = DeliveryStatus::where('id', $id)->first();
        $change->file_faktur_pajak = null;
        $change->invoice = null;
        $change->file_surat_jalan = null;
        $success = $change->save();

        Comment::where('tax_invoice_id', $id)
            ->forcedelete();

        return $success;
    }
    

    public function confirmFakturPajak($id){
        $db = $this->crud->model::where('id', $id)->first();
        $db->confirm_flag = 1;
        $db->confirm_date = now();
        $status = $db->save();
        return $status;
    }


    public function confirmRejectFakturPajak($id){
        $db = $this->crud->model::where('id', $id)->first();
        $db->confirm_flag = 2;
        $db->confirm_date = now();
        $status = $db->save();

        if($status){
            $me = backpack_user()->id;
            $validator = Validator::make(request()->all(), [
                'comment' => 'required',
                'id_payment' => [
                    'required',
                    'integer',
                    'exists:App\Models\TaxInvoice,id',
                ]
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $messageErrors = [];
                foreach ($errors->all() as $message) {
                    array_push($messageErrors, $message);
                }
                return response()->json([
                    'status' => 'failed',
                    'type' => 'warning',
                    'message' => $messageErrors
                ], 200);
            }
            if(Constant::getRole() != 'Admin PTKI'){
                $vendor = backpack_user()->vendor->vend_num;
                $cekVendor = DeliveryStatus::join('po', 'po.po_num', '=', 'delivery_status.po_num')
                ->where('delivery_status.id', request()->input('id_payment'));

                if($cekVendor->count() > 0){
                    $cekVendor = $cekVendor->select('po.vend_num')->first();
                    if($cekVendor->vend_num != $vendor){
                        return response()->json([
                            'status' => 'failed',
                            'type' => 'warning',
                            'message' => 'Anda tidak mempunyai hubungan dengan vendor ini'
                        ]);
                    }
                }
            }

            $comment = new Comment;
            $comment->comment = '[REJECT REASON] '.request()->input('comment');
            $comment->tax_invoice_id = request()->input('id_payment');
            $comment->user_id = $me;
            $comment->status = 1;
            $saving = $comment->save();
            if($saving){
                return response()->json([
                    'status' => 'success',
                ], 200);
            }
        }
    }


    public function showComments(req $request){
        if($request->input('id_payment')){
            $invoiceId = $request->input('id_payment');
            $comments = Comment::join('users', 'users.id', 'comments.user_id')
            ->where('tax_invoice_id', $invoiceId)
            ->select('comments.id', 'comment', 'name', 'user_id', 'comments.created_at')
            ->get();
            $data = $comments->mapWithKeys(function($data, $index){
                return [$index => [
                    'id' => $data->id,
                    'comment' => $data->comment,
                    'time' => Constant::formatDateComment($data->created_at),
                    'user' => $data->name,
                    'status_user' => ($data->user_id == backpack_user()->id) ? 'You' : 'Other',
                    'style' => ($data->user_id == backpack_user()->id) ? 'text-success' : 'text-info'
                ]];
            });

            Comment::where('tax_invoice_id', $invoiceId)
            ->where('user_id', '!=', backpack_user()->id)
            ->update(['status' => 0]);

            return response()->json([
                'result' => $data,
                'status' => 'success',
            ]);
        }
        return response()->json([
            'message' => 'ID payment not found in the sistem',
            'alert' => 'warning'
        ], 404);
    }


    public function sendMessage(req $request){

        $me = backpack_user()->id;
        $paymentId = $request->input('id_payment');

        $validator = Validator::make($request->all(), [
            'comment' => 'required',
            'id_payment' => [
                'required',
                'integer',
                'exists:App\Models\TaxInvoice,id',
            ]
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $messageErrors = [];
            foreach ($errors->all() as $message) {
                array_push($messageErrors, $message);
            }
            return response()->json([
                'status' => 'failed',
                'type' => 'warning',
                'message' => $messageErrors
            ], 200);
        }

        $deliveryStatus = DeliveryStatus::where('id', $paymentId)->first();

        if ($deliveryStatus->executed_flag == 1) {
            return response()->json([
                'status' => 'failed',
                'type' => 'warning',
                'message' => 'Not Allowed!'
            ], 504);
        }

        if(Constant::getRole() != 'Admin PTKI'){
            $vendor = backpack_user()->vendor->vend_num;
            $cekVendor = DeliveryStatus::join('po', 'po.po_num', '=', 'delivery_status.po_num')
            ->where('delivery_status.id', $paymentId);

            if($cekVendor->count() > 0){
                $cekVendor = $cekVendor->select('po.vend_num')->first();
                if($cekVendor->vend_num != $vendor){
                    return response()->json([
                        'status' => 'failed',
                        'type' => 'warning',
                        'message' => 'Anda tidak mempunyai hubungan dengan vendor ini'
                    ]);
                }
            }
        }

        $comment = new Comment;
        $comment->comment = $request->input('comment');
        $comment->tax_invoice_id = $paymentId;
        $comment->user_id = $me;
        $comment->status = 1;
        $saved = $comment->save();
        if($saved){
            return response()->json([
                'status' => 'success',
            ], 200);
        }
    }


    public function deleteMessage(req $request){
        $mesage = Comment::where('id', $request->input('id'))->delete();
        if($mesage){
            return response()->json([
                'status' => 'success',
            ], 200);
        }
    }


    public function show()
    {
        $entry = $this->crud->getCurrentEntry();

        if (!$this->handlePermissionNonAdmin($entry->po_num)) {
            abort(404);
        }

        $deliveryStatus = DeliveryStatus::where('ds_num', $entry->ds_num )
                            ->where('ds_line', $entry->ds_line)
                            ->first();

        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['delivery_show'] = $this->detailDS($entry->id)['delivery_show'];
        $data['delivery_status'] = $deliveryStatus;

        return view('vendor.backpack.crud.list_payment_show', $data);
    }


    private function detailDS($id)
    {
        $deliveryShow = DeliveryStatus::leftjoin('po_line', function ($join) {
                            $join->on('po_line.po_num', 'delivery_status.po_num')
                                ->on('po_line.po_line', 'delivery_status.po_line');
                        })
                        ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                        ->leftJoin('vendor', 'vendor.vend_num', 'po.vend_num')
                        ->where('delivery_status.id', $id)
                        ->get(['delivery_status.id as id','delivery_status.ds_num','delivery_status.ds_line', 'po_line.due_date', 
                        'delivery_status.po_release','po_line.item','vendor.vend_num as vendor_number','vendor.currency as vendor_currency',
                        'vendor.vend_num as vendor_name', 'delivery_status.no_surat_jalan_vendor','po_line.item_ptki',
                        'po.po_num as po_number','po_line.po_line as po_line', 'delivery_status.shipped_qty', 'delivery_status.unit_price',
                        'delivery_status.tax_status', 'delivery_status.description'])
                        ->first();

        $data['delivery_show'] = $deliveryShow;

        return $data;
    }


    private function setup2(){
        $this->crud2 = new TaxInvoice;
        $this->crud2 = $this->crud2->join('po', 'po.po_num', 'delivery_status.po_num')
                ->join('vendor', 'vendor.vend_num', 'po.vend_num');
        $this->crud2 = $this->crud2->select('delivery_status.*','vendor.currency',
            DB::raw("(SELECT comment FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as comment"),
            DB::raw("(SELECT user_id FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as user"),
            DB::raw("(SELECT status FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as status"),
            // DB::raw("(SELECT id FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as id_comment"),
            // DB::raw("(SELECT currency FROM vendor WHERE vend_num = (SELECT vend_num FROM po WHERE po.po_num = delivery_status.po_num)) as currency")
        );
        if(Constant::getRole() != 'Admin PTKI'){
            // jika user bukan admin ptki
            $this->crud2 = $this->crud2->whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num]);
        }

        $this->crud2->where('executed_flag','!=', 0);
    }


    public function search2()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $totalRows = $this->crud2->count();
        $filteredRows = $this->crud2->toBase()->getCountForPagination();

        $startIndex = request()->input('start') ?: 0;
        // if a search term was present
        if (request()->input('search') && request()->input('search')['value']) {
            // filter the results accordingly
            $this->applySearchTerm2(request()->input('search')['value']);
            // recalculate the number of filtered rows
            $filteredRows = $this->crud2->count();
        }
        // start the results according to the datatables pagination
        if (request()->input('start')) {
            $this->crud2->skip((int) request()->input('start'));
        }
        // limit the number of results according to the datatables pagination
        if (request()->input('length')) {
            $this->crud2->take((int) request()->input('length'));
        }
        if (request()->input('order')) {
            foreach ((array) request()->input('order') as $order) {
                $columnNumber = (int) $order['column'];
                $columnDirection = (strtolower((string) $order['dir']) == 'asc' ? 'ASC' : 'DESC');
                $column = $this->crud->findColumnById($columnNumber);
                if ($column['tableColumn'] && ! isset($column['orderLogic'])) {
                    $this->crud2->orderBy($column['name'], $columnDirection);
                }
            }
        }else{
            $this->crud2->orderBy('id', 'DESC');
        }

        $entries = $this->crud2->get();

        return $this->getEntriesAsJsonForDatatables2($entries, $totalRows, $filteredRows, $startIndex, 'line_2');
    }


    public function getEntriesAsJsonForDatatables2($entries, $totalRows, $filteredRows, $startIndex = false, $lineButton)
    {
        $rows = [];

        foreach ($entries as $row) {
            $rows[] = $this->getRowViews2($row, $startIndex === false ? false : ++$startIndex, $lineButton);
        }

        $draw = 0;
        if(request()->input('draw')){
            $draw = (int) request()->input('draw');
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $totalRows,
            'recordsFiltered' => $filteredRows,
            'data'            => $rows,
        ];
    }

    
    public function getRowViews2($entry, $rowNumber = false, $lineButton)
    {
        $row_items = [];

        foreach ($this->crud->columns() as $key => $column) {
            $row_items[] = $this->crud->getCellView($column, $entry, $rowNumber);
        }

        // add the buttons as the last column
        if ($this->crud->buttons()->where('stack', $lineButton)->count()) {
            $row_items[] = \View::make('crud::inc.button_stack', ['stack' => $lineButton])
                                ->with('crud', $this->crud)
                                ->with('entry', $entry)
                                ->with('row_number', $rowNumber)
                                ->render();
        }

        // add the details_row button to the first column
        if ($this->crud->getOperationSetting('detailsRow')) {
            $details_row_button = \View::make('crud::columns.inc.details_row_button')
                                           ->with('crud', $this)
                                           ->with('entry', $entry)
                                           ->with('row_number', $rowNumber)
                                           ->render();
            $row_items[0] = $details_row_button.$row_items[0];
        }

        return $row_items;
    }

    
    public function applySearchTerm2($searchTerm)
    {
        return $this->crud2->where(function ($query) use ($searchTerm) {
            foreach ($this->crud->columns() as $column) {
                if (! isset($column['type'])) {
                    abort(400, 'Missing column type when trying to apply search term.');
                }

                $this->crud->applySearchLogicForColumn($query, $column, $searchTerm);
            }
        });
    }


    public function showFiles($filename)
    {
        $m_file = public_path('files/'.$filename);
        if (file_exists($m_file)) {
            return response()->file($m_file);
        }else{
            abort(404);
        }

    }


    private function handlePermissionNonAdmin($poNum){
        $allowAccess = false;

        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $allowAccess = true;
        }else{
            $vendNum = PurchaseOrder::where('po_num', $poNum)->first()->vend_num;
            if (backpack_auth()->user()->vendor->vend_num == $vendNum) {
                $allowAccess = true;
            }
        }

        return $allowAccess;
    }
}
