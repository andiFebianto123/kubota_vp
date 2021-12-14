<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VendorRequest;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

/**
 * Class VendorCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VendorCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Vendor::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vendor');
        CRUD::setEntityNameStrings('vendor', 'vendors');
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

        CRUD::addColumn([
            'label'     => 'Number', // Table column heading
            'name'      => 'vend_num', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Vendor Name', // Table column heading
            'name'      => 'vend_name', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Vendor Email', // Table column heading
            'name'      => 'vend_email', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        
        CRUD::addColumn([
            'label'     => 'Buyer Name', // Table column heading
            'name'      => 'buyer_name', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Buyer Email', // Table column heading
            'name'      => 'buyer_email', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'label'     => 'Address', // Table column heading
            'name'      => 'vend_addr', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        
        CRUD::addColumn('currency');

        CRUD::column('created_at');
        CRUD::column('updated_at');

        $this->crud->addFilter([
            'name'        => 'vendor',
            'type'        => 'select2_ajax',
            'label'       => 'Name Vendor',
            'placeholder' => 'Pick a vendor'
        ],
        url('admin/test/ajax-vendor-options'),
        function($value) { 
            $this->crud->addClause('where', 'vend_num', $value);
        });
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
        CRUD::setValidation(VendorRequest::class);
        $this->myFields('create');

        // $this->currencyCrud();
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
        CRUD::setValidation(VendorRequest::class);
        $this->myFields('update');
    }

    private function myFields($field_for){
        CRUD::addField([
            'label'     => 'Number', // Table column heading
            'name'      => 'vend_num', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Vendor Name', // Table column heading
            'name'      => 'vend_name', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Vendor Email', // Table column heading
            'name'      => 'vend_email', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Buyer Name', // Table column heading
            'name'      => 'buyer_name', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Buyer Email', // Table column heading
            'name'      => 'buyer_email', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Address', // Table column heading
            'name'      => 'vend_addr', // the column that contains the ID of that connected entity;
            'type' => 'text',
        ]);
        $attr = [];
        if ($field_for == 'update') {
            $attr = ['disabled' => 'disabled'];
        }
        CRUD::addField([
            'label'     => 'Currency', // Table column heading
            'name'      => 'currency', // the column that contains the ID of that connected entity;
            'type' => 'text',
            'attributes' => $attr 
        ]);
    }

    // public function update($id)
    // {
    //     // show a success message
    //     Alert::success(trans('backpack::crud.update_success'))->flash();
        
    //     return redirect($this->crud->route);
    // }

    // public function destroy($id)
    // {
    //     return true;
    // }

    public function itemVendorOptions(Request $request){
        $term = $request->input('term');
        return \App\Models\Vendor::where('vend_name', 'like', '%'.$term.'%')
        ->select('vend_num', 'vend_name')
        ->get()
        ->mapWithKeys(function($vendor){
            return [$vendor->vend_num => $vendor->vend_name];
        });
    }
}
