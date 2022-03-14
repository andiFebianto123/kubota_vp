<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderSheetExport;
use App\Exports\PurchaseOrderExport;
use App\Exports\TemplateMassDsExport;
use App\Http\Requests\PurchaseOrderRequest;
use App\Imports\DeliverySheetImport;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Illuminate\Support\Facades\Mail;
use App\Mail\vendorNewPo;
use Illuminate\Support\Facades\DB;
use App\Helpers\Constant;

/**
 * Class PurchaseOrderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PurchaseOrderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\PurchaseOrder::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/purchase-order');
        CRUD::setEntityNameStrings('purchase order', 'purchase orders');
        if(Constant::checkPermission('Read Purchase Order')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
        if(!Constant::checkPermission('Read PO Detail')){
            $this->crud->denyAccess('show');
        }
    }

    protected function setupListOperation()
    {
        $current_role = backpack_auth()->user()->roles->pluck('name')->first();
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');     
        
        if(!Constant::checkPermission('Read Purchase Order')){
            $this->crud->removeButton('show');
        }
        if(Constant::checkPermission('Export Purchase Order')){
            $this->crud->addButtonFromModelFunction('top', 'excel_export', 'excelExport', 'beginning');
        }
        if(Constant::checkPermission('Send Mail New PO')){
            $this->crud->addButtonFromView('top', 'accept_vendor', 'accept_vendor', 'end');
        }
        if(Constant::checkPermission('Import Purchase Order')){
            $this->crud->addButtonFromView('top', 'massds', 'massds', 'end');
        }
        // $this->crud->enableExportButtons(); 
        $this->crud->orderBy('id', 'asc');
        if(!in_array($current_role, ['Admin PTKI'])){
            $this->crud->addClause('where', 'vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }


        CRUD::column('id')->label('ID');
        if(in_array($current_role,['Admin PTKI'])){
            CRUD::addColumn([
                'label'     => 'Kode Vendor', // Table column heading
                'name'      => 'vend_num', // the column that contains the ID of that connected entity;
                'entity'    => 'vendor', 
                'type' => 'relationship',
                'attribute' => 'vend_num',
            ]);
        }
        CRUD::addColumn([
            'label'     => 'PO Number', // Table column heading
            'name'      => 'po_num', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        // CRUD::addColumn([
        //     'label'     => 'Nama Vendor', // Table column heading
        //     'name'      => 'vendor_id', // the column that contains the ID of that connected entity;
        //     'entity'    => 'vendor', 
        //     'type' => 'relationship',
        //     'attribute' => 'name',
        // ]);
        CRUD::addColumn([
            'label'     => 'PO Date', // Table column heading
            'name'      => 'po_date', // the column that contains the ID of that connected entity;
            'type' => 'date',
            'format' => 'YYYY-M-D'
        ]);
        CRUD::addColumn([
            'name'     => 'email_flag',
            'label'    => 'Email Flag',
            'type'     => 'closure',
            'function' => function($entry) {
                return ($entry->email_flag) ? "✓":"-";
            }
        ]);        
        CRUD::addColumn([
            'label'     => 'PO Change', // Table column heading
            'name'      => 'po_change', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);

        // function() {
        //     return PurchaseOrderLine::groupBy('item')->select('item')->get()->mapWithKeys(function($item){
        //         return [$item->item => $item->item];
        //     })->toArray();
        // }

        $this->crud->addFilter([
            'name'  => 'item',
            'type'  => 'select2_multiple_ajax_po',
            'label' => 'Number Items',
            'url' => url('admin/filter-po/ajax-itempo-options'),
            'placeholder' => 'Pilih item number',
          ],
          function(){
          },
          function($values) { // if the filter is active
                $getPoLineSearch = PurchaseOrderLine::whereIn('item', json_decode($values));
                $keysValue = $getPoLineSearch->select('po_num')->get()->mapWithKeys(function($item, $index){
                    return [$index => $item->po_num];
                });
                $this->crud->addClause('whereIn', 'po_num', $keysValue->unique()->toArray());
          });

        if(in_array($current_role, ['Admin PTKI'])){
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor'
            ],
            url('admin/filter-vendor/ajax-itempo-options'),
            function($value) { 
                $this->crud->addClause('where', 'vend_num', $value);
            });
        }
        

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PurchaseOrderRequest::class);

        CRUD::field('id');
        CRUD::field('number');
        CRUD::field('vendor_id');
        CRUD::field('po_date');
        CRUD::field('email_flag');
        CRUD::field('created_at');
        CRUD::field('updated_at');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
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
        session()->put("last_url", request()->url());

        
        $po_lines = PurchaseOrderLine::where('po.po_num', $entry->po_num )
                                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                                // ->leftJoin('comments', function($query) {
                                //     $query->on('comments.article_id','=','articles.id')
                                //         ->whereRaw('comments.id IN (select MAX(a2.id) from comments as a2 join articles as u2 on u2.id = a2.article_id group by u2.id)');
                                // })
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->orderBy('po_line.po_change', 'desc')
                                ->get();

        $collection_po_lines = collect($po_lines)->unique('po_line')->sortBy('po_line');

        $po_changes_lines = PurchaseOrderLine::where('po_num', $entry->po_num )
                    ->where('po_change', '>', 0)
                    ->orderBy('po_change', 'desc')
                    ->groupBy('po_change')
                    ->get();
        /* not used
        $po_line_read_accs = PurchaseOrderLine::where('purchase_order_id', $entry->id )
                                ->where('read_at', '!=',null)
                                ->where('accept_flag', 1)
                                ->get();
        
        $po_line_read_rejects = PurchaseOrderLine::where('purchase_order_id', $entry->id )
                                ->where('read_at', '!=',null)
                                ->where('accept_flag', 2)
                                ->get();
        */
        $arr_po_line_status = (new Constant())->statusOFC();
        $arr_status = (new Constant())->arrStatus();
        
        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        // $data['po_line_read_accs'] = $po_line_read_accs;
        // $data['po_line_read_rejects'] = $po_line_read_rejects;
        $data['po_lines'] = $collection_po_lines;
        $data['po_changes_lines'] = $po_changes_lines;
        $data['arr_po_line_status'] = $arr_po_line_status;
        $data['arr_status'] = $arr_status;

        $can_access = false;
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $can_access = true;
        }else{
            $po = PurchaseOrder::where('id', $entry->id )->first();
            if (backpack_auth()->user()->vendor->vend_num == $po->vend_num) {
                $can_access = true;
            }
        }

        if ($can_access) {
            return view('vendor.backpack.crud.purchase-order-show', $data);
        }else{
            abort(404);
        }
    }

    public function detailChange($po_num, $po_change)
    {
        $po_lines = PurchaseOrderLine::where('po.po_num', $po_num )
                ->where('po_line.po_change', $po_change )
                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                ->orderBy('po_line.id', 'desc')
                ->get();
                
        $arr_po_line_status = (new Constant())->statusOFC();

        $data['crud'] = $this->crud;
        $data['po_num'] = $po_num;
        $data['po_change'] = $po_change;
        $data['po_lines'] = $po_lines;
        $data['arr_po_line_status'] = $arr_po_line_status;

        return view('vendor.backpack.crud.purchase-order-detail-change', $data);
    }


    public function update($id)
    {
        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();
        
        return redirect($this->crud->route);
    }

    public function destroy($id)
    {
        return true;
    }

    public function massRead(Request $request)
    {
        $po_line_ids = $request->po_line_ids;
        $po_id = $request->po_id;
        $flag_accept = $request->flag_accept;
        foreach ($po_line_ids as $key => $po_line_id) {
            $po_line = PurchaseOrderLine::where('id', $po_line_id)->first();
            $po_line->accept_flag = $flag_accept;
            $po_line->read_by = backpack_auth()->user()->id;
            $po_line->read_at = now();
            $po_line->save();
        }
        

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Read Successfully',
            'redirect_to' => url('admin/purchase-order')."/".$po_id."/show",
            'validation_errors' => []
        ], 200);
    }

    public function acceptPoLine(Request $request)
    {
        $po_line_ids = json_decode($request->po_line_ids);
        $po_id = $request->po_id;
        foreach ($po_line_ids as $key => $po_line_id) {
            $po_line = PurchaseOrderLine::where('id', $po_line_id)->first();
            $po_line->accept_flag = 1;
            $po_line->read_by = backpack_auth()->user()->id;
            $po_line->read_at = now();
            $po_line->save();
        }
        

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Accept Successfully',
            'redirect_to' => url('admin/purchase-order')."/".$po_id."/show",
            'validation_errors' => []
        ], 200);
    }

    public function rejectPoLine(Request $request)
    {
        $po_line_ids = json_decode($request->po_line_ids);
        $po_id = $request->po_id;
        $reason = $request->reason;
        foreach ($po_line_ids as $key => $po_line_id) {
            $po_line = PurchaseOrderLine::where('id', $po_line_id)->first();
            $po_line->reason = $reason;
            $po_line->accept_flag = 2;
            $po_line->read_by = backpack_auth()->user()->id;
            $po_line->read_at = now();
            $po_line->save();
        }
        

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Reject Successfully',
            'redirect_to' => url('admin/purchase-order')."/".$po_id."/show",
            'validation_errors' => []
        ], 200);
    }

    public function exportExcel()
    {
        return Excel::download(new PurchaseOrderExport, 'po-'.date('YmdHis').'.xlsx');

    }

    public function templateMassDs()
    {
        return Excel::download(new TemplateMassDsExport(backpack_auth()->user()), 'template-mass-ds-'.date('YmdHis').'.xlsx');

    }

    public function importDs(Request $request)
    {
        $rules = [
            'file_po' => 'required|mimes:xlsx,xls',
        ];

        $file = $request->file('file_po');
        

        $attrs['filename'] = $file;

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $message_errors = $this->validationMessage($validator, $rules);
            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => 'Required Form',
                'validation_errors' => $message_errors,
            ], 200);
        }

        try {
            $import = new DeliverySheetImport($attrs);
            $import->import($file);

            session()->flash('message', 'Data has been successfully import');
            session()->flash('status', 'success');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

             $failures = $e->failures();

             $arr_errors = [];

            foreach ($failures as $failure) {
                $arr_errors[] = [
                    'row' => $failure->row(),
                    'errormsg' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }
            $error_multiples = collect($arr_errors)->unique('row');

            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => 'Gagal mengimport data',
                'validation_errors' => [],
                'mass_errors' => $error_multiples
            ], 200);
        }

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Data has been successfully import',
            'redirect_to' => url('admin/temp-upload-delivery'),
            'validation_errors' => [],
        ], 200);
    }

    public function exportPdfOrderSheet($po_num)
    {
        $po = PurchaseOrder::where('po_num', $po_num)->first();

        $po_lines = PurchaseOrderLine::where('po.po_num', $po_num )
                                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->orderBy('po_line.id', 'desc')
                                ->where('status', 'O')
                                ->where('accept_flag', '<', 2)
                                ->get();
        $collection_po_lines = collect($po_lines)->unique('po_line')->sortBy('po_line');
        $arr_po_line_status = (new Constant())->statusOFC();
        
        $data['po_lines'] = $collection_po_lines;
        $data['po'] = $po;
        $data['arr_po_line_status'] = $arr_po_line_status;

        $pdf = PDF::loadview('exports.pdf.order-sheet',$data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream();
    }


    public function exportExcelOrderSheet($po_num)
    {
        $po = PurchaseOrder::where('po_num', $po_num)->first();

        $po_lines = PurchaseOrderLine::where('po.po_num', $po_num )
                                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->orderBy('po_line.id', 'desc')
                                ->where('status', 'O')
                                ->where('accept_flag', '<', 2)
                                ->get();
        $collection_po_lines = collect($po_lines)->unique('po_line')->sortBy('po_line');
        $arr_po_line_status = (new Constant())->statusOFC();
        
        $data['po_lines'] = $collection_po_lines;
        $data['po'] = $po;
        $data['arr_po_line_status'] = $arr_po_line_status;

        return Excel::download(new OrderSheetExport($data), 'order-sheet-'.date('YmdHis').'.xlsx');
    }

    private function validationMessage($validator,$rules)
    {
        $message_errors = [];
            $obj_validators = $validator->errors();
            foreach(array_keys($rules) as $key => $field){
                if ($obj_validators->has($field)) {
                    $message_errors[] = ['id' => $field , 'message'=> $obj_validators->first($field)];
                }
            }
        return $message_errors;
    }
    public function accept_all_po(){
        $pos = \App\Models\PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
        ->select('po.id as ID', 'vendor.vend_email as emails', 'vendor.buyer_email as buyers')
        ->whereNull('po.email_flag');
        if($pos->count() > 0){
            # alias terdapat data yang kosong
            $getPo = $pos->get();
            foreach($getPo as $po){
                $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";
                // $URL = url('admin/purchase-order/'.$po->ID.'/show');
                $details = [
                    'type' => 'reminder_po',
                    'title' => 'Ada PO baru',
                    'message' => 'Anda memiliki PO baru. Untuk melihat PO baru, anda dapat mengklik tombol dibawah ini.',
                    'url_button' => $URL."?prev_session=true" //url("admin/purchase-order/{$po->ID}/show")
                ];

                if($po->emails != null){
                    $pecahEmailVendor = explode(';', $po->emails); // email nya vendor
                    $pecahEmailBuyer = ($po->buyers != null) ? explode(';', $po->buyers) : '';
                    Mail::to($pecahEmailVendor)
                    ->cc($pecahEmailBuyer)
                    ->send(new vendorNewPo($details));
                }
                $updatePo = \App\Models\PurchaseOrder::where('id', $po->ID)->update([
                    'email_flag' => now()
                ]);
            }
        }
        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Request all Accept PO success',
        ], 200);
    }


    public function itemPoOptions(Request $request){
        $term = $request->input('term');
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            return PurchaseOrderLine::where('item', 'like', '%'.$term.'%')
                    ->groupBy('item')->select('item')->get()->mapWithKeys(function($item){
                return [$item->item => $item->item];
            });
        }else{
            return PurchaseOrderLine::join('po', 'po.po_num', 'po_line.po_num')
                    ->where('vend_num', backpack_auth()->user()->vendor->vend_num)
                    ->where('item', 'like', '%'.$term.'%')
                    ->groupBy('item')->select('item')->get()->mapWithKeys(function($item){
                return [$item->item => $item->item];
            });
        }
       
    }

}
