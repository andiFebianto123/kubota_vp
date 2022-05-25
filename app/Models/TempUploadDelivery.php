<?php

namespace App\Models;

use App\Helpers\DsValidation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class TempUploadDelivery extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;
    
    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $fillable = [
        'po_num',	
        'po_line',	
        'user_id',	
        'shipped_qty',	
        'data_attr',	
        'delivery_date',	
        'petugas_vendor',	
        'no_surat_jalan_vendor',	
    ];

    protected $appends = ['po_item', 'po_description', 'category_validation', 'available_qty', 'validation_message'];
 
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
                  ->where('po_line', $this->po_line)
                  ->orderBy('po_change', 'desc');
    }


    public function getPoItemAttribute()
    {
        return $this->purchaseOrderLine->item;
    }


    public function getPoDescriptionAttribute()
    {
        return $this->purchaseOrderLine->description;
    }

    public function getAvailableQtyAttribute()
    {
        return $this->rowCurrentMaxQty()['datas'];
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


    public function getValidationMessageAttribute(){
        $dangerMessage = [];
        foreach ($this->rowValidation() as $key => $v) {
            $dangerMessage[] = $v['message'];
        }
        
        return $dangerMessage;
    }


    private function rowCurrentMaxQty(){
        $dsValidation = new DsValidation();

       
        $args2 = [
            'po_num' => $this->po_num, 
            'po_line' => $this->po_line, 
        ];

        $currentMaxQty = $dsValidation->currentMaxQty($args2);
        if ($this->purchaseOrderLine->outhouse_flag == 1) {
            $currentMaxQty = $dsValidation->currentMaxQtyOuthouse($args2);       
        }

        return $currentMaxQty;
    }


    private function rowValidation(){
        $dsValidation = new DsValidation();
        $arrFilters = [];
        $arrValidation = [];
        $arrFilters[] = ['po_line.item', '=', $this->purchaseOrderLine->item];
        $currentMaxQty = $this->rowCurrentMaxQty();

        $args1 = [  
            'filters' => $arrFilters, 
            'due_date' => $this->purchaseOrderLine->due_date, 
            'po_num' => $this->po_num, 
            'po_line' => $this->po_line
        ];
        $unfinishedPoLine = $dsValidation->unfinishedPoLineMass($args1);

        if (sizeof($unfinishedPoLine['datas']) > 0 ) {
            $messageUpl = $unfinishedPoLine['message']." ";
            $show = true;
            foreach($unfinishedPoLine['datas'] as $key => $upl){
                $tud = TempUploadDelivery::where('po_num', $upl->po_num)
                                ->where('po_line', $upl->po_line)
                                ->first();
                $totalPoNumOld = 0;
                if (isset($tud)) {
                    $totalPoNumOld = $tud->shipped_qty;
                }

                $totalShipped = $upl->total_shipped_qty+$totalPoNumOld;
                $tsq = $totalShipped;
                $tsq .= "/".$upl->order_qty;
                $messageUpl .= $upl->po_num."-".$upl->po_line. " (".date('Y-m-d',strtotime($upl->due_date)).") ".$tsq. "<br>";

                if ($totalShipped >= $upl->order_qty) {
                    $show = false;
                }
            }
            if ($show) {
                $arrValidation[] = ['mode' => $unfinishedPoLine['mode'], 'message' => $messageUpl];
            }
        }
        if($currentMaxQty['datas'] < $this->shipped_qty){
            $msg = $currentMaxQty['message'];
            if ($currentMaxQty['datas'] == 0) {
                $msg = 'Available Qty sudah terpenuhi';
            }
            $arrValidation[] = ['mode' => $currentMaxQty['mode'], 'message' => $msg];
        }
        if($this->shipped_qty <= 0){
            $arrValidation[] = ['mode' => 'danger', 'message' => 'QTY cannot be 0'];
        }
        if($this->purchaseOrderLine->accept_flag != 1){
            $arrValidation[] = ['mode' => 'danger', 'message' => 'This PO LINE has not been accepted'];
        }
        if($this->purchaseOrderLine->status != 'O'){
            $arrValidation[] = ['mode' => 'danger', 'message' => 'This PO Line status is '.$this->purchaseOrderLine->status];
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
        if (date('Y-m-d', strtotime($this->delivery_date)) < date('Y-m-d', strtotime("2000-12-12"))) {
            $arrValidation[] = ['mode' => 'danger', 'message' => 'Delivery Date format is not valid (yyyy-mm-dd)'];
        }

        $minDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $maxDate = Carbon::now()->addDay(7)->format('Y-m-d');

        if (date('Y-m-d', strtotime($this->delivery_date)) >= $maxDate) {
            $arrValidation[] = ['mode' => 'danger', 'message' => 'The delivery date must be a date before '.$maxDate];
        }
        if (date('Y-m-d', strtotime($this->delivery_date)) <= $minDate) {
            $arrValidation[] = ['mode' => 'danger', 'message' => 'The delivery date must be a date after '.$minDate];
        }
        return $arrValidation;
    }
}
