<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Support\Facades\DB;

class MaterialOuthouseSummaryPerPo extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;

    protected $table = 'material_outhouse';
    protected $appends = ['qty_issued', 'remaining_qty', 'po_num_line'];

    public function getPoNumLineAttribute(){
        return $this->po_num . '-' .$this->po_line;
    }

    public function getLotQtyHeader(){
        $lotQty = MaterialOuthouse::where('po_num', $this->po_num)
                    ->where('po_line', $this->po_line)
                    ->sum('lot_qty');
        return $lotQty;
    }
    

    public function getQtyIssuedHeader(){
        $qtyIssued = IssuedMaterialOuthouse::leftJoin('delivery', 'delivery.ds_num', 'issued_material_outhouse.ds_num')
                        ->where('delivery.po_num', $this->po_num)
                        ->where('delivery.po_line', $this->po_line)
                        ->sum('issue_qty');
        return $qtyIssued;
    }

    public function getRemainingHeaderAttribute(){
        $lotQty = $this->getLotQtyHeader();
        $qtyIssued = $this->getQtyIssuedHeader();
        $qty = $lotQty - $qtyIssued;
        return $qty;
    }

    public function getLotQtyAttribute()
    {
        $lotQty = MaterialOuthouse::where('po_num', $this->po_num)
                    ->where('po_line', $this->po_line)
                    ->sum('lot_qty');
        return $lotQty;
    }

    public function getQtyIssuedAttribute()
    {
        $qtyIssued = IssuedMaterialOuthouse::whereHas('delivery', function($query) {
            $query->where('po_num', $this->po_num);
            // $query->where('po_line', $this->po_num);
         })->where('matl_item', $this->matl_item)->sum('issue_qty');

        return $qtyIssued;
    }

    public function getRemainingQtyAttribute()
    {
        $qtyIssued = $this->getQtyIssuedAttribute();
        $qty = $this->lot_qty - $qtyIssued;
        
        return $qty;
    }

    public function getRemainingQty2Attribute(){
        $status = $this->status;
        $po = $this->po_num;
        $po_line = $this->po_line;
        $query = DB::select("SELECT SUM(availabel_qty) as available_qty FROM (SELECT (SUM(material_outhouse.lot_qty) - IFNULL((
            SELECT SUM(issue_qty) FROM issued_material_outhouse 
            LEFT JOIN delivery ON delivery.ds_num = issued_material_outhouse.ds_num
            WHERE delivery.po_num = material_outhouse.po_num AND
            delivery.po_line = material_outhouse.po_line AND 
            issued_material_outhouse.matl_item = material_outhouse.matl_item
        ), 0)) as availabel_qty from `material_outhouse` inner join `po_line` as `pl` on `material_outhouse`.`po_num` = `pl`.`po_num` and `material_outhouse`.`po_line` = `pl`.`po_line` and `pl`.`status` = '{$status}' inner join `po` on `material_outhouse`.`po_num` = `po`.`po_num` where `material_outhouse`.`po_num` = '{$po}' and `material_outhouse`.`po_line` = {$po_line} group by `material_outhouse`.`matl_item`) as table_available_qty");
        return $query[0]->available_qty;
    }

    public function excelExportAdvance($crud = null){
        $url = url('mo-po-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export</a>';
    }

}
