<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class MaterialOuthouse extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    
    protected $table = 'material_outhouse';
    protected $appends = ['qty_issued', 'remaining_qty'];

    public function getQtyIssuedAttribute()
    {
         $qtyIssued = IssuedMaterialOuthouse::join('delivery', function($join){
            $join->on('delivery.ds_num', '=', 'issued_material_outhouse.ds_num');
            $join->on('delivery.ds_line', '=', 'issued_material_outhouse.ds_line');
        })
        ->where('delivery.po_num', $this->po_num)
        ->where('delivery.po_line', $this->po_line)
        ->where('matl_item', $this->matl_item)
        ->sum('issue_qty');

        return $qtyIssued;
    }


    public function getSumLotQty()
    {
         $qtyIssued = MaterialOuthouse::where('po_num', $this->po_num)
        ->where('po_line', $this->po_line)
        ->where('matl_item', $this->matl_item)
        ->where('seq', $this->seq)
        ->sum('lot_qty');

        return $qtyIssued;
    }


    public function getRemainingQtyAttribute()
    {
        $qtyIssued = $this->getQtyIssuedAttribute();
        $qty = $this->getSumLotQty() - $qtyIssued;
        
        return $qty;
    }

}
