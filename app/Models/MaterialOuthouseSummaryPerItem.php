<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class MaterialOuthouseSummaryPerItem extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    protected $table = 'material_outhouse';
    protected $appends = ['qty_issued', 'remaining_qty'];

    public function getLotQtyAttribute()
    {
        $lot_qty = MaterialOuthouse::where('matl_item', $this->matl_item)->sum('lot_qty');

        return $lot_qty;
    }

    public function getQtyIssuedAttribute()
    {
        //  $qty_issued = IssuedMaterialOuthouse::whereHas('delivery')
        //  ->where('matl_item', $this->matl_item)
        //  ->sum('issue_qty');
        $qty_issued = IssuedMaterialOuthouse::join('delivery', function($join){
            $join->on('issued_material_outhouse.ds_num', '=', 'delivery.ds_num');
            $join->on('issued_material_outhouse.ds_line', '=', 'delivery.ds_line');
        })
        ->join('po_line', function($join){
            $join->on('po_line.po_num', '=', 'delivery.po_num');
            $join->on('po_line.po_line', '=', 'delivery.po_line');
        })
        ->where('po_line.status', 'O')
        ->where('matl_item', $this->matl_item)
        ->sum('issue_qty');
        return $qty_issued;
    }

    public function getRemainingQtyAttribute()
    {
        $qty_issued = $this->getQtyIssuedAttribute();
        $qty = $this->lot_qty - $qty_issued;
        
        return $qty;
    }
}
