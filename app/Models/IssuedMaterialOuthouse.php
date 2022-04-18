<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class IssuedMaterialOuthouse extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;

    protected $table = 'issued_material_outhouse';
    protected $appends = ['sum_issued_qty'];

    public function delivery(){
        return $this->hasMany(Delivery::class, 'ds_num', 'ds_num');
    }
    

    public function getSumIssuedQtyAttribute()
    {
        $qtyIssued = IssuedMaterialOuthouse::join('delivery', function($join){
            $join->on('issued_material_outhouse.ds_num', '=', 'delivery.ds_num');
            $join->on('issued_material_outhouse.ds_line', '=', 'delivery.ds_line');
        })
        ->where('matl_item', $this->matl_item)
        ->sum('issue_qty');

        return $qtyIssued;
    }

    public function excelExportAdvance($crud = null){
        $url = url('admin/history-mo-item-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export</a>';
    }

    public function excelExportAdvance2($crud = null){
        $url = url('admin/history-mo-po-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export</a>';
    }

}
