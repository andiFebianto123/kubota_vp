<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialOuthouseSummaryPerPo extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    protected $table = 'material_outhouse';
        // protected $fillable = [];
    protected $appends = ['qty_issued', 'remaining_qty'];

    public function getLotQtyAttribute()
    {
        $lot_qty = MaterialOuthouse::where('po_num', $this->po_num)->sum('lot_qty');

        return $lot_qty;
    }


    public function getQtyIssuedAttribute()
    {
        $qty_issued = IssuedMaterialOuthouse::leftjoin('delivery', 'delivery.ds_num', 'issued_material_outhouse.ds_num')
                    ->where('delivery.po_num', $this->po_num)->sum('issue_qty');

        return $qty_issued;
    }

    public function getRemainingQtyAttribute()
    {
        $qty_issued = $this->getQtyIssuedAttribute();
        $qty = $this->lot_qty - $qty_issued;
        
        return $qty;
    }
}
