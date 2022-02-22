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

    // public function getLotQtyAttribute()
    // {
    //     $lot_qty = MaterialOuthouse::where('matl_item', $this->matl_item)->sum('lot_qty');

    //     return $lot_qty;
    // }

    public function getQtyIssuedAttribute()
    {
        $qty_issued = IssuedMaterialOuthouse::whereHas('delivery', function($query) {
            $query->where('po_num', $this->po_num);
            $query->where('po_line', $this->po_line);
         })->where('matl_item', $this->matl_item)->sum('issue_qty');

        return $qty_issued;
    }

    public function getRemainingQtyAttribute()
    {
        $qty_issued = $this->getQtyIssuedAttribute();
        $qty = $this->lot_qty - $qty_issued;
        
        return $qty;
    }
}
