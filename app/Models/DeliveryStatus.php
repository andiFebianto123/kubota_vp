<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class DeliveryStatus extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use CustomRevisionableTrait;

    protected $revisionEnabled = true;
    protected $revisionCreationsEnabled = true;
    protected $revisionForceDeleteEnabled = true;
    
    protected $table = 'delivery_status';

    protected $append = ['po_po_line'];

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'po_num', 'po_num');
    }

    
    public function excelExportAdvance($crud = null){
        $url = url('delivery-statuses-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export</a>';
    }


    public function getPoPoLineAttribute()
    {
        return $this->po_num. "-" .$this->po_line;
    }
}
