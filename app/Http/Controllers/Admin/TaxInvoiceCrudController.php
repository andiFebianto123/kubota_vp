<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TaxInvoiceRequest;
use App\Models\DeliveryStatus;
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
        // $c = Comment::where('id', 5)->delete();
        // ->orderBy('id_comment', 'DESC');

    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->removeButton('show');
        $this->crud->removeButton('update');
        $this->crud->addButtonFromView('line', 'accept_faktur_pajak', 'accept_faktur_pajak', 'begining');
        $this->crud->addButtonFromView('line', 'reject_faktur_pajak', 'reject_faktur_pajak', 'end');
        $this->crud->addButtonFromModelFunction('line', 'download', 'download', 'end');

        $this->crud->addClause('where', 'file_faktur_pajak', '!=', null);
        // dd($this->crud->getEntries());

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
        $delivery_statuses = DeliveryStatus::where('file_faktur_pajak', null)->get();
        $table_body = [];
        foreach ($delivery_statuses as $key => $ds) {
            $table_body[] =[
                'column' => [
                    $ds->po_num.'-'.$ds->po_line, 
                    $ds->ds_num.'-'.$ds->ds_line, 
                    $ds->item, 
                    $ds->description,
                    $ds->unit_price 
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
                $change->invoice = $filenameSuratJalan;
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
        return $status;
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

}
