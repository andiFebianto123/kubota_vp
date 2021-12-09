<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TaxInvoiceRequest;
use App\Models\DeliveryStatus;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Prologue\Alerts\Facades\Alert;

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
        CRUD::setEntityNameStrings('faktur pajak', 'faktur pajak');
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
        $this->crud->addButtonFromModelFunction('line', 'download', 'download', 'beginning');

        $this->crud->addClause('where', 'file_faktur_pajak', '!=', null);

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
            'type' => 'text',
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
            'label'     => 'PPN', // Table column heading
            'name'      => 'ppn', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'PPH', // Table column heading
            'name'      => 'pph', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Harga Sebelum Pajak', // Table column heading
            'name'      => 'harga_sebelum_pajak', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'No Faktur', // Table column heading
            'name'      => 'no_faktur_pajak', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'No Voucher', // Table column heading
            'name'      => 'no_voucher', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);

        CRUD::column('created_at');
        CRUD::column('updated_at');
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

        CRUD::addField([ 
            'name'        => 'ds_nums',
            'label'       => "Delivery Status",
            'type'        => 'checklist_table',
            'table'       =>  ['table_header' => $this->deliveryStatus()['header'], 'table_body'=> $this->deliveryStatus()['body']]
        ]);

    }

    private function deliveryStatus(){
        $table_header = ['PO', 'DS', 'Item', 'Description', 'Unit Price'];
        $delivery_statuses = DeliveryStatus::get();
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

}
