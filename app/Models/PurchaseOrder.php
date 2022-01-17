<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class PurchaseOrder extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    protected $table = 'po';
    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'vend_num', 'vend_num');
    }

    public function viewByPoNum()
    {
        return '<a class="btn btn-sm btn-link" href="'.url('admin/purchase-order/'.$this->po_num.'/show').'"><i class="la la la-eye"></i> View</a>';
    }

    public function excelExport($crud = false)
    {
        return '<a class="btn btn-sm btn-primary-vp" href="'.url('admin/purchase-order-export-excel').'"><i class="la la-file-excel"></i> Export</a>';
    }
}
