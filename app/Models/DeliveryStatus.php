<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class DeliveryStatus extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    
    protected $table = 'delivery_status';

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'po_num', 'po_num');
    }

    public function excelExportAdvance($crud = null){
        $url = url('admin/delivery-statuses-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export</a>';
    }
}
