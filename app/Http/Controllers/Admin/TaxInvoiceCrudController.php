<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TaxInvoiceRequest;
use App\Models\DeliveryStatus;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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

        

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        CRUD::addField([ 
            'name'        => 'ds_num_arr',
            'label'       => "Delivery Status",
            'type'        => 'select2_from_array',
            'options'     => $this->deliveryStatus(),
            'allows_null' => true,
            'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        CRUD::addField([   // Upload
            'name'      => 'file_faktur_pajak',
            'label'     => 'Faktur Pajak',
            'type'      => 'upload',
            'upload'    => true,
            'disk'      => 'uploads', // if you store files in the /public folder, please omit this; if you store them in /storage or S3, please specify it;
            // optional:
            'temporary' => 10 // if using a service, such as S3, that requires you to make temporary URLs this will make a URL that is valid for the number of minutes specified
        ]);  
    }

    private function deliveryStatus(){
        $delivery_statuses = DeliveryStatus::get();
        $arr_del = [];
        foreach ($delivery_statuses as $key => $ds) {
            $arr_del[$ds->id] = $ds->ds_num.'-'.$ds->ds_line;
        }
        return $arr_del;
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
}
