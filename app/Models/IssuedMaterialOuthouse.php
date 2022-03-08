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
        $qty_issued = IssuedMaterialOuthouse::join('delivery', function($join){
            $join->on('issued_material_outhouse.ds_num', '=', 'delivery.ds_num');
            $join->on('issued_material_outhouse.ds_line', '=', 'delivery.ds_line');
        })
        ->where('matl_item', $this->matl_item)
        ->sum('issue_qty');

        return $qty_issued;
    }
}
