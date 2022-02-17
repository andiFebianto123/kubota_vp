<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class MaterialOuthouseSummaryPerPo extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    protected $table = 'material_outhouse';
        // protected $fillable = [];
    protected $appends = ['qty_issued', 'remaining_qty'];

    public function getPoNumLineAttribute(){
        return $this->po_num . '-' .$this->po_line;
    }

    public function getLotQtyHeader(){
        $lot_qty = MaterialOuthouse::where('po_num', $this->po_num)
                    ->where('po_line', $this->po_line)
                    ->sum('lot_qty');
        return $lot_qty;
    }

    public function getQtyIssuedHeader(){
        $qty_issued = IssuedMaterialOuthouse::leftJoin('delivery', 'delivery.ds_num', 'issued_material_outhouse.ds_num')
                        ->where('delivery.po_num', $this->po_num)
                        ->where('delivery.po_line', $this->po_line)
                        ->sum('issue_qty');
        return $qty_issued;
    }

    public function getRemainingHeaderAttribute(){
        $lot_qty = $this->getLotQtyHeader();
        $qty_issued = $this->getQtyIssuedHeader();
        $qty = $lot_qty - $qty_issued;
        return $qty;
    }

    public function getLotQtyAttribute()
    {
        $lot_qty = MaterialOuthouse::where('po_num', $this->po_num)
                    ->where('matl_item', $this->matl_item)
                    ->sum('lot_qty');
        return $lot_qty;
    }

    public function getQtyIssuedAttribute()
    {
        $qty_issued = IssuedMaterialOuthouse::leftJoin('delivery', 'delivery.ds_num', 'issued_material_outhouse.ds_num')
                        ->where('delivery.po_num', $this->po_num)
                        ->where('issued_material_outhouse.matl_item', $this->matl_item)
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
