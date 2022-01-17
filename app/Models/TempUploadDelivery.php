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
        $arr_filters[] = ['po_line.item', '=', $this->purchaseOrderLine->item];
        $args1 = ['filters' => $arr_filters, 'due_date' => $this->purchaseOrderLine->due_date ];
        $args2 = ['po_num' => $this->po_num, 'po_line' => $this->po_line, 'order_qty' => $this->purchaseOrderLine->order_qty ];

        $ds_validation = new DsValidation();
        $unfinished_po_line = $ds_validation->unfinishedPoLine( $args1);
        $current_max_qty = $ds_validation->currentMaxQty($args2);
        
        if (sizeof($unfinished_po_line['datas']) > 0 ) {
            $arr_validation[] = ['mode' => $unfinished_po_line['mode'], 'message' => $unfinished_po_line['message']];
        }
        $arr_validation[] = ['mode' => $current_max_qty['mode'], 'message' => $current_max_qty['message']];
    
        return $arr_validation;
    }
}
