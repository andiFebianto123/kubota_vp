<?php

namespace App\Models;

use App\Helpers\DsValidation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class StatusTempUploadDelivery extends Model
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
        'message'
    ];
}



