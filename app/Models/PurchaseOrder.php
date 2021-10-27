<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id');
    }

    public function excelExport($crud = false)
    {
        return '<a class="btn btn-sm btn-primary-vp" target="_blank" href="#"><i class="la la-file-excel"></i> Export</a>';
    }
}
