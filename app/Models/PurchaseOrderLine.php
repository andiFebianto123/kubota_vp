<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class PurchaseOrderLine extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;

    protected $table = 'po_line';

    protected $append = [
        'read_by_user', 'change_unit_price', 'change_order_qty', 'change_total_price', 'change_due_date', 'reformat_flag_accept',
        'count_ds', 'total_shipped_qty', 'num_line', 'change_order_qty_bold', 'change_unit_price_bold',  'change_description_bold',
    ];


    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'po_num', 'po_num');
    }


    function delivery(){
		return $this->hasMany('App\Models\Delivery','po_num');
	}


    public function getReadByUserAttribute()
    {
        $user = User::where('id', $this->read_by)->first();

        return ($user) ? $user->username :'-';
    }


    public function getNumLineAttribute()
    {
        return $this->po_num."-".$this->po_line;
    }


    private function getLatestChange()
    {
        $lastPoLine = PurchaseOrderLine::where('po_num', $this->po_num)
        ->where('po_change', '<', $this->po_change)
        ->where('po_line', $this->po_line)
        ->orderBy('po_change', 'desc')
        ->first();

        return $lastPoLine;
    }


    private function changeUnitPrice($layout = 'html'){
        $value = number_format($this->unit_price,0,',','.');
        $htmlRow['html'] = $value; 
        $htmlRow['bold'] = $value; 
        if($this->po_change > 0){
            $lastPoLine = $this->getLatestChange();

            if (isset($lastPoLine)) {
                $change = number_format($lastPoLine->unit_price,0,',','.')." -> ".$value;

                if(number_format($lastPoLine->unit_price,0,',','.') != $value){
                    $htmlRow['html'] = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                    $htmlRow['bold'] = "<b><i><u>".$value."</u></i></b>";
                }
            }
        }

        return $htmlRow[$layout];
    }


    private function changeOrderQty($layout = 'html')
    {
        $value = $this->order_qty;

        $htmlRow['html'] = $value; 
        $htmlRow['bold'] = $value; 
        if($this->po_change > 0){
            
            $lastPoLine = $this->getLatestChange();
            
            if (isset($lastPoLine)) {
                $change = $lastPoLine->order_qty. " -> ". $value;
                if ($lastPoLine->order_qty != $value) {
                    $htmlRow['html'] = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                    $htmlRow['bold'] = "<b><i><u>".$value."</u></i></b>";
                }
            }
        }

        return $htmlRow[$layout];
    }


    private function changeDescription($layout = 'html')
    {
        $value = htmlspecialchars($this->description);
        $htmlRow['html'] = $value; 
        $htmlRow['bold'] = $value; 
        if($this->po_change > 0){
            
            $lastPoLine = $this->getLatestChange();
            
            if (isset($lastPoLine)) {
                $change = $lastPoLine->description. " -> ". $value;
                if ($lastPoLine->description != $value) {
                    $htmlRow['html'] = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                    $htmlRow['bold'] = "<b><i><u>".$value."</u></i></b>";
                }
            }
        }
        return $htmlRow[$layout];
    }


    private function changeDueDate($layout = 'html')
    {
        $value = date('Y-m-d', strtotime($this->due_date));
        $htmlRow['html'] = $value; 
        $htmlRow['bold'] = $value; 
        if($this->po_change > 0){
            $lastPoLine = $this->getLatestChange();

            if (isset($lastPoLine)) {
                $change = date('Y-m-d', strtotime($lastPoLine->due_date))." -> ".$value;
                if(date('Y-m-d', strtotime($lastPoLine->due_date)) != $value){
                    $htmlRow['html'] = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                    $htmlRow['bold'] = "<b><i><u>".$value."</u></i></b>";
                }
            }
        }
        return $htmlRow[$layout];
    }


    public function getChangeUnitPriceAttribute()
    {
        return $this->changeUnitPrice();
    }


    public function getChangeOrderQtyAttribute()
    {
        return $this->changeOrderQty();
    }


    public function getChangeDueDateAttribute()
    {
        return $this->changeDueDate();
    }


    public function getChangeUnitPriceBoldAttribute()
    {
        return $this->changeUnitPrice('bold');
    }


    public function getChangeOrderQtyBoldAttribute()
    {
        return $this->changeOrderQty('bold');
    }


    public function getChangeDueDateBoldAttribute()
    {
        return $this->changeDueDate('bold');
    }


    public function getChangeDescriptionBoldAttribute()
    {
        return $this->changeDescription('bold');
    }

    public function getChangeTotalPriceAttribute()
    {
        $value = number_format($this->unit_price*$this->order_qty,0,',','.');

        $htmlRow = $value; 
        if($this->po_change > 0){
            $lastPoLine = $this->getLatestChange();

            if (isset($lastPoLine)) {
                $from = $lastPoLine->unit_price*$lastPoLine->order_qty;

                $change = number_format($from,0,',','.')." -> ".$value;
                if(number_format($from,0,',','.') != $value){
                    $htmlRow = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                }
            }
            
        }
        return $htmlRow;
    }

    
    public function getReformatFlagAcceptAttribute()
    {
        $value = "<button class='btn p-0 ".['','text-success', 'text-danger'][$this->accept_flag]."'>".['','Accept', 'Reject'][$this->accept_flag]."</button>";
        $htmlRow = $value; 
        if($this->accept_flag == 2){
            $htmlRow = "<button class='btn p-0 ".['','text-success', 'text-danger'][$this->accept_flag]."' data-toggle='tooltip' data-placement='top' title='Reason :: ".$this->reason."'>".['','Accept', 'Reject'][$this->accept_flag]."</button>";
        }

        return $htmlRow;
    }


    public function getCountDsAttribute()
    {
        return Delivery::where('po_num', $this->po_num)->where('po_line', $this->po_line)->count();
    }


    public function getTotalShippedQtyAttribute()
    {
        return Delivery::where('po_num', $this->po_num)
            ->where('po_line', $this->po_line)
            ->whereIn("ds_type", ['00', '01', '02'])
            ->sum('shipped_qty');
    }


}
