<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VendorRequest;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use App\Helpers\Constant;
use App\Models\Vendor;

class VendorCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Vendor::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vendor');
        CRUD::setEntityNameStrings('vendor', 'vendors');
        if(Constant::checkPermission('Read Vendor')){
           $this->crud->allowAccess('list'); 
        }else{
            $this->crud->denyAccess('list');
        }
    }


    protected function setupListOperation()
    {
        $this->crud->removeButton('show');

        if(!Constant::checkPermission('Update Vendor')){
            $this->crud->removeButton('update');
        }
        if(!Constant::checkPermission('Create Vendor')){
            $this->crud->removeButton('create');
        }
        if(!Constant::checkPermission('Delete Vendor')){
            $this->crud->removeButton('delete');
        }

        CRUD::addColumn([
            'label'     => 'Number', 
            'name'      => 'vend_num', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Vendor Name', 
            'name'      => 'vend_name', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Vendor Email', 
            'name'      => 'vend_email', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Buyer Name', 
            'name'      => 'buyer', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Buyer Email', 
            'name'      => 'buyer_email', 
            'type' => 'text',
        ]);
        CRUD::addColumn([
            'label'     => 'Address', 
            'name'      => 'vend_addr', 
            'type' => 'text',
        ]);
        CRUD::addColumn('currency');
        CRUD::column('created_at');
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
                $this->crud->addClause('where', 'vend_num', $value);
            });
        }else{
            $this->crud->removeButton('create');
            $this->crud->addClause('where', 'id', '=', backpack_auth()->user()->vendor->id);
        }
    }


    private function handlePermissionNonAdmin($vendor_id){
        $allowAccess = false;

        if(in_array(Constant::getRole(),['Admin PTKI'])){
            $allowAccess = true;

        }else{
            if (backpack_auth()->user()->vendor->id == $vendor_id) {
                $allowAccess = true;
            }
        }

        return $allowAccess;
    }

   
    protected function setupCreateOperation()
    {
        if(!Constant::checkPermission('Create Vendor')){
            $this->crud->denyAccess('create');
        }
        CRUD::setValidation(VendorRequest::class);
        $this->myFields('create');
    }


    protected function setupUpdateOperation()
    {
        $id = $this->crud->getCurrentEntry()->id;

        if(!$this->handlePermissionNonAdmin($id)){
            abort(404);
        }

        CRUD::setValidation(VendorRequest::class);
        $this->myFields('update');
    }


    private function myFields($fieldFor){
        CRUD::addField([
            'label'     => 'Number', 
            'name'      => 'vend_num', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Vendor Name', 
            'name'      => 'vend_name', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Vendor Email', 
            'name'      => 'vend_email', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Buyer Name', 
            'name'      => 'buyer', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Buyer Email', 
            'name'      => 'buyer_email', 
            'type' => 'text',
        ]);
        CRUD::addField([
            'label'     => 'Address', 
            'name'      => 'vend_addr', 
            'type' => 'text',
        ]);
        $attr = [];
        if ($fieldFor == 'update') {
            $attr = ['disabled' => 'disabled'];
        }
        CRUD::addField([
            'label'     => 'Currency', 
            'name'      => 'currency', 
            'type' => 'text',
            'attributes' => $attr 
        ]);
    }


    public function itemVendorOptions(Request $request){
        $term = $request->input('term');
        return Vendor::where('vend_name', 'like', '%'.$term.'%')
            ->orWhere('vend_num', 'like', '%'.$term.'%')
        ->select('vend_num', 'vend_name')
        ->get()
        ->mapWithKeys(function($vendor){
            return [$vendor->vend_num => $vendor->vend_num.' - '.$vendor->vend_name];
        });
    }
    

    public function itemVendorOptions2(Request $request){
        $term = $request->input('term');
        return Vendor::where('vend_name', 'like', '%'.$term.'%')
        ->orWhere('vend_num', 'like', '%'.$term.'%')
        ->get()
        ->mapWithKeys(function($vendor){
            return [$vendor->vend_num => $vendor->vend_num.' - '.$vendor->vend_name];
        });
    }
}
