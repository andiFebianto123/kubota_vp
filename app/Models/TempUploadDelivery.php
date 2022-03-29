<?php

namespace App\Models;

use App\Helpers\DsValidation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class TempUploadDelivery extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;

    protected $fillable = [
        'petugas_vendor',
        'no_surat_jalan_vendor',
        'order_qty',
    ];

    protected $appends = ['po_item', 'po_description', 'category_validation'];
 
    public function cancelInsert($crud = false)
    {
        return '<button class="btn btn-sm btn-danger" onclick="window.history.back()"><i class="la la-file-pdf"></i> Cancel</button>';
    }

    public function getDeliveryDateAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }

    public function purchaseOrderLine()
    {
        return $this->belongsTo('App\Models\PurchaseOrderLine', 'po_num', 'po_num')
                  ->where('po_line', $this->po_line);
    }


    public function getPoItemAttribute()
    {
        return $this->purchaseOrderLine->item;
    }


    public function getPoDescriptionAttribute()
    {
        return $this->purchaseOrderLine->description;
    }


    public function getValidationText(){
        $strValidation = "<label class='validation-row-temp p-0 m-0'>";

        foreach ($this->rowValidation() as $key => $v) {
            $strValidation .= "<li><span class='text-".$v['mode']."'>".$v['message']."</span></li>";
        }
        
        return $strValidation."</label>";
    }
    

    public function getCategoryValidationAttribute(){
        $dangerMode = '';
        foreach ($this->rowValidation() as $key => $v) {
            if ($v['mode'] == 'danger') {
                $dangerMode = $v['mode'];
            }
        }
        
        return $dangerMode;
    }


    private function rowValidation(){
        $arrFilters = [];
        $arrValidation = [];
        $arrFilters[] = ['po_line.item', '=', $this->purchaseOrderLine->item];
        $args1 = [  
            'filters' => $arrFilters, 
            'due_date' => $this->purchaseOrderLine->due_date, 
            'po_num' => $this->po_num, 
            'po_line' => $this->po_line
        ];
        $args2 = [
            'po_num' => $this->po_num, 
            'po_line' => $this->po_line, 
        ];

        $dsValidation = new DsValidation();
        $unfinishedPoLine = $dsValidation->unfinishedPoLineMass($args1);
        $currentMaxQty = $dsValidation->currentMaxQty($args2);
        if ($this->purchaseOrderLine->outhouse_flag == 1) {
            $currentMaxQty = $dsValidation->currentMaxQtyOuthouse($args2);       
        }
        if (sizeof($unfinishedPoLine['datas']) > 0 ) {
            $messageUpl = $unfinishedPoLine['message']." ";
            foreach($unfinishedPoLine['datas'] as $key => $upl){
                $tsq = ($upl->total_shipped_qty)?$upl->total_shipped_qty:"0";
                $tsq .= "/".$upl->order_qty;
                $messageUpl .= $upl->po_num."-".$upl->po_line. " (".date('Y-m-d',strtotime($upl->due_date)).") ".$tsq. "<br>";
            }
                
            $arrValidation[] = ['mode' => $unfinishedPoLine['mode'], 'message' => $messageUpl];
        }
        if($currentMaxQty['datas'] < $this->shipped_qty){
            $arrValidation[] = ['mode' => $currentMaxQty['mode'], 'message' => $currentMaxQty['message']];
        }
        if($this->shipped_qty <= 0){
            $arrValidation[] = ['mode' => 'danger', 'message' => 'QTY cannot be 0'];
        }
        if($this->purchaseOrderLine->status != 1){
            $arrValidation[] = ['mode' => 'danger', 'message' => 'This PO LINE has not been accepted'];
        }
        if (!isset($this->petugas_vendor)) {
            $arrValidation[] = ['mode' => 'danger', 'message' => 'Petugas Vendor is required'];
        }
        if (!isset($this->no_surat_jalan_vendor)) {
            $arrValidation[] = ['mode' => 'danger', 'message' => 'No Surat Jalan Vendor is required'];
        }
        if (!isset($this->delivery_date)) {
            $arrValidation[] = ['mode' => 'danger', 'message' => 'Delivery Date is required'];
        }
    
        return $arrValidation;
    }
}
