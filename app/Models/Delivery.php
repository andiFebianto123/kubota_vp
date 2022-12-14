<?php

namespace App\Models;

use App\Http\Traits\CustomRevisionableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Delivery extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    use CustomRevisionableTrait;

    protected $revisionEnabled = true;
    protected $revisionCreationsEnabled = true;
    protected $revisionForceDeleteEnabled = true;

    protected $table = 'delivery';
    protected $append = ['po_po_line'];
    protected $fillable = [
        'id',
        'shipped_qty',
        'petugas_vendor',
        'no_surat_jalan_vendor',
        'order_qty',
    ];

    public function pdfExport($crud = false)
    {
        return '<a class="btn btn-sm btn-danger" href="#"><i class="la la-file-pdf"></i> PDF</a>';
    }


    public function purchaseOrderLine()
    {
        return $this->belongsTo('App\Models\PurchaseOrderLine', ['po_num', 'po_line'],  ['po_num', 'po_line']);
    }


    public function getShippedDateAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }


    public function pdfCheck($crud = false)
    {
        return "<div class='group-price-check'><input type='checkbox'> Dengan Harga</div>";
    }

    public function excelExportAdvance(){
        $url = url('delivery-sheet-export');
        return '<a class="btn btn-sm btn-primary-vp" href="'.$url.'"><i class="la la-file-excel"></i> Export </a>';
    }


    public function getPoPoLineAttribute()
    {
        return $this->po_num. "-" .$this->po_line;
    }
}
