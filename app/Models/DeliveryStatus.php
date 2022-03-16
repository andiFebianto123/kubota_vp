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
}
