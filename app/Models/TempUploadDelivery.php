<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempUploadDelivery extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    public function purchaseOrderLine()
    {
        return $this->belongsTo('App\Models\PurchaseOrderLine', 'po_line_id', 'id');
    }

    public function insertToDB($crud = false)
    {
        return '<button class="btn btn-sm btn-primary-vp" onclick="window.history.back()"><i class="la la-file-pdf"></i> Insert</button>';
    }

    public function cancelInsert($crud = false)
    {
        return '<button class="btn btn-sm btn-danger" onclick="window.history.back()"><i class="la la-file-pdf"></i> Cancel</button>';
    }
}