<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class PurchaseOrder extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use RevisionableTrait;

    protected $table = 'po';

    protected $fillable = [
        'po_num',
        'vend_num',
        'po_date',
        'po_change',
        'email_flag',
        'last_po_change_email',
        'session_batch_proccess',
    ];

    protected $append = ['status_accepted','total_po_line', 'accept_po_line', 'reject_po_line'];

    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'vend_num', 'vend_num');
    }

    public function viewByPoNum()
    {
        $url = url('admin/purchase-order/'.$this->po_num.'/show');
        return '<a class="btn btn-sm btn-link" href="'.$url.'"><i class="la la la-eye"></i> View</a>';
    }

    // public function excelExport($crud = false)
    // {
    //     $url = url('admin/purchase-order-export-excel');
    //     return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export All</a>';
    // }

    public function excelExportAdvance(){
        $url = url('admin/purchase-order-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export Advance</a>';
    }

    public function linkTempDs(){
        $url = url('admin/purchase-order/temp-upload-delivery');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-upload"></i> Temp DS</a>';
    }

    public function getPoDateAttribute($value)
    {
        return date("Y-m-d", strtotime($value));
    }


    public function getTotalPoLineAttribute(){
        $totalPo = PurchaseOrderLine::where('po_num', $this->po_num)->groupBy('po_num', 'po_change')->count();

        return $totalPo;
    }


    public function getAcceptPoLineAttribute(){
        return PurchaseOrderLine::where('po_num', $this->po_num)
                        // ->where('status', 'O')
                        ->where('accept_flag', 1)
                        ->groupBy('po_num', 'po_change')
                        ->count();
    }


    public function getRejectPoLineAttribute(){
        return PurchaseOrderLine::where('po_num', $this->po_num)
                        // ->where('status', 'O')
                        ->where('accept_flag', 2)
                        ->groupBy('po_num', 'po_change')
                        ->count();
    }


    // public function getStatusAcceptedAttribute(){
    //     $strStatus = "";
    //     if ($this->accept_po_line == 0) {
    //         $strStatus = "NEW";
    //     }else if ($this->accept_po_line == $this->total_po_line) {
    //         $strStatus = "COMPLETED";
    //     }else if ($this->accept_po_line < $this->total_po_line) {
    //         $strStatus = "ACC PROG";
    //     }

    //     return $strStatus;
    // }
}
