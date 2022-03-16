<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DeliveryStatusRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\Constant;
use App\Models\DeliveryStatus;


class DeliveryStatusCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


    public function setup()
    {
        CRUD::setModel(DeliveryStatus::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery-status');
        CRUD::setEntityNameStrings('delivery status', 'delivery statuses');
        if(Constant::checkPermission('Read Delivery Status in Table')){
            $this->crud->allowAccess('list');
        }else{
            $this->crud->denyAccess('list');
        }
    }


    protected function setupShowOperation()
    {
        $this->crud->denyAccess('show');
    }

    protected function setupListOperation()
    {
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');
        $this->crud->removeButton('delete');
        $this->crud->removeButton('show');

        CRUD::column('id')->label('ID');
        CRUD::column('ds_num')->label('DS Num');
        CRUD::column('ds_line')->label('DS Line');
        CRUD::column('ds_type')->label('DS Type');
        CRUD::column('po_release')->label('PO Release');
        CRUD::column('description')->label('Desc');
        CRUD::column('grn_num')->label('GRN Num');
        CRUD::column('grn_line')->label('GRN Line');
        CRUD::addColumn([
            'label'     => 'Received Flag', // Table column heading
            'name'      => 'received_flag', // the column that contains the ID of that connected entity;
            'type' => 'flag_checked_html',
        ]);
        CRUD::column('received_date')->label('Received Date');
        CRUD::column('payment_plan_date')->label('Due Date');
        CRUD::addColumn([
            'label'     => 'Validated Flag', // Table column heading
            'name'      => 'validate_by_fa_flag', // the column that contains the ID of that connected entity;
            'type' => 'flag_checked_html',
        ]);
        CRUD::addColumn([
            'label'     => 'Payment in Process Flag', // Table column heading
            'name'      => 'payment_in_process_flag', // the column that contains the ID of that connected entity;
            'type' => 'flag_checked_html',
        ]);
        CRUD::addColumn([
            'label'     => 'Executed Flag', // Table column heading
            'name'      => 'executed_flag', // the column that contains the ID of that connected entity;
            'type' => 'flag_checked_html',
        ]);
        CRUD::column('payment_date')->label('Payment Date');
        CRUD::column('tax_status')->label('Tax Status');
        CRUD::column('payment_ref_num')->label('Payment Ref Num');
        CRUD::column('bank');
        CRUD::column('shipped_qty')->label('Shipped Qty');
        CRUD::column('received_qty')->label('Received Qty');
        CRUD::column('rejected_qty')->label('Rejected Qty');
        CRUD::addColumn([
            'label'     => 'Unit Price', // Table column heading
            'name'      => 'unit_price', // the column that contains the ID of that connected entity;
            'type'     => 'closure',
            'function' => function($entry) {
                $currency = $entry->purchaseOrder->vendor->currency;
                $val = number_format($entry->unit_price, 0, ',', '.');
                return $currency." ".$val;
            }
        ]);
        CRUD::addColumn([
            'name'     => 'total',
            'label'    => 'Total',
            'type'     => 'closure',
            'function' => function($entry) {
                $currency = $entry->purchaseOrder->vendor->currency;
                $val = number_format($entry->total, 0, ',', '.');
                return $currency." ".$val;
            }
        ]);
        CRUD::column('petugas_vendor')->label('Petugas Vendor');
        CRUD::column('no_faktur_pajak')->label('No Faktur Pajak');
        CRUD::column('no_surat_jalan_vendor')->label('No Surat Jalan Vendor');
        CRUD::column('ref_ds_num')->label('Ref DS Num');
        CRUD::column('ref_ds_line')->label('Ref DS Line');
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
                // SELECT d.id, d.ds_num, d.po_num, p.vend_num FROM `delivery` d
                // JOIN po p ON p.po_num = d.po_num
                // WHERE p.vend_num = 'V001303'
                $dbGet = \App\Models\DeliveryStatus::join('po', 'po.po_num', 'delivery_status.po_num')
                ->select('delivery_status.id as id')
                ->where('po.vend_num', $value)
                ->get()
                ->mapWithKeys(function($po, $index){
                    return [$index => $po->id];
                });
                $this->crud->addClause('whereIn', 'id', $dbGet->unique()->toArray());
            });
        }else{
            $this->crud->query->join('po as po', function($join){
                $join->on('delivery_status.po_num', '=', 'po.po_num')
                ->where('po.vend_num', '=', backpack_auth()->user()->vendor->vend_num);
            });
        }
    }


    protected function setupCreateOperation()
    {
        $this->crud->denyAccess('create');
    }

    
    protected function setupUpdateOperation()
    {
        $this->crud->denyAccess('update');

        $this->setupCreateOperation();
    }
}
