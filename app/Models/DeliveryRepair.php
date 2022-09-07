<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class DeliveryRepair extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    
    protected $table = 'delivery_repair';

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


    public function createDsReturn(){
        $url = url('delivery-return').'/create-ds?num='.$this->ds_num_reject.'&line='.$this->ds_line_reject;
        return '<a class="btn btn-sm btn-link" href="'.$url.'"><i class="la la-plus"></i> Create</a>';
    }

    public function closeDsReturn(){
        $url = url('delivery-return').'?create='.$this->ds_num_reject.'-'.$this->ds_line_reject;
        return '<a class="btn btn-sm btn-link" href="'.$url.'"><i class="la la-times"></i> Close</a>';
    }
}
