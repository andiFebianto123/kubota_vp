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
    
    // public function purchaseOrderLine()
    // {
    //     return $this->belongsTo('App\Models\PurchaseOrderLine', 'po_line_id', 'id');
    // }

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
        // return $this->belongsTo('App\Models\PurchaseOrderLine', ['po_num', 'po_line'],  ['po_num', 'po_line']);
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
        $str_validation = "<label class='validation-row-temp p-0 m-0'>";

        foreach ($this->rowValidation() as $key => $v) {
            $str_validation .= "<li><span class='text-". $v['mode']."'>".$v['message']."</span></li>";
        }
        
        return $str_validation."</label>";
    }

    public function getCategoryValidationAttribute(){
        $mode_danger = '';

        foreach ($this->rowValidation() as $key => $v) {
            if ($v['mode'] == 'danger') {
                $mode_danger = $v['mode'];
            }
        }
        
        return $mode_danger;
    }

    private function rowValidation(){
        $arr_filters = [];
        $arr_validation = [];
        $arr_filters[] = ['po_line.item', '=', $this->purchaseOrderLine->item];
        $args1 = [  'filters' => $arr_filters, 
                    'due_date' => $this->purchaseOrderLine->due_date, 
                    'po_num' => $this->po_num, 
                    'po_line' => $this->po_line
                ];
        $args2 = ['po_num' => $this->po_num, 'po_line' => $this->po_line, 'order_qty' => $this->shipped_qty ];

        $ds_validation = new DsValidation();
        $unfinished_po_line = $ds_validation->unfinishedPoLineMass($args1);
        $current_max_qty = ($this->purchaseOrderLine->outhouse_flag == 1)? $ds_validation->currentMaxQtyOuthouse($args2) : $ds_validation->currentMaxQty($args2);
        
        if (sizeof($unfinished_po_line['datas']) > 0 ) {
            $message_upl = $unfinished_po_line['message']." ";
            foreach($unfinished_po_line['datas'] as $key => $upl){
                $tsq = ($upl->total_shipped_qty)?$upl->total_shipped_qty:"0";
                $tsq .= "/".$upl->order_qty;
                $message_upl .= $upl->po_num."-".$upl->po_line. " (".date('Y-m-d',strtotime($upl->due_date)).") ".$tsq. "<br>";
            }
                
            $arr_validation[] = ['mode' => $unfinished_po_line['mode'], 'message' => $message_upl];
        }
        if($current_max_qty['datas'] < $this->shipped_qty){
            $arr_validation[] = ['mode' => $current_max_qty['mode'], 'message' => $current_max_qty['message']];
        }
        if($this->shipped_qty <= 0){
            $arr_validation[] = ['mode' => 'danger', 'message' => 'QTY cannot be 0'];
        }
        if (!isset($this->petugas_vendor)) {
            $arr_validation[] = ['mode' => 'danger', 'message' => 'Petugas Vendor is required'];
        }
        if (!isset($this->no_surat_jalan_vendor)) {
            $arr_validation[] = ['mode' => 'danger', 'message' => 'No Surat Jalan Vendor is required'];
        }
        if (!isset($this->delivery_date)) {
            $arr_validation[] = ['mode' => 'danger', 'message' => 'Delivery Date is required'];
        }
    
        return $arr_validation;
    }
}
