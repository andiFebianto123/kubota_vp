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
use App\Models\Vendor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Carbon\Carbon;


/**
 * Class TaxInvoiceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TaxInvoiceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public $crud2;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\TaxInvoice::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/tax-invoice');
        CRUD::setEntityNameStrings('faktur pajak', 'List Payment');
        $this->crud->query = $this->crud->query->select('*', 
            DB::raw("(SELECT comment FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as comment"),
            DB::raw("(SELECT user_id FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as user"),
            DB::raw("(SELECT status FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as status"),
            DB::raw("(SELECT id FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as id_comment"),
            DB::raw("(SELECT currency FROM vendor WHERE vend_num = (SELECT vend_num FROM po WHERE po.po_num = delivery_status.po_num)) as currency")
        );

        $this->setup2();
        
        if(Constant::getRole() != 'Admin PTKI'){
            // jika user bukan admin ptki
            $this->crud->query = $this->crud->query->whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num]);
        }

        if(Constant::checkPermission('Read List Payment')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }

        $this->crud->setListView('vendor.backpack.crud.list-payment');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // $this->crud->removeButton('show');
        $this->crud->removeButton('update');
        if(!Constant::checkPermission('Create Invoice and Tax')){
            $this->crud->removeButton('create');
        }
        $this->crud->addButtonFromView('line', 'accept_faktur_pajak', 'accept_faktur_pajak', 'begining');
        $this->crud->addButtonFromView('line', 'reject_faktur_pajak', 'reject_faktur_pajak', 'end');
        if(Constant::checkPermission('Download Button List Payment')){
            $this->crud->addButtonFromModelFunction('line', 'download', 'download', 'end');
        }
        // $this->crud->addButtonFromModelFunction('line', 'downloadV2', 'downloadV2', 'end'); 

        $this->crud->addButtonFromView('line_2', 'show2', 'show', 'begining');

       // $this->crud->addClause('where', 'file_faktur_pajak', '!=', null);
       $this->crud->addClause('where', 'payment_in_process_flag', '=', 1);
       $this->crud->addClause('where', 'executed_flag', '=', 0);

        CRUD::addColumn([
            'name'     => 'po_po_line',
            'label'    => 'PO',
            'type'     => 'closure',
            'function' => function($entry) {
                return $entry->po_num.'-'.$entry->po_line;
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
            'name'      => 'qty_received', // the column that contains the ID of that connected entity;
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
                return $entry->currency.' '.Constant::getPrice($entry->harga_sebelum_pajak);
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
            }
        ]);
        CRUD::column('updated_at');
        $this->crud->addFilter([
            'name'        => 'vendor',
            'type'        => 'select2_ajax',
            'label'       => 'Name Vendor',
            'placeholder' => 'Pick a vendor'
        ],
        url('admin/test/ajax-vendor-options'),
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

        $this->crud->addFilter([
            'type'  => 'date_range',
            'name'  => 'from_to',
            'label' => 'Payment Plan Date'
          ],
          false,
          function ($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            $this->crud->addClause('where', 'payment_plan_date', '>=', $dates->from);
            $this->crud->addClause('where', 'payment_plan_date', '<=', $dates->to);
          });
        $this->crud->button_create = 'Invoice and Tax';

        // ini buat table yang ke 2
        $this->crud->addFilter([
            'name'        => 'vendor2',
            'type'        => 'select2_ajax_custom',
            'label'       => 'Name Vendor',
            'placeholder' => 'Pick a vendor',
            'custom_table' => true,
        ],
        url('admin/test/ajax-vendor-options'),
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
        $this->crud->addFilter([
            'type'  => 'date_range_custom',
            'name'  => 'from_to_2',
            'label' => 'Payment Plan Date',
            'custom_table' => true,
        ],
        false,
        function ($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            $this->crud2 = $this->crud2->where('payment_plan_date','>=', $dates->from)
            ->where('payment_plan_date', '<=', $dates->to);
        });

        // COMING SOON
        // $results = $this->crud->model->select('*', 
        //     DB::raw("(SELECT vend_num FROM `po` WHERE id = (SELECT MAX(id) FROM `po` WHERE po.po_num = delivery_status.po_num)) as nama_vendor"),
        //     DB::raw("(SELECT po.po_date FROM `po` WHERE id = (SELECT MAX(id) FROM `po` WHERE po.po_num = delivery_status.po_num)) as po_date"),
        //     DB::raw("(SELECT po.id FROM `po` WHERE id = (SELECT MAX(id) FROM `po` WHERE po.po_num = delivery_status.po_num)) as id_po")
        // );
        // dd($results->get());

    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
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

    }

    private function deliveryStatus(){
        $table_header = ['PO', 'DS', 'Item', 'Description', 'Unit Price'];
        $delivery_statuses = DeliveryStatus::select('*', 
            DB::raw("(SELECT currency FROM vendor WHERE vend_num = (SELECT vend_num FROM po WHERE po.po_num = delivery_status.po_num)) as currency"))
        ->where('file_faktur_pajak', null);
        if(Constant::getRole() != 'Admin PTKI'){
            $delivery_statuses = $delivery_statuses->whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num])
            ->get();
        }else{
            $delivery_statuses = $delivery_statuses->get();
        }
        $table_body = [];
        foreach ($delivery_statuses as $key => $ds) {
            $table_body[] =[
                'column' => [
                    $ds->po_num.'-'.$ds->po_line, 
                    $ds->ds_num.'-'.$ds->ds_line, 
                    $ds->item, 
                    $ds->description,
                    $ds->currency.' '.Constant::getPrice($ds->unit_price),
                ],
                'value' => $ds->id
            ];
        }

        $table['header'] = $table_header;
        $table['body'] = $table_body;

        return $table;
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


    public function store(Request $request)
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $request = $this->crud->getRequest();

        $urlfile = $request->file_faktur_pajak;
        $ds_nums = $request->input('ds_nums');

        $filename = "";
        if ($urlfile) {
            $filename = 'faktur_pajak_'.date('ymdhis').'.'.$urlfile->getClientOriginalExtension();
            $urlfile->move('files', $filename);
            $filename = asset('files/'.$filename);
            
            foreach ($ds_nums as $key => $ds) {
                $old_files = DeliveryStatus::where('id', $ds)->first()->file_faktur_pajak;
                if (isset($old_files)) {
                    $base_url = url('/');
                    $will_unlink_file =  str_replace($base_url."/","",$old_files);
                    unlink(public_path($will_unlink_file));
                }
                $change = DeliveryStatus::where('id', $ds)->first();
                $change->file_faktur_pajak = $filename;
                $change->save();
            }
        }
        // input invoice
        if(isset($request->invoice)){
            $filenameInvoice = 'faktur_pajak_invoice'.date('ymdhis').'.'.$request->invoice->getClientOriginalExtension();
            $request->invoice->move('files', $filenameInvoice);
            $filenameInvoice = asset('files/'.$filenameInvoice);

            foreach ($ds_nums as $key => $ds) {
                $old_files = DeliveryStatus::where('id', $ds)->first()->invoice;
                if (isset($old_files)) {
                    $base_url = url('/');
                    $will_unlink_file =  str_replace($base_url."/","",$old_files);
                    unlink(public_path($will_unlink_file));
                }
                $change = DeliveryStatus::where('id', $ds)->first();
                $change->invoice = $filenameInvoice;
                $change->save();
            }
        }

        // input file surat jalan
        if(isset($request->file_surat_jalan)){
            $filenameSuratJalan = 'faktur_pajak_surat_jalan'.date('ymdhis').'.'.$request->file_surat_jalan->getClientOriginalExtension();
            $request->file_surat_jalan->move('files', $filenameSuratJalan);
            $filenameSuratJalan = asset('files/'.$filenameSuratJalan);

            foreach ($ds_nums as $key => $ds) {
                $old_files = DeliveryStatus::where('id', $ds)->first()->file_surat_jalan;
                if (isset($old_files)) {
                    $base_url = url('/');
                    $will_unlink_file =  str_replace($base_url."/","",$old_files);
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
       
        $change = DeliveryStatus::where('id', $id)->first();
        $change->file_faktur_pajak = null;
        $success = $change->save();

        $deleteComments = Comment::where('tax_invoice_id', $id)
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

        // if($status){
        //     $me = backpack_user()->id;
        //     $comment = new Comment;
        //     $comment->comment = "[REJECT REASON]";
        //     $comment->tax_invoice_id = $db->id;
        //     $comment->user_id = $me;
        //     $comment->status = 1;
        //     $saving = $comment->save();
        //     return $saving;
        // }
        // return $status;

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
            // jika berhasil melewati pengecekan vendor dengan delivery status
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
            $id_invoice = $request->input('id_payment');
            $comments = Comment::join('users', 'users.id', 'comments.user_id')
            ->where('tax_invoice_id', $id_invoice)
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

            $editComments = Comment::where('tax_invoice_id', $id_invoice)
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


        if(Constant::getRole() != 'Admin PTKI'){
            $vendor = backpack_user()->vendor->vend_num;
            $cekVendor = DeliveryStatus::join('po', 'po.po_num', '=', 'delivery_status.po_num')
            ->where('delivery_status.id', $request->input('id_payment'));

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
        $comment->tax_invoice_id = $request->input('id_payment');
        $comment->user_id = $me;
        $comment->status = 1;
        $saving = $comment->save();
        if($saving){
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

        // dd($entry);
        return view('vendor.backpack.crud.list-payment-show', $data);
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

    private function setup2(){
        $this->crud2 = new TaxInvoice;
        $this->crud2 = $this->crud2->select('*', 
            DB::raw("(SELECT comment FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as comment"),
            DB::raw("(SELECT user_id FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as user"),
            DB::raw("(SELECT status FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as status"),
            DB::raw("(SELECT id FROM `comments` WHERE id = (SELECT MAX(id) FROM `comments` WHERE delivery_status.id = comments.tax_invoice_id AND comments.deleted_at IS NULL)) as id_comment"),
            DB::raw("(SELECT currency FROM vendor WHERE vend_num = (SELECT vend_num FROM po WHERE po.po_num = delivery_status.po_num)) as currency")
        );
        if(Constant::getRole() != 'Admin PTKI'){
            // jika user bukan admin ptki
            $this->crud2 = $this->crud2->whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num]);
        }
        $this->crud2->where('payment_in_process_flag', 1);
        $this->crud2->where('executed_flag', 1);
    }


    /**
     * The search function that is called by the data table.
     *
     * @return array JSON Array of cells in HTML form.
     */
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
            // $this->crud2->take((int) request()->input('length'));
            $this->crud2->take((int) request()->input('length'));
        }
        // overwrite any order set in the setup() method with the datatables order
        if (request()->input('order')) {
            // clear any past orderBy rules
            // $this->crud->query->getQuery()->orders = null;
            foreach ((array) request()->input('order') as $order) {
                $column_number = (int) $order['column'];
                $column_direction = (strtolower((string) $order['dir']) == 'asc' ? 'ASC' : 'DESC');
                $column = $this->crud->findColumnById($column_number);
                if ($column['tableColumn'] && ! isset($column['orderLogic'])) {
                    // apply the current orderBy rules
                    // $this->crud->orderByWithPrefix($column['name'], $column_direction);
                    $this->crud2->orderBy($column['name'], $column_direction);
                }

                // check for custom order logic in the column definition
                if (isset($column['orderLogic'])) {
                    // $this->crud->customOrderBy($column, $column_direction);
                }
            }
        }else{
            $this->crud2->orderBy('id', 'DESC');
        }

        // show newest items first, by default (if no order has been set for the primary column)
        // if there was no order set, this will be the only one
        // if there was an order set, this will be the last one (after all others were applied)
        // Note to self: `toBase()` returns also the orders contained in global scopes, while `getQuery()` don't.
        // $orderBy = $this->crud->orders;
        // $table = 'delivery_status';
        // $key = 'id';

        // $hasOrderByPrimaryKey = collect($orderBy)->some(function ($item) use ($key, $table) {
        //     return (isset($item['column']) && $item['column'] === $key)
        //         || (isset($item['sql']) && str_contains($item['sql'], "$table.$key"));
        // });

        // if (! $hasOrderByPrimaryKey) {
        // }

        $entries = $this->crud2->get();

        return $this->getEntriesAsJsonForDatatables2($entries, $totalRows, $filteredRows, $startIndex, 'line_2');
    }



    /**
     * Created the array to be fed to the data table.
     *
     * @param  array  $entries  Eloquent results.
     * @param  int  $totalRows
     * @param  int  $filteredRows
     * @param  bool|int  $startIndex
     * @return array
     */
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

    /**
     * Get the HTML of the cells in a table row, for a certain DB entry.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $entry  A db entry of the current entity;
     * @param  bool|int  $rowNumber  The number shown to the user as row number (index);
     * @return array Array of HTML cell contents.
     */
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

    /**
     * Add conditions to the CRUD query for a particular search term.
     *
     * @param  string  $searchTerm  Whatever string the user types in the search bar.
     * @return \Illuminate\Database\Eloquent\Builder
     */
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



}
