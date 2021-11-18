<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    protected $fillable = [
        'shipped_qty',
        'petugas_vendor',
        'no_surat_jalan_vendor',
        'order_qty',
    ];

    public function pdfExport($crud = false)
    {
        return '<a class="btn btn-sm btn-danger" href="#"><i class="la la-file-pdf"></i> PDF</a>';
    }

    public function pdfCheck($crud = false)
    {
        return "<div class='group-price-check'><input type='checkbox'> Dengan Harga</div>";
    }
}
