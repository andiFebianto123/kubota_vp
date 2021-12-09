<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempUploadDelivery extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    protected $fillable = [
        'petugas_vendor',
        'no_surat_jalan_vendor',
        'order_qty',
    ];

    protected $appends = ['po_item', 'po_description'];
    
    // public function purchaseOrderLine()
    // {
    //     return $this->belongsTo('App\Models\PurchaseOrderLine', 'po_line_id', 'id');
    // }

    public function cancelInsert($crud = false)
    {
        return '<button class="btn btn-sm btn-danger" onclick="window.history.back()"><i class="la la-file-pdf"></i> Cancel</button>';
    }

    public function getDeliveryDateAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }

    public function purchaseOrderLine()
    {
        // return $this->belongsTo('App\Models\PurchaseOrderLine', ['po_num', 'po_line'],  ['po_num', 'po_line']);
        return $this->belongsTo('App\Models\PurchaseOrderLine', 'po_num', 'po_num')
                  ->where('po_line', $this->po_line);
    }


    public function getPoItemAttribute()
    {
        return $this->purchaseOrderLine->item;
    }

    public function getPoDescriptionAttribute()
    {
        return $this->purchaseOrderLine->description;
    }
}
