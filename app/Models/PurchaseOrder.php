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

    protected $fillable = [
        'po_num',
        'vend_num',
        'po_date',
        'po_change',
        'email_flag',
        'session_batch_proccess',
    ];

    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'vend_num', 'vend_num');
    }

    public function viewByPoNum()
    {
        $url = url('admin/purchase-order/'.$this->po_num.'/show');
        return '<a class="btn btn-sm btn-link" href="'.$url.'"><i class="la la la-eye"></i> View</a>';
    }

    // public function excelExport($crud = false)
    // {
    //     $url = url('admin/purchase-order-export-excel');
    //     return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export All</a>';
    // }

    public function excelExportAdvance(){
        $url = url('admin/purchase-order-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export Advance</a>';
    }

    public function getPoDateAttribute($value)
    {
        return date("Y-m-d", strtotime($value));
    }
}
