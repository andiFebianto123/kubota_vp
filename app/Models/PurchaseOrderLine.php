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

    private function getLatestChange(){
        // $count_po_line = PurchaseOrderLine::where('po_num', $this->po_num)
        // // ->where('po_change', $this->po_change - 1)
        // ->where('po_line', $this->po_line)->count();

        $last_po_line = PurchaseOrderLine::where('po_num', $this->po_num)
        ->where('po_change', '<', $this->po_change)
        ->where('po_line', $this->po_line)
        ->orderBy('po_change', 'desc')
        ->first();

        return $last_po_line;
    }

    private function changeUnitPrice($layout = 'html'){
        $value = number_format($this->unit_price,0,',','.');
        $html_row['html'] = $value; 
        $html_row['bold'] = $value; 
        if($this->po_change > 0){
            $last_po_line = $this->getLatestChange();

            if (isset($last_po_line)) {
                $change = number_format($last_po_line->unit_price,0,',','.')." -> ".$value;

                if(number_format($last_po_line->unit_price,0,',','.') != $value){
                    $html_row['html'] = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                    $html_row['bold'] = "<b><i><u>".$value."</u></i></b>";
                }
            }

        }

        return $html_row[$layout];
    }

    private function changeOrderQty($layout = 'html')
    {
        $value = $this->order_qty;

        $html_row['html'] = $value; 
        $html_row['bold'] = $value; 
        if($this->po_change > 0){
            
            $last_po_line = $this->getLatestChange();
            
            if (isset($last_po_line)) {
                $change = $last_po_line->order_qty. " -> ". $value;
                if ($last_po_line->order_qty != $value) {
                    $html_row['html'] = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                    $html_row['bold'] = "<b><i><u>".$value."</u></i></b>";
                }
            }
        }

        return $html_row[$layout];
    }

    private function changeDescription($layout = 'html')
    {
        $value = $this->description;

        $html_row['html'] = $value; 
        $html_row['bold'] = $value; 
        if($this->po_change > 0){
            
            $last_po_line = $this->getLatestChange();
            
            if (isset($last_po_line)) {
                $change = $last_po_line->description. " -> ". $value;
                if ($last_po_line->description != $value) {
                    $html_row['html'] = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                    $html_row['bold'] = "<b><i><u>".$value."</u></i></b>";
                }
            }
        }

        return $html_row[$layout];
    }


    private function changeDueDate($layout = 'html')
    {
        $value = date('Y-m-d', strtotime($this->due_date));

        $html_row['html'] = $value; 
        $html_row['bold'] = $value; 
        if($this->po_change > 0){
            $last_po_line = $this->getLatestChange();

            if (isset($last_po_line)) {
                $change = date('Y-m-d', strtotime($last_po_line->due_date))." -> ".$value;
                if(date('Y-m-d', strtotime($last_po_line->due_date)) != $value){
                    $html_row['html'] = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                    $html_row['bold'] = "<b><i><u>".$value."</u></i></b>";
                }
            }
        }

        return $html_row[$layout];
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

        $html_row = $value; 
        if($this->po_change > 0){
            $last_po_line = $this->getLatestChange();

            if (isset($last_po_line)) {
                $from = $last_po_line->unit_price*$last_po_line->order_qty;

                $change = number_format($from,0,',','.')." -> ".$value;
                if(number_format($from,0,',','.') != $value){
                    $html_row = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
                }
            }
            
        }

        return $html_row;
    }

    
    public function getReformatFlagAcceptAttribute()
    {
        $value = "<button class='btn p-0 ".['','text-success', 'text-danger'][$this->accept_flag]."'>".['','Accept', 'Reject'][$this->accept_flag]."</button>";

        $html_row = $value; 
        if($this->accept_flag == 2){
            $html_row = "<button class='btn p-0 ".['','text-success', 'text-danger'][$this->accept_flag]."' data-toggle='tooltip' data-placement='top' title='Reason :: ".$this->reason."'>".['','Accept', 'Reject'][$this->accept_flag]."</button>";
        }

        return $html_row;
    }

    public function getCountDsAttribute()
    {
        return Delivery::where('po_num', $this->po_num)->where('po_line', $this->po_line)->count();
    }


    public function getTotalShippedQtyAttribute()
    {
        return Delivery::where('po_num', $this->po_num)->where('po_line', $this->po_line)->sum('shipped_qty');
    }


}
