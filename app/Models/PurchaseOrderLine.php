<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    
    protected $append = [
        'read_by_user'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'purchase_order_id', 'id');
    }

    public function getReadByUserAttribute()
    {
        $user = User::where('id', $this->read_by)->first();

        return ($user) ? $user->username :'-';
    }
}
