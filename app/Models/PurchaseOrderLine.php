<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    
    protected $append = [
        'read_by_user', 'change_unit_price', 'change_order_qty', 'change_total_price', 'change_due_date', 'reformat_flag_accept',
        'count_ds'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder', 'purchase_order_id', 'id');
    }

    function delivery(){
		return $this->hasMany('App\Models\Delivery','po_line_id');
	}

    public function getReadByUserAttribute()
    {
        $user = User::where('id', $this->read_by)->first();

        return ($user) ? $user->username :'-';
    }

    public function getChangeUnitPriceAttribute()
    {
        $value = number_format($this->unit_price,0,',','.');
        $html_row =  $this->vendor_currency." ".$value; 
        if($this->po_change > 0){
            $last_po_line = PurchaseOrderLine::where('po_line', $this->po_line)->get()[$this->po_change - 1];

            $change = number_format($last_po_line->unit_price,0,',','.')." -> ".$value;

            if(number_format($last_po_line->unit_price,0,',','.') != $value){
                $html_row = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$this->vendor_currency." ".$value."</b></button>";
            }
        }

        return $html_row;
    }

    public function getChangeOrderQtyAttribute()
    {
        $value = $this->order_qty;

        $html_row = $value;
        if($this->po_change > 0){
            $last_po_line = PurchaseOrderLine::where('po_line', $this->po_line)->get()[$this->po_change - 1];

            $change = $last_po_line->order_qty. " -> ". $value;
            if ($last_po_line->order_qty != $value) {
                $html_row = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
            }
        }

        return $html_row;
    }

    public function getChangeTotalPriceAttribute()
    {
        $value = number_format($this->unit_price*$this->order_qty,0,',','.');

        $html_row = $this->vendor_currency." " .$value; 
        if($this->po_change > 0){
            $last_po_line = PurchaseOrderLine::where('po_line', $this->po_line)->get()[$this->po_change - 1];
            $from = $last_po_line->unit_price*$last_po_line->order_qty;

            $change = number_format($from,0,',','.')." -> ".$value;
            if(number_format($from,0,',','.') != $value){
                $html_row = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$this->vendor_currency." ".$value."</b></button>";
            }
        }

        return $html_row;
    }

    public function getChangeDueDateAttribute()
    {
        $value = date('Y-m-d', strtotime($this->due_date));

        $html_row = $value; 
        if($this->po_change > 0){
            $last_po_line = PurchaseOrderLine::where('po_line', $this->po_line)->get()[$this->po_change - 1];

            $change = date('Y-m-d', strtotime($last_po_line->due_date))." -> ".$value;
            if(date('Y-m-d', strtotime($last_po_line->due_date)) != $value){
                $html_row = "<button type='button' class='btn btn-link p-0' data-toggle='tooltip' data-placement='top' title='".$change."'><b>".$value."</b></button>";
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
        return Delivery::where('po_line_id', $this->id)->count();
    }


}
