<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderSheetExport;
use App\Exports\TemplateExportAll;
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
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\vendorNewPo;
use App\Helpers\EmailLogWriter;
use App\Helpers\Constant;
use App\Models\TempUploadDelivery;
use Illuminate\Support\Facades\DB;
use Throwable;
use Maatwebsite\Excel\Events\AfterSheet;

// export with spout
use App\Library\ExportXlsx;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;


class PurchaseOrderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


    public function setup()
    {
        CRUD::setModel(PurchaseOrder::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/purchase-order');
        CRUD::setEntityNameStrings('purchase order', 'purchase orders');
        // $this->crud->filterPoNum = false;
        // $this->crud->filterVendNum = false;

        if(Constant::checkPermission('Read Purchase Order')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
        if(!Constant::checkPermission('Read PO Detail')){
            $this->crud->denyAccess('show');
        }
        $this->crud->allowAccess('advanced_export_excel');

    }

    protected function setupListOperation()
    {
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');     
        $this->crud->orderBy('id', 'asc');

        if(!Constant::checkPermission('Read Purchase Order')){
            $this->crud->removeButton('show');
        }
        if(Constant::checkPermission('Send Mail New PO')){
            $this->crud->enableBulkActions();
            $this->crud->addButtonFromView('top', 'bulk_send_mail_new_po', 'bulk_send_mail_new_po', 'beginning');
        }
        // if(Constant::checkPermission('Export Purchase Order')){
        //     $this->crud->addButtonFromModelFunction('top', 'excel_export', 'excelExport', 'end');
        // }
        if(Constant::checkPermission('Import Purchase Order')){
            $this->crud->addButtonFromView('top', 'mass_ds', 'mass_ds', 'end');
        }

        $this->crud->exportRoute = url('admin/purchase-order-export');
        $this->crud->addButtonFromView('top', 'advanced_export_excel', 'advanced_export_excel', 'end');

        // $this->crud->addButtonFromModelFunction('top', 'excel_export_advance', 'excelExportAdvance', 'end');

        if(!in_array(Constant::getRole(), ['Admin PTKI'])){
            $this->crud->addClause('where', 'vend_num', '=', backpack_auth()->user()->vendor->vend_num);
        }

        CRUD::column('id')->label('ID');
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            CRUD::addColumn([
                'label'     => 'Kode Vendor',
                'name'      => 'vend_num',
                'entity'    => 'vendor', 
                'type' => 'relationship',
                'attribute' => 'vend_num',
            ]);
        }
        CRUD::addColumn([
            'label'     => 'PO Number', 
            'name'      => 'po_num', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'PO Date', 
            'name'      => 'po_date', 
            'type' => 'text',
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
            'label'     => 'PO Change', 
            'name'      => 'po_change', 
            'type' => 'text',
        ]);

        $this->crud->addFilter([
            'name'  => 'item',
            'type'  => 'select2_multiple_ajax_po',
            'label' => 'Number Items',
            'url' => url('admin/filter-po/ajax-itempo-options'),
            'placeholder' => 'Pilih item number',
          ],
          function(){
            session()->put("filter_po_num", null);
            session()->put("filter_item", null);
          },
          function($values) {
                $getPoLineSearch = PurchaseOrderLine::whereIn('item', json_decode($values));
                $keysValue = $getPoLineSearch->select('po_num')->get()->mapWithKeys(function($item, $index){
                    return [$index => $item->po_num];
                });
                // $this->crud->filterPoNum = $keysValue->unique()->toArray();
                session()->put("filter_po_num", $keysValue->unique()->toArray());
                session()->put("filter_item", $values);
                $this->crud->addClause('whereIn', 'po_num', $keysValue->unique()->toArray());
          });

        if(in_array(Constant::getRole(), ['Admin PTKI'])){
            if (!request('vendor')) {
                session()->put("filter_vend_num", null);
            }
            $this->crud->addFilter([
                'name'        => 'vendor',
                'type'        => 'select2_ajax',
                'label'       => 'Name Vendor',
                'placeholder' => 'Pick a vendor'
            ],
            url('admin/filter-vendor/ajax-itempo-options'),
            function($value) { 
                // $this->crud->filterVendNum = $value;
                    $this->crud->addClause('where', 'vend_num', $value);
                    session()->put("filter_vend_num", $value);
            });
        }
    }


    function show()
    {
        $entry = $this->crud->getCurrentEntry();
        session()->put("last_url", request()->url());

        $arrPoLineStatus = (new Constant())->statusOFC();
        $arrStatus = (new Constant())->arrStatus();

        $poLines = PurchaseOrderLine::where('po.po_num', $entry->po_num )
                                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->orderBy('po_line.po_change', 'desc')
                                ->get();

        $collectionPoLines = collect($poLines)->unique('po_line')->sortBy('po_line');

        $poChangesLines = PurchaseOrderLine::where('po_num', $entry->po_num )
                    ->where('po_change', '>', 0)
                    ->orderBy('po_change', 'desc')
                    ->groupBy('po_change')
                    ->get();
        
        $data['crud'] = $this->crud;
        $data['entry'] = $entry;
        $data['po_lines'] = $collectionPoLines;
        $data['po_changes_lines'] = $poChangesLines;
        $data['arr_po_line_status'] = $arrPoLineStatus;
        $data['arr_status'] = $arrStatus;

        $canAccess = false;
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $canAccess = true;
        }else{
            $po = PurchaseOrder::where('id', $entry->id )->first();
            if (backpack_auth()->user()->vendor->vend_num == $po->vend_num) {
                $canAccess = true;
            }
        }

        if ($canAccess) {
            return view('vendor.backpack.crud.purchase_order_show', $data);
        }else{
            abort(404);
        }
    }


    public function detailChange($poNum, $poChange)
    {
        $poLines = PurchaseOrderLine::where('po.po_num', $poNum)
                ->where('po_line.po_change', $poChange )
                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                ->orderBy('po_line.id', 'desc')
                ->get();
                
        $arrPoLineStatus = (new Constant())->statusOFC();

        $data['crud'] = $this->crud;
        $data['po_num'] = $poNum;
        $data['po_change'] = $poChange;
        $data['po_lines'] = $poLines;
        $data['arr_po_line_status'] = $arrPoLineStatus;

        return view('vendor.backpack.crud.purchase_order_detail_change', $data);
    }


    public function massRead(Request $request)
    {
        $poLineIds = $request->po_line_ids;
        $poId = $request->po_id;
        $flagAccept = $request->flag_accept;
        foreach ($poLineIds as $key => $poLineId) {
            $po_line = PurchaseOrderLine::where('id', $poLineId)->first();
            $po_line->accept_flag = $flagAccept;
            $po_line->read_by = backpack_auth()->user()->id;
            $po_line->read_at = now();
            $po_line->save();
        }
        
        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Read Successfully',
            'redirect_to' => url('admin/purchase-order')."/".$poId."/show",
            'validation_errors' => []
        ], 200);
    }


    public function acceptPoLine(Request $request)
    {
        $poLineIds = json_decode($request->po_line_ids);
        $poId = $request->po_id;
        foreach ($poLineIds as $key => $poLineId) {
            $po_line = PurchaseOrderLine::where('id', $poLineId)->first();
            $po_line->accept_flag = 1;
            $po_line->read_by = backpack_auth()->user()->id;
            $po_line->read_at = now();
            $po_line->save();
        }
        

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Accept Successfully',
            'redirect_to' => url('admin/purchase-order')."/".$poId."/show",
            'validation_errors' => []
        ], 200);
    }


    public function rejectPoLine(Request $request)
    {
        $poLineIds = json_decode($request->po_line_ids);
        $poId = $request->po_id;
        $reason = $request->reason;

        foreach ($poLineIds as $key => $poLineId) {
            $po_line = PurchaseOrderLine::where('id', $poLineId)->first();
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
            'redirect_to' => url('admin/purchase-order')."/".$poId."/show",
            'validation_errors' => []
        ], 200);
    }


    public function exportExcel()
    {
        $filename = 'po-'.date('YmdHis').'.xlsx';

        return Excel::download(new PurchaseOrderExport, $filename);
    }


    public function templateMassDs(Request $request)
    {
        $filename = 'template-mass-ds-'.date('YmdHis').'.xlsx';
        $headerRange = "M";
        $styleRange = "I";
        if(Constant::checkPermission('Show Price In PO Menu')){
            $headerRange = "N";
            $styleRange = "J";
        }
        $attrs['filter_po_num'] = session()->get('filter_po_num') ?? null;
        $attrs['filter_vend_num'] = session()->get('filter_vend_num') ?? null;
        $attrs['filter_item'] = session()->get('filter_item') ?? null;
        $attrs['header_range'] = $headerRange; // default M
        $attrs['style_range'] = $styleRange; // default I

        Excel::store(new TemplateMassDsExport($attrs),$filename, 'excel_export');
        // public_path('export-excel/'.$filename);

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses Generate PDF',
            'newtab' => true,
            'redirect_to' => asset('export-excel/'.$filename) ,
            'validation_errors' => []
        ], 200);
    }


    public function checkExistingTemp(){
        $countTemp = TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->count();

        return response()->json([
            'status' => true,
            'counting' => $countTemp,
        ], 200);
    }


    public function importDs(Request $request)
    {
        $rules = [
            'file_po' => 'required|mimes:xlsx,xls',
        ];

        $insertOrUpdate = $request->input('insert_or_update');
        $file = $request->file('file_po');

        DB::beginTransaction();
        
        $attrs['insert_or_update'] = $insertOrUpdate;

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $this->validationMessage($validator, $rules);
            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => 'Required Form',
                'validation_errors' => $errorMessages,
            ], 200);
        }

        try {
            if ($insertOrUpdate == 'insert') {
                TempUploadDelivery::where('user_id', backpack_auth()->user()->id)->delete();
            }
            $import = new DeliverySheetImport($attrs);
            $import->import($file);
            DB::commit();

            session()->flash('message', 'Data has been successfully import');
            session()->flash('status', 'success');
        } catch (Throwable $e) {

            // $failures = $e->mes();
            // $arrErrors = [];

            // foreach ($failures as $failure) {
            //     $arrErrors[] = [
            //         'row' => $failure->row(),
            //         'errormsg' => $failure->errors(),
            //         'values' => $failure->values(),
            //     ];
            // }
            // $errorMultiples = collect($arrErrors)->unique('row');

            DB::rollback();

            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => 'Gagal mengimport data, periksa kembali file Anda',
                'validation_errors' => [],
                'mass_errors' => []
            ], 500);
        }

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Data has been successfully import',
            'redirect_to' => url('admin/temp-upload-delivery'),
            'validation_errors' => [],
        ], 200);
    }


    public function exportPdfOrderSheet($poNum)
    {
        $po = PurchaseOrder::where('po_num', $poNum)->first();

        $poLines = PurchaseOrderLine::where('po.po_num', $poNum )
                                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->orderBy('po_line.id', 'desc')
                                ->where('status', 'O')
                                ->where('accept_flag', '<', 2)
                                ->get();

        $collectionPoLines = collect($poLines)->unique('po_line')->sortBy('po_line');
        $arrPoLineStatus = (new Constant())->statusOFC();
        
        $data['po_lines'] = $collectionPoLines;
        $data['po'] = $po;
        $data['arr_po_line_status'] = $arrPoLineStatus;

        $pdf = PDF::loadview('exports.pdf.order_sheet',$data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream();
    }


    public function exportExcelOrderSheet($poNum)
    {
        $po = PurchaseOrder::where('po_num', $poNum)->first();

        $poLines = PurchaseOrderLine::where('po.po_num', $poNum )
                                ->leftJoin('po', 'po.po_num', 'po_line.po_num')
                                ->leftJoin('vendor', 'po.vend_num', 'vendor.vend_num')
                                ->select('po_line.*', 'vendor.vend_name as vendor_name', 'vendor.currency as vendor_currency')
                                ->orderBy('po_line.id', 'desc')
                                ->where('status', 'O')
                                ->where('accept_flag', '<', 2)
                                ->get();

        $collectionPoLines = collect($poLines)->unique('po_line')->sortBy('po_line');
        $arrPoLineStatus = (new Constant())->statusOFC();
        $filename = 'order-sheet-'.date('YmdHis').'.xlsx';
        
        $data['po_lines'] = $collectionPoLines;
        $data['po'] = $po;
        $data['arr_po_line_status'] = $arrPoLineStatus;

        return Excel::download(new OrderSheetExport($data), $filename);
    }


    private function validationMessage($validator,$rules)
    {
        $errorMessages = [];
            $objValidators = $validator->errors();
            foreach(array_keys($rules) as $key => $field){
                if ($objValidators->has($field)) {
                    $errorMessages[] = ['id' => $field , 'message'=> $objValidators->first($field)];
                }
            }
        return $errorMessages;
    }


    public function acceptAllPo(){
        $pos = PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
        ->select('po.id as ID', 'po.po_num as poNumber','vendor.vend_email as emails', 'vendor.buyer_email as buyers')
        ->whereNull('po.email_flag');
        if($pos->count() > 0){
            $getPo = $pos->get();
            foreach($getPo as $po){
                $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";
                $details = [
                    'po_num' => $po->poNumber,
                    'type' => 'reminder_po',
                    'title' => 'Ada PO baru - '.$po->poNumber,
                    'message' => 'Anda memiliki PO baru. Untuk melihat PO baru, anda dapat mengklik tombol dibawah ini.',
                    'url_button' => $URL."?prev_session=true" 
                ];

                if($po->emails != null){
                    try{
                        $pecahEmailVendor = explode(';', $po->emails);
                        $pecahEmailBuyer = ($po->buyers != null) ? explode(';', $po->buyers) : '';
                        Mail::to($pecahEmailVendor)
                        ->cc($pecahEmailBuyer)
                        ->send(new vendorNewPo($details));
                    }
                    catch(Exception $e){
                        $subject = 'New Purchase Order - [' . $details['po_num'] . ']New Purchase Order - [' . $details['po_num'] . ']';
                        $pecahEmailVendor = implode(", ", explode(';', $po->emails));
                        $pecahEmailBuyer = ($po->buyers != null) ?  implode(", ", explode(';', $po->buyers)) : '';
                        
                        (new EmailLogWriter())->create($subject, $pecahEmailVendor, $e->getMessage(), $pecahEmailBuyer);
                        DB::commit();
                        
                        return response()->json([
                            'status' => false,
                            'alert' => 'Error',
                            'message' => 'Mail not sent. Please check in email logs for further information',
                        ], 500);
                    }
                }
                PurchaseOrder::where('id', $po->ID)->update([
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


    public function sendMailNewPo(Request $request){
        $poIds = $request->ids;

        DB::beginTransaction();
        try {
            $pos = PurchaseOrder::join('vendor', 'po.vend_num', '=', 'vendor.vend_num')
            ->select('po.id as ID','po.po_num as poNumber', 'vendor.vend_email as emails', 'vendor.buyer_email as buyers')
            ->whereIn('po.id', $poIds);

            if($pos->count() > 0){
                $getPo = $pos->get();
                foreach($getPo as $po){
                    $URL = env('APP_URL_PRODUCTION') . "/purchase-order/{$po->ID}/show";
                    $details = [
                        'po_num' => $po->poNumber,
                        'type' => 'reminder_po',
                        'title' => 'Ada PO baru - ' . $po->poNumber,
                        'message' => 'Anda memiliki PO baru. Untuk melihat PO baru, anda dapat mengklik tombol dibawah ini.',
                        'url_button' => $URL."?prev_session=true" 
                    ];
                    
                    if($po->emails != null){
                        try{
                            $pecahEmailVendor = explode(';', $po->emails);
                            $pecahEmailBuyer = ($po->buyers != null) ? explode(';', $po->buyers) : '';
                            Mail::to($pecahEmailVendor)
                            ->cc($pecahEmailBuyer)
                            ->send(new vendorNewPo($details));
                        }
                        catch(Exception $e){
                            $subject = 'New Purchase Order - [' . $details['po_num'] . ']New Purchase Order - [' . $details['po_num'] . ']';
                            $pecahEmailVendor = implode(", ", explode(';', $po->emails));
                            $pecahEmailBuyer = ($po->buyers != null) ?  implode(", ", explode(';', $po->buyers)) : '';
                            
                            (new EmailLogWriter())->create($subject, $pecahEmailVendor, $e->getMessage(), $pecahEmailBuyer);
                            DB::commit();
                            
                            return response()->json([
                                'status' => false,
                                'alert' => 'Error',
                                'message' => 'Mail Not Sent. Please check in email logs for further information',
                            ], 500);
                        }
                            
                    }
                    PurchaseOrder::where('id', $po->ID)->update([
                        'email_flag' => now()
                    ]);
                }
                DB::commit();
            }

            return response()->json([
                'status' => true,
                'alert' => 'success',
                'message' => 'Mail Sent Successfully',
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


    public function itemPoOptions(Request $request){
        $term = $request->input('term');
        if(in_array(Constant::getRole(),['Admin PTKI'])){
            return PurchaseOrderLine::where('item', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%')
                    ->groupBy('item')
                    ->select('item', 'description')
                    ->get()
                    ->mapWithKeys(function($item){
                    return [$item->item => $item->item.'-'.$item->description];
            });
        }else{
            return PurchaseOrderLine::join('po', 'po.po_num', 'po_line.po_num')
                    ->where('vend_num', backpack_auth()->user()->vendor->vend_num)
                    ->where(function($q) use ($term) {
                        $q->where('item', 'like', '%'.$term.'%')
                          ->orWhere('description', 'like', '%'.$term.'%');
                    })
                    ->groupBy('item')
                    ->select('item', 'description')
                    ->get()
                    ->mapWithKeys(function($item){
                return [$item->item => $item->item.'-'.$item->description];
            });
        }
       
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

          
            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'id' => $result->id,
                    'vend_num' => $result->vend_num,
                    'po_num' => $result->po_num,
                    'po_date' => $result->po_date,
                    'po_change' => $result->po_change,
                    'email_flag' => function($result) {
                        return ($result->email_flag) ? "✓":"-";
                    }
                ];
            };

            $filename = 'po-'.date('YmdHis').'.xlsx';

            // $GLOBALS['col'] = '<cols>';
            // $GLOBALS['col'] .= '<col min="1" max="1" width="10" customWidth="1"/>';
            // $GLOBALS['col'] .= '<col min="2" max="2" width="15" customWidth="1"/>';
            // $GLOBALS['col'] .= "</cols>";
    
            $export = new ExportXlsx($filename);
    
            $styleForHeader = (new StyleBuilder())
                            ->setFontBold()
                            ->setFontColor(Color::WHITE)
                            ->setCellAlignment(CellAlignment::LEFT)
                            ->setBackgroundColor(Color::rgb(102, 171, 163))
                            ->build();
    
            $firstSheet = $export->currentSheet();
    
            $export->addRow(['No', 
            'ID',
            'Kode Vendor',
            'PO Number',
            'PO Date',
            'Email Flag',
            'PO Change'
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
    } 

    public function exportAdvance2(Request $request){
        if(session()->has('sqlSyntax')){
            $sqlQuery = session('sqlSyntax');
            $pattern = '/((limit+\s+[0-9]+)|(offset+\s+[0-9]+))/i';
            $query = preg_replace($pattern, "", $sqlQuery);
            $data = DB::select($query);

            $filename = 'po-'.date('YmdHis').'.xlsx';


            $title = "Report PO";

            $header = [
                'no' => 'No',
                'id' => 'ID',
                'vend_num' => 'Kode Vendor',
                'po_num' => 'PO Number',
                'po_date' => 'PO Date',
                'email_flag' => 'Email Flag',
                'po_change' => 'PO Change'
            ];

            $resultCallback = function($result){
                return [
                    'no' => '<number>',
                    'id' => $result->id,
                    'vend_num' => $result->vend_num,
                    'po_num' => $result->po_num,
                    'po_date' => $result->po_date,
                    'po_change' => $result->po_change,
                    'email_flag' => function($result) {
                        return ($result->email_flag) ? "✓":"-";
                    }
                ];
            };

            $styleHeader = function(AfterSheet $event){
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

                $arrColumns = range('A', 'G');
                foreach ($arrColumns as $key => $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                    $event->sheet->getStyle($col.'1')->getFont()->setBold(true);
                }
                
                $event->sheet->getDelegate()->getStyle('A1:G1')->applyFromArray($styleHeader);
            };

            return Excel::download(new TemplateExportAll($data, $header, $resultCallback, $styleHeader, $title), $filename);
        }
        return 0;
    }

}
